<?php

namespace App\EventListener;

use App\Cron\Crontab;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class CronListener
{

    public function onKernelRequest(GetResponseEvent $event)
    {

        $session = $event->getRequest()->getSession();

        if (!empty($session->get('loginId'))) {
            $cron = new Crontab();
            $cron->execute();
        }
    }
}
