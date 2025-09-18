<?php

namespace App\Manager;

use App\Entity\Availability;
use App\Entity\Booking;
use App\Entity\Center;
use App\Entity\Patient;
use App\Entity\Slots;
use App\Entity\Status;
use App\Entity\StatusBooking;
use App\Repository\AdministratorRepository;
use App\Repository\AvailabilityRepository;
use App\Repository\BookingRepository;
use App\Repository\SlotsRepository;
use App\Repository\StatusRepository;
use App\Services\Identifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class BookingManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private Identifier $identifier,
        private AvailabilityRepository $availabilityRepository,
        private BookingRepository $bookingRepository,
        private StatusRepository $statusRepository,
        private SlotsRepository $slotsRepository,
        private AdministratorRepository $administratorRepository,
        private AvailabilityManager $availabilityManager
    ) {}

    public function saveAllBooking(FormInterface $form, Center $center, Patient $patient, array $listAvailability): bool
    {
        try {
            foreach ($listAvailability as $keyDate => $dataDay) {
                $dateOfAvailability = new \DateTime($keyDate);
                foreach ($dataDay as $timeSlot) {
                    $slot = $this->slotsRepository->findOneBy(['name' => $timeSlot]);
                    $availability = $this->availabilityRepository->findOneBy(['date' => $dateOfAvailability, 'slot' => $slot]);;
                    if ($this->patientHasNeverBookedOnThisDate($patient, $dateOfAvailability, $availability->getSlot())) {
                        $resultBooking = $this->createBooking($form, $center, $patient, $availability, $dateOfAvailability, $availability->getSlot());
                        if (!$resultBooking) return false;
                    }
                }
            }

            $this->entityManager->flush();
            $this->logger->info('Réservation enregistré pour le patient : ' . $patient->getId());
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Réservation non enregistré pour le patient id ' . $patient->getId() . ' : ' . $e->getMessage());
            return false;
        }
    }

    public function createBooking(FormInterface $form, Center $center, Patient $patient, Availability $availability, \DateTime $dateReserve, Slots $timeSlot): bool
    {
        try {
            $booking = new Booking();
            $status = $this->statusRepository->findOneBy(['status_wait' => true]);
            $resultBooking = $this->addNewStatusBooking($status, $booking);
            if ($resultBooking['success'] == false) return false;

            $booking->setCenter($center);
            $booking->setPatient($patient);
            $booking->setComment($form->get('comment')->getData());
            $booking->setReason($form->get('reason')->getData());
            $booking->setSlot($timeSlot);
            $booking->setDateReserve($dateReserve);

            $resultAvailability = $this->availabilityManager->updateAvailabilityForBooking($availability);
            if (!$resultAvailability) return false;
            $booking->setAvailability($availability);

            $this->entityManager->persist($booking);
            $this->entityManager->flush();
            $this->logger->info('La réservation numéro : ' . $booking->getId() . ' a bien été engistré');
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Problémes lors de l\'enregistrement de la réservation : ' . $e->getMessage());
            return false;
        }
    }

    public function updateBooking(FormInterface $form): bool
    {
        try {
            $booking = $form->getData();
            // dd($booking);
            $this->entityManager->persist($booking);
            $this->entityManager->flush();
            $this->logger->info('La modification de la réservation id ' . $booking->getId() . ' a bien été engistré');
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Problémes lors de l\'enregistrement de la modification de la réservation id  : ' . $e->getMessage());
            return false;
        }
    }

    public function addNewStatusBooking(Status $status, Booking $booking): array
    {
        try {
            if ($this->checkStatusExist($booking, $status) === false) {
                $resultStatus = $this->disabledStatus($booking);
                if (!$resultStatus){
                    $this->logger->error('Problème lors de la désactivation des anciens statuts idBooking : '. $booking->getId());
                    return ['success' => false, 'message' => 'Problème lors de la désactivation des anciens statuts', 'data' => null];
                }
                $statusBooking = new StatusBooking();
                $statusBooking->setBooking($booking);
                $statusBooking->setStatus($status);
                $statusBooking->setStatusActive(true);
                $status->addStatusBooking($statusBooking);
                $booking->addStatusBooking($statusBooking);

                if ($status->isStatusCanceled() || $status->isStatusDenied()) {
                    $availability = $booking->getAvailability();
                    $availability->setReservedPlace($availability->getReservedPlace() - 1);
                    $availability->setAvailablePlace($availability->getAvailablePlace() + 1);
                    $this->entityManager->persist($availability);
                }

                $this->entityManager->persist($statusBooking);
                $this->logger->info('Le nouveau statut a bien été ajouté à la réservation id : ' . $booking->getId());
                return ['success' => true, 'message' => 'Le nouveau statut a bien été ajouté à la réservation', 'data' => $statusBooking];
            } else {
                $this->logger->warning('La tentative d\'ajout du nouveau statut à échoué car il existe déjà');
                return ['success' => false, 'message' => 'Le status existe déjà pour cette réservation', 'data' => null];
            }
        } catch (\Exception $e) {
            $this->logger->error('Problémes lors de l\'enregistrement de l\'ajout du nouveau statut : ' . $e->getMessage());
            return ['success' => false, 'message' => 'Problème lors de l\'enregistrement du nouveau statut', 'data' => $e->getMessage()];
        }
    }

    public function changeStatusBooking(Status $status, UserInterface $user, Booking $booking): array
    {
        try {
            if ($this->isUserAuthorizedToChangeStatus($user, $booking, $status)) {
                $result = $this->addNewStatusBooking($status, $booking);
                if ($result['success'] == false) {
                    $this->logger->error($result['message'] . ' / idBooking : ' . $booking->getId());
                    return ['message' => $result['message'], 'data' => null, 'code' => 400];
                }else {
                    $this->entityManager->flush();
                    return ['message' => 'Le status de la réservation a bien été modifié', 'data' => $result, 'code' => 200];
                }
            } else {
                $this->logger->error('Status de reservation echec  : l\'utilisateur n\'a pas les droits pour changer le status de la réservation');
                return ['message' => 'Vous n\'avez pas les droits pour changer le status de la réservation', 'data' => null, 'code' => 403];
            }
        } catch (\Exception $e) {
            $this->logger->error('Status de reservation echec  : ' . $e->getMessage());
            return ['message' => 'Une erreur est survenue lors du changement de status de la réservation', 'data' => $e->getMessage(), 'code' => 500];
        }

    }

    public function replaceStatusBooking(StatusBooking $oldStatus, Status $newStatus, Booking $booking): array
    {
        try {
            $oldStatus->setStatus($newStatus);
            $this->entityManager->persist($oldStatus);
            $this->entityManager->flush();
            $this->logger->info('Le statut de la réservation id : ' . $booking->getId() . ' a bien été remplacé');

            return ['success' => true, 'message' => 'Le statut a bien été remplacé', 'data' => $oldStatus->getBooking(), 'code' => 200];

        } catch (\Exception $e) {
            $this->logger->error('Problème lors du remplacement du statut : ' . $e->getMessage());
            return ['success' => false, 'message' => 'Problème lors du remplacement du statut', 'data' => $e->getMessage(), 'code' => 500];
        }
    }


    public function changeStatusBookingBatch(UserInterface $user, Status $status, $bookingsToChange): array
    {
        try {
            if ($this->identifier->isadminOsmose($user)) {
                foreach ($bookingsToChange as $booking) {
                    $result = $this->changeStatusBooking($status, $user, $booking);
                    if ($result['code'] != 200) {
                        $this->logger->error('Echec du changement de status pour la réservation id : ' . $booking->getId() . ' / Message : ' . $result['message']);
                        return ['message' => 'Echec du changement de status pour la réservation id : ' . $booking->getId() . ' / Message : ' . $result['message'], 'data' => null, 'code' => 500];
                    }
                }
                return ['message' => 'Le status de toutes les réservations a bien été modifié', 'data' => null, 'code' => 200];
            } else {
                foreach ($bookingsToChange as $booking) {
                    if ($this->checkAdminAuthorisationChangeStatus($user, $booking, $status)) {
                        $result = $this->changeStatusBooking($status, $user, $booking);
                        if ($result['code'] != 200) {
                            $this->logger->error('Echec du changement de status pour la réservation id : ' . $booking->getId() . ' / Message : ' . $result['message']);
                            return ['message' => 'Echec du changement de status pour la réservation id : ' . $booking->getId() . ' / Message : ' . $result['message'], 'data' => null, 'code' => 500];
                        }
                    } else {
                        $this->logger->error('Status de reservation echec  : l\'utilisateur n\'a pas les droits pour changer le status de la réservation');
                        return ['message' => 'Vous n\'avez pas les droits pour changer le status de cet réservation', 'data' => null, 'code' => 403];
                    }
                }
                $this->entityManager->flush();
                return ['message' => 'Le status de toutes les réservations a bien été modifié', 'data' => null, 'code' => 200];
            }
        } catch (\Exception $e) {
            $this->logger->error('Status de reservation echec  : ' . $e->getMessage());
            return ['message' => 'Une erreur est survenue lors du changement de status des réservations', 'data' => $e->getMessage(), 'code' => 500];
        }
    }

    public function checkStatusExist(Booking $booking, Status $status): bool
    {
        if ($booking->getStatusBookings() != null) {
            foreach ($booking->getStatusBookings() as $statusInBooking) {
                if ($statusInBooking->getStatus()->getId() == $status->getId()) {
                    return true;
                }
            }
        }
        return false;
    }

    public function disabledStatus(Booking $booking): bool
    {
        try {
            if ($booking->getStatusBookings() != null) {
                foreach ($booking->getStatusBookings() as $status) {
                    $status->setStatusActive(false);
                    $this->entityManager->persist($status);
                }
            }
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Problémes lors de l\'enregistrement de la désactivation du statut : ' . $e->getMessage());
            return false;
        }
    }

    private function isUserAuthorizedToChangeStatus(UserInterface $user, Booking $booking, Status $status): bool
    {
        if (!$user->isAdmin() && $user->getId() == $booking->getPatient()->getUser()->getId()) {
            return true;
        }

        if ($user->isAdmin() && $this->checkAdminAuthorisationChangeStatus($user, $booking, $status)) {
            return true;
        }

        if ($this->identifier->isadminOsmose($user)) {
            return true;
        }

        return false;
    }

    private function checkAdminAuthorisationChangeStatus(UserInterface $user, Booking $booking, Status $newStatus): Bool
    {
        $admin = $this->administratorRepository->findAdminByOneCenterAndUserId($user, $booking->getCenter());
        $allStatus = $this->statusRepository->findAll();
        if ($admin != null) {
            foreach ($allStatus as $status) {
                if ($status->getId() == $newStatus->getId()) {
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }
    }

    public function patientHasNeverBookedOnThisDate(Patient $patient, \DateTime $dateReserve, Slots $slots): Bool
    {
        $bookingOfPatient = $this->bookingRepository->findOneBy(['patient' => $patient, 'dateReserve' => $dateReserve, 'slot' => $slots]);
        if ($bookingOfPatient != null) {
            return false;
        } else {
            return true;
        }
    }

    public function deleteBooking(int $idBooking): array
    {
        try {
            $booking = $this->bookingRepository->find($idBooking);
            if (!$booking) {
                return ['message' => 'Réservation non trouvée', 'data' => null, 'code' => 404];
            }
            $this->entityManager->remove($booking);
            $this->entityManager->flush();
            $this->logger->info('La suppression de la réservation id ' . $booking->getId() . ' a bien été engistré');
            return ['message' => 'La reservation a bien été supprimé a bien été supprimé', 'data' => null, 'code' => 200];
        } catch (\Exception $e) {
            $this->logger->error('Problème lors de la suppression de la réservation : ' . $e->getMessage());
            return ['message' => 'Problème lors de la suppression de la réservation', 'data' => null, 'exception' => $e->getMessage(),'code' => 500];
        }
    }

    public function deleteMultipleBooking(array $listIdBooking): array
    {
        try {
            $listBooking = $this->bookingRepository->findBookingByArrayIdBooking($listIdBooking);
            if (count($listBooking) === 0) {
                return ['message' => 'Aucune réservation trouvée', 'code' => 404];
            }
            foreach ($listBooking as $booking) {
                $this->entityManager->remove($booking);
            }
            $this->entityManager->flush();
            $this->logger->info('La suppression de plusieurs réservations a bien été engistré');
            return ['message' => 'Les réservations ont bien été supprimées', 'code' => 200];
        } catch (\Exception $e) {
            $this->logger->error('Problème lors de la suppression des réservations : ' . $e->getMessage());
            return ['message' => 'Problème lors de la suppression des réservations', 'exception' => $e->getMessage(), 'code' => 500];
        }
    }
}
