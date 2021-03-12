<?php

namespace Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PLBWebTestCase extends WebTestCase
{
    protected function logInAgent($agent, $rights = array(99, 100)) {
        $_SESSION['login_id'] = $agent->id();

        $agent->droits($rights);

        global $entityManager;
        $entityManager->persist($agent);
        $entityManager->flush();

        $GLOBALS['droits'] = $rights;
    }
}
