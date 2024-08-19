<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240819113759 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reading_list_book DROP FOREIGN KEY FK_451C380916A2B381');
        $this->addSql('ALTER TABLE reading_list_book DROP FOREIGN KEY FK_451C3809793785BE');
        $this->addSql('DROP TABLE reading_list_book');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reading_list_book (reading_list_id INT NOT NULL, book_id INT NOT NULL, INDEX IDX_451C3809793785BE (reading_list_id), INDEX IDX_451C380916A2B381 (book_id), PRIMARY KEY(reading_list_id, book_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE reading_list_book ADD CONSTRAINT FK_451C380916A2B381 FOREIGN KEY (book_id) REFERENCES book (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reading_list_book ADD CONSTRAINT FK_451C3809793785BE FOREIGN KEY (reading_list_id) REFERENCES reading_list (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
