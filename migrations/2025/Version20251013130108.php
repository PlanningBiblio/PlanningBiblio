<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251013130108 extends AbstractMigration
{

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("
            DELETE c FROM {$dbprefix}cron c
            JOIN (
                SELECT command, MIN(id) AS keep_id
                FROM {$dbprefix}cron
                GROUP BY command
            ) k ON k.command = c.command
            WHERE c.id <> k.keep_id
        ");

        $this->addSql("UPDATE {$dbprefix}cron SET command='app:workinghour:daily' WHERE command='cron.planning_hebdo_daily.php';");
        $this->addSql("UPDATE {$dbprefix}cron SET command='app:holiday:reset:remainder' WHERE command='cron.holiday_reset_remainder.php';");
        $this->addSql("UPDATE {$dbprefix}cron SET command='app:holiday:reset:credits' WHERE command='cron.holiday_reset_credits.php';");
        $this->addSql("UPDATE {$dbprefix}cron SET command='app:holiday:reset:comp-time' WHERE command='cron.holiday_reset_comp_time.php';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE {$dbprefix}cron SET command='cron.planning_hebdo_daily.php' WHERE command='app:workinghour:daily';");
        $this->addSql("UPDATE {$dbprefix}cron SET command='cron.holiday_reset_remainder.php' WHERE command='app:holiday:reset:remainder';");
        $this->addSql("UPDATE {$dbprefix}cron SET command='cron.holiday_reset_credits.php' WHERE command='app:holiday:reset:credits';");
        $this->addSql("UPDATE {$dbprefix}cron SET command='cron.holiday_reset_comp_time.php' WHERE command='app:holiday:reset:comp-time';");
    }
}
