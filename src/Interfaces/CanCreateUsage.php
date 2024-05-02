<?php

namespace ThomasBrillion\UseIt\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface CanCreateUsage
{
    public function usages(): MorphMany;
}
