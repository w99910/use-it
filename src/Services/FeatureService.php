<?php

namespace ThomasBrillion\UseIt\Services;

use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use ThomasBrillion\UseIt\Interfaces\Actions\CanUseFeature;
use ThomasBrillion\UseIt\Interfaces\Models\FeatureInterface;
use ThomasBrillion\UseIt\Models\Ability;
use ThomasBrillion\UseIt\Models\Usage;
use ThomasBrillion\UseIt\Support\Enums\FeatureType;
use ThomasBrillion\UseIt\Support\ModelResolver;

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
     * @param  array  $meta
     * @param  bool  $disabled
     * @return Model|FeatureInterface
     * @throws Exception
     */
    public function create(
        string $name,
        string $description,
        FeatureType $type,
        array $meta = [],
        bool $disabled = false
    ): Model|FeatureInterface {
        return $this->featureQuery->create([
            'name' => $name,
            'description' => $description,
            'type' => $type,
            'meta' => $meta,
            'disabled' => $disabled,
        ]);
    }

    /**
     * @param  string  $featureName
     * @return Model|null
     */
    public function findFeature(string $featureName): Model|null
    {
        return $this->featureQuery->firstWhere('name', $featureName);
    }

    /**
     * @param  string|FeatureInterface  $feature
     * @return Model|FeatureInterface
     * @throws Exception
     */
    public function resolveFeature(string|FeatureInterface $feature): Model|FeatureInterface
    {
        if (is_string($feature)) {
            $feature = $this->findFeature($feature);
            if (! $feature) {
                throw new Exception('Feature not found', 404);
            }
        }

        return $feature;
    }

    /**
     * @param  FeatureInterface|string  $feature
     * @param  DateTime  $expireAt
     * @param  int|null  $total
     * @param  int  $level
     * @param  array  $meta
     * @return Ability|Usage
     * @throws Exception
     */
    public function grantFeature(
        FeatureInterface|string $feature,
        DateTime $expireAt,
        int $total = null,
        int $level = 0,
        array $meta = []
    ): Ability|Usage {
        $feature = $this->resolveFeature($feature);

        return match ($feature->getType()) {
            FeatureType::Ability => (new AbilityService($this->creator))->create($feature, $expireAt, $meta),
            FeatureType::Quantity => (new UsageService($this->creator))->create(
                $feature,
                $expireAt,
                $total,
                $level,
                $meta
            ),
        };
    }

    /**
     * @param  array  $features
     * @param  DateTime  $expireAt
     * @param  int|null  $total
     * @param  int  $level
     * @param  array  $meta
     * @return array
     * @throws Exception
     */
    public function grantFeatures(
        array $features,
        DateTime $expireAt,
        int $total = null,
        int $level = 0,
        array $meta = []
    ): array {
        $response = [];
        foreach ($features as $feature) {
            $response[] = $this->grantFeature($feature, $expireAt, $total, $level, $meta);
        }

        return $response;
    }

    /**
     * @param  FeatureInterface|string  $feature
     * @return bool
     * @throws Exception
     */
    public function disableFeature(FeatureInterface|string $feature): bool
    {
        $feature = $this->resolveFeature($feature);
        if (! $feature->isDisabled()) {
            $feature->toggleDisability();

            return true;
        }

        return false;
    }

    /**
     * @param  FeatureInterface|string  $feature
     * @return bool
     * @throws Exception
     */
    public function enableFeature(FeatureInterface|string $feature): bool
    {
        $feature = $this->resolveFeature($feature);
        if ($feature->isDisabled()) {
            $feature->toggleDisability();

            return true;
        }

        return false;
    }

    /**
     * @param  FeatureInterface|string  $feature
     * @param  int|null  $amount
     * @param  array  $meta
     * @return Model|bool
     * @throws Exception
     */
    public function try(
        FeatureInterface|string $feature,
        int $amount = null,
        array $meta = []
    ): Model|bool {
        $feature = $this->resolveFeature($feature);
        if ($feature->isDisabled()) {
            return false;
        }

        return match ($feature->getType()) {
            FeatureType::Ability => (new AbilityService($this->creator))->try($feature),
            FeatureType::Quantity => (new UsageService($this->creator))->try($feature, $amount, $meta)
        };
    }

    /**
     * @param  FeatureInterface|string  $feature
     * @param  int|null  $amount
     * @return bool
     * @throws Exception
     */
    public function canUse(
        FeatureInterface|string $feature,
        int $amount = null
    ): bool {
        $feature = $this->resolveFeature($feature);
        if ($feature->isDisabled()) {
            return false;
        }

        return match ($feature->getType()) {
            FeatureType::Ability => (new AbilityService($this->creator))->try($feature),
            FeatureType::Quantity => (new UsageService($this->creator))->try($feature, $amount, [], true)
        };
    }

    /**
     * @param  FeatureInterface|string  $feature
     * @return void
     * @throws Exception
     */
    public function revokeToFeature(FeatureInterface|string $feature): void
    {
        $feature = $this->resolveFeature($feature);
        // delete granted abilities and usages of feature
        $this->creator->abilities()->where('feature_id', $feature->getId())->delete();
        $this->creator->usages()->where('feature_id', $feature->getId())->delete();
    }
}
