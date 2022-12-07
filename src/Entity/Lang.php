<?php

namespace App\Entity;

use App\Repository\LangRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LangRepository::class)]
class Lang
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 7)]
    private $iso_code;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $charset;

    #[ORM\OneToMany(targetEntity: BlogTranslation::class, mappedBy: 'lang')]
    private $blogTranslations;

    #[ORM\OneToMany(targetEntity: CategoryTranslation::class, mappedBy: 'lang', orphanRemoval: true)]
    private $categoryTranslations;

    #[ORM\OneToMany(targetEntity: PageTranslation::class, mappedBy: 'lang')]
    private $pageTranslations;

    #[ORM\OneToMany(targetEntity: MenuTranslation::class, mappedBy: 'lang')]
    private $menuTranslations;

    #[ORM\OneToMany(targetEntity: TravelTranslation::class, mappedBy: 'lang', orphanRemoval: true)]
    private $travelTranslations;

    #[ORM\OneToMany(targetEntity: OptionsTranslations::class, mappedBy: 'lang', orphanRemoval: true)]
    private $optionsTranslations;

    #[ORM\OneToMany(targetEntity: Texts::class, mappedBy: 'lang')]
    private $texts;

    #[ORM\OneToMany(targetEntity: TextTranslations::class, mappedBy: 'Lang')]
    private $textTranslations;

    #[ORM\OneToMany(targetEntity: ContinentTranslation::class, mappedBy: 'lang')]
    private $continentTranslations;

    #[ORM\OneToMany(targetEntity: PopupsTranslation::class, mappedBy: 'lang', orphanRemoval: true)]
    private $popupsTranslations;

    #[ORM\OneToOne(targetEntity: Blog::class, mappedBy: 'lang', cascade: ['persist', 'remove'])]
    private $blog;

    public function __construct()
    {
        $this->blogTranslations = new ArrayCollection();
        $this->categoryTranslations = new ArrayCollection();
        $this->pageTranslations = new ArrayCollection();
        $this->menuTranslations = new ArrayCollection();
        $this->travelTranslations = new ArrayCollection();
        $this->optionsTranslations = new ArrayCollection();
        $this->texts = new ArrayCollection();
        $this->textTranslations = new ArrayCollection();
        $this->continentTranslations = new ArrayCollection();
        $this->popupsTranslations = new ArrayCollection();
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

    public function getIsoCode(): ?string
    {
        return $this->iso_code;
    }

    public function setIsoCode(string $iso_code): self
    {
        $this->iso_code = $iso_code;

        return $this;
    }

    public function getCharset(): ?string
    {
        return $this->charset;
    }

    public function setCharset(?string $charset): self
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * @return Collection|BlogTranslation[]
     */
    public function getBlogTranslations(): Collection
    {
        return $this->blogTranslations;
    }

    public function addBlogTranslation(BlogTranslation $blogTranslation): self
    {
        if (!$this->blogTranslations->contains($blogTranslation)) {
            $this->blogTranslations[] = $blogTranslation;
            $blogTranslation->setLang($this);
        }

        return $this;
    }

    public function removeBlogTranslation(BlogTranslation $blogTranslation): self
    {
        if ($this->blogTranslations->removeElement($blogTranslation)) {
            // set the owning side to null (unless already changed)
            if ($blogTranslation->getLang() === $this) {
                $blogTranslation->setLang(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CategoryTranslation[]
     */
    public function getCategoryTranslations(): Collection
    {
        return $this->categoryTranslations;
    }

    public function addCategoryTranslation(CategoryTranslation $categoryTranslation): self
    {
        if (!$this->categoryTranslations->contains($categoryTranslation)) {
            $this->categoryTranslations[] = $categoryTranslation;
            $categoryTranslation->setLang($this);
        }

        return $this;
    }

    public function removeCategoryTranslation(CategoryTranslation $categoryTranslation): self
    {
        if ($this->categoryTranslations->removeElement($categoryTranslation)) {
            // set the owning side to null (unless already changed)
            if ($categoryTranslation->getLang() === $this) {
                $categoryTranslation->setLang(null);
            }
        }

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
            $pageTranslation->setLang($this);
        }

        return $this;
    }

    public function removePageTranslation(PageTranslation $pageTranslation): self
    {
        if ($this->pageTranslations->removeElement($pageTranslation)) {
            // set the owning side to null (unless already changed)
            if ($pageTranslation->getLang() === $this) {
                $pageTranslation->setLang(null);
            }
        }

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
            $menuTranslation->setLang($this);
        }

        return $this;
    }

    public function removeMenuTranslation(MenuTranslation $menuTranslation): self
    {
        if ($this->menuTranslations->removeElement($menuTranslation)) {
            // set the owning side to null (unless already changed)
            if ($menuTranslation->getLang() === $this) {
                $menuTranslation->setLang(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TravelTranslation[]
     */
    public function getTravelTranslations(): Collection
    {
        return $this->travelTranslations;
    }

    public function addTravelTranslation(TravelTranslation $travelTranslation): self
    {
        if (!$this->travelTranslations->contains($travelTranslation)) {
            $this->travelTranslations[] = $travelTranslation;
            $travelTranslation->setLang($this);
        }

        return $this;
    }

    public function removeTravelTranslation(TravelTranslation $travelTranslation): self
    {
        if ($this->travelTranslations->removeElement($travelTranslation)) {
            // set the owning side to null (unless already changed)
            if ($travelTranslation->getLang() === $this) {
                $travelTranslation->setLang(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
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
            $optionsTranslation->setLang($this);
        }

        return $this;
    }

    public function removeOptionsTranslation(OptionsTranslations $optionsTranslation): self
    {
        if ($this->optionsTranslations->removeElement($optionsTranslation)) {
            // set the owning side to null (unless already changed)
            if ($optionsTranslation->getLang() === $this) {
                $optionsTranslation->setLang(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Texts[]
     */
    public function getTexts(): Collection
    {
        return $this->texts;
    }

    public function addText(Texts $text): self
    {
        if (!$this->texts->contains($text)) {
            $this->texts[] = $text;
            $text->setLang($this);
        }

        return $this;
    }

    public function removeText(Texts $text): self
    {
        if ($this->texts->removeElement($text)) {
            // set the owning side to null (unless already changed)
            if ($text->getLang() === $this) {
                $text->setLang(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TextTranslations[]
     */
    public function getTextTranslations(): Collection
    {
        return $this->textTranslations;
    }

    public function addTextTranslation(TextTranslations $textTranslation): self
    {
        if (!$this->textTranslations->contains($textTranslation)) {
            $this->textTranslations[] = $textTranslation;
            $textTranslation->setLang($this);
        }

        return $this;
    }

    public function removeTextTranslation(TextTranslations $textTranslation): self
    {
        if ($this->textTranslations->removeElement($textTranslation)) {
            // set the owning side to null (unless already changed)
            if ($textTranslation->getLang() === $this) {
                $textTranslation->setLang(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ContinentTranslation[]
     */
    public function getContinentTranslations(): Collection
    {
        return $this->continentTranslations;
    }

    public function addContinentTranslation(ContinentTranslation $continentTranslation): self
    {
        if (!$this->continentTranslations->contains($continentTranslation)) {
            $this->continentTranslations[] = $continentTranslation;
            $continentTranslation->setLang($this);
        }

        return $this;
    }

    public function removeContinentTranslation(ContinentTranslation $continentTranslation): self
    {
        if ($this->continentTranslations->removeElement($continentTranslation)) {
            // set the owning side to null (unless already changed)
            if ($continentTranslation->getLang() === $this) {
                $continentTranslation->setLang(null);
            }
        }

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
            $popupsTranslation->setLang($this);
        }

        return $this;
    }

    public function removePopupsTranslation(PopupsTranslation $popupsTranslation): self
    {
        if ($this->popupsTranslations->removeElement($popupsTranslation)) {
            // set the owning side to null (unless already changed)
            if ($popupsTranslation->getLang() === $this) {
                $popupsTranslation->setLang(null);
            }
        }

        return $this;
    }

    public function getBlog(): ?Blog
    {
        return $this->blog;
    }

    public function setBlog(?Blog $blog): self
    {
        // unset the owning side of the relation if necessary
        if ($blog === null && $this->blog !== null) {
            $this->blog->setLang(null);
        }

        // set the owning side of the relation if necessary
        if ($blog !== null && $blog->getLang() !== $this) {
            $blog->setLang($this);
        }

        $this->blog = $blog;

        return $this;
    }
}
