<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261028101104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT53228: Add column absences_documents.storage_id';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}absences_documents ADD COLUMN IF NOT EXISTS storage_id VARCHAR(255) NULL DEFAULT NULL AFTER filename;");
        $this->addSql("UPDATE {$dbprefix}absences_documents SET storage_id = filename WHERE storage_id IS NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}absences_documents MODIFY COLUMN storage_id VARCHAR(255) NOT NULL DEFAULT '';");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Reverting this migration would require moving files back to their original location');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
