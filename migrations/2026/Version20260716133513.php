<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/*
 * "Lunch" positions can now be counted towards the public service quota.
 * We are making this change only once to ensure consistent statistics for users who do not wish to alter the existing behavior.
 */

final class Version20260716133513 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT52706: Disable the public service quota for lunch positions';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE `{$dbprefix}postes` SET `quota_sp` = 0 WHERE `lunch` = 1;");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Can not rollback this migration.');
    }
}
