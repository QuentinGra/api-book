<?php

namespace App\Tests\Utils\Providers;

trait NameTrait
{
    public function provideName(): array
    {
        return [
            'max_length' => [
                'name' => str_repeat('a', 256),
                'number' => 1,
            ],
            'empty' => [
                'name' => '',
                'number' => 1,
            ],
        ];
    }
}
