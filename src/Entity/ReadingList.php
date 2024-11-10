<?php

namespace App\Entity;

use App\Entity\Utils\DateTimeTrait;
use App\Repository\ReadingListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReadingListRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ReadingList
{
    use DateTimeTrait;

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

    #[ORM\ManyToOne(inversedBy: 'readingLists')]
    private ?User $user = null;

    /**
     * @var Collection<int, ReadingListBook>
     */
    #[ORM\OneToMany(targetEntity: ReadingListBook::class, mappedBy: 'readingList', cascade: ['remove'])]
    private Collection $readingListBooks;

    public function __construct()
    {
        $this->readingListBooks = new ArrayCollection();
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, ReadingListBook>
     */
    public function getReadingListBooks(): Collection
    {
        return $this->readingListBooks;
    }

    public function addReadingListBook(ReadingListBook $readingListBook): static
    {
        if (!$this->readingListBooks->contains($readingListBook)) {
            $this->readingListBooks->add($readingListBook);
            $readingListBook->setReadingList($this);
        }

        return $this;
    }

    public function removeReadingListBook(ReadingListBook $readingListBook): static
    {
        if ($this->readingListBooks->removeElement($readingListBook)) {
            // set the owning side to null (unless already changed)
            if ($readingListBook->getReadingList() === $this) {
                $readingListBook->setReadingList(null);
            }
        }

        return $this;
    }
}
