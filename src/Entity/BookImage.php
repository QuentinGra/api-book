<?php

namespace App\Entity;

use App\Entity\Utils\DateTimeTrait;
use App\Entity\Utils\EnableTrait;
use App\Repository\BookImageRepository;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: BookImageRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class BookImage
{
    use DateTimeTrait;
    use EnableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['bookImage:read'])]
    private ?int $id = null;

    #[Vich\UploadableField(mapping: 'bookImages', fileNameProperty: 'imageName')]
    #[OA\Property(type: 'image')]
    private ?File $image = null;

    #[ORM\Column(length: 255)]
    #[Groups(['bookImage:read', 'readingList:read'])]
    private ?string $imageName = null;

    #[ORM\ManyToOne(inversedBy: 'bookImages', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Book $book = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $image
     */
    public function setImage(?File $image = null): void
    {
        $this->image = $image;

        if (null !== $image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImage(): ?File
    {
        return $this->image;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
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
}
