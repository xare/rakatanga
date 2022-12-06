<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Dates.
 *
 */
#[ORM\Entity(repositoryClass: 'App\Repository\DatesRepository')]
class Dates
{
    /**
     * @var int
     *
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * @var \Date
     *
     */
    #[Groups('main')]
    #[ORM\Column(name: 'debut', type: 'date', nullable: false)]
    private $debut;

    /**
     * @var \Date
     *
     */
    #[Groups('main')]
    #[ORM\Column(name: 'fin', type: 'date', nullable: false)]
    private $fin;

    /**
     * @var string
     *
     */
    #[Groups('main')]
    #[ORM\Column(name: 'prix_pilote', type: 'decimal', precision: 10, scale: 2, nullable: false)]
    private $prixPilote;

    /**
     * @var string
     *
     */
    #[Groups('main')]
    #[ORM\Column(name: 'prix_accomp', type: 'decimal', precision: 10, scale: 2, nullable: false)]
    private $prixAccomp;

    /**
     * @var string
     *
     */
    #[Groups('main')]
    #[ORM\Column(name: 'statut', type: 'string', length: 15, nullable: false)]
    private $statut;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'date')]
    private $users;

    #[ORM\ManyToOne(targetEntity: Travel::class, inversedBy: 'dates')]
    #[ORM\JoinColumn(nullable: false)]
    private $travel;

    #[ORM\Column(type: 'json', nullable: true)]
    private $requestedDocs = [];

    #[ORM\ManyToMany(targetEntity: Document::class, mappedBy: 'date')]
    private $documents;

    #[ORM\ManyToMany(targetEntity: Travellers::class, mappedBy: 'date')]
    private $travellers;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'date', cascade: ['persist', 'remove'])]
    private $reservations;

    #[ORM\OneToMany(targetEntity: Oldreservations::class, mappedBy: 'dates')]
    private $oldreservations;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->travellers = new ArrayCollection();
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
     * @return Collection|Travellers[]
     */
    public function getTravellers(): Collection
    {
        return $this->travellers;
    }

    public function addTraveller(Travellers $traveller): self
    {
        if (!$this->travellers->contains($traveller)) {
            $this->travellers[] = $traveller;
            $traveller->addDate($this);
        }

        return $this;
    }

    public function removeTraveller(Travellers $traveller): self
    {
        if ($this->travellers->removeElement($traveller)) {
            $traveller->removeDate($this);
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
