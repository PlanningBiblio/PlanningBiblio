<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260326143620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT52775: Set default values for planning_hebdo';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE `{$dbprefix}planning_hebdo` SET `nb_semaine` = 1 WHERE `nb_semaine` IS NULL;");
        $this->addSql("ALTER TABLE `{$dbprefix}planning_hebdo` MODIFY `debut` DATE NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE `{$dbprefix}planning_hebdo` MODIFY `fin` DATE NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE `{$dbprefix}planning_hebdo` MODIFY `nb_semaine` INT(11) NOT NULL DEFAULT 1;");
        $this->addSql("ALTER TABLE `{$dbprefix}planning_hebdo` MODIFY `perso_id` INT(11) NOT NULL DEFAULT 0;");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}planning_hebdo` MODIFY `debut` DATE NOT NULL;");
        $this->addSql("ALTER TABLE `{$dbprefix}planning_hebdo` MODIFY `fin` DATE NOT NULL;");
        $this->addSql("ALTER TABLE `{$dbprefix}planning_hebdo` MODIFY `nb_semaine` INT(11) NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE `{$dbprefix}planning_hebdo` MODIFY `perso_id` INT(11) NOT NULL;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
