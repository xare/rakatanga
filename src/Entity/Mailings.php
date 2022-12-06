<?php

namespace App\Entity;

use App\Repository\MailingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MailingsRepository::class)]
class Mailings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $subject;

    #[ORM\Column(type: 'text', nullable: true)]
    private $content;

    #[ORM\Column(type: 'text')]
    private $ToAddresses;

    #[ORM\Column(type: 'text', nullable: true)]
    private $attachment;

    #[ORM\ManyToOne(targetEntity: Reservation::class, inversedBy: 'mailings')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private $reservation;

    /**
     * @var \DateTimeInterface
     *
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $date_sent;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $category;

    public function __construct()
    {
        $this->date_sent = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getToAddresses(): ?string
    {
        return $this->ToAddresses;
    }

    public function setToAddresses(string $ToAddresses): self
    {
        $this->ToAddresses = $ToAddresses;

        return $this;
    }

    public function getAttachment(): ?string
    {
        return $this->attachment;
    }

    public function setAttachment(?string $attachment): self
    {
        $this->attachment = $attachment;

        return $this;
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

    public function getDateSent(): ?\DateTimeImmutable
    {
        return $this->date_sent;
    }

    public function setDateSent(?\DateTimeImmutable $date_sent): self
    {
        $this->date_sent = $date_sent;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;

        return $this;
    }
}
