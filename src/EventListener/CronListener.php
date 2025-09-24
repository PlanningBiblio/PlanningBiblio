<?php

namespace App\EventListener;

use App\Cron\Crontab;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class CronListener
{

    public function onKernelRequest(RequestEvent $event): void
    {

        $session = $event->getRequest()->getSession();

        if (!empty($session->get('loginId'))) {
            $cron = new Crontab();
            $cron->execute();
        }
    }
}
