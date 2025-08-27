<?php

declare(strict_types=1);

namespace <namespace>;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class <className> extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

//         $this->addSql("CREATE TABLE IF NOT EXISTS {$dbprefix}table_name (id int(11) NOT NULL AUTO_INCREMENT, ..., PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
//         $this->addSql("INSERT IGNORE INTO {$dbprefix}table_name ...");
//         $this->addSql("ALTER TABLE {$dbprefix}table_name ADD COLUMN IF NOT EXISTS  ...");

    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

//         $this->addSql("DROP TABLE IF EXISTS {$dbprefix}table_name;");
//         $this->addSql("DELETE FROM {$dbprefix}table_name WHERE ...");
//         $this->addSql("ALTER TABLE {$dbprefix}table_name DROP COLUMN IF EXISTS  ...");

    }
}
