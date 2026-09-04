<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260811150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create messenger_messages table (Symfony Messenger Doctrine transport, used for the async planning generation)';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("CREATE TABLE IF NOT EXISTS `{$dbprefix}messenger_messages` (
            `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `body` LONGTEXT NOT NULL,
            `headers` LONGTEXT NOT NULL,
            `queue_name` VARCHAR(190) NOT NULL,
            `created_at` DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            `available_at` DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            `delivered_at` DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
            PRIMARY KEY (`id`),
            KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`, `available_at`, `delivered_at`, `id`))
            ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("DROP TABLE IF EXISTS `{$dbprefix}messenger_messages`;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
