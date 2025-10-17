<?php

namespace App\PlanningBiblio;

use App\Entity\Log;

class Logger
{
    private $entityManager;
    private $stdout;

    public function __construct($entityManager, $stdout = false)
    {
        $this->entityManager = $entityManager;
        $this->stdout = $stdout;
    }

    public function log($message, $program) {
        if ($this->stdout) {
            echo $message . "\n";
	    }

        $log = new Log();
        $log->setMessage($message);
        $log->setProgram($program);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
