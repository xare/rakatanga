<?php

namespace App\Entity;

use App\Repository\PaymentsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentsRepository::class)]
class Payments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $ammount;

    #[ORM\Column()]
    private \DateTimeImmutable $date_ajout;

    #[ORM\ManyToOne(targetEntity: Reservation::class, inversedBy: 'payments', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private Reservation $reservation;

    #[ORM\Column(nullable: true)]
    private ?string $stripeId = null;

    #[ORM\OneToOne(mappedBy: 'payment', cascade: ['persist', 'remove'])]
    private ?TransferDocument $transferDocument = null;


    public function __construct()
    {
        $this->date_ajout = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmmount(): ?float
    {
        return $this->ammount;
    }

    public function setAmmount(float $ammount): self
    {
        $this->ammount = $ammount;

        return $this;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->date_ajout;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): self
    {
        $this->reservation = $reservation;

        return $this;
    }

    public function getStripeId(): ?string
    {
        return $this->stripeId;
    }

    public function setStripeId(?string $stripeId): self
    {
        $this->stripeId = $stripeId;

        return $this;
    }

    public function getTransferDocument(): ?TransferDocument
    {
        return $this->transferDocument;
    }

    public function setTransferDocument(TransferDocument $transferDocument): self
    {
        // set the owning side of the relation if necessary
        if ($transferDocument->getPayment() !== $this) {
            $transferDocument->setPayment($this);
        }

        $this->transferDocument = $transferDocument;

        return $this;
    }


}
