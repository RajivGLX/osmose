<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class BackofficeController extends AbstractController
{
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    #[Route('/backoffice', name: 'backoffice')]
    public function index(): Response
    {
        if ($this->kernel->getEnvironment() === 'dev') {
            // Redirection vers le serveur Angular en dev
            return $this->redirect('http://localhost:4200');
        }

        // En prod, servir le build Angular
        return $this->render('backoffice/index.html.twig');
    }

    #[Route('/api/login_check', name: 'test')]
    public function test(): Response
    {
        if ($this->kernel->getEnvironment() === 'dev') {
            // Redirection vers le serveur Angular en dev
            return $this->redirect('http://localhost:4200');
        }

        // En prod, servir le build Angular
        return $this->render('backoffice/index.html.twig');
    }
}
