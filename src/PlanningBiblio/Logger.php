<?php

namespace App\PlanningBiblio;

class Logger
{
    private $entityManager;
    private $dbprefix;
    private $stdout;

    public function __construct($entityManager, $stdout)
    {
        $this->entityManager = $entityManager;
        $this->dbprefix = $_ENV['DATABASE_PREFIX'];
        $this->stdout = $stdout;
    }

    public function log($message, $application) {
        if ($this->stdout) {
            echo $message . "\n";
	    }
        $query = "INSERT INTO " . $this->dbprefix . "log (msg, program) VALUES (?,?)";
        $this->entityManager->getConnection()->prepare($query)->execute([$message, $application]);
    }
}
