<?php

namespace App\Entity;

use App\Service\UploadHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
class Media
{
    #[Groups('main')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[Groups('main')]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $type;

    #[Assert\NotBlank]
    #[Groups('main')]
    #[ORM\Column()]
    private ?string $name = null;

    #[Groups('main')]
    #[ORM\Column(nullable: true)]
    private ?string $path = null;

    #[Groups('main')]
    #[ORM\Column(nullable: true)]
    private ?string $url = null;

    #[ORM\ManyToMany(targetEntity: Travel::class, mappedBy: 'media')]
    private Collection $travel;

    #[Groups('main')]
    #[ORM\Column()]
    private ?string $filename = null;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'media')]
    private Collection $category;

    #[ORM\OneToOne(targetEntity: Category::class, mappedBy: 'mainPhoto', cascade: ['persist', 'remove'])]
    private ?Category $mainCategoryPhoto = null;

    #[ORM\ManyToMany(targetEntity: Pages::class, mappedBy: 'media')]
    private Collection $pages;

    #[ORM\OneToOne(targetEntity: Pages::class, mappedBy: 'mainPhoto', cascade: ['persist', 'remove'])]
    private ?Pages $page = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isGallery;

    #[ORM\ManyToMany(targetEntity: Articles::class, mappedBy: 'media')]
    private Collection $articles;

    #[ORM\OneToOne(targetEntity: Articles::class, mappedBy: 'mainPhoto', cascade: ['persist', 'remove'])]
    private ?Articles $article = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isYTVideo;

    #[ORM\ManyToMany(targetEntity: Popups::class, mappedBy: 'media')]
    private Collection $popups;

    public function __construct()
    {
        $this->travel = new ArrayCollection();
        $this->category = new ArrayCollection();
        $this->pages = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->popups = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Collection|Travel[]
     */
    public function getTravel(): Collection
    {
        return $this->travel;
    }

    public function addTravel(Travel $travel): self
    {
        if (!$this->travel->contains($travel)) {
            $this->travel[] = $travel;
            $travel->addMedium($this);
        }

        return $this;
    }

    public function removeTravel(Travel $travel): self
    {
        if ($this->travel->removeElement($travel)) {
            $travel->removeMedium($this);
        }

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getMediaPath()
    {
        return UploadHelper::MEDIA_FOLDER.'/'.$this->getFilename();
    }

    public function getDocumentPath()
    {
        return UploadHelper::DOCUMENT.'/'.$this->getFilename();
    }

    public function __toString()
    {
        return $this->name;
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
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->category->removeElement($category);

        return $this;
    }

    public function getMainCategoryPhoto(): ?Category
    {
        return $this->mainCategoryPhoto;
    }

    public function setMainCategoryPhoto(?Category $mainCategoryPhoto): self
    {
        // unset the owning side of the relation if necessary
        if ($mainCategoryPhoto === null && $this->mainCategoryPhoto !== null) {
            $this->mainCategoryPhoto->setMainPhoto(null);
        }

        // set the owning side of the relation if necessary
        if ($mainCategoryPhoto !== null && $mainCategoryPhoto->getMainPhoto() !== $this) {
            $mainCategoryPhoto->setMainPhoto($this);
        }

        $this->mainCategoryPhoto = $mainCategoryPhoto;

        return $this;
    }

    /**
     * @return Collection|Pages[]
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(Pages $page): self
    {
        if (!$this->pages->contains($page)) {
            $this->pages[] = $page;
            $page->addMedium($this);
        }

        return $this;
    }

    public function removePage(Pages $page): self
    {
        if ($this->pages->removeElement($page)) {
            $page->removeMedium($this);
        }

        return $this;
    }

    public function getPage(): ?Pages
    {
        return $this->page;
    }

    public function setPage(?Pages $page): self
    {
        // unset the owning side of the relation if necessary
        if ($page === null && $this->page !== null) {
            $this->page->setMainPhoto(null);
        }

        // set the owning side of the relation if necessary
        if ($page !== null && $page->getMainPhoto() !== $this) {
            $page->setMainPhoto($this);
        }

        $this->page = $page;

        return $this;
    }

    public function getIsGallery(): ?bool
    {
        return $this->isGallery;
    }

    public function setIsGallery(bool $isGallery): self
    {
        $this->isGallery = $isGallery;

        return $this;
    }

    /**
     * @return Collection|Articles[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Articles $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->addMedium($this);
        }

        return $this;
    }

    public function removeArticle(Articles $article): self
    {
        if ($this->articles->removeElement($article)) {
            $article->removeMedium($this);
        }

        return $this;
    }

    public function getArticle(): ?Articles
    {
        return $this->article;
    }

    public function setArticle(?Articles $article): self
    {
        // unset the owning side of the relation if necessary
        if ($article === null && $this->article !== null) {
            $this->article->setMainPhoto(null);
        }

        // set the owning side of the relation if necessary
        if ($article !== null && $article->getMainPhoto() !== $this) {
            $article->setMainPhoto($this);
        }

        $this->article = $article;

        return $this;
    }

    public function getIsYTVideo(): ?bool
    {
        return $this->isYTVideo;
    }

    public function setIsYTVideo(bool $isYTVideo): self
    {
        $this->isYTVideo = $isYTVideo;

        return $this;
    }

    /**
     * @return Collection|Popups[]
     */
    public function getPopups(): Collection
    {
        return $this->popups;
    }

    public function addPopup(Popups $popup): self
    {
        if (!$this->popups->contains($popup)) {
            $this->popups[] = $popup;
            $popup->addMedium($this);
        }

        return $this;
    }

    public function removePopup(Popups $popup): self
    {
        if ($this->popups->removeElement($popup)) {
            $popup->removeMedium($this);
        }

        return $this;
    }
}
