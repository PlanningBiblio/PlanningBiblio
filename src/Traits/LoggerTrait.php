<?php

namespace App\Traits;

use App\Entity\Log;

trait LoggerTrait
{
    public function log($message, $program, $stdout = false): void {
        if ($stdout) {
            echo $message . "\n";
	    }

        $log = new Log();
        $log->setMessage($message);
        $log->setProgram($program);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
