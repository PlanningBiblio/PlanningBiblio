<?php

use App\PlanningBiblio\Notifier;
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../public/include/function.php');

class NotifierTest extends TestCase
{
    public function testWithErrors() {
        $GLOBALS['config']['Mail-IsEnabled'] = 1;

        $notifier = new Notifier();

        $notifier->send();
        $this->assertEquals('no message code provided', $notifier->getError(), 'Use notifier without message code');

        $notifier->setMessageCode('unknown_message_code');
        $notifier->send();
        $expected = 'Unknown message code: unknown_message_code';
        $this->assertEquals($expected, $notifier->getError(), 'Use notifier without a valid message code');
    }

    public function testGetSubject()
    {
        $GLOBALS['config']['Mail-IsEnabled'] = 1;

        $notifier = new Notifier();

        $notifier->setMessageCode('create_account');
        $notifier->send();
        $this->assertEquals('Création de compte', $notifier->subject, 'Subject is "Création de compte"');
    }

    public function testGetBody()
    {
        $GLOBALS['config']['Mail-IsEnabled'] = 1;

        $notifier = new Notifier();

        $notifier->setMessageCode('create_account');
        $notifier->send();
        $expected = 'Votre compte Planning Biblio a été créé :
            <ul><li>Login : %login</li><li>Mot de passe : %password</li></ul>';

        $this->assertEquals($expected, $notifier->body, 'Get body without placeholders replacements');
        $notifier->setMessageParameters(array(
            '%login' => 'joe',
            '%password' => 'foo'
        ));
        $notifier->send();
        $expected = 'Votre compte Planning Biblio a été créé :
            <ul><li>Login : joe</li><li>Mot de passe : foo</li></ul>';

        $this->assertEquals($expected, $notifier->body, 'Get body with placeholders replacements');
    }

    public function testTransporter()
    {
        $GLOBALS['config']['Mail-IsEnabled'] = 1;

        $notifier = new Notifier();

        $notifier->setMessageCode('create_account');
        $notifier->setTransporter(new \CJMail());
        $notifier->send();
        $this->assertEquals('', $notifier->getError(), 'No transporter error');
    }
}