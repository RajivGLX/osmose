<?php

namespace App\Controller\patientInterface;

use App\Entity\Booking;
use App\Entity\Patient;
use App\Form\BookingType;
use App\Form\PatientInformationMinType;
use App\Manager\BookingManager;
use App\Manager\PatientManager;
use App\Model\Month;
use App\Repository\AvailabilityRepository;
use App\Repository\BookingRepository;
use App\Repository\CenterRepository;
use App\Repository\PatientRepository;
use App\Repository\SlotsRepository;
use App\Services\Identifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BookingController extends AbstractController
{
    public function __construct(
        private CenterRepository $centerRepository,
        private AvailabilityRepository $availabilityRepository,
        private PatientRepository $patientRepository,
        private BookingRepository $bookingRepository,
        private SlotsRepository $slotsRepository,
        private BookingManager $bookingManager,
        private TranslatorInterface $translator,
        private PatientManager $patientManager,
        private Identifier $identifier
    ) {}

    #[Route('/centre/reservation/{slug}/{month}/{year}', name: 'app_booking', requirements: ['month' => '\d+', 'year' => '\d+'])]
    public function book(Request $request, string $slug, int $month = null, int $year = null): Response
    {
        $month = new Month(null, $month, $year);
        $center = $this->centerRepository->findOneBySlug($slug);
        $patient = $this->patientRepository->findOneBy(['user' => $this->getUser()]);
        $isPatientHasMinInfo = $this->identifier->isPatientHasMinInfo($patient);
        $allPatientBooking = $this->bookingRepository->findBookingsByPatientAndMonth($patient, $month->month);
        $allSlot = $this->slotsRepository->findAll();
        $availability = $this->availabilityRepository->findAvailabilityByCenterAndMonth($center, $month->month);
        $booking = new Booking();

        $formBooking = $this->createForm(BookingType::class, $booking);
        $formBooking->handleRequest($request);
        $formPatient = $this->createForm(PatientInformationMinType::class, $patient, ['translator' => $this->translator]);
        $formPatient->handleRequest($request);

        if ($formBooking->isSubmitted() && $formBooking->isValid()) {
            if($isPatientHasMinInfo === false) {
                $this->addFlash('notice', ['nature' => 'danger', 'message' => 'Veuillez compléter les informations minimum avant de continuer']);
                return $this->redirectToRoute('app_booking', ['slug' => $slug, 'month' => $month->month, 'year' => $month->year,]);
            }
            if (array_key_exists('availability', $request->getPayload()->all())) {
                $listAvailability = $request->getPayload()->all()['availability'];
                if ($this->bookingManager->saveAllBooking($formBooking, $center, $patient, $listAvailability)) {
                    $this->addFlash('notice', ['nature' => 'success', 'message' => 'Nous avons bien pris en compte votre demande nous l\'avons transmise au centre de Dialyse. Nous vous invitons a suivre votre demande via votre espace patient.']);
                } else {
                    $this->addFlash('notice', ['nature' => 'danger', 'message' => 'Une erreur est survenue lors de la sauvegarde de votre demande. Veuillez réessayer ou contacter le support technique']);
                }
            } else {
                $this->addFlash('notice', ['nature' => 'danger', 'message' => 'Veuillez sélectionner au moins un créneaux pour valider votre demande']);
            }
            return $this->redirectToRoute('app_booking', ['slug' => $slug, 'month' => $month->month, 'year' => $month->year,]);
        }

        return $this->render('patientInterface/pages/center.html.twig', [
            'center' => $center,
            'allPatientBooking' => $allPatientBooking,
            'month' => $month,
            'availability' => $availability,
            'allSlot' => $allSlot,
            'isPatientHasMinInfo' => $isPatientHasMinInfo,
            'formBooking' => $formBooking->createView(),
            'formPatient' => $formPatient->createView(),
        ]);
    }

    #[Route('/patient-information', name: 'app_patient_information')]
    public function patientInformation(Request $request): JsonResponse
    {
        $patient = $this->getUser()->getPatient();
        $form = $this->createForm(PatientInformationMinType::class, $patient, ['translator' => $this->translator]);
        $form->handleRequest($request);

        return $this->handleAjaxFormSubmission($form, $patient);
    }

    private function handleAjaxFormSubmission($form, Patient $patient): JsonResponse
    {
        if ($form->isSubmitted() && $form->isValid()) {
            if($this->patientManager->updatePatient($form, $patient)) {
                return $this->json([
                    'success' => true,
                    'message' => 'Les informations du patient ont été mises à jour avec succès.'
                ]);
            }else{
                return $this->json([
                    'success' => false,
                    'message' => 'Les informations du patient ont été mises à jour avec succès.'
                ]);
            }
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $errors = $this->getFormErrors($form);

            return $this->json([
                'success' => false,
                'errors' => $errors,
                'message' => 'Le formulaire contient des erreurs.'
            ]);
        }

        return $this->json([
            'success' => false,
            'message' => 'Le formulaire n\'a pas été soumis correctement.'
        ]);
    }


    // Méthode pour récupérer les erreurs de formulaire
    private function getFormErrors($form)
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            /** @var FormErrorIterator $error */
            $errors[$error->getOrigin()->getName()] = $error->getMessage();
        }
        return $errors;
    }
}
