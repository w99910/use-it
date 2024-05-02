<?php

namespace ThomasBrillion\UseIt\Support\Enums;

enum FeatureType: string
{
    case Ability = 'ability';
    case Quantity = 'quantity';

    public static function values(): array
    {
        return [
            FeatureType::Ability->value,
            FeatureType::Quantity->value
        ];
    }
}
