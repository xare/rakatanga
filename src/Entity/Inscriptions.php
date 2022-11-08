<?php

namespace App\Entity;

use App\Repository\InscriptionsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Adamski\Symfony\PhoneNumberBundle\Model\PhoneNumber;
use Adamski\Symfony\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;

/**
 * @ORM\Entity(repositoryClass=InscriptionsRepository::class)
 */
class Inscriptions
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", name="telephone", length=25, nullable=true)
     * @AssertPhoneNumber
     */
    private $telephone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $position;

    /**
     * @ORM\Column(type="integer")
     */
    private $arrhes;

    /**
     * @ORM\Column(type="integer")
     */
    private $solde;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $assurance;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $vols;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $statut;

    /**
     * @ORM\Column(type="text")
     */
    private $remarque;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_ajout;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $langue;

    /**
     * @ORM\OneToMany(targetEntity=Oldreservations::class, mappedBy="inscriptions")
     */
    private $oldreservations;

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
        return $this->getPrenom() . ' ' . $this->getNom() . ' - ' . $this->getEmail();
    }
}
