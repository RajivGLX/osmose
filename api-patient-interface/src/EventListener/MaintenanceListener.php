<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class MaintenanceListener
{
    public function __construct(
        private RouterInterface $router
    )
    {}

    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function onKernelRequest(RequestEvent $event)
    {

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        // Check if maintenance mode is enabled
        $maintenanceMode = $_ENV['MAINTENANCE_MODE'] === 'true';

        // Skip redirection for the maintenance route itself and other specific routes to avoid infinite loop
        $excludedRoutes = ['maintenance', 'api_route', 'admin_route']; // Add other routes to exclude if necessary


        if ($maintenanceMode && !in_array($route, $excludedRoutes, true)) {
            $event->setResponse(new RedirectResponse($this->router->generate('maintenance')));
        }
    }

}