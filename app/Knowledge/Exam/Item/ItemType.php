<?php

namespace App\Knowledge\Exam\Item;

use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

enum ItemType: string implements \JsonSerializable
{
    case SIMPLE = 'simple';
    case MULTIPLE_CHOICE = 'multiple-choice';
    case ESSAY = 'essay';
    case INTERVIEW = 'interview';

    public static function getOptions($except = []): array
    {
        return collect(self::cases())
            ->mapWithKeys(function (self $type) use ($except) {
                if (! in_array($type->value, $except)) {
                    return [$type->value => (string) Str::of($type->value)->title()->replace('-', ' ')];
                }

                return [];
            })
            ->toArray();
    }

    public function preference(): array
    {
        return match ($this) {
            self::SIMPLE => [
                'is_visible' => false,
                'is_scoreable' => false,
                'has_answers' => false,
                'description' => null,
            ],
            self::MULTIPLE_CHOICE => [
                'is_visible' => true,
                'is_scoreable' => true,
                'has_answers' => true,
                'description' => null,
            ],
            self::ESSAY, self::INTERVIEW => [
                'is_visible' => true,
                'is_scoreable' => true,
                'has_answers' => false,
                'description' => null,
            ],
        };
    }

    #[ArrayShape(['value' => 'string', 'name' => 'mixed', 'preference' => 'array'])]
    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
            'name' => self::getOptions()[$this->value],
            'preference' => $this->preference(),
        ];
    }
}
