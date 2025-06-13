<?php

namespace App\Entity;

use App\Repository\VehiculeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Photo;

#[ORM\Entity(repositoryClass: VehiculeRepository::class)]
class Vehicule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $marque = null;

    #[ORM\Column(length: 255)]
    private ?string $modele = null;

    #[ORM\Column(length: 255)]
    private ?string $immatriculation = null;

    #[ORM\Column(type: 'float')]
    private ?float $prixParJour = null;

    #[ORM\Column(length: 255)]
    private ?string $couleur = null;

    #[ORM\Column(type: 'float')]
    private ?float $poids = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $disponible = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateAjout = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\OneToMany(mappedBy: 'vehicule', targetEntity: Commentaire::class, orphanRemoval: true, cascade: ['remove'])]
    private Collection $commentaires;

    #[ORM\OneToMany(mappedBy: 'vehicule', targetEntity: Reservation::class, cascade: ['remove'])]
    private Collection $reservations;

    #[ORM\OneToMany(mappedBy: 'vehicule', targetEntity: Photo::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $photos;

    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->photos = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getMarque(): ?string { return $this->marque; }
    public function setMarque(string $marque): self { $this->marque = $marque; return $this; }

    public function getModele(): ?string { return $this->modele; }
    public function setModele(string $modele): self { $this->modele = $modele; return $this; }

    public function getImmatriculation(): ?string { return $this->immatriculation; }
    public function setImmatriculation(string $immatriculation): self { $this->immatriculation = $immatriculation; return $this; }

    public function getPrixParJour(): ?float { return $this->prixParJour; }
    public function setPrixParJour(float $prixParJour): self { $this->prixParJour = $prixParJour; return $this; }

    public function getCouleur(): ?string { return $this->couleur; }
    public function setCouleur(string $couleur): self { $this->couleur = $couleur; return $this; }

    public function getPoids(): ?float { return $this->poids; }
    public function setPoids(float $poids): self { $this->poids = $poids; return $this; }

    public function isDisponible(): ?bool { return $this->disponible; }
    public function setDisponible(bool $disponible): self { $this->disponible = $disponible; return $this; }

    public function getDateAjout(): ?\DateTimeImmutable { return $this->dateAjout; }
    public function setDateAjout(?\DateTimeImmutable $dateAjout): self { $this->dateAjout = $dateAjout; return $this; }

    public function getPhoto(): ?string { return $this->photo; }
    public function setPhoto(?string $photo): self { $this->photo = $photo; return $this; }

    public function getCommentaires(): Collection { return $this->commentaires; }

    public function addCommentaire(Commentaire $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires[] = $commentaire;
            $commentaire->setVehicule($this);
        }
        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): self
    {
        if ($this->commentaires->removeElement($commentaire)) {
            if ($commentaire->getVehicule() === $this) {
                $commentaire->setVehicule(null);
            }
        }
        return $this;
    }

    public function getReservations(): Collection { return $this->reservations; }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setVehicule($this);
        }
        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getVehicule() === $this) {
                $reservation->setVehicule(null);
            }
        }
        return $this;
    }

    public function getPhotos(): Collection { return $this->photos; }

    public function addPhoto(Photo $photo): self
    {
        if (!$this->photos->contains($photo)) {
            $this->photos[] = $photo;
            $photo->setVehicule($this);
        }
        return $this;
    }

    public function removePhoto(Photo $photo): self
    {
        if ($this->photos->removeElement($photo)) {
            if ($photo->getVehicule() === $this) {
                $photo->setVehicule(null);
            }
        }
        return $this;
    }
}
