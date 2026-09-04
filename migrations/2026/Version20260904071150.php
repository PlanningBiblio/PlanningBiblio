<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20260904071150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Release 26.04.08';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE {$dbprefix}config SET valeur = '26.04.08' WHERE nom = 'Version';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE {$dbprefix}config SET valeur = '26.04.07' WHERE nom = 'Version';");
    }

}
