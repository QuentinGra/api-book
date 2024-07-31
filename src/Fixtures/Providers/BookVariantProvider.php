<?php

namespace App\Fixtures\Providers;

class BookVariantProvider
{
    public function randomType(): string
    {
        $typeList = [
            'brocher',
            'poche',
            'relier',
        ];

        return $typeList[array_rand($typeList)];
    }
}
