<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211190004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT50832_Agent: Populate ICS codes in the personnel table';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $this->addSql("UPDATE {$dbprefix}personnel SET `code_ics` = LEFT(MD5(RAND()), 32) WHERE `code_ics` = '' OR `code_ics` IS NULL;");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('We cannot undo the populating of ICS codes in the personnel table.');
    }
}
