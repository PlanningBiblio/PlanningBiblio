<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251024125815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Inserts default Symfony commands for scheduled tasks.';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("
            ALTER TABLE {$dbprefix}cron
            MODIFY m   VARCHAR(32) NOT NULL,
            MODIFY h   VARCHAR(32) NOT NULL;
        ");
        $this->addSql("INSERT INTO {$dbprefix}cron (m, h, dom, mon, dow, command, comments, last, disabled) VALUES
            ('0','6','*','*','*','app:absence:delete-documents','Remove old documents depending the configuration',null,1),
            ('*/15','6-23','*','*','*','app:absence:import-csv','Import absences from a CSV file',null,1),
            ('*/15','6-23','*','*','*','app:absence:import-ics','Import absences from ICS flows',null,1),
            ('0','8','*','*','*','app:holiday:reminder','Send reminders for holidays to be validated',null,1),
            ('*/15','6-23','*','*','*','app:import:ms-graph-calendar','Import calendars from Microsoft Graph API',null,1),
            ('0','8','*','*','*','app:planning:control','Check upcoming schedules and sends a report to the planning team',null,1),
            ('0','5','1','1','*','app:purge:data','Purge Planno old data',null,1),
            ('30','6','*','*','*','app:purge:log-table','Purge Planno log table',null,1),
            ('30','7','*','*','*','app:update-db','Update database',null,1),
            ('30','8','*','*','*','app:workinghour:export','Export working hours to a CSV file',null,1);
        ");
        
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];
        
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
        $this->addSql("
            ALTER TABLE {$dbprefix}cron
            MODIFY m   varchar(2) NULL,
            MODIFY h   varchar(2) NULL;
        ");
    }
}
