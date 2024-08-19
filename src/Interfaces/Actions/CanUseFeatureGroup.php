<?php

namespace ThomasBrillion\UseIt\Interfaces\Actions;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface CanUseFeatureGroup extends CanUseFeature
{
    public function featureGroups(): BelongsToMany;
}
