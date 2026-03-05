<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313091816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT46629: Change the route to the holiday list';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE `{$dbprefix}menu` SET `url` = '/holiday' WHERE `url` = '/holiday/index';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE `{$dbprefix}menu` SET `url` = '/holiday/index' WHERE `url` = '/holiday';");
    }
}
