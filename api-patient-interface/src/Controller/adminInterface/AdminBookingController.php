<?php

namespace App\Controller\adminInterface;

use App\Entity\Patient;
use App\Entity\Status;
use App\Repository\SlotsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Manager\AvailabilityManager;
use App\Manager\BookingManager;
use App\Manager\CenterManager;
use App\Model\Month;
use App\Repository\AvailabilityRepository;
use App\Repository\BookingRepository;
use App\Repository\CenterRepository;
use App\Repository\PatientRepository;
use App\Repository\StatusRepository;
use App\Services\Identifier;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminBookingController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator,
        private Identifier $identifier,
        private CenterRepository $centerRepository,
        private BookingRepository $bookingRepository,
        private AvailabilityRepository $availabilityRepository,
        private SlotsRepository $slotsRepository,
        private StatusRepository $statusRepository,
        private AvailabilityManager $availabilityManager,
        private BookingManager $bookingManager
    ) {}

    #[Route(path: '/admin/rendez-vous/{slug}/{monday}/{month}/{year}', name: 'app_admin_booking', requirements: ['monday' => '\d+', 'month' => '\d+', 'year' => '\d+'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function showCalendar(string $slug, int $monday = null, int $month = null, int $year = null): Response
    {
        $center = $this->centerRepository->findOneBySlug($slug);
        $centerBelongAdmin = $this->identifier->isCenterBelongAdmin($this->getUser(), $center);
        if (!$centerBelongAdmin) {
            $this->addFlash('error', $this->translator->trans('backend.global.unauthorized'));
            return $this->redirectToRoute('app_admin_centers');
        }
        $month = new Month($monday, $month, $year);
        $booksInMonth = $this->bookingRepository->findBookingsByCenterAndMonth($center, $month->month);
        $availability = $this->availabilityRepository->findAvailabilityByCenterAndMonth($center, $month->month);
        $mondayForTheWeekView = $month->getLastMonday($month->getStartingWeek());
        $allSlots = $this->slotsRepository->findAll();

        if ($_POST) {
            $requestResult = $this->availabilityManager->saveAvailability($_POST, $center);
            if ($requestResult) {
                $this->addFlash('success', $this->translator->trans('backend.booking.save_success'));
            } else {
                $this->addFlash('error', $this->translator->trans('backend.booking.save_error'));
            }
            return $this->redirectToRoute('app_admin_booking', [
                'slug' => $slug,
                'monday' => $month->monday,
                'month' => $month->month,
                'year' => $month->year,
            ]);
        }

        return $this->render('admin/booking/bookingCalendar.html.twig', [
            'center' => $center,
            'month' => $month,
            'dateToString' => $month->dateToString(),
            'weeks' => $month->getAllWeeksInMonth(),
            'days' => $month->days,
            'start' => $month->getFirstDayOfWeek(),
            'booksInMonth' => $booksInMonth,
            'availability' => $availability,
            'mondayForTheWeekView' => $mondayForTheWeekView,
            'allSlots' => $allSlots,
        ]);
    }


    #[Route('/api/send-availability', name: 'api_send_availability', methods: ['POST'])]
    public function availabilityCenter(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $allAvailability = $data['availability'];
        $center = $this->centerRepository->find($data['idCenter']);

        $centerBelongAdmin = $this->identifier->isCenterBelongAdmin($this->getUser(), $center);
        if (!$centerBelongAdmin) {
            return new JsonResponse(['error' => 'Unauthorized'], 403);
        }

        $result = $this->availabilityManager->saveAvailability($allAvailability, $center);
        return $this->json(['message' => $result['message'], 'data' => $result['data']], $result['code'], [], ['groups' => 'info_center']);

    }


    #[Route('/api/get-all-futur-booking', name: 'api_all_futur_booking', methods: ['GET'])]
    public function getAllFuturBooking(): Response
    {
        $user = $this->getUser();
        if($this->identifier->isAdminDialyzone($user)){
            $allBooking = $this->bookingRepository->findAll();
        } else {
            $allBooking = $this->bookingRepository->findFuturBookingsByCenter($user->getAdministrator()->getCenters());
        }
        return $this->json($allBooking, 200, [], ['groups' => 'info_booking']);
    }

    #[Route('/api/get-all-past-booking', name: 'api_all_past_booking', methods: ['GET'])]
    public function getAllPastBooking(): Response
    {
        $user = $this->getUser();
        if($this->identifier->isAdminDialyzone($user)){
            $allBooking = $this->bookingRepository->findAll();
        } else {
            $allBooking = $this->bookingRepository->findPastBookingsByCenter($user->getAdministrator()->getCenters());
        }
        return $this->json($allBooking, 200, [], ['groups' => 'info_booking']);
    }

    #[Route('/api/get-status-admin', name: 'api_status_admin', methods: ['GET'])]
    public function getStatusAdmin(): Response
    {
        $user = $this->getUser();
        $allstatus = $this->statusRepository->findAll();
        if ($this->identifier->isAdminDialyzone($user)) {
            return $this->json($allstatus, 200, [], ['groups' => 'status_admin']);
        } else {
            $statusForAdmin = [];
            foreach ($allstatus as $status) {
                if ($status->isStatusConfirm() || $status->isStatusDenied() || $status->isStatusContact()) {
                    array_push($statusForAdmin, $status);
                }
            }
            return $this->json($statusForAdmin, 200, [], ['groups' => 'status_admin']);
        }
        
    }

    #[Route('/api/add-status-admin', name: 'api_add_status_admin', methods: ['POST'])]
    public function addStatusAdmin(Request $request): Response
    {
        $data = json_decode($request->getContent());
        $user = $this->getUser();
        $booking = $this->bookingRepository->find($data->idBooking);
        $status = $this->statusRepository->find($data->idStatus);

        $response = $this->bookingManager->changeStatusBooking($status, $user, $booking);

        if ($response) {
            return $this->json($booking,200, [], ['groups' => 'info_booking']);
        } else {
            return $this->json($response, 500);
        }
    }

    #[Route('/api/add-multiple-status-admin', name: 'api_add_multiple_status_admin', methods: ['POST'])]
    public function addMultipleStatusAdmin(Request $request): Response
    {
        $data = json_decode($request->getContent());
        $user = $this->getUser();
        $bookingsdata = $this->bookingRepository->findBookingByArrayIdBooking($data->bookings);
        $status = $this->statusRepository->find($data->idStatus);

        $response = $this->bookingManager->changeStatusBookingBatch($user, $status, $bookingsdata, true);

        if ($response) {
            return $this->json($bookingsdata, 200, [], ['groups' => 'info_booking']);
        } else {
            return $this->json($response, 500);
        }
    }

    #[Route('/api/get-booking-by-patient/{id}', name: 'api_booking_patient', methods: ['GET'])]
    public function getBookingPatient(Patient $patient): Response
    {
        $allBooking = $this->bookingRepository->findFutureBookingsByPatient($patient);

        return $this->json($allBooking, 200, [], ['groups' => 'info_booking']);
    }
}
