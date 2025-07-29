<?php

namespace App\Knowledge\Category;

use Illuminate\Support\Str;

enum CategoryType: string
{
    case DISEASE_GROUP = 'disease-group';
    case REGION_GROUP = 'region-group';
    case SPECIFIC_PART = 'specific-part';
    case TYPICAL_GROUP = 'typical-group';

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(static fn (CategoryType $type) => [
                $type->value => (string) Str::of($type->value)->title()->replace('-', ' '),
            ])
            ->toArray();
    }
}
