<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250612170724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Corrige les relations entre photo et vehicule, sans recréer les tables.';
    }

    public function up(Schema $schema): void
    {
        // ❗ Ne pas ajouter FK si déjà existe (peut causer duplication)
        // Il vaut mieux gérer manuellement si FK existe déjà

        // --- PHOTO ---
        // Solo agregar la FK si no está ya creada en la base de datos
        // (Puedes hacer esta verificación tú en phpMyAdmin antes de ejecutar)

        // --- COMMENTAIRE ---
        // Si la clé ya ha sido eliminada manualmente, esta línea dará error. Puedes comentarla si es necesario.
        // $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC4A4A3511');

        // Vuelve a crear la clave con un nombre válido
        $this->addSql('
            ALTER TABLE commentaire 
            ADD CONSTRAINT FK_COMMENTAIRE_VEHICULE 
            FOREIGN KEY (vehicule_id) REFERENCES vehicule (id)
        ');

        // Crea el índice si no existe
        $this->addSql('
            CREATE INDEX IDX_COMMENTAIRE_VEHICULE ON commentaire (vehicule_id)
        ');
    }

    public function down(Schema $schema): void
    {
        // Elimina la FK personalizada
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_COMMENTAIRE_VEHICULE');
        $this->addSql('DROP INDEX IDX_COMMENTAIRE_VEHICULE ON commentaire');
    }
}