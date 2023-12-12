<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231121085040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE xmlelement ADD parent_code VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE xmlelement DROP parent_id');
        $this->addSql('ALTER TABLE xmlelement ALTER code DROP NOT NULL');
        $this->addSql('ALTER TABLE xmlelement ALTER content DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE xmlelement ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE xmlelement DROP parent_code');
        $this->addSql('ALTER TABLE xmlelement ALTER code SET NOT NULL');
        $this->addSql('ALTER TABLE xmlelement ALTER content SET NOT NULL');
    }
}
