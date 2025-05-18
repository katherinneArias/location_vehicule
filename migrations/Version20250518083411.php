<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250518083411 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B784184A4A3511');
        $this->addSql('DROP TABLE photo');
        $this->addSql('ALTER TABLE vehicule ADD photos VARCHAR(255) DEFAULT NULL, CHANGE marque marque VARCHAR(255) NOT NULL, CHANGE modele modele VARCHAR(255) NOT NULL, CHANGE immatriculation immatriculation VARCHAR(255) NOT NULL, CHANGE prix_par_jour prix_par_jour DOUBLE PRECISION NOT NULL, CHANGE couleur couleur VARCHAR(255) NOT NULL, CHANGE poids poids DOUBLE PRECISION NOT NULL, CHANGE disponible disponible TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE photo (id INT AUTO_INCREMENT NOT NULL, vehicule_id INT DEFAULT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_14B784184A4A3511 (vehicule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B784184A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE vehicule DROP photos, CHANGE marque marque VARCHAR(100) DEFAULT NULL, CHANGE modele modele VARCHAR(100) DEFAULT NULL, CHANGE immatriculation immatriculation VARCHAR(20) DEFAULT NULL, CHANGE prix_par_jour prix_par_jour DOUBLE PRECISION DEFAULT NULL, CHANGE couleur couleur VARCHAR(50) DEFAULT NULL, CHANGE poids poids DOUBLE PRECISION DEFAULT NULL, CHANGE disponible disponible TINYINT(1) DEFAULT NULL');
    }
}
