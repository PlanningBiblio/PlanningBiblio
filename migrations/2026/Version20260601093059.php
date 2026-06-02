<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260601093059 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration de la table config pour supporter des configurations spécifiques par réseau';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("CREATE TABLE IF NOT EXISTS `{$dbprefix}config_network` (
            id int(11) NOT NULL AUTO_INCREMENT,
            config_id int(11) NOT NULL,
            network_id int(20) NOT NULL DEFAULT '1',
            value text NOT NULL,
            PRIMARY KEY (`id`),
             KEY `network_id` (`network_id`),
             KEY `config_id` (`config_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->addSql("CREATE TABLE IF NOT EXISTS `{$dbprefix}config_technical` (
            id int(11) NOT NULL AUTO_INCREMENT,
            config_id int(11) NOT NULL,
            value text NOT NULL,
            PRIMARY KEY (`id`),
            KEY `config_id` (`config_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->addSql("UPDATE `{$dbprefix}config` SET `technical` = 1 WHERE `nom` IN ('Version', 'URL')");
        $this->addSql("INSERT INTO `{$dbprefix}config_technical` (`config_id`, `value`) SELECT id, valeur FROM `{$dbprefix}config` WHERE technical = 1");
        $this->addSql("INSERT INTO `{$dbprefix}config_network` (`config_id`, `network_id`, `value`) SELECT id, 1, valeur FROM `{$dbprefix}config` WHERE technical = 0");
        $this->addSql("ALTER TABLE `{$dbprefix}config` DROP COLUMN `valeur`");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("DROP TABLE IF EXISTS `{$dbprefix}network_config`");
        $this->addSql("DROP TABLE IF EXISTS `{$dbprefix}network_config`");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
