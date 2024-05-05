<?php

namespace ThomasBrillion\UseIt\Services;

use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use ThomasBrillion\UseIt\Interfaces\Actions\CanUseFeature;
use ThomasBrillion\UseIt\Models\Ability;
use ThomasBrillion\UseIt\Models\Feature;
use ThomasBrillion\UseIt\Models\Usage;
use ThomasBrillion\UseIt\Support\Enums\FeatureType;
use ThomasBrillion\UseIt\Support\ModelResolver;

// TO-DO: implement methods

// revoke consumer to such feature

class FeatureService
{
    protected Builder $featureQuery;

    public function __construct(protected CanUseFeature $creator)
    {
        $this->featureQuery = (new (ModelResolver::getFeatureModel()))->query();
    }

    /**
     * @param  string  $name
     * @param  string  $description
     * @param  FeatureType  $type
     * @param  int|null  $quantity
     * @param  array  $meta
     * @param  bool  $disabled
     * @return Model|Feature
     * @throws Exception
     */
    public function create(
        string $name,
        string $description,
        FeatureType $type,
        int $quantity = null,
        array $meta = [],
        bool $disabled = false
    ): Model|Feature {
        if ($type === FeatureType::Quantity && !$quantity) {
            throw new Exception('Please provide quantity for quantity-typed feature', 401);
        }

        return $this->featureQuery->create([
            'name' => $name,
            'description' => $description,
            'type' => $type,
            'quantity' => $quantity,
            'meta' => $meta,
            'disabled' => $disabled,
        ]);
    }

    /**
     * @param  Feature  $feature
     * @param  DateTime  $expireAt
     * @param  array  $meta
     * @return Ability|Usage
     * @throws Exception
     */
    public function grantFeature(
        Feature $feature,
        DateTime $expireAt,
        array $meta = []
    ): Ability|Usage {
        return match ($feature->type) {
            FeatureType::Ability => (new AbilityService($this->creator))->create($feature, $expireAt, $meta),
            FeatureType::Quantity => (new UsageService($this->creator))->create($feature, $expireAt, $meta),
        };
    }

    /**
     * @param  Feature  $feature
     * @param  int|null  $amount
     * @param  array  $meta
     * @return Model|bool
     * @throws Exception
     */
    public function try(Feature $feature, int $amount = null, array $meta = []): Model|bool
    {
        return match ($feature->type) {
            FeatureType::Ability => (new AbilityService($this->creator))->try($feature),
            FeatureType::Quantity => (new UsageService($this->creator))->try($feature, $amount, $meta)
        };
    }

    public function revokeToFeature(Feature $feature): void
    {
        // delete granted abilities and usages of feature
        $this->creator->abilities()->where('feature_id', $feature->id)->delete();
        $this->creator->usages()->where('feature_id', $feature->id)->delete();
    }
}
