<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250827085945 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'First migration created to avoid the error The version "latest" couldn\'t be reached';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("SELECT valeur FROM {$dbprefix}config WHERE nom = 'Version';");
    }

    public function down(Schema $schema): void
    {
    }
}
