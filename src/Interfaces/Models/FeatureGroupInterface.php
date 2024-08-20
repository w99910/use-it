<?php

namespace ThomasBrillion\UseIt\Interfaces\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface FeatureGroupInterface
{
    public function features(): BelongsToMany;

    public function getId(): string|int;
}
