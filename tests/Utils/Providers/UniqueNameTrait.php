<?php

namespace App\Tests\Utils\Providers;

trait UniqueNameTrait
{
    public function provideName(): array
    {
        return [
            'non_unique' => [
                'name' => 'test',
            ],
            'max_length' => [
                'name' => str_repeat('a', 256),
            ],
            'empty' => [
                'name' => '',
            ],
        ];
    }
}
