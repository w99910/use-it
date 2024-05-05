<?php

namespace ThomasBrillion\UseIt\Interfaces\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface AbilityInterface
{
    public function creator(): MorphTo;

    public function feature(): BelongsTo;
}
