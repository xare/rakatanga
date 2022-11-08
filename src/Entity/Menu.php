<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MenuRepository::class)
 */
class Menu
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @ORM\OneToMany(targetEntity=MenuTranslation::class, mappedBy="Menu", cascade={"persist"})
     */
    private $menuTranslations;

    /**
     * @ORM\OneToOne(targetEntity=Pages::class, inversedBy="menu", cascade={"persist", "remove"})
     */
    private $page;

    /**
     * @ORM\Column(type="boolean")
     */
    private $visibility;

    
    /**
     * @ORM\ManyToMany(targetEntity=MenuLocation::class, mappedBy="Menus", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="menu_location_menu",
     *  joinColumns={@ORM\JoinColumn(name="menu_id", referencedColumnName="id")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="menu_location_id", referencedColumnName="id")}
     * )
     */
    private $menuLocations;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $route_name;

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

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

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

    public function removeMenuTranslations(): self
    {
        foreach ($this->getMenuTranslations() as $menuTranslation){
            $this->removeMenuTranslation($menuTranslation);
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
            $$menuLocations->addMenu($this);
        }

        return $this;
    }

    public function removeMenu(MenuLocation $menuLocation): self
    {
        if ($this->menuLocations->removeElement($menuLocation)) {
            $menuLocation->removeMenu($this);
        }

        return $this;
    }


    public function __toString()
    {
        return $this->title;
    }

    public function getRouteName(): ?string
    {
        return $this->route_name;
    }

    public function setRouteName(?string $route_name): self
    {
        $this->route_name = $route_name;

        return $this;
    }
}
