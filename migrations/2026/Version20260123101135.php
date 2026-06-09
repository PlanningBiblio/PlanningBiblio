<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20260123101135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Release 25.11.14';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE {$dbprefix}config SET valeur = '25.11.14' WHERE nom = 'Version';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE {$dbprefix}config SET valeur = '25.11.13' WHERE nom = 'Version';");
    }

}
