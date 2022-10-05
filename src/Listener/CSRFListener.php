<?php

namespace App\Listener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use App\Controller\CSRFController;

final class CSRFListener
{

    public function onKernelRequest(RequestEvent $event)
    {
        $method = $event->getRequest()->getMethod();

        if (in_array($method, ['POST', 'PUT', 'DELETE'])) {

            $request = $event->getRequest();

            $control = new CSRFController();
            $control->csrf_protection($request);
        }
    }
}
