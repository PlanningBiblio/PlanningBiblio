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
        $route = $event->getRequest()->attributes->get('_route');

        if (str_starts_with($route, '_')) {
            return;
        }

        $routeParameters = $request->attributes->get('_route_params');
        if (isset($routeParameters['no-csrf']) && $routeParameters['no-csrf']) {
            return;
        }

        // Handle CSRF protection only for route with the token param.
        $controller->csrf_protection($request);
    }
}
