<?php

namespace App\Entity;

use App\Entity\Utils\DateTimeTrait;
use App\Entity\Utils\EnableTrait;
use App\Repository\CategoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[UniqueEntity(fields: ['name'], message: 'Une categorie existe déjàs avec ce nom')]
#[ORM\HasLifecycleCallbacks]
class Category
{
    use DateTimeTrait;
    use EnableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['category:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.',
    )]
    #[Assert\NotBlank]
    #[Groups(['category:read'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['category:read'])]
    private ?string $description = null;

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

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
