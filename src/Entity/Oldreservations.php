<?php

namespace App\Entity;

use App\Repository\OldreservationsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OldreservationsRepository::class)]
class Oldreservations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 2)]
    private ?string $langue = null;

    #[ORM\Column()]
    private ?int $nbpilotes = null;

    #[ORM\Column()]
    private ?int $nbAccomp = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(type: 'text')]
    private ?string $log = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $codepromo = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?float $montant = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?float $reduction = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?float $totalttc = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes;

    #[ORM\Column(type: 'string', length: 15)]
    private ?string $statut = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $origine_ajout = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $date_ajout = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $date_paiement_1 = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $date_paiement_2 = null;

    #[ORM\ManyToOne(targetEntity: Inscriptions::class, inversedBy: 'oldreservations')]
    private ?Inscriptions $inscriptions = null;

    #[ORM\ManyToOne(targetEntity: Travel::class, inversedBy: 'oldreservations')]
    private ?Travel $Travel = null;

    #[ORM\ManyToOne(targetEntity: Dates::class, inversedBy: 'oldreservations')]
    private ?Dates $dates = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNbpilotes(): ?int
    {
        return $this->nbpilotes;
    }

    public function setNbpilotes(int $nbpilotes): self
    {
        $this->nbpilotes = $nbpilotes;

        return $this;
    }

    public function getNbAccomp(): ?int
    {
        return $this->nbAccomp;
    }

    public function setNbAccomp(int $nbAccomp): self
    {
        $this->nbAccomp = $nbAccomp;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getLog(): ?string
    {
        return $this->log;
    }

    public function setLog(string $log): self
    {
        $this->log = $log;

        return $this;
    }

    public function getCodepromo(): ?string
    {
        return $this->codepromo;
    }

    public function setCodepromo(?string $codepromo): self
    {
        $this->codepromo = $codepromo;

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

    public function getReduction(): ?string
    {
        return $this->reduction;
    }

    public function setReduction(string $reduction): self
    {
        $this->reduction = $reduction;

        return $this;
    }

    public function getTotalttc(): ?string
    {
        return $this->totalttc;
    }

    public function setTotalttc(string $totalttc): self
    {
        $this->totalttc = $totalttc;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

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

    public function getOrigineAjout(): ?string
    {
        return $this->origine_ajout;
    }

    public function setOrigineAjout(string $origine_ajout): self
    {
        $this->origine_ajout = $origine_ajout;

        return $this;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->date_ajout;
    }

    public function setDateAjout(\DateTimeInterface $date_ajout): self
    {
        $this->date_ajout = $date_ajout;

        return $this;
    }

    public function getDatePaiement1(): ?\DateTimeInterface
    {
        return $this->date_paiement_1;
    }

    public function setDatePaiement1(?\DateTimeInterface $date_paiement_1): self
    {
        $this->date_paiement_1 = $date_paiement_1;

        return $this;
    }

    public function getDatePaiement2(): ?\DateTimeInterface
    {
        return $this->date_paiement_2;
    }

    public function setDatePaiement2(?\DateTimeInterface $date_paiement_2): self
    {
        $this->date_paiement_2 = $date_paiement_2;

        return $this;
    }

    public function getInscriptions(): ?Inscriptions
    {
        return $this->inscriptions;
    }

    public function setInscriptions(?Inscriptions $inscriptions): self
    {
        $this->inscriptions = $inscriptions;

        return $this;
    }

    public function getTravel(): ?Travel
    {
        return $this->Travel;
    }

    public function setTravel(?Travel $Travel): self
    {
        $this->Travel = $Travel;

        return $this;
    }

    public function getDates(): ?Dates
    {
        return $this->dates;
    }

    public function setDates(?Dates $dates): self
    {
        $this->dates = $dates;

        return $this;
    }
}
