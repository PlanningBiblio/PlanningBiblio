<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420162700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT51958: Do not display password in configuration page';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];
        $this->addSql("UPDATE {$dbprefix}config SET commentaires = 'Mot de passe de connexion (ne sera pas modifié si laissé vide)' WHERE nom = 'LDAP-Password' LIMIT 1;");
        $this->addSql("UPDATE {$dbprefix}config SET commentaires = 'Mot de passe pour le serveur SMTP (ne sera pas modifié si laissé vide)' WHERE nom = 'Mail-Password' LIMIT 1;");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];
        $this->addSql("UPDATE {$dbprefix}config SET commentaires = 'Mot de passe de connexion' WHERE nom = 'LDAP-Password' LIMIT 1;");
        $this->addSql("UPDATE {$dbprefix}config SET commentaires = 'Mot de passe pour le serveur SMTP' WHERE nom = 'Mail-Password' LIMIT 1;");
    }
}
