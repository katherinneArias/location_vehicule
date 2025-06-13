<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250612154012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Corrige el campo vehicule_id en commentaire y lo hace NOT NULL';
    }

    public function up(Schema $schema): void
    {
        // Eliminar temporalmente la foreign key (usa el nombre correcto desde tu base de datos)
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC4A4A3511');

        // Modificar la columna (hacerla NOT NULL)
        $this->addSql('ALTER TABLE commentaire CHANGE vehicule_id vehicule_id INT NOT NULL');

        // Volver a agregar la foreign key
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC4A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id) ON DELETE CASCADE');

        // Si quieres renombrar el índice de autor, mantenlo (opcional)
        $this->addSql('ALTER TABLE commentaire RENAME INDEX auteur_id TO IDX_67F068BC60BB6FE6');
    }

    public function down(Schema $schema): void
    {
        // Eliminar la foreign key agregada
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC4A4A3511');

        // Revertir la columna a NULLABLE
        $this->addSql('ALTER TABLE commentaire CHANGE vehicule_id vehicule_id INT DEFAULT NULL');

        // Restaurar la foreign key
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC4A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id) ON DELETE CASCADE');

        // Restaurar nombre de índice original (opcional)
        $this->addSql('ALTER TABLE commentaire RENAME INDEX IDX_67F068BC60BB6FE6 TO auteur_id');
    }
}