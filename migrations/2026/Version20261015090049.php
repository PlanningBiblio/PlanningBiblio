<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261015090049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'InnoDB: convert user_tokens table to InnoDB';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE IF EXISTS `{$dbprefix}user_tokens` ENGINE=InnoDB;");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE IF EXISTS `{$dbprefix}user_tokens` ENGINE=MyISAM;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
