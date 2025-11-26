<?php

namespace App\Entity;

use App\Enum\Statut;
use App\Repository\TrajetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ORM\Entity(repositoryClass: TrajetRepository::class)]
class Trajet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $heureDepart = null;

    #[ORM\Column(length: 100)]
    private ?string $lieuDepart = null;


    #[ORM\Column(length: 100)]
    private ?string $lieuArrivee = null;



    #[ORM\Column(enumType: Statut::class)]
    private ?Statut $statut = null;

    #[ORM\Column]
    private ?int $prixPersonne = null;

    #[ORM\ManyToOne(inversedBy: 'trajets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Voiture $voiture = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'trajets')]
    private Collection $participants;

    #[ORM\Column]
    private ?int $dureeInSeconds = null;

    #[ORM\ManyToOne(inversedBy: 'trajetsAsChauffeur')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $chauffeur = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'trajet', orphanRemoval: true)]
    private Collection $reservations;

    /**
     * @var Collection<int, Avis>
     */
    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'trajet')]
    private Collection $avis;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->avis = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHeureDepart(): ?\DateTimeInterface
    {
        return $this->heureDepart;
    }

    public function setHeureDepart(\DateTimeInterface $heureDepart): static
    {
        $this->heureDepart = $heureDepart;

        return $this;
    }

    public function getLieuDepart(): ?string
    {
        return $this->lieuDepart;
    }

    public function setLieuDepart(string $lieuDepart): static
    {
        $this->lieuDepart = $lieuDepart;

        return $this;
    }

    public function getLieuArrivee(): ?string
    {
        return $this->lieuArrivee;
    }

    public function setLieuArrivee(string $lieuArrivee): static
    {

        $this->lieuArrivee = $lieuArrivee;

        return $this;
    }

    public function getStatut(): ?Statut
    {
        // $currentTime = new \DateTime();
        // $heureDepart = $this->getHeureDepart(); // DateTimeImmutable
        // $duree = $this->getDureeInSeconds(); // int, durée en secondes
        // $arrivalDateTimeStamp = $heureDepart->getTimestamp() + $duree;
        // $arrivalDateTime = new \DateTime();
        // $arrivalDateTime->setTimestamp($heureDepart->getTimestamp() + 86400)->setTimezone($currentTime->getTimezone());

        // if ($currentTime > $arrivalDateTime) {
        //     $statut = Statut::Termine;
        // } else if ($currentTime > $heureDepart) {
        //     $statut = Statut::EnCours;
        // } else {
        //     $statut = Statut::Planifie;
        // }

        return $this->statut;
    }

    public function setStatut(Statut $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getPrixPersonne(): ?int
    {
        return $this->prixPersonne;
    }

    public function setPrixPersonne(int $prixPersonne): static
    {
        $this->prixPersonne = $prixPersonne;

        return $this;
    }

    public function getVoiture(): ?Voiture
    {
        return $this->voiture;
    }

    public function setVoiture(?Voiture $voiture): static
    {
        $this->voiture = $voiture;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function getPlacesRestante() {
        $result = $this->getVoiture()->getPlaces() -  1; //on enlève le chauffeur

        $reservations = $this->getReservations();

        foreach ($reservations as $reservation) {
            $result -= $reservation->getNbPlaces();
        }


        return $result;
    }

    public function getDureeInSeconds(): ?int
    {
        return $this->dureeInSeconds;
    }

    public function setDureeInSeconds(int $dureeInSeconds): static
    {
        $this->dureeInSeconds = $dureeInSeconds;

        return $this;
    }

    public function getChauffeur(): ?User
    {
        return $this->chauffeur;
    }

    public function setChauffeur(?User $chauffeur): static
    {
        $this->chauffeur = $chauffeur;

        return $this;
    }

    

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setTrajet($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getTrajet() === $this) {
                $reservation->setTrajet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Avis>
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): static
    {
        if (!$this->avis->contains($avi)) {
            $this->avis->add($avi);
            $avi->setTrajet($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getTrajet() === $this) {
                $avi->setTrajet(null);
            }
        }

        return $this;
    }
}
