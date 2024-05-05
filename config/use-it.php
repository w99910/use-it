<?php

return [
    'routes' => false,

    'models' => [
        'feature' => \ThomasBrillion\UseIt\Models\Feature::class,

        'ability' => \ThomasBrillion\UseIt\Models\Ability::class,

        'usage' => \ThomasBrillion\UseIt\Models\Usage::class,

        'consumption' => \ThomasBrillion\UseIt\Models\Consumption::class,
    ]
];
