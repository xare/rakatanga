<?php

namespace App\Entity;

use App\Repository\InvoicesRepository;
use App\Service\UploadHelper;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoicesRepository::class)]
class Invoices
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?string $nif = null;

    #[ORM\Column(nullable: true)]
    private ?string $address = null;

    #[ORM\Column(nullable: true)]
    private ?string $postalcode = null;

    #[ORM\Column(nullable: true)]
    private ?string $city = null;

    #[ORM\Column(nullable: true)]
    private ?string $country = null;

    #[ORM\Column()]
    private ?string $invoice_number = null;

    #[ORM\Column()]
    private ?string $filename = null;

    #[ORM\Column()]
    private ?string $originalFilename = null;

    #[ORM\Column()]
    private \DateTimeImmutable $date_created;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?float $dueAmmount = null;

    #[ORM\OneToOne(targetEntity: Reservation::class, inversedBy: 'invoice', cascade: ['persist'])]
    private ?Reservation $reservation = null;

    public function __construct()
    {
        $this->date_created = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNif(): ?string
    {
        return $this->nif;
    }

    public function setNif(?string $nif): self
    {
        $this->nif = $nif;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPostalcode(): ?string
    {
        return $this->postalcode;
    }

    public function setPostalcode(?string $postalcode): self
    {
        $this->postalcode = $postalcode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoice_number;
    }

    public function setInvoiceNumber(string $invoice_number): self
    {
        $this->invoice_number = $invoice_number;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return UploadHelper::INVOICES.'/'.$this->getFilename();
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): self
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->date_created;
    }

    /* public function setDateCreated(?\DateTimeInterface $date_created): self
    {
        $this->date_created = $date_created;

        return $this;
    } */

    public function getDueAmmount(): ?string
    {
        return $this->dueAmmount;
    }

    public function setDueAmmount(string $dueAmmount): self
    {
        $this->dueAmmount = $dueAmmount;

        return $this;
    }

    /**
     * getReservation function.
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
}
