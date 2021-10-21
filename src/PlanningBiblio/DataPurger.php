<?php

namespace App\PlanningBiblio;

use App\PlanningBiblio\Logger;

class DataPurger
{

    private $dbprefix;
    private $delay;
    private $entityManager;
    private $stdout;

    public function __construct($entityManager, $delay, $stdout)
    {
        $this->dbprefix = $_ENV['DATABASE_PREFIX'];
        $this->delay = $delay;
        $this->entityManager = $entityManager;
        $this->logger = new Logger($entityManager, $stdout);
    }

    public function purge() {
        $this->log("Start purging $this->delay years old data");
        $this->log("End purging old data");
    }

    private function log($message) {
        $this->logger->log($message, "DataPurger");
    }

}
