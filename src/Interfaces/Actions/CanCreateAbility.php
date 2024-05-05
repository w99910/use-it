<?php

namespace ThomasBrillion\UseIt\Interfaces\Actions;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface CanCreateAbility
{
    public function abilities(): MorphMany;
}
