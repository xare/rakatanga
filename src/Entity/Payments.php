<?php

namespace App\Entity;

use App\Repository\PaymentsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PaymentsRepository::class)
 */
class Payments
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $ammount;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeInterface $date_ajout;

    /**
     * @ORM\ManyToOne(targetEntity=Reservation::class, inversedBy="payments", cascade={"persist","remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $reservation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $stripeId;

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
}
