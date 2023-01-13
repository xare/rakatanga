<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use App\Service\UploadHelper;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    #[Groups('main')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[Groups('main')]
    #[ORM\Column()]
    private ?string $filename = null;

    #[Groups('main')]
    #[ORM\Column()]
    private ?string $originalFilename = null;

    #[Groups('main')]
    #[ORM\Column()]
    private ?string $mimeType = null;

    #[ORM\ManyToOne(targetEntity: Travellers::class, inversedBy: 'documents')]
    private ?Travellers $traveller = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTime $date_from;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTime $date_until = null;

    #[ORM\ManyToMany(targetEntity: Dates::class, inversedBy: 'documents')]
    private Collection $date;

    #[Groups('main')]
    #[ORM\Column(nullable: true)]
    private ?string $doctype = null;

    #[ORM\Column(nullable: true)]
    private ?string $docnumber = null;

    #[ORM\ManyToMany(targetEntity: ReservationData::class, mappedBy: 'documents')]
    private Collection $reservationData;

    #[ORM\ManyToOne(targetEntity: Reservation::class, inversedBy: 'documents')]
    private ?Reservation $reservation = null;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->date = new ArrayCollection();
        $this->reservationData = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getFilePath(): string
    {
        return UploadHelper::DOCUMENT.'/'.$this->getFilename();
    }

    public function getTraveller(): ?travellers
    {
        return $this->traveller;
    }

    public function setTraveller(?travellers $traveller): self
    {
        $this->traveller = $traveller;

        return $this;
    }

    public function getDateFrom(): ?\DateTimeInterface
    {
        return $this->date_from;
    }

    public function setDateFrom(?\DateTimeInterface $date_from): self
    {
        $this->date_from = $date_from;

        return $this;
    }

    public function getDateUntil(): ?\DateTimeInterface
    {
        return $this->date_until;
    }

    public function setDateUntil(?\DateTimeInterface $date_until): self
    {
        $this->date_until = $date_until;

        return $this;
    }

    /**
     * @return Collection|Dates[]
     */
    public function getDate(): Collection
    {
        return $this->date;
    }

    public function addDate(Dates $date): self
    {
        if (!$this->date->contains($date)) {
            $this->date[] = $date;
        }

        return $this;
    }

    public function removeDate(Dates $date): self
    {
        $this->date->removeElement($date);

        return $this;
    }

    public function getDoctype(): ?string
    {
        return $this->doctype;
    }

    public function setDoctype(?string $doctype): self
    {
        $this->doctype = $doctype;

        return $this;
    }

    public function getDocnumber(): ?string
    {
        return $this->docnumber;
    }

    public function setDocnumber(?string $docnumber): self
    {
        $this->docnumber = $docnumber;

        return $this;
    }

    /**
     * @return Collection|ReservationData[]
     */
    public function getReservationData(): Collection
    {
        return $this->reservationData;
    }

    public function addReservationData(ReservationData $reservationData): self
    {
        if (!$this->reservationData->contains($reservationData)) {
            $this->reservationData[] = $reservationData;
            $reservationData->addDocument($this);
        }

        return $this;
    }

    public function removeReservationData(ReservationData $reservationData): self
    {
        if ($this->reservationData->removeElement($reservationData)) {
            $reservationData->removeDocument($this);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getFilename();
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
}
