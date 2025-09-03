<?php

namespace App\Controller\patientInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StaticPageController extends AbstractController
{
    #[Route('/guide-utilisation', name: 'app_user_manual')]
    public function showUserManual(): Response
    {
        return $this->render('patientInterface/pages/userManual.html.twig');
    }

    #[Route('/mention-legale', name: 'app_legal_notice')]
    public function showLegalNotice(): Response
    {
        return $this->render('patientInterface/pages/legalNotice.html.twig');
    }

    #[Route('/conditions-generale-d-utilisation', name: 'app_cgu')]
    public function showCgu(): Response
    {
        return $this->render('patientInterface/pages/cgu.html.twig');
    }
}