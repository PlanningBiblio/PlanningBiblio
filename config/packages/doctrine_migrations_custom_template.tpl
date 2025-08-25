<?php

declare(strict_types=1);

namespace <namespace>;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs using the following instructions!
 * 
 * - Add your migration to the up function, the opposite migration to the down function and a short description to the getDescription function.
 * - Use $dbprefix for all queries. E.g. "CREATE TABLE IF NOT EXISTS {$dbprefix}table_name ..."
 * - Use appropriate SQL conditions when possible. E.g. CREATE TABLE IF NOT EXISTS, ADD COLUMN IF NOT EXISTS, INSERT IGNORE, etc. You can also use the "skipIf" method.
 * - When you are unable to create the down migration, use $this->throwIrreversibleMigrationException(); in the down function with a comment explaining why.
 * - Some commented lines have been added to help you create your migrations using best practices.
 *   When you are done, please delete all commented lines, including this paragraph and except your own.
 * 
 * List of available functions:
 * - addSql : add a new query
 * - abortIf : abord the migration if the condition is met
 * - skipIf : skip the migration if the condition is met
 * - throwIrreversibleMigrationException : in down method only, display a warning message and forbids to run the migration down
 * - warnIf : display a message if the condition is met
 * - write` : display a message
 * 
 * You can also add preUp, preDown, postUp and postDown methods.
 * 
 * See https://symfony.com/bundles/DoctrineMigrationsBundle/current/index.html
 */

final class <className> extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        // $this->addSql("CREATE TABLE IF NOT EXISTS {$dbprefix}table_name (id int(11) NOT NULL AUTO_INCREMENT, ..., PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        // $this->addSql("INSERT IGNORE INTO {$dbprefix}table_name ...");
        // $this->addSql("ALTER TABLE {$dbprefix}table_name ADD COLUMN IF NOT EXISTS  ...");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        // $this->addSql("DROP TABLE IF EXISTS {$dbprefix}table_name;");
        // $this->addSql("DELETE FROM {$dbprefix}table_name WHERE ...");
        // $this->addSql("ALTER TABLE {$dbprefix}table_name DROP COLUMN IF EXISTS  ...");
    }
}
