<?php

namespace ThomasBrillion\UseIt\Interfaces\Actions;

use Illuminate\Database\Eloquent\Model;
use ThomasBrillion\UseIt\Models\Feature;

interface CanUseFeature extends CanCreateAbility, CanCreateUsage, CanConsumeUsage
{
    public function try(string|Feature $feature, int $amount, array $meta = []): Model|bool;

    public function canUseFeature(string|Feature $feature, int $amount = null): bool;
}
