<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261015090016 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'InnoDB: convert ip_blocker table to InnoDB';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE IF EXISTS `{$dbprefix}ip_blocker` ENGINE=InnoDB;");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE IF EXISTS `{$dbprefix}ip_blocker` ENGINE=MyISAM;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
