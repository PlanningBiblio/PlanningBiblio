<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211190005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT50832_Agent: Remove HTML entities in table "personnel", field "actif"';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE `{$dbprefix}personnel` SET `actif` = 'Supprimé' WHERE `actif` LIKE 'Supprim%';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE `{$dbprefix}personnel` SET `actif` = 'Supprim&eacute;' WHERE `actif` = 'Supprimé';");
    }
}
