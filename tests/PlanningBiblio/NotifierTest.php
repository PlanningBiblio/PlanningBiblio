<?php

use App\PlanningBiblio\Notifier;
use PHPUnit\Framework\TestCase;

use Tests\Fake\FakeMailTransporter;

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

	$notifier->setRecipients('joe@bar.com');
        $notifier->setMessageCode('create_account');
        $notifier->send();
        $this->assertEquals('Création de compte', $notifier->subject, 'Subject is "Création de compte"');
    }

    public function testGetBody()
    {
        $GLOBALS['config']['Mail-IsEnabled'] = 1;

        $notifier = new Notifier();

	$notifier->setRecipients('joe@bar.com');
        $notifier->setMessageCode('create_account');
        $notifier->send();
        $expected = 'Votre compte Planno a été créé :
            <ul><li>Login : %login</li><li>Mot de passe : %password</li></ul>';

        $this->assertEquals($expected, $notifier->body, 'Get body without placeholders replacements');
        $notifier->setMessageParameters(array(
            'login' => 'joe',
            'password' => 'foo'
        ));
        $notifier->send();
        $expected = 'Votre compte Planno a été créé :
            <ul><li>Login : joe</li><li>Mot de passe : foo</li></ul>';

        $this->assertEquals($expected, $notifier->body, 'Get body with placeholders replacements');
    }

    public function testTransporter()
    {
        $GLOBALS['config']['Mail-IsEnabled'] = 1;

        $notifier = new Notifier();

	$notifier->setRecipients('joe@bar.com');
        $notifier->setMessageCode('create_account');
        $notifier->setTransporter(new FakeMailTransporter());
        $notifier->send();
        $this->assertEquals('', $notifier->getError(), 'No transporter error');
    }

    public function testDefaultTransporter()
    {
        $GLOBALS['config']['Mail-IsEnabled'] = 1;

        $notifier = new Notifier();

        $notifier->setRecipients('joe@bar.com');
        $notifier->setMessageCode('create_account');
        $this->assertEquals('CJMail', get_class($notifier->getTransporter()), 'Default transporter is CJMail');
        $this->assertEquals('', $notifier->getError(), 'No transporter error');
    }

    public function testMailRecipients()
    {
        $GLOBALS['config']['Mail-IsEnabled'] = 1;
        $GLOBALS['config']['Mail-From'] = 'notifications@planno.fr';

        $destinataires = ['alice@example.com', 'bob@example.com', 'eve@example.com'];

        $m = new CJMail();
        $m->notReally = true;
        $m->to = $destinataires;
        $m->send();

        $toAddresses = [];
        foreach ($m->successAddresses as $elem) {
            $toAddresses[] = $elem[0][0];
        }

        $this->assertEquals('alice@example.com', $toAddresses[0], 'When multiple recipients are given, they are all in To (in seperate emails) (Alice)');
        $this->assertEquals('bob@example.com', $toAddresses[1], 'When multiple recipients are given, they are all in To (in seperate emails) (Bob)');
        $this->assertEquals('eve@example.com', $toAddresses[2], 'When multiple recipients are given, they are all in To (in seperate emails) (Eve)');
        $this->assertNotContains('notifications@planno.fr', $toAddresses, 'When multiple recipients are given, Mail-From is no longer used as TO');
    }

}
