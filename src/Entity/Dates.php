<?php

namespace App\Entity;

use App\Repository\DatesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Dates.
 */
#[ORM\Entity(repositoryClass: DatesRepository::class)]
class Dates
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[Groups('main')]
    #[ORM\Column(type: 'date', nullable: false)]
    private \DateTime $debut;

    #[Groups('main')]
    #[ORM\Column(type: 'date', nullable: false)]
    private \DateTime $fin;

    #[Groups('main')]
    #[ORM\Column(name: 'prix_pilote', type: 'decimal', precision: 10, scale: 2, nullable: false)]
    private float $prixPilote;

    #[Groups('main')]
    #[ORM\Column(name: 'prix_accomp', type: 'decimal', precision: 10, scale: 2, nullable: false)]
    private float $prixAccomp;

    #[Groups('main')]
    #[ORM\Column(length: 15, nullable: false)]
    private ?string $statut;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'date')]
    private Collection $users;

    #[ORM\ManyToOne(targetEntity: Travel::class, inversedBy: 'dates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Travel $travel = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private mixed $requestedDocs = [];

    #[ORM\ManyToMany(targetEntity: Document::class, mappedBy: 'date')]
    private Collection $documents;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'date', cascade: ['persist', 'remove'])]
    private Collection $reservations;

    #[ORM\OneToMany(targetEntity: Oldreservations::class, mappedBy: 'dates')]
    private Collection $oldreservations;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->oldreservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDebut(): ?\DateTimeInterface
    {
        return $this->debut;
    }

    public function setDebut(\DateTimeInterface $debut): self
    {
        $this->debut = $debut;

        return $this;
    }

    public function getFin(): ?\DateTimeInterface
    {
        return $this->fin;
    }

    public function setFin(\DateTimeInterface $fin): self
    {
        $this->fin = $fin;

        return $this;
    }

    public function getPrixPilote(): ?string
    {
        return $this->prixPilote;
    }

    public function setPrixPilote(string $prixPilote): self
    {
        $this->prixPilote = $prixPilote;

        return $this;
    }

    public function getPrixAccomp(): ?string
    {
        return $this->prixAccomp;
    }

    public function setPrixAccomp(string $prixAccomp): self
    {
        $this->prixAccomp = $prixAccomp;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addDate($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeDate($this);
        }

        return $this;
    }

    public function getTravel(): ?Travel
    {
        return $this->travel;
    }

    public function setTravel(?Travel $travel): self
    {
        $this->travel = $travel;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getTravel().' - '.date_format($this->getDebut(), 'd/m/Y').' - '.date_format($this->getFin(), 'd/m/Y');
    }

    public function getRequestedDocs(): ?array
    {
        return $this->requestedDocs;
    }

    public function setRequestedDocs(?array $requestedDocs): self
    {
        $this->requestedDocs = $requestedDocs;

        return $this;
    }

    /**
     * @return Collection|Document[]
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->addDate($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            $document->removeDate($this);
        }

        return $this;
    }

    /**
     * @return Collection|Reservation[]
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setDate($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getDate() === $this) {
                $reservation->setDate(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Oldreservations[]
     */
    public function getOldreservations(): Collection
    {
        return $this->oldreservations;
    }

    public function addOldreservation(Oldreservations $oldreservation): self
    {
        if (!$this->oldreservations->contains($oldreservation)) {
            $this->oldreservations[] = $oldreservation;
            $oldreservation->setDates($this);
        }

        return $this;
    }

    public function removeOldreservation(Oldreservations $oldreservation): self
    {
        if ($this->oldreservations->removeElement($oldreservation)) {
            // set the owning side to null (unless already changed)
            if ($oldreservation->getDates() === $this) {
                $oldreservation->setDates(null);
            }
        }

        return $this;
    }
}
