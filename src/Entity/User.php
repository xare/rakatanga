<?php

namespace App\Entity;

use Adamski\Symfony\PhoneNumberBundle\Model\PhoneNumber;
use Adamski\Symfony\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(fields: ['email'], message: 'This value is already used.')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface
{
    public const REGISTERED_SUCCESFULLY = 'Se ha registrado exitosamente';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Groups('main')]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $email;

    #[Groups('main')]
    #[ORM\Column(type: 'json')]
    private $roles = [];

    /**
     * @var string The hashed password
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', nullable: true)]
    private $password;

    #[Groups('main')]
    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    private $langue;

    #[Groups('main')]
    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $nom;

    #[Groups('main')]
    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $prenom;

    /**
     * @AssertPhoneNumber
     */
    #[Groups('main')]
    #[ORM\Column(name: 'telephone', type: 'phone_number', nullable: true)]
    private $telephone;

    #[Groups('main')]
    #[ORM\Column(type: 'string', length: 15, nullable: true)]
    private $position;

    #[Groups('main')]
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $date_ajout;

    #[ORM\ManyToMany(targetEntity: Dates::class, inversedBy: 'users')]
    private $date;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private $reservation;

    #[ORM\OneToMany(targetEntity: BlogTranslation::class, mappedBy: 'user', orphanRemoval: true)]
    private $blogTranslations;

    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'user')]
    private $documents;

    #[ORM\OneToMany(targetEntity: Travellers::class, mappedBy: 'user', fetch: 'EXTRA_LAZY', orphanRemoval: true, cascade: ['persist'])]
    private $travellers;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $address;

    #[ORM\Column(type: 'string', length: 6, nullable: true)]
    private $postcode;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $city;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $country;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $nationality;

    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    private $sizes;

    #[ORM\OneToMany(targetEntity: ReservationData::class, mappedBy: 'User', orphanRemoval: true)]
    private $reservationData;

    #[ORM\OneToMany(targetEntity: Codespromo::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private $codespromos;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $idcard;

    #[ORM\Column(type: 'datetime_immutable')]
    private $agreedTermsAt;

    public function __construct()
    {
        $this->date_ajout = new \DateTimeImmutable();
        $this->agreedTermsAt = new \DateTimeImmutable();
        $this->date = new ArrayCollection();
        $this->reservation = new ArrayCollection();
        $this->blogTranslations = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->travellers = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->reservationData = new ArrayCollection();
        $this->codespromos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLangue(): ?string
    {
        return $this->langue;
    }

    public function setLangue(string $langue): self
    {
        $this->langue = $langue;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?PhoneNumber
    {
        return $this->telephone;
    }

    public function setTelephone(?PhoneNumber $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->date_ajout;
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

    /**
     * @return Collection|Reservations[]
     */
    public function getReservation(): Collection
    {
        return $this->reservation;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservation->contains($reservation)) {
            $this->reservation[] = $reservation;
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        $this->reservation->removeElement($reservation);

        return $this;
    }

    /**
     * @return Collection|BlogTranslation[]
     */
    public function getBlogTranslations(): Collection
    {
        return $this->blogTranslations;
    }

    public function addBlogTranslation(BlogTranslation $blogTranslation): self
    {
        if (!$this->blogTranslations->contains($blogTranslation)) {
            $this->blogTranslations[] = $blogTranslation;
            $blogTranslation->setUser($this);
        }

        return $this;
    }

    public function removeBlogTranslation(BlogTranslation $blogTranslation): self
    {
        if ($this->blogTranslations->removeElement($blogTranslation)) {
            // set the owning side to null (unless already changed)
            if ($blogTranslation->getUser() === $this) {
                $blogTranslation->setUser(null);
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
            $document->setUser($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getUser() === $this) {
                $document->setUser(null);
            }
        }

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
            $traveller->setUser($this);
        }

        return $this;
    }

    public function removeTraveller(Travellers $traveller): self
    {
        if ($this->travellers->removeElement($traveller)) {
            // set the owning side to null (unless already changed)
            if ($traveller->getUser() === $this) {
                $traveller->setUser(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getUserIdentifier();
    }

    /**
     * @return Collection|Reservation[]
     */
    public function getReservations(): Collection
    {
        return $this->reservation;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): self
    {
        $this->nationality = $nationality;

        return $this;
    }

    public function getSizes(): ?string
    {
        return $this->sizes;
    }

    public function setSizes(?string $sizes): self
    {
        $this->sizes = $sizes;

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
            $reservationData->setUser($this);
        }

        return $this;
    }

    public function removeReservationData(ReservationData $reservationData): self
    {
        if ($this->reservationData->removeElement($reservationData)) {
            // set the owning side to null (unless already changed)
            if ($reservationData->getUser() === $this) {
                $reservationData->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Codespromo[]
     */
    public function getCodespromos(): Collection
    {
        return $this->codespromos;
    }

    public function addCodespromo(Codespromo $codespromo): self
    {
        if (!$this->codespromos->contains($codespromo)) {
            $this->codespromos[] = $codespromo;
            $codespromo->setUser($this);
        }

        return $this;
    }

    public function removeCodespromo(Codespromo $codespromo): self
    {
        if ($this->codespromos->removeElement($codespromo)) {
            // set the owning side to null (unless already changed)
            if ($codespromo->getUser() === $this) {
                $codespromo->setUser(null);
            }
        }

        return $this;
    }

    public function getIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getIdcard(): ?string
    {
        return $this->idcard;
    }

    public function setIdcard(?string $idcard): self
    {
        $this->idcard = $idcard;

        return $this;
    }

    public function getAgreedTermsAt(): ?\DateTimeImmutable
    {
        return $this->agreedTermsAt;
    }

    public function agreeTerms(): self
    {
        $this->agreedTermsAt = new \DateTimeImmutable();

        return $this;
    }
}
