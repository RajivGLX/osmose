<?php

namespace App\Controller\centerInterface;

use App\Repository\ErpRepository;
use App\Repository\ServiceRepository;
use App\Repository\UrbanismeRepository;
use App\Service\EmailingService;
use App\Service\NotificationsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ServicesController extends AbstractController
{
    /**
     * Retourne la liste des services
     *
     * @param ServiceRepository $serviceRepo
     * @return Response
     */
    #[Route('/api/getListeServices', name: 'app_get_liste_services')]
    public function getListeServices(ServiceRepository $serviceRepo): Response
    {
        return $this->json($serviceRepo->findAll(), 200, [], ['groups' => 'services_list']);
    }


//    route test emailing
    #[Route('/api/sendMail', name: 'app_send_mail_commande')]
    public function sendMail(EmailingService $emailingService, ErpRepository $erpRepository, NotificationsService $notificationsService): Response
    {

        $erp = $erpRepository->find(4);

//        dd($notificationsService->userRefusedNotifications($user,'demande_urbanisme_mail'));
        return $emailingService->sendMailDemandeTraitee($erp);



    }


}
