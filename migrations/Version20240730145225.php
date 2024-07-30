<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240730145225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE book_variant (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', enable TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE book_variant_book (book_variant_id INT NOT NULL, book_id INT NOT NULL, INDEX IDX_7DFCFFFCA4C4E31C (book_variant_id), INDEX IDX_7DFCFFFC16A2B381 (book_id), PRIMARY KEY(book_variant_id, book_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE book_variant_book ADD CONSTRAINT FK_7DFCFFFCA4C4E31C FOREIGN KEY (book_variant_id) REFERENCES book_variant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE book_variant_book ADD CONSTRAINT FK_7DFCFFFC16A2B381 FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book_variant_book DROP FOREIGN KEY FK_7DFCFFFCA4C4E31C');
        $this->addSql('ALTER TABLE book_variant_book DROP FOREIGN KEY FK_7DFCFFFC16A2B381');
        $this->addSql('DROP TABLE book_variant');
        $this->addSql('DROP TABLE book_variant_book');
    }
}
