<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[Groups('main')]
    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private string $status;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'categories')]
    private Collection $Category;

    #[ORM\ManyToMany(targetEntity: Category::class, mappedBy: 'Category')]
    private Collection $categories;

    #[ORM\ManyToMany(targetEntity: Blog::class, mappedBy: 'category')]
    private Collection $blogs;

    #[ORM\OneToMany(targetEntity: CategoryTranslation::class, mappedBy: 'category', orphanRemoval: true, cascade: ['persist'])]
    private Collection $categoryTranslations;

    #[ORM\OneToMany(targetEntity: Travel::class, mappedBy: 'category')]
    private Collection $travel;

    #[Groups('main')]
    #[ORM\Column()]
    private string $name;

    #[ORM\ManyToMany(targetEntity: Media::class, mappedBy: 'category')]
    private Collection $media;

    #[ORM\OneToOne(targetEntity: Media::class, inversedBy: 'mainCategoryPhoto', cascade: ['persist', 'remove'])]
    private Media $mainPhoto;

    #[ORM\ManyToOne(targetEntity: Continents::class, inversedBy: 'category')]
    private Continents $continents;

    public function __construct()
    {
        $this->Category = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->blogs = new ArrayCollection();
        $this->categoryTranslations = new ArrayCollection();
        $this->travel = new ArrayCollection();
        $this->media = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getCategory(): Collection
    {
        return $this->Category;
    }

    public function addCategory(self $category): self
    {
        if (!$this->Category->contains($category)) {
            $this->Category[] = $category;
        }

        return $this;
    }

    public function removeCategory(self $category): self
    {
        $this->Category->removeElement($category);

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @return Collection|Blog[]
     */
    public function getBlogs(): Collection
    {
        return $this->blogs;
    }

    public function addBlog(Blog $blog): self
    {
        if (!$this->blogs->contains($blog)) {
            $this->blogs[] = $blog;
            $blog->addCategory($this);
        }

        return $this;
    }

    public function removeBlog(Blog $blog): self
    {
        if ($this->blogs->removeElement($blog)) {
            $blog->removeCategory($this);
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
            $categoryTranslation->setCategory($this);
        }

        return $this;
    }

    public function removeCategoryTranslation(CategoryTranslation $categoryTranslation): self
    {
        if ($this->categoryTranslations->removeElement($categoryTranslation)) {
            // set the owning side to null (unless already changed)
            if ($categoryTranslation->getCategory() === $this) {
                $categoryTranslation->setCategory(null);
            }
        }

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
            $travel->setCategory($this);
        }

        return $this;
    }

    public function removeTravel(Travel $travel): self
    {
        if ($this->travel->removeElement($travel)) {
            // set the owning side to null (unless already changed)
            if ($travel->getCategory() === $this) {
                $travel->setCategory(null);
            }
        }

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

    public function __toString()
    {
        return $this->name;
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
            $medium->addCategory($this);
        }

        return $this;
    }

    public function removeMedium(Media $medium): self
    {
        if ($this->media->removeElement($medium)) {
            $medium->removeCategory($this);
        }

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

    public function getContinents(): ?Continents
    {
        return $this->continents;
    }

    public function setContinents(?Continents $continents): self
    {
        $this->continents = $continents;

        return $this;
    }
}
