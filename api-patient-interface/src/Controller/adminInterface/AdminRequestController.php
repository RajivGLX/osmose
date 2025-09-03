<?php

namespace App\Controller\adminInterface;

use App\Entity\Center;
use App\Form\BookingStatusBatchType;
use App\Form\BookingStatusType;
use App\Manager\BookingManager;
use App\Repository\AdministratorRepository;
use App\Repository\BookingRepository;
use App\Repository\StatusRepository;
use App\Services\Identifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\CenterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminRequestController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator,
        private EntityManagerInterface $entityManager,
        private CenterRepository $centerRepository,
        private AdministratorRepository $administratorRepository,
        private BookingRepository $bookingRepository,
        private StatusRepository $statusRepository,
        private BookingManager $bookingManager,
        private Identifier $identifier
    )
    {}

    #[Route(path: '/admin/demandes', name: 'app_admin_all_list_request_in_progress')]
    #[IsGranted('ROLE_ADMIN_DIALYZONE')]
    public function showAllListRequestInProgress(): Response
    {
        if (!$this->identifier->isAdminDialyzone($this->getUser())) {
            throw $this->createAccessDeniedException();
        }
        $allBookings = $this->bookingRepository->findAll();


        return $this->render('admin/request/requestList.html.twig', [
            'allBookings' => $allBookings,
        ]);
    }
    #[Route(path: '/admin/demandes/{slug}', name: 'app_admin_list_request_in_progress')]
    #[IsGranted('ROLE_ADMIN')]
    public function showListRequestInProgress(string $slug): Response
    {
        $center = $this->centerRepository->findOneBySlug($slug);
        $admin = $this->administratorRepository->findAdminByOneCenterAndUserId($this->getUser(), $center);
        if (!$admin) {
            throw $this->createAccessDeniedException();
        }
        $allBookings = $center->getBookings();


        return $this->render('admin/request/requestList.html.twig', [
            'center' => $center,
            'allBookings' => $allBookings,
        ]);
    }

    #[Route(path: '/admin/demande/{slug}/{id}', name: 'app_admin_request_in_progress')]
    #[IsGranted('ROLE_ADMIN')]
    public function showRequestInProgress(string $slug, int $id,Request $request): Response
    {
        $center = $this->centerRepository->findOneBySlug($slug);
        $autorized = $this->administratorRepository->findAdminByOneCenterAndUserId($this->getUser(), $center);

        if (!$autorized && !$this->identifier->isAdminDialyzone($this->getUser())) {
            $this->addFlash('error', $this->translator->trans('backend.user.non_authorized'));

            return $this->redirectToRoute('app_admin_index');
        }

        $booking = $this->bookingRepository->find($id);
        $formBooking = $this->createForm(BookingStatusType::class);
        $formBatch = $this->createForm(BookingStatusBatchType::class, ['patient' => $booking->getPatient()]);
        $formBooking->handleRequest($request);
        $formBatch->handleRequest($request);

        if ($formBooking->isSubmitted() && $formBooking->isValid()) {
            $requestResult = $this->bookingManager->changeStatusBooking($formBooking->get('statusBookings')->getData()[0],$this->getUser(),$booking);

            if($requestResult){
                $this->addFlash('success', $this->translator->trans('backend.request.modify_status'));
            } else{
                $this->addFlash('error', $this->translator->trans('backend.request.modify_status'));
            }

            return $this->redirectToRoute('app_admin_request_in_progress', ['slug' => $slug, 'id' => $id]);
        }

        if ($formBatch->isSubmitted() && $formBatch->isValid()) {
            $requestResult = $this->bookingManager->changeStatusBookingBatch($this->getUser(),$formBatch->get('status')->getData(),$formBatch->get('bookings')->getData());

            if($requestResult){
                $this->addFlash('success', $this->translator->trans('backend.request.success_modify_all_status'));
            }else{
                $this->addFlash('error', $this->translator->trans('backend.request.error_modify_all_status'));
            }

            return $this->redirectToRoute('app_admin_request_in_progress', ['slug' => $slug, 'id' => $id]);
        }

        return $this->render('admin/request/request.html.twig', [
            'center' => $center,
            'booking' => $booking,
            'formBooking' => $formBooking->createView(),
            'formBatch' => $formBatch->createView(),
        ]);
    }
}