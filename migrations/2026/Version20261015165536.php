<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261015165536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a specific access right for the calendar view.';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("INSERT IGNORE INTO `{$dbprefix}acces` (`nom`, `groupe_id`, `groupe`, `page`, `ordre`, `categorie`) VALUES ('Accès à la vue calendaire', 1501, 'Accès à la vue calendaire', '', 70, 'Absences');");
        $this->addSql("UPDATE `{$dbprefix}menu` SET `condition` = 'acl=1501' WHERE `url` = '/absence/calendar/view';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("DELETE FROM `{$dbprefix}acces` WHERE `nom` = 'Accès à la vue calendaire';");
        $this->addSql("UPDATE `{$dbprefix}menu` SET `condition` = NULL WHERE `url` = '/absence/calendar/view';");
    }
}
