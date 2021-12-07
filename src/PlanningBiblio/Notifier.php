<?php

namespace App\PlanningBiblio;

use App\PlanningBiblio\NotificationTransporter\NotificationTransporterInterface;
use App\PlanningBiblio\Notifier\Message;

class Notifier
{
    private $transporter;

    private $recipients;

    private $message_code;

    private $message_parameters = array();

    private $config = array();

    private $messages = array(
        'create_account' => array(
            'subject' => 'Création de compte',
            'body' => 'Votre compte Planning Biblio a été créé :
            <ul><li>Login : %login</li><li>Mot de passe : %password</li></ul>'
        )
    );

    public $subject;

    public $body;

    private $error = false;

    public function __construct()
    {
        $this->config = $GLOBALS['config'];
        $this->setTransporter(new \CJMail());
    }

    public function send()
    {
        if (!$this->config['Mail-IsEnabled']) {
            return;
        }

        if (!$this->message_code) {
            $this->error = "no message code provided";
            return;
        }

        $message = new Message($this->message_code);

        if ($message->error()) {
            $this->error = "Unknown message code: $this->message_code";
            return;
        }

        $this->subject = $message->subject();
        $this->body = $this->setPlaceHolders($message->body());

        if (!$this->transporter) {
            $this->error = "no transporter provided";
            return;
        }

        $transporter = $this->transporter;
        $transporter->setSubject($this->subject)
            ->setBody($this->body)
            ->setRecipients($this->recipients)
            ->send();

        if ($transporter->error) {
            $this->error = $transporter->error_CJInfo;
        }
    }

    public function setTransporter(NotificationTransporterInterface $transporter)
    {
        $this->transporter = $transporter;

        return $this;
    }

    public function setRecipients($recipients)
    {
        $this->recipients = $recipients;

        return $this;
    }

    public function setMessageCode($code)
    {
        $this->message_code = $code;

        return $this;
    }

    public function setMessageParameters($parameters)
    {
        $this->message_parameters = $parameters;

        return $this;
    }

    private function setPlaceHolders($string)
    {
        $params = $this->message_parameters;
        return str_replace(array_keys($params), array_values($params), $string);
    }

    public function getError()
    {
        return $this->error;
    }
}
