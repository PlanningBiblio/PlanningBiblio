<?php

use Tests\FixtureBuilder;
use App\PlanningBiblio\Logger;
use League\OAuth2\Client\Provider\GenericProvider;

use PHPUnit\Framework\TestCase;


class LoggerTest extends TestCase
{
    public function testLog(){
        global $entityManager;
        global $stdout;

        $stdout = "test";

        $logger = new Logger($entityManager, $stdout);

        $logger->log("Unable to get token", get_class($this));

        $this->expectOutputString("Unable to get token\n");

    }
}