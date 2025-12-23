<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20251223213418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Release 25.10.10';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE {$dbprefix}config SET valeur = '25.10.10' WHERE nom = 'Version';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE {$dbprefix}config SET valeur = '25.10.09' WHERE nom = 'Version';");
    }

}
