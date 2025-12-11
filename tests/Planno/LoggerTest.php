<?php

use Tests\FixtureBuilder;
use League\OAuth2\Client\Provider\GenericProvider;

use PHPUnit\Framework\TestCase;


class LoggerTest extends TestCase
{
    use \App\Traits\LoggerTrait;

    private $entityManager;

    public function testLog(){
        global $entityManager;
        global $stdout;

        $this->entityManager = $entityManager;
        $stdout = 'test';

        $this->log('Unable to get token', get_class($this), $stdout);

        $this->expectOutputString("Unable to get token\n");

    }
}