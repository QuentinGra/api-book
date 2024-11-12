<?php

namespace App\Entity\Utils;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait EnableTrait
{
    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull]
    #[Assert\Type(
        type: 'boolean',
        message: 'The value {{ value }} need to be of type {{ type }}'
    )]
    #[Groups(['app:read'])]
    private ?bool $enable = null;

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function setEnable(?bool $enable): static
    {
        $this->enable = $enable;

        return $this;
    }
}
