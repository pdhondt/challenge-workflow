<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210510141547 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket ADD user_created_id INT NOT NULL, ADD assigned_agent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3F987D8A8 FOREIGN KEY (user_created_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA349197702 FOREIGN KEY (assigned_agent_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_97A0ADA3F987D8A8 ON ticket (user_created_id)');
        $this->addSql('CREATE INDEX IDX_97A0ADA349197702 ON ticket (assigned_agent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3F987D8A8');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA349197702');
        $this->addSql('DROP INDEX IDX_97A0ADA3F987D8A8 ON ticket');
        $this->addSql('DROP INDEX IDX_97A0ADA349197702 ON ticket');
        $this->addSql('ALTER TABLE ticket DROP user_created_id, DROP assigned_agent_id');
    }
}
