<?php

namespace ThomasBrillion\UseIt\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface CanCreateAbility
{
    public function abilities(): MorphMany;
}
