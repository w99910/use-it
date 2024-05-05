<?php

namespace ThomasBrillion\UseIt\Interfaces\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface FeatureInterface
{
    public function usages(): HasMany;

    public function abilities(): HasMany;
}
