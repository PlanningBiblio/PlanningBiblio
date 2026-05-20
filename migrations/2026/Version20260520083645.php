<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260520083645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT46629: Move /detached page from Planning to Administration';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];
        $this->addSql("UPDATE `{$dbprefix}menu` SET `niveau1` = 50, `niveau2` = 76, `titre` = 'Les agents volants' WHERE `url` = '/detached';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];
        $this->addSql("UPDATE `{$dbprefix}menu` SET `niveau1` = 30, `niveau2` = 90, `titre` = 'Agents volants'  WHERE `url` = '/detached';");
    }
}
