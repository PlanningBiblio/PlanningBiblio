<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260429101149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT46629: Reorder absence menu';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE `{$dbprefix}menu` SET `niveau2` = 22 WHERE `url` = '/absence/import';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE `{$dbprefix}menu` SET `niveau2` = 25 WHERE `url` = '/absence/import';");
    }
}
