<?php

namespace ThomasBrillion\UseIt\Services;

use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Model;
use ThomasBrillion\UseIt\Interfaces\CanCreateAbility;
use ThomasBrillion\UseIt\Models\Ability;
use ThomasBrillion\UseIt\Models\Feature;
use ThomasBrillion\UseIt\Support\Enums\FeatureType;

class AbilityService
{
    public function __construct(protected CanCreateAbility $creator)
    {

    }

    /**
     * @param  Feature  $feature
     * @param  DateTime  $expire_at
     * @param  array  $meta
     * @return Model|Ability
     * @throws Exception
     */
    public function create(Feature $feature, DateTime $expire_at, array $meta = []): Model|Ability
    {
        if ($feature->type !== FeatureType::Ability) {
            throw new Exception('Feature should be ability type', 401);
        }
        return $this->creator->abilities()->create([
            'name' => $feature->name,
            'feature_id' => $feature->id,
            'expire_at' => $expire_at,
            'meta' => $meta,
        ]);
    }

    public function try(Feature $feature): bool
    {
        return $this->creator->abilities()
            ->where('feature_id', $feature->id)
            ->where('expire_at', '>', new DateTime)->exists();
    }
}
