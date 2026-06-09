<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211230225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add default values to the absences_documents table';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}absences_documents` CHANGE `absence_id` `absence_id` INT(11) NOT NULL DEFAULT 0;");
        $this->addSql("ALTER TABLE `{$dbprefix}absences_documents` CHANGE `filename` `filename` MEDIUMTEXT NOT NULL DEFAULT '';");
        $this->addSql("ALTER TABLE `{$dbprefix}absences_documents` CHANGE `date` `date` DATETIME NULL DEFAULT NULL;");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}absences_documents` CHANGE `absence_id` `absence_id` INT(11) NOT NULL;");
        $this->addSql("ALTER TABLE `{$dbprefix}absences_documents` CHANGE `filename` `filename` MEDIUMTEXT NOT NULL;");
        $this->addSql("ALTER TABLE `{$dbprefix}absences_documents` CHANGE `date` `date` DATETIME NOT NULL;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
