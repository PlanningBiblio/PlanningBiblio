<?php

namespace App\PlanningBiblio;

class Logger
{
    private $entityManager;
    private $dbprefix;

    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
        $this->dbprefix = $_ENV['DATABASE_PREFIX'];
    }

    public function log($message, $application) {
        echo $message . "\n";
        $query = "INSERT INTO " . $this->dbprefix . "log (msg, program) VALUES (?,?)";
        $this->entityManager->getConnection()->prepare($query)->execute([$message, $application]);
    }
}
