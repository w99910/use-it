<?php

namespace ThomasBrillion\UseIt\Interfaces\Actions;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface CanConsumeUsage
{
    public function consumptions(): MorphMany;
}
