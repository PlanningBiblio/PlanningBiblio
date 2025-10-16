<?php

namespace App\EventListener;

use App\Command\CronTabCommand;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class CronListener
{
    private $cronCommand;

    public function __construct(CronTabCommand $cronCommand) {
        $this->cronCommand = $cronCommand;
    }

    public function onKernelRequest(RequestEvent $event)
    {

        $session = $event->getRequest()->getSession();

        if (!empty($session->get('loginId'))) {
            $input = new ArrayInput([]);
            $output = new NullOutput();
            $this->cronCommand->run($input, $output);
        }
    }
}
