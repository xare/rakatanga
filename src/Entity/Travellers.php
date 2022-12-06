<?php

namespace App\Entity;

use App\Repository\TravellersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TravellersRepository::class)
 */
class Travellers
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="travellers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    private $langue;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups('main')]
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups('main')]
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[Groups('main')]
    private $telephone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups('main')]
    private $position;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $assurance;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $vols;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_ajout;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="traveller")
     */
    private $documents;

    /**
     * @ORM\ManyToMany(targetEntity=Dates::class, inversedBy="travellers")
     */
    private $date;

    /**
     * @ORM\ManyToMany(targetEntity=Reservation::class, mappedBy="travellers")
     */
    private $reservations;

    /**
     * @ORM\OneToMany(targetEntity=ReservationData::class, mappedBy="travellers")
     */
    private $reservationData;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->date = new ArrayCollection();
        $this->reservations = new ArrayCollection();
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

    /* public function setDateAjout(\DateTimeInterface $date_ajout): self */
    public function setDateAjout(?\DateTimeInterface $date_ajout): self
    {
        // $date_ajout = new \DateTime();
        $this->date_ajout = $date_ajout;

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
     * @return Collection|Dates[]
     */
    public function getDate(): Collection
    {
        return $this->date;
    }

    public function addDate(Dates $date): self
    {
        if (!$this->date->contains($date)) {
            $this->date[] = $date;
        }

        return $this;
    }

    public function removeDate(Dates $date): self
    {
        $this->date->removeElement($date);

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
            $reservation->addTraveller($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            $reservation->removeTraveller($this);
        }

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
}
