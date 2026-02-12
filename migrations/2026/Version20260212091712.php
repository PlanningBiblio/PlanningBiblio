<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260212091712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE {$dbprefix}hidden_tables SET hidden_tables = REPLACE(hidden_tables, '&#34;', '') WHERE hidden_tables LIKE '%&#34;%';)");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE {$dbprefix}hidden_tables SET hidden_tables = CONCAT('[&#34;',REPLACE(REPLACE(REPLACE(hidden_tables, '[', ''),']', ''),',', '&#34;,&#34;'),'&#34;]') WHERE hidden_tables NOT LIKE '%&#34;%';");
    }
}
