<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310081813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT52356: Remove HTML entities from select_categories';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE `{$dbprefix}select_categories` SET `valeur` = REPLACE(`valeur`, '&eacute;', 'é');");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE `{$dbprefix}select_categories` SET `valeur` = REPLACE(`valeur`, 'é', '&eacute;');");
    }
}
