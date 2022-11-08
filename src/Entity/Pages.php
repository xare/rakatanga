<?php

namespace App\Entity;

use App\Repository\PagesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PagesRepository::class)
 */
class Pages
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("main")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Groups("main")
     */
    private $default_slug;

     /**
     * @var \DateTimeInterface
     * @ORM\Column(type="datetime_immutable")
     * @Groups("main")
     */
    private \DateTimeInterface $date_created;


    /**
     * @ORM\Column(type="datetime")
     * @Groups("main")
     */
    private $date_modified;

    /**
     * @ORM\OneToMany(targetEntity=PageTranslation::class, mappedBy="Page", orphanRemoval=true, cascade={"persist"})
     */
    private $pageTranslations;

    /**
     * @ORM\OneToOne(targetEntity=Menu::class, mappedBy="page", cascade={"persist", "remove"})
     */
    private $menu;

    /**
     * @ORM\ManyToMany(targetEntity=Media::class, inversedBy="pages")
     */
    private $media;

    /**
     * @ORM\OneToOne(targetEntity=Media::class, inversedBy="page", cascade={"persist", "remove"})
     */
    private $mainPhoto;

    public function __construct()
    {
        $this->date_created = new \DateTimeImmutable();
        $this->pageTranslations = new ArrayCollection();
        $this->media = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDefaultSlug(): ?string
    {
        return $this->default_slug;
    }

    public function setDefaultSlug(string $default_slug): self
    {
        $this->default_slug = $default_slug;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->date_created;
    }


    public function getDateModified(): ?\DateTimeInterface
    {
        return $this->date_modified;
    }

    public function setDateModified(\DateTimeInterface $date_modified): self
    {
        $this->date_modified = $date_modified;

        return $this;
    }

    /**
     * @return Collection|PageTranslation[]
     */
    public function getPageTranslations(): Collection
    {
        return $this->pageTranslations;
    }

    public function addPageTranslation(PageTranslation $pageTranslation): self
    {
        if (!$this->pageTranslations->contains($pageTranslation)) {
            $this->pageTranslations[] = $pageTranslation;
            $pageTranslation->setPage($this);
        }

        return $this;
    }

    public function removePageTranslation(PageTranslation $pageTranslation): self
    {
        if ($this->pageTranslations->removeElement($pageTranslation)) {
            // set the owning side to null (unless already changed)
            if ($pageTranslation->getPage() === $this) {
                $pageTranslation->setPage(null);
            }
        }

        return $this;
    }

   /*  public function __toString(){
        return $this->default_slug;
    } */

   public function getMenu(): ?Menu
   {
       return $this->menu;
   }

   public function setMenu(?Menu $menu): self
   {
       // unset the owning side of the relation if necessary
       if ($menu === null && $this->menu !== null) {
           $this->menu->setPage(null);
       }

       // set the owning side of the relation if necessary
       if ($menu !== null && $menu->getPage() !== $this) {
           $menu->setPage($this);
       }

       $this->menu = $menu;

       return $this;
   }

   public function __toString()
   {
       return $this->title;
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
