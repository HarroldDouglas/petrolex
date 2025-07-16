<?php

namespace App\Casts;

use Spatie\Enum\Laravel\Enum;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class SpatieEnumCast implements Cast
{
    /** @var class-string<Enum> */
    protected string $enumClass;

    public function __construct(string $enumClass)
    {
        $this->enumClass = $enumClass;
    }

    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if (is_null($value)) {
            return null;
        }

        /** @var class-string<Enum> $enumClass */
        $enumClass = $this->enumClass;

        return $enumClass::from($value);
    }
}
