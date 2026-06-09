<?php

namespace App\Planno\Notifier;

use Exception;

class Message
{
    private $message;

    private $error;

    public function __construct($code = null)
    {
        $message_class = "\\App\\Planno\\Notifier\\Messages\\$code";

        if (class_exists($message_class)) {
            $message = new $message_class;
            $this->message = $message;
        } else {
            $this->error = "unknown_message";
        }
    }

    public function error()
    {
        return $this->error;
    }

    public function subject()
    {
        return $this->message->subject();
    }

    public function body()
    {
        return $this->message->body();
    }
}
