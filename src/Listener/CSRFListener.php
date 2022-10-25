<?php

namespace App\Listener;

use Symfony\Component\HttpKernel\Event\ControllerEvent;

class CSRFListener
{
    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController();

        // when a controller class defines multiple action methods, the controller
        // is returned as [$controllerInstance, 'methodName'].
        if (is_array($controller)) {
            $controller = $controller[0];
        }

        $request = $event->getRequest();
        $routeParameters = $request->attributes->get('_route_params');

        // Handle CSRF protection only for route with the token param.
        if (isset($routeParameters['csrf']) && $routeParameters['csrf']) {
            $controller->csrf_protection($request);
        }
    }
}
