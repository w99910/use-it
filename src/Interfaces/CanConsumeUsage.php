<?php

namespace ThomasBrillion\UseIt\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface CanConsumeUsage
{
    public function consumptions(): MorphMany;
}
