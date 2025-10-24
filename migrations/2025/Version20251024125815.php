<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251024125815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE {$dbprefix}cron SET command='app:workinghour:daily' WHERE command='cron.planning_hebdo_daily.php';");
        $this->addSql("UPDATE {$dbprefix}cron SET command='app:holiday:reset:remainder' WHERE command='cron.holiday_reset_remainder.php';");
        $this->addSql("UPDATE {$dbprefix}cron SET command='app:holiday:reset:credits' WHERE command='cron.holiday_reset_credits.php';");
        $this->addSql("UPDATE {$dbprefix}cron SET command='app:holiday:reset:comp-time' WHERE command='cron.holiday_reset_comp_time.php';");
        $this->addSql("INSERT INTO {$dbprefix}cron (m, h, dom, mon, dow, command, comments, last, disabled) VALUES
            ('30','3','','','*','app:absence:delete-documents','Remove old documents depending the configuration',null,1),
            ('0','0','','','*','app:absence:import-csv','',null,1),
            ('15','6-23','','','*','app:absence:import-ics','Import absences from ICS flows',null,1),
            ('0','0','','','*','app:holiday:reminder','',null,1),
            ('0','0','','','*','app:import:ms-graph-calendar','',null,1),
            ('0','0','','','*','app:planning:control','',null,1),
            ('0','0','','','*','app:purge:data','',null,1),
            ('0','0','','','*','app:purge:log-table','',null,1),
            ('0','0','','','*','app:update-db','',null,1),
            ('0','0','','','*','app:workinghour:export','',null,1);
        ");
        
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE {$dbprefix}cron SET command='cron.planning_hebdo_daily.php' WHERE command='app:workinghour:daily';");
        $this->addSql("UPDATE {$dbprefix}cron SET command='cron.holiday_reset_remainder.php' WHERE command='app:holiday:reset:remainder';");
        $this->addSql("UPDATE {$dbprefix}cron SET command='cron.holiday_reset_credits.php' WHERE command='app:holiday:reset:credits';");
        $this->addSql("UPDATE {$dbprefix}cron SET command='cron.holiday_reset_comp_time.php' WHERE command='app:holiday:reset:comp-time';");
        $this->addSql("DELETE FROM {$dbprefix}cron WHERE command IN (
            'app:absence:delete-documents',
            'app:absence:import-csv',
            'app:absence:import-ics',
            'app:holiday:reminder',
            'app:import:ms-graph-calendar',
            'app:planning:control',
            'app:purge:data',
            'app:purge:log-table',
            'app:update-db',
            'app:workinghour:export'
        );");
    }
}
