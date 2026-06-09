<?php

namespace App\Planno\Notifier\Messages;

class create_account
{
    private $subject = 'Création de compte';

    private $body = 'Votre compte Planno a été créé :
            <ul><li>Login : %login</li><li>Mot de passe : %password</li></ul>';

    public function subject()
    {
        return $this->subject;
    }

    public function body()
    {
        return $this->body;
    }
}
