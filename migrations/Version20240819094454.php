<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240819094454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reading_list (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_739E904CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reading_list_book (reading_list_id INT NOT NULL, book_id INT NOT NULL, INDEX IDX_451C3809793785BE (reading_list_id), INDEX IDX_451C380916A2B381 (book_id), PRIMARY KEY(reading_list_id, book_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reading_list ADD CONSTRAINT FK_739E904CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reading_list_book ADD CONSTRAINT FK_451C3809793785BE FOREIGN KEY (reading_list_id) REFERENCES reading_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reading_list_book ADD CONSTRAINT FK_451C380916A2B381 FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reading_list DROP FOREIGN KEY FK_739E904CA76ED395');
        $this->addSql('ALTER TABLE reading_list_book DROP FOREIGN KEY FK_451C3809793785BE');
        $this->addSql('ALTER TABLE reading_list_book DROP FOREIGN KEY FK_451C380916A2B381');
        $this->addSql('DROP TABLE reading_list');
        $this->addSql('DROP TABLE reading_list_book');
    }
}
