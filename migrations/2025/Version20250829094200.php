<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250829094200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop unused tables conges_CET & pl_poste_history';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("DROP TABLE IF EXISTS {$dbprefix}conges_CET;");
        $this->addSql("DROP TABLE IF EXISTS {$dbprefix}pl_poste_history;");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Can not rollback DROP TABLE conges_CET / pl_poste_history');
    }
}
