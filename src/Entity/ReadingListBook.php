<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReadingListBookRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReadingListBookRepository::class)]
class ReadingListBook
{
    public const STATUS_READING = 'reading';
    public const STATUS_READ = 'read';
    public const STATUS_WISH = 'wish';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'readingListBooks')]
    private ?Book $book = null;

    #[ORM\ManyToOne(inversedBy: 'readingListBooks')]
    private ?ReadingList $readingList = null;

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

    public function __construct()
    {
        $this->status = self::STATUS_WISH;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): static
    {
        $this->book = $book;

        return $this;
    }

    public function getReadingList(): ?ReadingList
    {
        return $this->readingList;
    }

    public function setReadingList(?ReadingList $readingList): static
    {
        $this->readingList = $readingList;

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
}
