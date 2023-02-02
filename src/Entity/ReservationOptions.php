<?php

namespace App\Entity;

use App\Repository\ReservationOptionsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationOptionsRepository::class)]
class ReservationOptions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Reservation::class, inversedBy: 'reservationOptions', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private Reservation $reservation;

    #[ORM\ManyToOne(targetEntity: Options::class, inversedBy: 'reservationOptions', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private Options $option;

    #[ORM\Column(type: 'integer')]
    private ?int $ammount = null;

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

    public function getOption(): ?Options
    {
        return $this->option;
    }

    public function setOption(?Options $option): self
    {
        $this->option = $option;

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
