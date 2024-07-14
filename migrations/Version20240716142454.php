<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240716142454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add bookformatss';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE book_format (id INT AUTO_INCREMENT NOT NULL, book_id INT NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_F76D795216A2B381 (book_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE book_format ADD CONSTRAINT FK_F76D795216A2B381 FOREIGN KEY (book_id) REFERENCES book (id)');
        $this->addSql('ALTER TABLE kobo_device DROP FOREIGN KEY FK_42E56EFA76ED395');
        $this->addSql('DROP INDEX idx_42e56efa76ed395 ON kobo_device');
        $this->addSql('CREATE INDEX IDX_2EB06A2BA76ED395 ON kobo_device (user_id)');
        $this->addSql('ALTER TABLE kobo_device ADD CONSTRAINT FK_42E56EFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book_format DROP FOREIGN KEY FK_F76D795216A2B381');
        $this->addSql('DROP TABLE book_format');
        $this->addSql('ALTER TABLE kobo_device DROP FOREIGN KEY FK_2EB06A2BA76ED395');
        $this->addSql('DROP INDEX idx_2eb06a2ba76ed395 ON kobo_device');
        $this->addSql('CREATE INDEX IDX_42E56EFA76ED395 ON kobo_device (user_id)');
        $this->addSql('ALTER TABLE kobo_device ADD CONSTRAINT FK_2EB06A2BA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }
}
