<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Utils\DateTimeTrait;
use App\Repository\ReadingListRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ReadingListRepository::class)]
#[UniqueEntity(fields: ['name'], message: 'Une categorie existe déjàs avec ce nom')]
#[ORM\HasLifecycleCallbacks]
class ReadingList
{
    use DateTimeTrait;

    public const STATUS_READING = 'reading';
    public const STATUS_READ = 'read';
    public const STATUS_WISH = 'wish';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['readingList:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Groups(['readingList:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    #[Assert\Length(max: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(
        choices: [
            self::STATUS_READING,
            self::STATUS_READ,
            self::STATUS_WISH,
        ]
    )]
    #[Groups(['readingList:read'])]
    private ?string $status = null;

    /**
     * @var Collection<int, Book>
     */
    #[ORM\ManyToMany(targetEntity: Book::class)]
    private Collection $books;

    #[ORM\ManyToOne(inversedBy: 'readingLists')]
    private ?User $user = null;

    public function __construct()
    {
        $this->books = new ArrayCollection();
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Book>
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }

    public function addBook(Book $book): static
    {
        if (!$this->books->contains($book)) {
            $this->books->add($book);
        }

        return $this;
    }

    public function removeBook(Book $book): static
    {
        $this->books->removeElement($book);

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
