<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261008145142 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix the `personnel` table: set the `supprime` field to 1 when it is currently 0, despite the agent having been deleted.';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE {$dbprefix}personnel SET supprime='1' WHERE supprime='0' AND actif Like 'Supprim%';");
    }

    public function down(Schema $schema): void
    {
    }
}
