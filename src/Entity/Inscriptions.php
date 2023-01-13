<?php

namespace App\Entity;

use Adamski\Symfony\PhoneNumberBundle\Validator as AssertPhoneNumber;
use App\Repository\InscriptionsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionsRepository::class)]
class Inscriptions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $nom;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $prenom = null;

    #[AssertPhoneNumber\PhoneNumber]
    #[ORM\Column(length: 25, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column()]
    private ?string $email = null;

    #[ORM\Column(length: 15)]
    private ?string $position = null;

    #[ORM\Column()]
    private ?int $arrhes = null;

    #[ORM\Column()]
    private ?int $solde = null;

    #[ORM\Column(length: 3)]
    private ?string $assurance = null;

    #[ORM\Column(length: 3)]
    private ?string $vols = null;

    #[ORM\Column(length: 15)]
    private ?string $statut = null;

    #[ORM\Column(type: 'text')]
    private ?string $remarque = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $date_ajout = null;

    #[ORM\Column(length: 3)]
    private ?string $langue = null;

    #[ORM\OneToMany(targetEntity: Oldreservations::class, mappedBy: 'inscriptions')]
    private Collection $oldreservations;

    public function __construct()
    {
        $this->oldreservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

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

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getArrhes(): ?int
    {
        return $this->arrhes;
    }

    public function setArrhes(int $arrhes): self
    {
        $this->arrhes = $arrhes;

        return $this;
    }

    public function getSolde(): ?int
    {
        return $this->solde;
    }

    public function setSolde(int $solde): self
    {
        $this->solde = $solde;

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

    public function setVols(string $vols): self
    {
        $this->vols = $vols;

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

    public function getRemarque(): ?string
    {
        return $this->remarque;
    }

    public function setRemarque(string $remarque): self
    {
        $this->remarque = $remarque;

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

    public function getLangue(): ?string
    {
        return $this->langue;
    }

    public function setLangue(string $langue): self
    {
        $this->langue = $langue;

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
            $oldreservation->setInscriptions($this);
        }

        return $this;
    }

    public function removeOldreservation(Oldreservations $oldreservation): self
    {
        if ($this->oldreservations->removeElement($oldreservation)) {
            // set the owning side to null (unless already changed)
            if ($oldreservation->getInscriptions() === $this) {
                $oldreservation->setInscriptions(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getPrenom().' '.$this->getNom().' - '.$this->getEmail();
    }
}
