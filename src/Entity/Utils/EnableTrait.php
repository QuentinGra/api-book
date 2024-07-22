<?php

namespace App\Entity\Utils;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

trait EnableTrait
{
    #[ORM\Column(type: 'boolean')]
    #[Groups(['app:read'])]
    private ?bool $enable = null;

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

        return $this;
    }
}
