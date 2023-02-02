<?php

namespace App\Entity;

use App\Repository\TravelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TravelRepository::class)]
class Travel
{
    #[Groups('main')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[Groups('main')]
    #[ORM\Column(nullable: true)]
    private ?int $km = null;

    #[ORM\ManyToMany(targetEntity: Media::class, inversedBy: 'travel', cascade: ['persist'])]
    private Collection $media;

    #[Groups('main')]
    #[ORM\Column(nullable: true)]
    private ?string $status = null;

    #[Groups('main')]
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $duration = null;

    #[Groups('main')]
    #[ORM\Column(length: 3, nullable: true)]
    private ?string $level = null;

    #[Groups('main')]
    #[ORM\Column()]
    private \DateTime $date;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'travel')]
    private ?Category $category = null;

    #[Groups('main')]
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: TravelTranslation::class, mappedBy: 'travel', orphanRemoval: true, cascade: ['persist'])]
    private Collection $travelTranslation;

    #[Groups('main')]
    #[ORM\Column()]
    private ?string $main_title = null;

    #[ORM\OneToMany(targetEntity: Dates::class, mappedBy: 'travel', orphanRemoval: true)]
    private Collection $dates;

    #[ORM\OneToOne(targetEntity: Media::class, cascade: ['persist', 'remove'])]
    private ?Media $mainPhoto = null;

    #[ORM\OneToMany(targetEntity: Options::class, mappedBy: 'travel', fetch: 'EXTRA_LAZY')]
    private Collection $options;

    #[ORM\ManyToMany(targetEntity: Hotels::class, mappedBy: 'travel')]
    private Collection $hotels;

    #[ORM\ManyToMany(targetEntity: Infodocs::class, mappedBy: 'travel')]
    private Collection $infodocs;

    #[ORM\OneToMany(targetEntity: Oldreservations::class, mappedBy: 'Travel')]
    private Collection $oldreservations;

    public function __construct()
    {
        $this->media = new ArrayCollection();
        $this->travelTranslation = new ArrayCollection();
        $this->dates = new ArrayCollection();
        $this->options = new ArrayCollection();
        $this->hotels = new ArrayCollection();
        $this->infodocs = new ArrayCollection();
        $this->oldreservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKm(): ?int
    {
        return $this->km;
    }

    public function setKm(?int $km): self
    {
        $this->km = $km;

        return $this;
    }

    /**
     * @return Collection|Media[]
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(Media $medium): self
    {
        if (!$this->media->contains($medium)) {
            $this->media[] = $medium;
        }

        return $this;
    }

    public function removeMedium(Media $medium): self
    {
        $this->media->removeElement($medium);

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

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection|TravelTranslation[]
     */
    public function getTravelTranslation(): Collection
    {
        return $this->travelTranslation;
    }

    public function addTravelTranslation(TravelTranslation $travelTranslation): self
    {
        if (!$this->travelTranslation->contains($travelTranslation)) {
            $this->travelTranslation[] = $travelTranslation;
            $travelTranslation->setTravel($this);
        }

        return $this;
    }

    public function removeTravelTranslation(TravelTranslation $travelTranslation): self
    {
        if ($this->travelTranslation->removeElement($travelTranslation)) {
            // set the owning side to null (unless already changed)
            if ($travelTranslation->getTravel() === $this) {
                $travelTranslation->setTravel(null);
            }
        }

        return $this;
    }

    public function getMainTitle(): ?string
    {
        return $this->main_title;
    }

    public function setMainTitle(?string $main_title): self
    {
        $this->main_title = $main_title;

        return $this;
    }

    public function __toString()
    {
        return $this->getCategory().' '.$this->main_title;
    }

    /**
     * @return Collection|Dates[]
     */
    public function getDates(): Collection
    {
        return $this->dates;
    }

    public function addDate(Dates $date): self
    {
        if (!$this->dates->contains($date)) {
            $this->dates[] = $date;
            $date->setTravel($this);
        }

        return $this;
    }

    public function removeDate(Dates $date): self
    {
        if ($this->dates->removeElement($date)) {
            // set the owning side to null (unless already changed)
            if ($date->getTravel() === $this) {
                $date->setTravel(null);
            }
        }

        return $this;
    }

    public function getMainPhoto(): ?Media
    {
        return $this->mainPhoto;
    }

    public function setMainPhoto(?Media $mainPhoto): self
    {
        $this->mainPhoto = $mainPhoto;

        return $this;
    }

    /**
     * @return Collection|Options[]
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(Options $option): self
    {
        if (!$this->options->contains($option)) {
            $this->options[] = $option;
            $option->setTravel($this);
        }

        return $this;
    }

    public function removeOption(Options $option): self
    {
        if ($this->options->removeElement($option)) {
            // set the owning side to null (unless already changed)
            if ($option->getTravel() === $this) {
                $option->setTravel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Hotels[]
     */
    public function getHotels(): Collection
    {
        return $this->hotels;
    }

    public function addHotel(Hotels $hotel): self
    {
        if (!$this->hotels->contains($hotel)) {
            $this->hotels[] = $hotel;
            $hotel->addTravel($this);
        }

        return $this;
    }

    public function removeHotel(Hotels $hotel): self
    {
        if ($this->hotels->removeElement($hotel)) {
            $hotel->removeTravel($this);
        }

        return $this;
    }

    /**
     * @return Collection|Infodocs[]
     */
    public function getInfodocs(): Collection
    {
        return $this->infodocs;
    }

    public function addInfodoc(Infodocs $infodoc): self
    {
        if (!$this->infodocs->contains($infodoc)) {
            $this->infodocs[] = $infodoc;
            $infodoc->addTravel($this);
        }

        return $this;
    }

    public function removeInfodoc(Infodocs $infodoc): self
    {
        if ($this->infodocs->removeElement($infodoc)) {
            $infodoc->removeTravel($this);
        }

        return $this;
    }

    /**
     * @return Collection|Oldreservations[]
     */
    public function getOldreservations(): Collection
    {
        return $this->oldreservations;
    }

    public function addOldreservation(Oldreservations $oldreservation): self
    {
        if (!$this->oldreservations->contains($oldreservation)) {
            $this->oldreservations[] = $oldreservation;
            $oldreservation->setTravel($this);
        }

        return $this;
    }

    public function removeOldreservation(Oldreservations $oldreservation): self
    {
        if ($this->oldreservations->removeElement($oldreservation)) {
            // set the owning side to null (unless already changed)
            if ($oldreservation->getTravel() === $this) {
                $oldreservation->setTravel(null);
            }
        }

        return $this;
    }
}
