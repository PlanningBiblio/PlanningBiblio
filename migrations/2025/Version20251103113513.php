<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251103113513 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE {$dbprefix}cron SET command = 'app:holiday:reset:comp-time --force' WHERE command = 'app:holiday:reset:comp-time';");
        $this->addSql("UPDATE {$dbprefix}cron SET command = 'app:holiday:reset:credits --force' WHERE command = 'app:holiday:reset:credits';");
        $this->addSql("UPDATE {$dbprefix}cron SET command = 'app:holiday:reset:remainder --force' WHERE command = 'app:holiday:reset:remainder';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE {$dbprefix}cron SET command = 'app:holiday:reset:comp-time' WHERE command = 'app:holiday:reset:comp-time --force';");
        $this->addSql("UPDATE {$dbprefix}cron SET command = 'app:holiday:reset:credits' WHERE command = 'app:holiday:reset:credits --force';");
        $this->addSql("UPDATE {$dbprefix}cron SET command = 'app:holiday:reset:remainder' WHERE command = 'app:holiday:reset:remainder --force';");
    }
}
