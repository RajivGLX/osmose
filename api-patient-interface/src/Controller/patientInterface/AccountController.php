<?php

namespace App\Controller\patientInterface;

use App\Entity\Booking;
use App\Form\BookingType;
use App\Form\ChangePasswordFormType;
use App\Form\UserInformationType;
use App\Manager\BookingManager;
use App\Manager\UserManager;
use App\Repository\BookingRepository;
use App\Repository\PatientRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccountController extends AbstractController
{
    public function __construct(
        private UserManager $userManager,
        private BookingManager $bookingManager,
        private UserRepository $userRepository,
        private PatientRepository $patientRepository,
        private BookingRepository $bookingRepository,
        private StatusRepository $statusRepository,
        private PaginatorInterface $paginator,
        private TranslatorInterface $translator
    )
    {}

    #[Route('/compte', name: 'app_account')]
    #[IsGranted('ROLE_PATIENT')]
    public function index(): Response
    {
        return $this->render('account/homeAccount.html.twig', [
            'controller_name' => 'AccountController',
        ]);
    }

    #[Route('/compte/modification-mot-de-passe', name: 'account_change_password')]
    public function updatePasswordPatient(Request $request): Response
    {
        $user = $this->userRepository->findOneBy(['id' => $this->getUser()->getId()]);
        $form = $this->createForm(ChangePasswordFormType::class, $user, ['translator' => $this->translator]);
        $errorPassword = false;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $requestResponse = $this->userManager->changePassword($form,$user);
            if ($requestResponse){
                $this->addFlash('notice',['nature'=>'success', 'message'=>'Votre mot de passe a bien été modifier']);
            }else{
                $this->addFlash('notice',['nature'=>'danger', 'message'=>'une erreur est survenue lors de la modification de votre mot de passe']);
            }
            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/patientUpdatePassword.html.twig', [
            'form' => $form,
            'errorPassword' => $errorPassword,
        ]);
    }

    #[Route('/compte/vos-informations', name: 'account_information')]
    public function informationPatient(Request $request): Response
    {
        $user = $this->userRepository->findOneBy(['id' => $this->getUser()->getId()]);
        $center = $user->getPatient()->getCenter();
        $form = $this->createForm(UserInformationType::class,$user, ['translator' => $this->translator]);
        $form->remove('new_password');
        $form->remove('password');
        $form->remove('email');
        $form->get('patient')->remove('checked');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $requestResponse = $this->userManager->updatePatient($form,$user);
            if ($requestResponse){
                $this->addFlash('notice',['nature'=>'success', 'message'=>'Vos information ont bien été modifié']);
            }else{
                $this->addFlash('danger',['nature'=>'success', 'message'=>'Une erreur est survenue lors de la modification de vos informations']);
            }
            return $this->redirectToRoute('account_information');
        }

        return $this->render('account/patientInformation.html.twig', [
            'form' => $form,
            'patientCenter' => $center
        ]);
    }

    #[Route('/compte/vos-reservations', name: 'account_list_future_booking')]
    public function futureListBooking(Request $request): Response
    {
        $patient = $this->patientRepository->findOneBy(['user' => $this->getUser()]);

        $futureBooking = $this->bookingRepository->findFutureBookingsByPatient($patient);

        $pagination = $this->paginator->paginate($futureBooking, $request->query->get('page', 1), 7);
        return $this->render('account/bookingList.html.twig', [
            'patient' => $patient,
            'pagination' => $pagination,
            'title' => 'future'
        ]);
    }

    #[Route('/compte/vos-anciennes-reservations', name: 'account_old_list_booking')]
    public function oldListBooking(Request $request): Response
    {
        $patient = $this->patientRepository->findOneBy(['user' => $this->getUser()]);
        $oldBooking = $this->bookingRepository->findOldBookingsByPatient($patient);
        $pagination = $this->paginator->paginate($oldBooking, $request->query->get('page', 1), 7);

        return $this->render('account/bookingList.html.twig', [
            'patient' => $patient,
            'pagination' => $pagination,
            'title' => 'old'
        ]);
    }

    #[Route('/compte/votre-reservation/{id}', name: 'account_booking')]
    public function futreBooking(Booking $booking,Request $request): Response
    {
        $patient = $this->patientRepository->findOneBy(['user' => $this->getUser()]);
        $now = new \DateTime();
        if ($booking->getPatient() !== $patient) return $this->redirectToRoute('account_list_future_booking');
        if ($booking->getDateReserve() < $now)return $this->redirectToRoute('account_old_booking', ['id'=>$booking->getId()]);

        $form = $this->createForm(BookingType::class, $booking, [
            'remove_reason' => true,
        ]);
        $form->handleRequest($request);
        $form->remove('reason');

        if ($form->isSubmitted() && $form->isValid()) {
            $resultUpdate = $this->bookingManager->updateBooking($form);
            if ($resultUpdate){
                $this->addFlash('notice',['nature' => 'success', 'message' => 'Votre modification a bien été enregistrer']);
            }else{
                $this->addFlash('notice',['nature' => 'danger', 'message' => 'Une erreur est survenue lors de la modification de votre réservation']);
            }

            return $this->redirectToRoute('account_booking',['id' => $booking->getId()]);
        }

        return $this->render('account/booking.html.twig', [
            'booking' => $booking,
            'form' => $form,
        ]);
    }

    #[Route('/compte/votre-ancienne-reservation/{id}', name: 'account_old_booking')]
    public function oldBooking(Booking $booking): Response
    {
        $patient = $this->patientRepository->findOneBy(['user' => $this->getUser()]);
        $now = new \DateTime();
        if ($booking->getPatient() !== $patient)return $this->redirectToRoute('account_list_future_booking');
        if ($booking->getDateReserve() > $now)return $this->redirectToRoute('account_list_future_booking');

        return $this->render('account/booking.html.twig', [
            'booking' => $booking,
        ]);
    }

    #[Route('/compte/annulation-reservation/{id}', name: 'account_booking_cancel')]
    public function cancelBooking(Booking $booking): Response
    {
        $patient = $this->patientRepository->findOneBy(['user' => $this->getUser()]);
        if ($booking->getPatient() === $patient){
            $requestResult = $this->bookingManager->changeStatusBooking($this->statusRepository->findOneBy(['status_canceled' => true]),$this->getUser(),$booking);
            if ($requestResult){
                $this->addFlash('notice',['nature' => 'success', 'message' => 'Votre annulation a bien été prise en compte']);
            }else{
                $this->addFlash('notice',['nature' => 'danger', 'message' => 'Une erreur est survenue lors de l\'annulation de votre réservation']);
            }
        }else{
            $this->addFlash('notice',['nature' => 'danger', 'message' => 'Vous n\'etes pas autorisé à annuler cette réservation']);
        }
        return $this->redirectToRoute('account_booking',['id' => $booking->getId()]);
    }
}
