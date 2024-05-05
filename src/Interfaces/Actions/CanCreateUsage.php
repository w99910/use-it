<?php

namespace ThomasBrillion\UseIt\Interfaces\Actions;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface CanCreateUsage
{
    public function usages(): MorphMany;
}
