<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=ReservationRepository::class)
 */
class Reservation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups('main')]
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Dates::class, inversedBy="reservations")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="reservation")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    #[Groups('main')]
    private $comment;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $log;

    /**
     * @ORM\ManyToMany(targetEntity=Travellers::class, inversedBy="reservations")
     * @ORM\JoinTable(name="reservation_travellers",
     *          joinColumns={@ORM\JoinColumn(name="reservation_id",
     *          referencedColumnName="id")},
     *          inverseJoinColumns={@ORM\JoinColumn(name="travellers_id",
     *          referencedColumnName="id")})
     */
    private $travellers;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups('main')]
    private \DateTimeInterface $date_ajout;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    #[Groups('main')]
    private $date_paiement;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[Groups('main')]
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=ReservationOptions::class, mappedBy="reservation", orphanRemoval=true, cascade={"persist"})
     */
    private $reservationOptions;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    #[Groups('main')]
    private $nbpilotes;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    #[Groups('main')]
    private $nbaccomp;

    /**
     * @ORM\OneToMany(targetEntity=ReservationData::class, mappedBy="reservation", orphanRemoval=true)
     */
    private $reservationData;

    /**
     * @ORM\OneToMany(targetEntity=Payments::class,
     * mappedBy="reservation",
     * orphanRemoval=true,
     * cascade={"persist","remove"})
     */
    private $payments;

    /**
     * @ORM\OneToMany(targetEntity=Document::class,
     * mappedBy="reservation",
     * orphanRemoval=true)
     */
    private $documents;

    /**
     * @ORM\OneToOne(targetEntity=Invoices::class, mappedBy="reservation", cascade={"persist", "remove"})
     */
    private $invoice;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $code;

    /**
     * @ORM\OneToMany(targetEntity=Mailings::class, mappedBy="reservation")
     */
    private $mailings;

    /**
     * @ORM\ManyToOne(targetEntity=Codespromo::class, inversedBy="reservations")
     */
    private $codespromo;

    public function __construct()
    {
        $this->date_ajout = new \DateTimeImmutable();
        $this->travellers = new ArrayCollection();
        $this->reservationOptions = new ArrayCollection();
        $this->reservationData = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->mailings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?dates
    {
        return $this->date;
    }

    public function setDate(?dates $date): self
    {
        $this->date = $date;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getLog(): ?string
    {
        return $this->log;
    }

    public function setLog(?string $log): self
    {
        $this->log = $log;

        return $this;
    }

    /**
     * @return Collection|Travellers[]
     */
    public function getTravellers(): Collection
    {
        return $this->travellers;
    }

    public function addTraveller(Travellers $traveller): self
    {
        if (!$this->travellers->contains($traveller)) {
            $this->travellers[] = $traveller;
        }

        return $this;
    }

    public function removeTraveller(Travellers $traveller): self
    {
        $this->traveller->removeElement($traveller);

        return $this;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->date_ajout;
    }

    public function getDatePaiement(): ?\DateTimeInterface
    {
        return $this->date_paiement;
    }

    public function setDatePaiement(?\DateTimeInterface $date_paiement): self
    {
        $this->date_paiement = $date_paiement;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|ReservationOptions[]
     */
    public function getReservationOptions(): Collection
    {
        return $this->reservationOptions;
    }

    public function addReservationOption(ReservationOptions $reservationOption): self
    {
        if (!$this->reservationOptions->contains($reservationOption)) {
            $this->reservationOptions[] = $reservationOption;
            $reservationOption->setReservation($this);
        }

        return $this;
    }

    public function removeReservationOption(ReservationOptions $reservationOption): self
    {
        if ($this->reservationOptions->removeElement($reservationOption)) {
            // set the owning side to null (unless already changed)
            if ($reservationOption->getReservation() === $this) {
                $reservationOption->setReservation(null);
            }
        }

        return $this;
    }

    public function getNbpilotes(): ?int
    {
        return $this->nbpilotes;
    }

    public function setNbpilotes(?int $nbpilotes): self
    {
        $this->nbpilotes = $nbpilotes;

        return $this;
    }

    public function getNbAccomp(): ?int
    {
        return $this->nbaccomp;
    }

    public function setNbAccomp(?int $nbaccomp): self
    {
        $this->nbaccomp = $nbaccomp;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getStatus();
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
            $reservationData->setReservation($this);
        }

        return $this;
    }

    public function removeReservationData(ReservationData $reservationData): self
    {
        if ($this->reservationData->removeElement($reservationData)) {
            // set the owning side to null (unless already changed)
            if ($reservationData->getReservation() === $this) {
                $reservationData->setReservation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Payments[]
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payments $payment): self
    {
        if (!$this->payments->contains($payment)) {
            $this->payments[] = $payment;
            $payment->setReservation($this);
        }

        return $this;
    }

    public function removePayment(Payments $payment): self
    {
        if ($this->payments->removeElement($payment)) {
            // set the owning side to null (unless already changed)
            if ($payment->getReservation() === $this) {
                $payment->setReservation(null);
            }
        }

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
            $document->setReservation($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getReservation() === $this) {
                $document->setReservation(null);
            }
        }

        return $this;
    }

    /**
     * @return Invoices $invoice
     */
    public function getInvoice(): ?Invoices
    {
        return $this->invoice;
    }

    /**
     * setInvoice.
     */
    public function setInvoice(?Invoices $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection|Mailings[]
     */
    public function getMailings(): Collection
    {
        return $this->mailings;
    }

    public function addMailing(Mailings $mailing): self
    {
        if (!$this->mailings->contains($mailing)) {
            $this->mailings[] = $mailing;
            $mailing->setReservation($this);
        }

        return $this;
    }

    public function removeMailing(Mailings $mailing): self
    {
        if ($this->mailings->removeElement($mailing)) {
            // set the owning side to null (unless already changed)
            if ($mailing->getReservation() === $this) {
                $mailing->setReservation(null);
            }
        }

        return $this;
    }

    public function getCodespromo(): ?Codespromo
    {
        return $this->codespromo;
    }

    public function setCodespromo(?Codespromo $codespromo): self
    {
        $this->codespromo = $codespromo;

        return $this;
    }
}
