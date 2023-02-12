<?php

namespace App\Entity;

use App\Repository\MailingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MailingsRepository::class)]
class Mailings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?string $subject = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: 'text')]
    private ?string $ToAddresses = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $attachment = null;

    #[ORM\ManyToOne(targetEntity: Reservation::class, inversedBy: 'mailings')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Reservation $reservation = null;

    /**
     * @var \DateTimeInterface
     */
    #[ORM\Column(nullable: true)]
    private \DateTimeImmutable $date_sent;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $category = null;

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
