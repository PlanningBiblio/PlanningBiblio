<?php

namespace Tests;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Panther\PantherTestCase;

class PLBWebTestCase extends PantherTestCase
{
    protected $builder;
    protected $client;
    protected $CSRFToken;
    protected $entityManager;

    protected function setUp(): void
    {
        global $entityManager;

        $CSRFToken = '00000';

        $this->client = static::createClient();
        $this->CSRFToken = $CSRFToken;
        $this->builder = new FixtureBuilder();
        $this->entityManager = $entityManager;

        $_SESSION['oups']['Auth-Mode'] = 'SQL';
        $_SESSION['login_id'] = 1;
        $_SESSION['oups']['CSRFToken'] = $CSRFToken;
        $GLOBALS['CSRFSession'] = $CSRFToken;
    }

    protected function logInAgent($agent, $rights = array(99, 100)) {
        $_SESSION['login_id'] = $agent->id();

        $agent->droits($rights);

        global $entityManager;
        $entityManager->persist($agent);
        $entityManager->flush();

        $GLOBALS['droits'] = $rights;

        $session = self::$container->get('session');
        $session->set('loginId', $agent->id());
        $session->save();
    
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    protected function setUpPantherClient()
    {
        $this->client = static::createPantherClient(
            array(
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--headless'
            )
        );
    }

    protected function login($agent)
    {
        $this->logout();
        global $entityManager;

        $password = password_hash("MyPass", PASSWORD_BCRYPT);
        $agent->password($password);
        $entityManager->persist($agent);
        $entityManager->flush();

        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Valider')->form();
        $form['login'] = $agent->login();
        $form['password'] = 'MyPass';

        $crawler = $this->client->submit($form);

        $this->client->waitForVisibility('html');
    }

    protected function logout()
    {
        $this->client->request('GET', '/logout');
    }


    protected function jqueryAjaxFinished(): callable
    {
        return static function ($driver): bool {
            return $driver->executeScript('return $.active === 0;');
        };
    }

    protected function getSelect($id = null)
    {
        $driver = $this->client->getWebDriver();

        $select = new WebDriverSelect($driver->findElement(WebDriverBy::id($id)));

        return $select;
    }

    protected function getSelectValues($id = null)
    {
        $select = $this->getSelect($id);
        $options = array();

        foreach ($select->getOptions() as $option) {
            $options[] = $option->getAttribute('value');
        }

        return $options;
    }

    protected function getElementsText($selector = null)
    {
        $driver = $this->client->getWebDriver();

        $elements = $driver->findElements(WebDriverBy::cssSelector('ul#perso_ul1 li'));
        $values = array();

        foreach ($elements as $element) {
            $values[] = $element->getText();
        }

        return $values;
    }
}
