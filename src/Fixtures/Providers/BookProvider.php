<?php

namespace App\Fixtures\Providers;

class BookProvider
{
    public function randomTitle(): string
    {
        $titleList = [
            'The People of the Ruins',
            'Alien Clay',
            'The Stardust Grail',
            'Service Model',
            'Masquerade',
            'The Sky on Fire',
            'A Sorceress Comes to Call',
            'A Millionaire Vision: How to Create the Life You Really Want',
            'The Trade Off',
            'Milestoneville Drama Series',
        ];

        return $titleList[array_rand($titleList)];
    }
}
