<?php

namespace App\Planno\NotificationTransporter;

interface NotificationTransporterInterface
{
    public function setSubject($subject);

    public function setBody($body);

    public function setRecipients($recipients);

    public function send();
}
