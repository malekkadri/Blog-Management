<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240322084807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post_favorites (post_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_45071E024B89032C (post_id), INDEX IDX_45071E02A76ED395 (user_id), PRIMARY KEY(post_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE post_favorites ADD CONSTRAINT FK_45071E024B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_favorites ADD CONSTRAINT FK_45071E02A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post_favorites DROP FOREIGN KEY FK_45071E024B89032C');
        $this->addSql('ALTER TABLE post_favorites DROP FOREIGN KEY FK_45071E02A76ED395');
        $this->addSql('DROP TABLE post_favorites');
    }
}
