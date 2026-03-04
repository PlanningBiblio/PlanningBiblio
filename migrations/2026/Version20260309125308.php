<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260309125308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Decode HTML entities in menu.titre';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $menu_entries = $this->connection->fetchAllAssociative("SELECT id, titre FROM {$dbprefix}menu");
        foreach ($menu_entries as $menu_entry) {
            $decoded_title = html_entity_decode($menu_entry['titre'], ENT_QUOTES|ENT_HTML5, 'UTF-8');
            if ($decoded_title !== $menu_entry['titre']) {
                $this->addSql("UPDATE {$dbprefix}menu SET titre = ? WHERE id = ?", [$decoded_title, $menu_entry['id']]);
            }
        }
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $menu_entries = $this->connection->fetchAllAssociative("SELECT id, titre FROM {$dbprefix}menu");
        foreach ($menu_entries as $menu_entry) {
            $encoded_title = htmlentities($menu_entry['titre'], ENT_QUOTES|ENT_SUBSTITUTE|ENT_HTML5, 'UTF-8');
            if ($encoded_title !== $menu_entry['titre']) {
                $this->addSql("UPDATE {$dbprefix}menu SET titre = ? WHERE id = ?", [$encoded_title, $menu_entry['id']]);
            }
        }
    }
}
