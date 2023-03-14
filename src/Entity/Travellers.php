<?php

namespace App\Entity;

use App\Repository\TravellersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TravellersRepository::class)]
class Travellers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'travellers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $langue = null;

    #[Groups('main')]
    #[ORM\Column()]
    private ?string $nom = null;

    #[Groups('main')]
    #[ORM\Column()]
    private ?string $prenom = null;

    #[Groups('main')]
    #[ORM\Column()]
    private ?string $telephone = null;

    #[Groups('main')]
    #[ORM\Column()]
    private ?string $position = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $assurance = null;

    #[ORM\Column(nullable: true)]
    private ?string $vols = null;

    #[ORM\Column()]
    private \DateTimeImmutable $date_ajout;

    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'traveller')]
    private Collection $documents;

    #[ORM\ManyToOne(targetEntity: Reservation::class, inversedBy: 'travellers')]
    private ?Reservation $reservation = null;

    #[ORM\OneToMany(targetEntity: ReservationData::class, mappedBy: 'traveller')]
    private Collection $reservationData;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $email = null;

    #[ORM\Column]
    private ?bool $isReservationUser = false;

    public function __construct()
    {
        $this->date_ajout = new \DateTimeImmutable();
        $this->documents = new ArrayCollection();
        $this->reservationData = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getLangue(): ?string
    {
        return $this->langue;
    }

    public function setLangue(string $langue): self
    {
        $this->langue = $langue;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getAssurance(): ?string
    {
        return $this->assurance;
    }

    public function setAssurance(string $assurance): self
    {
        $this->assurance = $assurance;

        return $this;
    }

    public function getVols(): ?string
    {
        return $this->vols;
    }

    public function setVols(?string $vols): self
    {
        $this->vols = $vols;

        return $this;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->date_ajout;
    }

   /*  public function setDateAjout(): self
    {
        $this->date_ajout = new \DateTimeImmutable();
        return $this;
    } */

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
            $document->setTraveller($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getTraveller() === $this) {
                $document->setTraveller(null);
            }
        }

        return $this;
    }

    /**
     * @return Reservation
     */
    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): self
    {
        $this->reservation = $reservation;
        return $this;
    }


    public function __toString()
    {
        return $this->nom;
    }

    /**
     * @return Collection|ReservationData[]
     */
    public function getReservationData(): Collection
    {
        return $this->reservationData;
    }

    public function addReservationData(ReservationData $reservationData): self
    {
        if (!$this->reservationData->contains($reservationData)) {
            $this->reservationData[] = $reservationData;
            $reservationData->setTraveller($this);
        }

        return $this;
    }

    public function removeReservationData(ReservationData $reservationData): self
    {
        if ($this->reservationData->removeElement($reservationData)) {
            // set the owning side to null (unless already changed)
            if ($reservationData->getTraveller() === $this) {
                $reservationData->setTraveller(null);
            }
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function isIsReservationUser(): ?bool
    {
        return $this->isReservationUser;
    }

    public function setIsReservationUser(bool $isReservationUser): self
    {
        $this->isReservationUser = $isReservationUser;

        return $this;
    }
}
