<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251114074411 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the name column to the cron table';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}cron ADD COLUMN IF NOT EXISTS `name` VARCHAR(30) NOT NULL DEFAULT '' AFTER dow;");
        $this->addSql("UPDATE {$dbprefix}cron SET `name` = 'workingHourDaily' WHERE command = 'cron.planning_hebdo_daily.php';");
        $this->addSql("UPDATE {$dbprefix}cron SET `name` = 'holidayResetRemainder' WHERE command = 'cron.holiday_reset_remainder.php';");
        $this->addSql("UPDATE {$dbprefix}cron SET `name` = 'holidayResetCredit' WHERE command = 'cron.holiday_reset_credits.php';");
        $this->addSql("UPDATE {$dbprefix}cron SET `name` = 'holidayResetCompTime' WHERE command = 'cron.holiday_reset_comp_time.php';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}cron DROP COLUMN IF EXISTS `name`;");
    }
}
