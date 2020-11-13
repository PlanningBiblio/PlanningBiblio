<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

require_once(__DIR__.'/../../public/include/db.php');

/**
 * Auto-generated Migration: Please modify to your needs!
 */

final class Version20201113152728 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'This is a test';
    }

    public function up(Schema $schema) : void
    {
        $this->getDescription();
        $this->addSql('CREATE TABLE example_table (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql("UPDATE config SET valeur='20.11.00.002' WHERE nom='Version'");
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE example_table');
        $this->addSql("UPDATE config SET valeur='20.11.00.001' WHERE nom='Version'");
    }
}
