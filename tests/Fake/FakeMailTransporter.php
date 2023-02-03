<?php

namespace Tests\Fake;

use App\PlanningBiblio\NotificationTransporter\NotificationTransporterInterface;

class FakeMailTransporter implements NotificationTransporterInterface
{
    public $error = '';

    public function setSubject($subject)
    {
        return $this;
    }

    public function setBody($body)
    {
        return $this;
    }

    public function setRecipients($recipients)
    {
        return $this;
    }

    public function send()
    {
    }
}
