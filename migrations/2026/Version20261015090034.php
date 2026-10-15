<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261015090034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'InnoDB: convert pl_poste_modeles_tab table to InnoDB';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE IF EXISTS `{$dbprefix}pl_poste_modeles_tab` ENGINE=InnoDB;");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE IF EXISTS `{$dbprefix}pl_poste_modeles_tab` ENGINE=MyISAM;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
