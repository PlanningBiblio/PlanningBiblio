<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251205155710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ICS-Delay technical option';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `technical`, `ordre`) VALUES ('ICS-Delay', 'text', '1', 'ICS', 'Délai entre chaque requête ICS lors de l\'import', 1, '71');");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("DELETE FROM `{$dbprefix}config` WHERE nom='ICS-Delay' LIMIT 1;");
    }
}
