<?php

namespace ThomasBrillion\UseIt\Traits;

use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use ThomasBrillion\UseIt\Interfaces\Models\FeatureGroupInterface;
use ThomasBrillion\UseIt\Interfaces\Models\FeatureInterface;
use ThomasBrillion\UseIt\Interfaces\Models\UsageInterface;
use ThomasBrillion\UseIt\Services\ConsumptionService;
use ThomasBrillion\UseIt\Services\FeatureGroupService;
use ThomasBrillion\UseIt\Services\FeatureService;
use ThomasBrillion\UseIt\Services\UsageService;
use ThomasBrillion\UseIt\Support\ModelResolver;

trait CanUseIt
{
    public function featureGroups(): BelongsToMany
    {
        if (! method_exists($this, 'belongsToMany')) {
            throw new Exception('belongsToMany method not found', 404);
        }

        return $this->belongsToMany(ModelResolver::getFeatureGroupModel());
    }

    /**
     * @return MorphMany
     * @throws Exception
     */
    public function abilities(): MorphMany
    {
        if (! method_exists($this, 'morphMany')) {
            throw new Exception('morphMany method not found', 404);
        }

        return $this->morphMany(ModelResolver::getAbilityModel(), 'creator');
    }

    /**
     * @return MorphMany
     * @throws Exception
     */
    public function usages(): MorphMany
    {
        if (! method_exists($this, 'morphMany')) {
            throw new Exception('morphMany method not found', 404);
        }

        return $this->morphMany(ModelResolver::getUsageModel(), 'creator');
    }

    /**
     * @return MorphMany
     * @throws Exception
     */
    public function consumptions(): MorphMany
    {
        if (! method_exists($this, 'morphMany')) {
            throw new Exception('morphMany method not found', 404);
        }

        return $this->morphMany(ModelResolver::getConsumptionModel(), 'consumer');
    }

    /**
     * @param  string|FeatureInterface  $feature
     * @param  int|null  $amount
     * @param  array  $meta
     * @return Model|bool
     * @throws Exception
     */
    public function try(string|FeatureInterface $feature, ?int $amount = null, array $meta = []): Model|bool
    {
        return FeatureService::of($this)->try($feature, $amount, $meta);
    }

    /**
     * @param  string|FeatureInterface|array  $feature
     * @param  int|null  $amount
     * @return bool
     * @throws Exception
     */
    public function canUseFeature(string|FeatureInterface|array $features, ?int $amount = null): bool
    {
        $featureService = FeatureService::of($this);

        if (is_string($features)) {
            $features = explode(',', $features);
        }

        if ($features instanceof FeatureInterface) {
            $features = [$features];
        }

        foreach ($features as $feature) {
            if (! $featureService->canUse($feature, $amount)) {
                return false;
            }
        }

        return true;
    }

    public function canUseAnyFeature(string|array $features, ?int $amount = null)
    {
        $canUse = false;
        if (is_string($features)) {
            $features = explode(',', $features);
        }

        $featureService = FeatureService::of($this);

        foreach ($features as $feature) {
            if ($featureService->canUse($feature, $amount)) {
                $canUse = true;

                break;
            }
        }

        return $canUse;
    }

    /**
     * @param  string|FeatureGroupInterface  $featureGroup
     * @return bool
     */
    public function hasFeatureGroup(string|FeatureGroupInterface $featureGroup)
    {
        return FeatureGroupService::of($this)->hasFeatureGroup($featureGroup);
    }

    /**
     * @param  string|FeatureInterface  $feature
     * @return Collection
     * @throws Exception
     */
    public function getConsumableUsagesOfFeature(string|FeatureInterface $feature): Collection
    {
        $feature = FeatureService::resolveFeature($feature);

        return (new UsageService($this))->getConsumableUsagesOf($feature);
    }

    /**
     * @param  string|FeatureInterface  $feature
     * @return Collection
     * @throws Exception
     */
    public function getAllUsagesOfFeature(string|FeatureInterface $feature): Collection
    {
        $feature = FeatureService::resolveFeature($feature);

        return (new UsageService($this))->getAllUsagesOf($feature);
    }

    /**
     * @param  string|FeatureInterface  $feature
     * @return UsageInterface|null
     * @throws Exception
     */
    public function getCurrentUsageOfFeature(string|FeatureInterface $feature): ?UsageInterface
    {
        $feature = FeatureService::resolveFeature($feature);

        return (new UsageService($this))->getConsumableUsagesOf($feature)->first();
    }

    /**
     * @param  string|FeatureInterface  $feature
     * @param  string|DateTime|null  $startDate
     * @param  string|DateTime|null  $endDate
     * @return array
     * @throws Exception
     */
    public function getConsumptionsOfFeature(
        string|FeatureInterface $feature,
        string|DateTime $startDate = null,
        string|DateTime $endDate = null
    ): array {
        $feature = FeatureService::resolveFeature($feature);

        $consumptionService = new ConsumptionService($this);

        $consumptions = [];

        if ($startDate && is_string($startDate)) {
            $startDate = new DateTime($startDate);
        }

        if ($endDate && is_string($endDate)) {
            $endDate = new DateTime($endDate);
        }

        foreach ($this->getAllUsagesOfFeature($feature) as $usage) {
            if ($startDate && $endDate) {
                $consumptions[$usage->getId() ?? $usage->id] = $consumptionService->getConsumptionsOfUsageBetween(
                    $usage,
                    $startDate,
                    $endDate
                );

                continue;
            }
            $consumptions[$usage->getId() ?? $usage->id] = $consumptionService->getConsumptionsOfUsage($usage);
        }

        return $consumptions;
    }
}
