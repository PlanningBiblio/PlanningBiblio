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

        $this->addSql("UPDATE `{$dbprefix}config` SET `technical` = 1 WHERE `nom` IN ('Version', 'URL', 'LDAP-Password')");
        $this->addSql("DELETE FROM `{$dbprefix}config_technical`");
        $this->addSql("DELETE FROM `{$dbprefix}config_network`");
        $this->addSql("INSERT INTO `{$dbprefix}config_technical` (`config_id`, `value`) SELECT id, valeur FROM `{$dbprefix}config` WHERE technical = 1");
        $this->addSql("INSERT INTO `{$dbprefix}config_network` (`config_id`, `network_id`, `value`) SELECT id, 1, valeur FROM `{$dbprefix}config` WHERE technical = 0");
        $this->addSql("ALTER TABLE `{$dbprefix}config` DROP COLUMN IF EXISTS `valeur`");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE `{$dbprefix}config` ADD COLUMN `valeur` TEXT NOT NULL");
        $this->addSql("UPDATE `{$dbprefix}config` c INNER JOIN `{$dbprefix}config_technical` ct ON ct.config_id = c.id SET c.valeur = ct.value");
        $this->addSql("UPDATE `{$dbprefix}config` c INNER JOIN `{$dbprefix}config_network` cn ON cn.config_id = c.id SET c.valeur = cn.value WHERE cn.network_id = 1");
        $this->addSql("UPDATE `{$dbprefix}config` SET `technical` = 0 WHERE `nom` IN ('Version', 'URL', 'LDAP-Password')");
        $this->addSql("DROP TABLE IF EXISTS `{$dbprefix}config_technical`");
        $this->addSql("DROP TABLE IF EXISTS `{$dbprefix}config_network`");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
