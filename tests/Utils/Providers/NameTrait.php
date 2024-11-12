<?php

namespace App\Tests\Utils\Providers;

trait NameTrait
{
    public function provideName(): array
    {
        return [
            'max_length' => [
                'name' => str_repeat('a', 256),
            ],
            'empty' => [
                'name' => '',
            ],
        ];
    }
}
