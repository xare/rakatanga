<?php

namespace App\Entity;

use App\Repository\PopupsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PopupsRepository::class)]
class Popups
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $title;

    #[ORM\OneToMany(targetEntity: PopupsTranslation::class, mappedBy: 'popup', orphanRemoval: true, cascade: ['persist'])]
    private $popupsTranslations;

    #[ORM\Column(type: 'date')]
    private $date_start;

    #[ORM\Column(type: 'date', nullable: true)]
    private $date_end;

    #[ORM\ManyToMany(targetEntity: Media::class, inversedBy: 'popups')]
    private $media;

    #[ORM\OneToOne(targetEntity: Media::class, cascade: ['persist', 'remove'])]
    private $mainPhoto;

    public function __construct()
    {
        $this->popupsTranslations = new ArrayCollection();
        $this->media = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection|PopupsTranslation[]
     */
    public function getPopupsTranslations(): Collection
    {
        return $this->popupsTranslations;
    }

    public function addPopupsTranslation(PopupsTranslation $popupsTranslation): self
    {
        if (!$this->popupsTranslations->contains($popupsTranslation)) {
            $this->popupsTranslations[] = $popupsTranslation;
            $popupsTranslation->setPopup($this);
        }

        return $this;
    }

    public function removePopupsTranslation(PopupsTranslation $popupsTranslation): self
    {
        if ($this->popupsTranslations->removeElement($popupsTranslation)) {
            // set the owning side to null (unless already changed)
            if ($popupsTranslation->getPopup() === $this) {
                $popupsTranslation->setPopup(null);
            }
        }

        return $this;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->date_start;
    }

    public function setDateStart(\DateTimeInterface $date_start): self
    {
        $this->date_start = $date_start;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->date_end;
    }

    public function setDateEnd(?\DateTimeInterface $date_end): self
    {
        $this->date_end = $date_end;

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

    public function getMainPhoto(): ?Media
    {
        return $this->mainPhoto;
    }

    public function setMainPhoto(?Media $mainPhoto): self
    {
        $this->mainPhoto = $mainPhoto;

        return $this;
    }
}
