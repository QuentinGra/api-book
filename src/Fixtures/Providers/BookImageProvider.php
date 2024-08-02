<?php

namespace App\Fixtures\Providers;

class BookImageProvider
{
    public function image(): string
    {
        $filenames = [
            'sylius.png',
            'symfony.png'
        ];

        return $filenames[array_rand($filenames)];
    }
}
