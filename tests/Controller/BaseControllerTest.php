<?php

namespace App\Tests\Controller;

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseControllerTest extends WebTestCase
{
    protected static function createKernel(array $options = [])
    {
        return new Kernel('test', true);
    }
}
