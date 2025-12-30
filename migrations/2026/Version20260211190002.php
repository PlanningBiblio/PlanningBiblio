<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211190002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT50832_Agent: Set DateTime fields to null when their value is 0000-00-00 in the personnel table';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE {$dbprefix}personnel SET arrivee = NULL WHERE arrivee <= '1900-01-01 00:00:00'");
        $this->addSql("UPDATE {$dbprefix}personnel SET depart = NULL WHERE depart <= '1900-01-01 00:00:00'");
        $this->addSql("UPDATE {$dbprefix}personnel SET last_login = NULL WHERE last_login <= '1900-01-01 00:00:00'");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];
        
        $this->addSql("UPDATE {$dbprefix}personnel SET arrivee = '0000-00-00' WHERE arrivee IS NULL");
        $this->addSql("UPDATE {$dbprefix}personnel SET depart = '0000-00-00' WHERE depart IS NULL");
        $this->addSql("UPDATE {$dbprefix}personnel SET last_login = '0000-00-00 00:00:00' WHERE last_login IS NULL");
    }

}
