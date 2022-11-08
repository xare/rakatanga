<?php

namespace App\Entity;

use App\Repository\MenuLocationRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Menu;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass=MenuLocationRepository::class)
 */
class MenuLocation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity=Menu::class, inversedBy="menuLocations", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="menu_location_menu",
     *  joinColumns={@ORM\JoinColumn(name="menu_location_id", referencedColumnName="id")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="menu_id", referencedColumnName="id")}
     * )
     */
    private $Menus;

    public function __construct()
    {
        $this->Menus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Menus[]
     */
    public function getMenus(): Collection
    {
        return $this->Menus;
    }

    public function addMenu(Menu $menu): self
    {
        if (!$this->menus->contains($menu)) {
            $this->menus[] = $menu;
        }

        return $this;
    }

    public function removeMenu(Menu $menu): self
    {
        $this->menus->removeElement($menu);

        return $this;
    }

}
