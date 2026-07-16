<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260716160155 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT54305: Allow SQL fallback authentication with OpenIDConnect';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE `{$dbprefix}config` SET `valeurs` = 'SQL,LDAP,LDAP-SQL,CAS,CAS-SQL,OpenIDConnect,OpenIDConnect-SQL' WHERE `nom` = 'Auth-Mode';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE `{$dbprefix}config` SET `valeurs` = 'SQL,LDAP,LDAP-SQL,CAS,CAS-SQL,OpenIDConnect' WHERE `nom` = 'Auth-Mode';");
    }
}
