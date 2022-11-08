<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DocumentRepository;
use App\Service\UploadHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 */
class Document
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("main")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="documents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("main")
     */
    private $filename;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("main")
     */
    private $originalFilename;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("main")
     */
    private $mimeType;

    /**
     * @ORM\ManyToOne(targetEntity=Travellers::class, inversedBy="documents")
     */
    private $traveller;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_from;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_until;

    /**
     * @ORM\ManyToMany(targetEntity=Dates::class, inversedBy="documents")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("main")
     */
    private $doctype;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $docnumber;

    /**
     * @ORM\ManyToMany(targetEntity=ReservationData::class, mappedBy="documents")
     */
    private $reservationData;

    /**
     * @ORM\ManyToOne(targetEntity=Reservation::class, inversedBy="documents")
     */
    private $reservation;

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
        return UploadHelper::DOCUMENT . '/' . $this->getFilename();
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
