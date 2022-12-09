<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;
    
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?string $position = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $routeName = null;

    #[ORM\OneToMany(targetEntity: MenuTranslation::class, mappedBy: 'menu', cascade: ['persist', 'remove'])]
    private Collection $menuTranslations;

    #[ORM\OneToOne(targetEntity: Pages::class, inversedBy: 'menu', cascade: ['persist', 'remove'])]
    private ?Pages $page = null;

    #[ORM\Column(type: 'boolean')]
    private bool $visibility;

    #[ORM\ManyToMany(targetEntity: MenuLocation::class, inversedBy: 'menus')]
    private Collection $menuLocations;

    public function __construct()
    {
        $this->menuTranslations = new ArrayCollection();
        $this->menuLocations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function setRouteName(?string $routeName): self
    {
        $this->routeName = $routeName;

        return $this;
    }

    /**
     * @return Collection|MenuTranslation[]
     */
    public function getMenuTranslations(): Collection
    {
        return $this->menuTranslations;
    }

    public function addMenuTranslation(MenuTranslation $menuTranslation): self
    {
        if (!$this->menuTranslations->contains($menuTranslation)) {
            $this->menuTranslations[] = $menuTranslation;
            $menuTranslation->setMenu($this);
        }

        return $this;
    }

    public function removeMenuTranslation(MenuTranslation $menuTranslation): self
    {
        if ($this->menuTranslations->removeElement($menuTranslation)) {
            // set the owning side to null (unless already changed)
            if ($menuTranslation->getMenu() === $this) {
                $menuTranslation->setMenu(null);
            }
        }

        return $this;
    }

    public function getPage(): ?Pages
    {
        return $this->page;
    }

    public function setPage(?Pages $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getVisibility(): ?bool
    {
        return $this->visibility;
    }

    public function setVisibility(bool $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * @return Collection|MenuLocation[]
     */
    public function getMenuLocations(): Collection
    {
        return $this->menuLocations;
    }

    public function addMenuLocation(MenuLocation $menuLocation): self
    {
        if (!$this->menuLocations->contains($menuLocation)) {
            $this->menuLocations[] = $menuLocation;
        }

        return $this;
    }

    public function removeMenuLocation(MenuLocation $menuLocation): self
    {
        $this->menuLocations->removeElement($menuLocation);

        return $this;
    }
}
