<?php

namespace App\Entity;

use App\Repository\ContinentsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Continents.
 */
#[ORM\Table(name: 'continents')]
#[ORM\Entity(repositoryClass: ContinentsRepository::class)]
class Continents
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    /**
     * @var string
     */
    #[ORM\Column(length: 2, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private string $code;

    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: ContinentTranslation::class, mappedBy: 'continents', orphanRemoval: true, cascade: ['persist'])]
    private Collection $continentTranslation;

    #[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'continents')]
    private Collection $category;

    public function __construct()
    {
        $this->continentTranslation = new ArrayCollection();
        $this->category = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection|ContinentTranslation[]
     */
    public function getContinentTranslation(): Collection
    {
        return $this->continentTranslation;
    }

    public function addContinentTranslation(ContinentTranslation $continentTranslation): self
    {
        if (!$this->continentTranslation->contains($continentTranslation)) {
            $this->continentTranslation[] = $continentTranslation;
            $continentTranslation->setContinents($this);
        }

        return $this;
    }

    public function removeContinentTranslation(ContinentTranslation $continentTranslation): self
    {
        if ($this->continentTranslation->removeElement($continentTranslation)) {
            // set the owning side to null (unless already changed)
            if ($continentTranslation->getContinents() === $this) {
                $continentTranslation->setContinents(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->code;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->category->contains($category)) {
            $this->category[] = $category;
            $category->setContinents($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->category->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getContinents() === $this) {
                $category->setContinents(null);
            }
        }

        return $this;
    }
}
