<?php

namespace ThomasBrillion\UseIt\Interfaces\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface FeatureGroupInterface
{
    public function features(): BelongsToMany;

    public function getId(): string|int;
}