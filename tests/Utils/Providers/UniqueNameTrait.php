<?php

namespace App\Tests\Utils\Providers;

trait UniqueNameTrait
{
    public function provideName(): array
    {
        return [
            'non_unique' => [
                'name' => 'test',
                'number' => 1,
            ],
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
