<?php

namespace App\Entity;

use App\Repository\ReservationDataRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ReservationDataRepository::class)]
class ReservationData
{
    #[Groups('main')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?string $passportNo = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTime $passportIssueDate = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTime $passportExpirationDate = null;

    #[ORM\Column(nullable: true)]
    private ?string $visaNumber = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTime $visaIssueDate = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTime $visaExpirationDate = null;

    #[ORM\Column(nullable: true)]
    private ?string $driversNumber = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTime $driversIssueDate = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTime $driversExpirationDate = null;

    #[ORM\ManyToOne(targetEntity: Reservation::class, inversedBy: 'reservationData')]
    #[ORM\JoinColumn(nullable: false)]
    private Reservation $reservation;

    #[ORM\ManyToMany(targetEntity: Document::class, inversedBy: 'reservationData', fetch: 'EXTRA_LAZY')]
    private Collection $documents;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reservationData')]
    #[ORM\JoinColumn(nullable: false)]
    private User $User;

    #[ORM\ManyToOne(targetEntity: Travellers::class, inversedBy: 'reservationData')]
    private ?Travellers $travellers = null;

    #[ORM\Column(nullable: true)]
    private ?string $insuranceCompany = null;

    #[ORM\Column(nullable: true)]
    private ?string $insuranceContractNumber = null;

    #[ORM\Column(nullable: true)]
    private ?string $abroadPhoneNumber = null;

    #[ORM\Column(nullable: true)]
    private ?string $contactPersonName = null;

    #[ORM\Column(nullable: true)]
    private ?string $contactPersonPhone = null;

    #[ORM\Column(nullable: true)]
    private ?string $flightNumber = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $flightArrival = null;

    #[ORM\Column(nullable: true)]
    private ?string $flightArrivalAirport = null;

    #[ORM\Column(nullable: true)]
    private ?string $ArrivalHotel = null;

    #[ORM\Column(nullable: true)]
    private ?string $flightDepartureNumber = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $flightDeparture = null;

    #[ORM\Column(nullable: true)]
    private ?string $flightDepartureAirport = null;

    #[ORM\Column(nullable: true)]
    private ?string $DepartureHotel = null;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPassportNo(): ?string
    {
        return $this->passportNo;
    }

    public function setPassportNo(?string $passportNo): self
    {
        $this->passportNo = $passportNo;

        return $this;
    }

    public function getPassportIssueDate(): ?\DateTimeInterface
    {
        return $this->passportIssueDate;
    }

    public function setPassportIssueDate(?\DateTimeInterface $passportIssueDate): self
    {
        $this->passportIssueDate = $passportIssueDate;

        return $this;
    }

    public function getPassportExpirationDate(): ?\DateTimeInterface
    {
        return $this->passportExpirationDate;
    }

    public function setPassportExpirationDate(?\DateTimeInterface $passportExpirationDate): self
    {
        $this->passportExpirationDate = $passportExpirationDate;

        return $this;
    }

    public function getVisaNumber(): ?string
    {
        return $this->visaNumber;
    }

    public function setVisaNumber(?string $visaNumber): self
    {
        $this->visaNumber = $visaNumber;

        return $this;
    }

    public function getVisaIssueDate(): ?\DateTimeInterface
    {
        return $this->visaIssueDate;
    }

    public function setVisaIssueDate(?\DateTimeInterface $visaIssueDate): self
    {
        $this->visaIssueDate = $visaIssueDate;

        return $this;
    }

    public function getVisaExpirationDate(): ?\DateTimeInterface
    {
        return $this->visaExpirationDate;
    }

    public function setVisaExpirationDate(?\DateTimeInterface $visaExpirationDate): self
    {
        $this->visaExpirationDate = $visaExpirationDate;

        return $this;
    }

    public function getDriversNumber(): ?string
    {
        return $this->driversNumber;
    }

    public function setDriversNumber(?string $driversNumber): self
    {
        $this->driversNumber = $driversNumber;

        return $this;
    }

    public function getDriversIssueDate(): ?\DateTimeInterface
    {
        return $this->driversIssueDate;
    }

    public function setDriversIssueDate(?\DateTimeInterface $driversIssueDate): self
    {
        $this->driversIssueDate = $driversIssueDate;

        return $this;
    }

    public function getDriversExpirationDate(): ?\DateTimeInterface
    {
        return $this->driversExpirationDate;
    }

    public function setDriversExpirationDate(?\DateTimeInterface $driversExpirationDate): self
    {
        $this->driversExpirationDate = $driversExpirationDate;

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

    /**
     * @return Collection|Document[]
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        $this->documents->removeElement($document);

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;

        return $this;
    }

    public function getTraveller(): ?Travellers
    {
        return $this->travellers;
    }

    public function setTraveller(?Travellers $travellers): self
    {
        $this->travellers = $travellers;

        return $this;
    }

    public function getInsuranceCompany(): ?string
    {
        return $this->insuranceCompany;
    }

    public function setInsuranceCompany(?string $insuranceCompany): self
    {
        $this->insuranceCompany = $insuranceCompany;

        return $this;
    }

    public function getInsuranceContractNumber(): ?string
    {
        return $this->insuranceContractNumber;
    }

    public function setInsuranceContractNumber(?string $insuranceContractNumber): self
    {
        $this->insuranceContractNumber = $insuranceContractNumber;

        return $this;
    }

    public function getAbroadPhoneNumber(): ?string
    {
        return $this->abroadPhoneNumber;
    }

    public function setAbroadPhoneNumber(?string $abroadPhoneNumber): self
    {
        $this->abroadPhoneNumber = $abroadPhoneNumber;

        return $this;
    }

    public function getContactPersonName(): ?string
    {
        return $this->contactPersonName;
    }

    public function setContactPersonName(?string $contactPersonName): self
    {
        $this->contactPersonName = $contactPersonName;

        return $this;
    }

    public function getContactPersonPhone(): ?string
    {
        return $this->contactPersonPhone;
    }

    public function setContactPersonPhone(?string $contactPersonPhone): self
    {
        $this->contactPersonPhone = $contactPersonPhone;

        return $this;
    }

    public function getFlightNumber(): ?string
    {
        return $this->flightNumber;
    }

    public function setFlightNumber(?string $flightNumber): self
    {
        $this->flightNumber = $flightNumber;

        return $this;
    }

    public function getFlightArrival(): ?\DateTimeInterface
    {
        return $this->flightArrival;
    }

    public function setFlightArrival(?\DateTimeInterface $flightArrival): self
    {
        $this->flightArrival = $flightArrival;

        return $this;
    }

    public function getFlightArrivalAirport(): ?string
    {
        return $this->flightArrivalAirport;
    }

    public function setFlightArrivalAirport(?string $flightArrivalAirport): self
    {
        $this->flightArrivalAirport = $flightArrivalAirport;

        return $this;
    }

    public function getArrivalHotel(): ?string
    {
        return $this->ArrivalHotel;
    }

    public function setArrivalHotel(?string $ArrivalHotel): self
    {
        $this->ArrivalHotel = $ArrivalHotel;

        return $this;
    }

    public function getFlightDepartureNumber(): ?string
    {
        return $this->flightDepartureNumber;
    }

    public function setFlightDepartureNumber(?string $flightDepartureNumber): self
    {
        $this->flightDepartureNumber = $flightDepartureNumber;

        return $this;
    }

    public function getFlightDeparture(): ?\DateTimeInterface
    {
        return $this->flightDeparture;
    }

    public function setFlightDeparture(?\DateTimeInterface $flightDeparture): self
    {
        $this->flightDeparture = $flightDeparture;

        return $this;
    }

    public function getFlightDepartureAirport(): ?string
    {
        return $this->flightDepartureAirport;
    }

    public function setFlightDepartureAirport(?string $flightDepartureAirport): self
    {
        $this->flightDepartureAirport = $flightDepartureAirport;

        return $this;
    }

    public function getDepartureHotel(): ?string
    {
        return $this->DepartureHotel;
    }

    public function setDepartureHotel(?string $DepartureHotel): self
    {
        $this->DepartureHotel = $DepartureHotel;

        return $this;
    }
}
