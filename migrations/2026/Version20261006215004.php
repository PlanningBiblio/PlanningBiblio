<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261006215004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename the fields in the menu table to English';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}menu CHANGE niveau1 level1 INT(11) NOT NULL DEFAULT 0;");
        $this->addSql("ALTER TABLE {$dbprefix}menu CHANGE niveau2 level2 INT(11) NOT NULL DEFAULT 0;");
        $this->addSql("ALTER TABLE {$dbprefix}menu CHANGE titre title VARCHAR(100) NOT NULL DEFAULT '';");
        $this->addSql("ALTER TABLE {$dbprefix}menu CHANGE url url VARCHAR(100) NOT NULL DEFAULT '';");
        $this->addSql("ALTER TABLE {$dbprefix}menu CHANGE `condition` requirement VARCHAR(100) NULL DEFAULT NULL;");
        $this->addSql("UPDATE {$dbprefix}menu SET url = SUBSTR(url, 2);");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("ALTER TABLE {$dbprefix}menu CHANGE level1 niveau1 INT(11) NOT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}menu CHANGE level2 niveau2 INT(11) NOT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}menu CHANGE title titre VARCHAR(100) NOT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}menu CHANGE url url VARCHAR(500) NOT NULL;");
        $this->addSql("ALTER TABLE {$dbprefix}menu CHANGE requirement `condition` VARCHAR(100) NULL DEFAULT NULL;");
        $this->addSql("UPDATE {$dbprefix}menu SET url = CONCAT('/', url);");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
