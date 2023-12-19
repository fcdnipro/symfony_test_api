<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231218204757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

        public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE book_author (book_id integer NOT NULL, author_id integer NOT NULL, PRIMARY KEY(book_id, author_id))');
        $this->addSql('ALTER TABLE book_author ADD CONSTRAINT FK_A5A2D5317B00758 FOREIGN KEY (book_id) REFERENCES book (id)');
        $this->addSql('ALTER TABLE book_author ADD CONSTRAINT FK_A5A2D531716A2B41 FOREIGN KEY (author_id) REFERENCES author (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
    }
}
