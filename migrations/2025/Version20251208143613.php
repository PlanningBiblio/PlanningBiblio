<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20251208143613 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix postes.categories invalid values';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE {$dbprefix}postes SET categories = '[]' WHERE categories = '0'");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Can not rollback this migration.');
    }
}
