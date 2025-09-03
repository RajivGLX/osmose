<?php

namespace App\Controller\patientInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MaintenanceController extends AbstractController
{
    #[Route('/maintenance', name: 'maintenance')]
    public function index(): Response
    {
        return $this->render('security/maintenance.html.twig');
    }
}