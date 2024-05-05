<?php

namespace ThomasBrillion\UseIt\Interfaces\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface ConsumptionInterface
{
    public function usage(): BelongsTo;

    public function consumer(): MorphTo;
}
