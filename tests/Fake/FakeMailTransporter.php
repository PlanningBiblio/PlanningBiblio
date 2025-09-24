<?php

namespace Tests\Fake;

use App\PlanningBiblio\NotificationTransporter\NotificationTransporterInterface;

class FakeMailTransporter implements NotificationTransporterInterface
{
    public $error = '';

    public function setSubject($subject): static
    {
        return $this;
    }

    public function setBody($body): static
    {
        return $this;
    }

    public function setRecipients($recipients): static
    {
        return $this;
    }

    public function send()
    {
    }
}
