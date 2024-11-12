<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240819114712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reading_list_book (id INT AUTO_INCREMENT NOT NULL, book_id INT DEFAULT NULL, reading_list_id INT DEFAULT NULL, status VARCHAR(50) NOT NULL, INDEX IDX_451C380916A2B381 (book_id), INDEX IDX_451C3809793785BE (reading_list_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reading_list_book ADD CONSTRAINT FK_451C380916A2B381 FOREIGN KEY (book_id) REFERENCES book (id)');
        $this->addSql('ALTER TABLE reading_list_book ADD CONSTRAINT FK_451C3809793785BE FOREIGN KEY (reading_list_id) REFERENCES reading_list (id)');
        $this->addSql('ALTER TABLE reading_list DROP status');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reading_list_book DROP FOREIGN KEY FK_451C380916A2B381');
        $this->addSql('ALTER TABLE reading_list_book DROP FOREIGN KEY FK_451C3809793785BE');
        $this->addSql('DROP TABLE reading_list_book');
        $this->addSql('ALTER TABLE reading_list ADD status VARCHAR(50) NOT NULL');
    }
}
