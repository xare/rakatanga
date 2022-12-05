<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Options
 *
 * @ORM\Table(name="options")
 * @ORM\Entity(repositoryClass="App\Repository\OptionsRepository")
 */
class Options
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="prix", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity=Travel::class, inversedBy="options")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $travel;

    /**
     * @ORM\OneToMany(targetEntity=OptionsTranslations::class, mappedBy="options", orphanRemoval=true, cascade={"persist"})
     */
    private $optionsTranslations;

    /**
     * @ORM\OneToMany(targetEntity=ReservationOptions::class, mappedBy="options", orphanRemoval=true, cascade={"persist"})
     */
    private $reservationOptions;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isExtension;

    public function __construct()
    {
        $this->optionsTranslations = new ArrayCollection();
        $this->reservationOptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getTravel(): ?Travel
    {
        return $this->travel;
    }

    public function setTravel(?Travel $travel): self
    {
        $this->travel = $travel;

        return $this;
    }

    /**
     * @return Collection|OptionsTranslations[]
     */
    public function getOptionsTranslations(): Collection
    {
        return $this->optionsTranslations;
    }

    public function addOptionsTranslation(OptionsTranslations $optionsTranslation): self
    {
        if (!$this->optionsTranslations->contains($optionsTranslation)) {
            $this->optionsTranslations[] = $optionsTranslation;
            $optionsTranslation->setOptions($this);
        }

        return $this;
    }

    public function removeOptionsTranslation(OptionsTranslations $optionsTranslation): self
    {
        if ($this->optionsTranslations->removeElement($optionsTranslation)) {
            // set the owning side to null (unless already changed)
            if ($optionsTranslation->getOptions() === $this) {
                $optionsTranslation->setOptions(null);
            }
        }

        return $this;
    }
    public function __toString():string
    {
        return $this->travel->getMainTitle();
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
            $reservationOption->setOptions($this);
        }

        return $this;
    }

    public function removeReservationOption(ReservationOptions $reservationOption): self
    {
        if ($this->reservationOptions->removeElement($reservationOption)) {
            // set the owning side to null (unless already changed)
            if ($reservationOption->getOptions() === $this) {
                $reservationOption->setOptions(null);
            }
        }

        return $this;
    }

    public function getIsExtension(): ?bool
    {
        return $this->isExtension;
    }

    public function setIsExtension(?bool $isExtension): self
    {
        $this->isExtension = $isExtension;

        return $this;
    }

}
