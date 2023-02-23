<?php

namespace App\Entity;

use App\Repository\CodespromoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Codespromo.
 */
#[ORM\Table(name: 'codespromo')]
#[ORM\Entity(repositoryClass: CodespromoRepository::class)]
class Codespromo
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    /**
     * @var string
     */
    #[ORM\Column(length: 10, nullable: false)]
    private string $code;

    /**
     * @var string
     */
    #[ORM\Column(length: 100, nullable: false)]
    private string $libelle;

    #[ORM\Column(nullable: false)]
    private string $commentaire;

    /**
     * @var string
     */
    #[ORM\Column(name: 'montant', type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private $montant;

    /**
     * @var int
     */
    #[ORM\Column(name: 'pourcentage', type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $pourcentage = null;

    /**
     * @var string
     */
    #[ORM\Column(length: 15, nullable: false)]
    private string $type;

    /**
     * @var int
     */
    #[ORM\Column(nullable: true, options: ['unsigned' => true])]
    private ?int $nombre = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTime $debut = null;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTime $fin = null;

    #[ORM\Column(nullable: true)]
    private ?string $email = null;

    #[ORM\Column(nullable: false)]
    private string $statut;


    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $dateAjout = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'codespromos')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $nombreTotal = null;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'codespromo')]
    private Collection $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getPourcentage(): ?int
    {
        return $this->pourcentage;
    }

    public function setPourcentage(int $pourcentage): self
    {
        $this->pourcentage = $pourcentage;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getNombre(): ?int
    {
        return $this->nombre;
    }

    public function setNombre(int $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getDebut(): ?\DateTimeInterface
    {
        return $this->debut;
    }

    public function setDebut(?\DateTimeInterface $debut): self
    {
        $this->debut = $debut;

        return $this;
    }

    public function getFin(): ?\DateTimeInterface
    {
        return $this->fin;
    }

    public function setFin(?\DateTimeInterface $fin): self
    {
        $this->fin = $fin;

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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->dateAjout;
    }

    public function setDateAjout(?\DateTimeInterface $dateAjout): self
    {
        $this->dateAjout = $dateAjout;

        return $this;
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

    public function __toString(): string
    {
        return $this->getCode();
    }

    public function getNombreTotal(): ?int
    {
        return $this->nombreTotal;
    }

    public function setNombreTotal(?int $nombreTotal): self
    {
        $this->nombreTotal = $nombreTotal;

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
            $reservation->setCodespromo($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getCodespromo() === $this) {
                $reservation->setCodespromo(null);
            }
        }

        return $this;
    }
}
