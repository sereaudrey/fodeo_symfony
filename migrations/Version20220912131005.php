<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220912131005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movies ADD qrcode_url VARCHAR(250) DEFAULT NULL, CHANGE type type VARCHAR(20) NOT NULL, CHANGE genre genre VARCHAR(50) DEFAULT NULL, CHANGE date_sortie date_sortie DATE DEFAULT NULL, CHANGE synopsis synopsis VARCHAR(512) NOT NULL, CHANGE note note VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movies DROP qrcode_url, CHANGE type type VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, CHANGE genre genre VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, CHANGE date_sortie date_sortie VARCHAR(10) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, CHANGE synopsis synopsis LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, CHANGE note note NUMERIC(2, 1) DEFAULT NULL');
    }
}
