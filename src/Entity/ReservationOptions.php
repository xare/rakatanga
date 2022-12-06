<?php

namespace App\Entity;

use App\Repository\ReservationOptionsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationOptionsRepository::class)]
class ReservationOptions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Reservation::class, inversedBy: 'reservationOptions', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private $reservation;

    #[ORM\ManyToOne(targetEntity: Options::class, inversedBy: 'reservationOptions', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private $options;

    #[ORM\Column(type: 'integer')]
    private $ammount;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOptions(): ?Options
    {
        return $this->options;
    }

    public function setOptions(?Options $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getAmmount(): ?int
    {
        return $this->ammount;
    }

    public function setAmmount(int $ammount): self
    {
        $this->ammount = $ammount;

        return $this;
    }

    public function __toString()
    {
        return $this->id;
    }
}
