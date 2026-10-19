<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261019202911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename the columns of the information table into English';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("RENAME TABLE {$dbprefix}infos TO {$dbprefix}information;");
        $this->addSql("ALTER TABLE {$dbprefix}information CHANGE debut start DATE NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}information CHANGE fin end DATE NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}information CHANGE texte text TEXT NOT NULL DEFAULT '';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}information CHANGE start debut DATE NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}information CHANGE end fin DATE NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}information CHANGE text texte TEXT NULL DEFAULT NULL;");
        $this->addSql("RENAME TABLE {$dbprefix}information TO {$dbprefix}infos;");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
