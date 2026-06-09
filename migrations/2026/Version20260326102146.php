<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260326102146 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT52751: Change hidden_tables:hidden_tables type (JSON) and default value ([])';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE `{$dbprefix}hidden_tables` SET `hidden_tables` = REPLACE(`hidden_tables`, '&#34;', '');");
        $this->addSql("ALTER TABLE `{$dbprefix}hidden_tables` CHANGE `hidden_tables` `hidden_tables` JSON NOT NULL DEFAULT '[]';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}hidden_tables` CHANGE `hidden_tables` `hidden_tables` MEDIUMTEXT NULL DEFAULT NULL;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
