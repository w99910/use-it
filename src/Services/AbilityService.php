<?php

namespace ThomasBrillion\UseIt\Services;

use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use ThomasBrillion\UseIt\Interfaces\Actions\CanCreateAbility;
use ThomasBrillion\UseIt\Interfaces\Models\AbilityInterface;
use ThomasBrillion\UseIt\Interfaces\Models\FeatureInterface;
use ThomasBrillion\UseIt\Support\Enums\FeatureType;

class AbilityService
{
    public function __construct(protected CanCreateAbility $creator)
    {

    }

    /**
     * @param  FeatureInterface  $feature
     * @param  DateTime  $expire_at
     * @param  array  $meta
     * @return Model|AbilityInterface
     * @throws Exception
     */
    public function create(FeatureInterface $feature, DateTime $expire_at, array $meta = []): Model|AbilityInterface
    {
        if ($feature->getType() !== FeatureType::Ability) {
            throw new Exception('Feature should be ability type', 401);
        }

        return $this->creator->abilities()->create([
            'name' => $feature->getName(),
            'feature_id' => $feature->getId(),
            'expire_at' => $expire_at,
            'meta' => $meta,
        ]);
    }

    public function try(FeatureInterface $feature): bool
    {
        return $this->creator->abilities()
            ->where('feature_id', $feature->getId())
            ->where('expire_at', '>', new DateTime())->exists();
    }

    public function list(array $meta = []): Collection
    {
        $query = $this->creator->abilities();
        if (count($meta) > 0) {
            foreach ($meta as $key => $value) {
                $aggregateName = is_array($value) ? "whereIn" : "where";
                $query->$aggregateName("meta.$key", $value);
            }
        }

        return $query->get();
    }
}
