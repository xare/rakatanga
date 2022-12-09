<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuTranlationRepository::class)]
class MenuTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Menu::class, inversedBy: 'menuTranslations')]
    #[ORM\JoinColumn(name: 'menu_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Menu $menu = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $title = null;

    #[ORM\Column(nullable: true)]
    private ?string $slug = null;

    #[ORM\ManyToOne(targetEntity: Lang::class, inversedBy: 'menuTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private Lang $lang;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): self
    {
        $this->menu = $menu;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getLang(): ?Lang
    {
        return $this->lang;
    }

    public function setLang(?Lang $lang): self
    {
        $this->lang = $lang;

        return $this;
    }
}
