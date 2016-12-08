<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161207135533 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE connection_request ADD type VARCHAR(255) NOT NULL');
        $this->addSql("UPDATE connection_request SET type = 'friend'");
        $this->addSql("UPDATE connection_request SET type = 'music' WHERE music_friend = 1");
        $this->addSql('ALTER TABLE connection_request DROP music_friend');

        $this->addSql('ALTER TABLE fos_user ADD type VARCHAR(255) NOT NULL');
        $this->addSql("UPDATE fos_user SET type = 'friend'");
        $this->addSql("UPDATE fos_user SET type = 'music' WHERE music_friend = 1");
        $this->addSql('ALTER TABLE fos_user DROP music_friend');

        $this->addSql('ALTER TABLE connection ADD type VARCHAR(255) NOT NULL');
        $this->addSql("UPDATE connection SET type = 'friend'");
        $this->addSql("UPDATE connection SET type = 'music' WHERE music_friend = 1");
        $this->addSql('ALTER TABLE connection DROP music_friend');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');


        $this->addSql('ALTER TABLE connection_request ADD music_friend TINYINT(1) NOT NULL');
        $this->addSql("UPDATE connection_request SET music_friend = 1 WHERE type = 'music'");
        $this->addSql('ALTER TABLE connection_request DROP type');

        $this->addSql('ALTER TABLE fos_user ADD music_friend TINYINT(1) NOT NULL');
        $this->addSql("UPDATE fos_user SET music_friend = 1 WHERE type = 'music'");
        $this->addSql('ALTER TABLE fos_user DROP type');

        $this->addSql('ALTER TABLE connection ADD music_friend TINYINT(1) NOT NULL');
        $this->addSql("UPDATE connection SET music_friend = 1 WHERE type = 'music'");
        $this->addSql('ALTER TABLE connection DROP type');
    }
}
