<?php

namespace App\Entity;

use App\Repository\TransferDocumentRepository;
use App\Service\UploadHelper;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransferDocumentRepository::class)]
class TransferDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filename = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $originalFilename = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mimeType = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date_added;

    #[ORM\OneToOne(inversedBy: 'transferDocument', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Payments $payment = null;

    #[ORM\ManyToOne(inversedBy: 'transferDocuments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;


    public function __construct()
    {
        $this->date_added = new \DateTimeImmutable();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(?string $originalFilename): self
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getFilePath(): string
    {
        return UploadHelper::TRANSFER_DOCUMENT.'/'.$this->getFilename();
    }
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getDateAdded(): ?\DateTimeImmutable
    {
        return $this->date_added;
    }

    public function setDateAdded(\DateTimeImmutable $date_added): self
    {
        $this->date_added = $date_added;

        return $this;
    }

    public function getPayment(): ?Payments
    {
        return $this->payment;
    }

    public function setPayment(Payments $payment): self
    {
        $this->payment = $payment;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

}
