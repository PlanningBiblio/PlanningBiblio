<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260330112513 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT50936: Reset planning_hebdo/actuel from WorkingHour::index';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("DELETE FROM `{$dbprefix}cron` WHERE `command` = 'app:workinghour:daily';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("INSERT IGNORE INTO `{$dbprefix}cron` (`m`, `h`, `dom`, `mon`, `dow`, `name`, `command`, `comments`, `last`, `disabled`) VALUES 
            ('0', '0', '*', '*', '*', 'workingHourDaily', 'app:workinghour:daily', 'Daily Cron for Planning Hebdo module', '0000-00-00 00:00:00', 0);");
    }

}
