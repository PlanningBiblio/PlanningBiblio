<?php

namespace App\PlanningBiblio\Notifier\Messages;

class create_account
{
    private $subject = 'Création de compte';

    private $body = 'Votre compte Planning Biblio a été créé :
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
