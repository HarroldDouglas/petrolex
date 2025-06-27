<?php

namespace App\Transformers;

use Illuminate\Support\Facades\Log;
use Spatie\Enum\Enum;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;

class EnumValueTransformer implements Transformer
{
    public function transform(
        DataProperty $property,
        mixed $value,
        TransformationContext $context): mixed
    {
        Log::info('EnumValueTransformer', ['EnumValueTransformer']);
        if ($value instanceof Enum) {
            return $value->value;
        }

        return $value;
    }
}
