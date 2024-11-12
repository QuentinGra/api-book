<?php

namespace App\Tests\Utils\Providers;

trait EnableTrait
{
    public function provideEnable(): array
    {
        return [
            'empty' => [
                'enable' => null,
            ],
        ];
    }
}
