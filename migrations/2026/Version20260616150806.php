<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260616150806 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT46629: Update the Auth-PasswordLength parameter type from text to number';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];
        $this->addSql("UPDATE `{$dbprefix}config` SET `type` = 'number', valeurs = '{\"min\":8,\"max\":100,\"step\":1}' WHERE `nom` = 'Auth-PasswordLength';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];
        $this->addSql("UPDATE `{$dbprefix}config` SET `type` = 'text', valeurs = '' WHERE `nom` = 'Auth-PasswordLength';");
    }
}
