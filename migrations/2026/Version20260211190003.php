<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211190003 extends AbstractMigration

{
    public function getDescription(): string
    {
        return 'MT50832_Agent: Set empty arrays for array typed fields on table personnel when empty';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE {$dbprefix}personnel SET postes = '[]' WHERE postes IS NULL OR postes = '';");
        $this->addSql("UPDATE {$dbprefix}personnel SET droits = '[]' WHERE droits IS NULL OR droits = '';");
        $this->addSql("UPDATE {$dbprefix}personnel SET temps = '[]' WHERE temps IS NULL OR temps = '';");
        $this->addSql("UPDATE {$dbprefix}personnel SET sites = '[]' WHERE sites IS NULL OR sites = '';");
        $this->addSql("UPDATE {$dbprefix}planning_hebdo SET temps = '[]' WHERE temps IS NULL OR temps = '';");
        $this->addSql("UPDATE {$dbprefix}planning_hebdo SET breaktime = '[]' WHERE breaktime IS NULL OR breaktime = '';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE {$dbprefix}personnel SET postes = '' WHERE postes = '[]';");
        $this->addSql("UPDATE {$dbprefix}personnel SET droits = '' WHERE droits = '[]';");
        $this->addSql("UPDATE {$dbprefix}personnel SET temps = '' WHERE temps = '[]';");
        $this->addSql("UPDATE {$dbprefix}personnel SET sites = '' WHERE sites = '[]';");
        $this->addSql("UPDATE {$dbprefix}planning_hebdo SET temps = '' WHERE temps = '[]';");
        $this->addSql("UPDATE {$dbprefix}planning_hebdo SET breaktime = '' WHERE breaktime = '[]';");
    }
}
