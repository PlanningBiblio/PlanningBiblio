<?php

declare(strict_types=1);

namespace App\Migrations;

use App\Entity\Cron;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\entityManagerInterface;
use Doctrine\Migrations\AbstractMigration;


final class Version20251013130108 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $crons = $entityManager->getRepository(Cron::class)->findAll();

        $tab = [];
        $exclude = [];
        foreach ($crons as $elem){
            if(in_array($elem->getCommand()){
                $exclude[] = $elem;
            }else{
                $tab[] = $elem;
            }
        }

        foreach ($exclude as $elem){
            $this->addSql("DELETE FROM {$dbprefix}cron WHERE id = '{$elem->getId()}';");
        }
        $this->addSql("UPDATE {$dbprefix}cron SET command='' WHERE command='';");
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        // $this->addSql("DROP TABLE IF EXISTS {$dbprefix}table_name;");
        // $this->addSql("DELETE FROM {$dbprefix}table_name WHERE ...");
        // $this->addSql("ALTER TABLE {$dbprefix}table_name DROP COLUMN IF EXISTS  ...");
    }
}
