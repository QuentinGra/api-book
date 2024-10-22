<?php

namespace App\Entity;

use App\Entity\Utils\DateTimeTrait;
use App\Entity\Utils\EnableTrait;
use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[UniqueEntity(fields: ['name'], message: 'Un livre existe déjàs avec ce nom')]
#[ORM\HasLifecycleCallbacks]
class Book
{
    use DateTimeTrait;
    use EnableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['book:read', 'category:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.',
    )]
    #[Assert\NotBlank]
    #[Groups(['book:read', 'readingList:read', 'rating:read', 'category:read'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['book:read'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    #[Groups(['book:read', 'readingList:read'])]
    private ?\DateTimeInterface $dateEdition = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'books', cascade: ['persist'])]
    #[Groups(['book:read'])]
    private Collection $categories;

    #[ORM\ManyToOne(inversedBy: 'books', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['book:read'])]
    #[Assert\NotBlank]
    private ?Edition $edition = null;

    #[ORM\ManyToOne(inversedBy: 'books', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['book:read', 'category:read'])]
    #[Assert\NotBlank]
    private ?Author $author = null;

    /**
     * @var Collection<int, BookVariant>
     */
    #[ORM\ManyToMany(targetEntity: BookVariant::class, mappedBy: 'books')]
    #[Groups(['book:read'])]
    private Collection $bookVariants;

    /**
     * @var Collection<int, BookImage>
     */
    #[ORM\OneToMany(targetEntity: BookImage::class, mappedBy: 'book', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['readingList:read', 'rating:read', 'category:read'])]
    private Collection $bookImages;

    /**
     * @var Collection<int, ReadingListBook>
     */
    #[ORM\OneToMany(targetEntity: ReadingListBook::class, mappedBy: 'book', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['readingList:read'])]
    private Collection $readingListBooks;

    /**
     * @var Collection<int, Rating>
     */
    #[ORM\OneToMany(targetEntity: Rating::class, mappedBy: 'book', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $ratings;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->bookVariants = new ArrayCollection();
        $this->bookImages = new ArrayCollection();
        $this->readingListBooks = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDateEdition(): ?\DateTimeInterface
    {
        return $this->dateEdition;
    }

    public function setDateEdition(\DateTimeInterface $dateEdition): static
    {
        $this->dateEdition = $dateEdition;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function getEdition(): ?Edition
    {
        return $this->edition;
    }

    public function setEdition(?Edition $edition): static
    {
        $this->edition = $edition;

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, BookVariant>
     */
    public function getBookVariants(): Collection
    {
        return $this->bookVariants;
    }

    public function addBookVariant(BookVariant $bookVariant): static
    {
        if (!$this->bookVariants->contains($bookVariant)) {
            $this->bookVariants->add($bookVariant);
            $bookVariant->addBook($this);
        }

        return $this;
    }

    public function removeBookVariant(BookVariant $bookVariant): static
    {
        if ($this->bookVariants->removeElement($bookVariant)) {
            $bookVariant->removeBook($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, BookImage>
     */
    public function getBookImages(): Collection
    {
        return $this->bookImages;
    }

    public function addBookImage(BookImage $bookImage): static
    {
        if (!$this->bookImages->contains($bookImage)) {
            $this->bookImages->add($bookImage);
            $bookImage->setBook($this);
        }

        return $this;
    }

    public function removeBookImage(BookImage $bookImage): static
    {
        if ($this->bookImages->removeElement($bookImage)) {
            // set the owning side to null (unless already changed)
            if ($bookImage->getBook() === $this) {
                $bookImage->setBook(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReadingListBook>
     */
    public function getReadingListBooks(): Collection
    {
        return $this->readingListBooks;
    }

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): static
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings->add($rating);
            $rating->setBook($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): static
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getBook() === $this) {
                $rating->setBook(null);
            }
        }

        return $this;
    }
}
