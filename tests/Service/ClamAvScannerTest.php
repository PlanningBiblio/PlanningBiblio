<?php

namespace App\Tests\Service;

use App\Service\ClamAvScanner;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Psr\Log\LoggerInterface;
use Monolog\Logger;

class ClamAvScannerTest extends KernelTestCase
{
    public function testScan(): void
    {
        self::bootKernel();

        $logger = static::getContainer()->get(LoggerInterface::class);
        $socket = self::getContainer()->getParameter('clamav.socket');

        if ($socket) {
            $scanner = new ClamAvScanner($socket, $logger);
            $result = $scanner->scan( __DIR__ . '/../data/eicar.com');
            // https://packagist.org/packages/adriengras/php-clamav says:
            // ->scan returns true if the file is clean, false otherwise
            // However, '' is returned instead of false, and 1 instead of true
            // That's why we use assertEquals and not assertSame here
            $this->assertEquals(false,  $result, "Eicar file triggers clamav");

            $result = $scanner->scan( __DIR__ . '/../data/random.txt');
            $this->assertEquals(true, $result, "Random file does not trigger clamav");

            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessageMatches('/^File does not exist/');
            $result = $scanner->scan( __DIR__ . '/../data/does_not_exist.txt');
        } else {
            $this->assertEquals(true, true, "Nothing to test when ClavAvScanner is not enabled");
        }
    }
}
