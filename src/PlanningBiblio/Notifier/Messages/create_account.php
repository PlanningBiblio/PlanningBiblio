<?php

namespace App\PlanningBiblio\Notifier\Messages;

class create_account
{
    private string $subject = 'Création de compte';

    private string $body = 'Votre compte Planno a été créé :
            <ul><li>Login : %login</li><li>Mot de passe : %password</li></ul>';

    public function subject(): string
    {
        return $this->subject;
    }

    public function body(): string
    {
        return $this->body;
    }
}
