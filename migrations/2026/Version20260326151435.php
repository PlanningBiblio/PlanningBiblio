<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260326151435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT52775: Change planning_hebdo/breaktime and planning_hebdo/temps to JSON';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE `{$dbprefix}planning_hebdo` SET `breaktime` = '[]' WHERE `breaktime` IN ('', 'null');");
        $this->addSql("UPDATE `{$dbprefix}planning_hebdo` SET `temps` = '[]' WHERE `temps` = '';");
        $this->addSql("ALTER TABLE `{$dbprefix}planning_hebdo` MODIFY `breaktime` JSON NOT NULL DEFAULT '[]';");
        $this->addSql("ALTER TABLE `{$dbprefix}planning_hebdo` MODIFY `temps` JSON NOT NULL DEFAULT '[]';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}planning_hebdo` MODIFY `breaktime` MEDIUMTEXT NOT NULL;");
        $this->addSql("ALTER TABLE `{$dbprefix}planning_hebdo` MODIFY `temps` MEDIUMTEXT NOT NULL;");
        $this->addSql("UPDATE `{$dbprefix}planning_hebdo` SET `breaktime` = '' WHERE `breaktime` = '[]';");
        $this->addSql("UPDATE `{$dbprefix}planning_hebdo` SET `temps` = '' WHERE `temps` = '[]';");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
