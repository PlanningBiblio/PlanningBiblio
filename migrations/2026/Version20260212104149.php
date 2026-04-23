<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260212104149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT50734: Add a web interface to manage the crontab, create menus entries';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("INSERT IGNORE INTO {$dbprefix}menu (niveau1, niveau2, titre, url, `condition`) VALUES (50, 91, 'Ordonnanceur', '/crontab', NULL);");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("DELETE FROM {$dbprefix}menu WHERE titre = 'Ordonnanceur';");
    }

}
