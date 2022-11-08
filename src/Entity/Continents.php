<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Continents
 *
 * @ORM\Table(name="continents")
 * @ORM\Entity(repositoryClass="App\Repository\ContinentsRepository")
 */
class Continents
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=2, nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $code;


    /**
     * @ORM\OneToMany(targetEntity=ContinentTranslation::class, mappedBy="continents", orphanRemoval=true, cascade={"persist"})
     * @Assert\Valid()
     */
    private $continentTranslation;

    /**
     * @ORM\OneToMany(targetEntity=Category::class, mappedBy="continents")
     */
    private $category;

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
