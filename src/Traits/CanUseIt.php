<?php

namespace ThomasBrillion\UseIt\Traits;

use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use ThomasBrillion\UseIt\Interfaces\Models\UsageInterface;
use ThomasBrillion\UseIt\Models\Feature;
use ThomasBrillion\UseIt\Services\ConsumptionService;
use ThomasBrillion\UseIt\Services\FeatureService;
use ThomasBrillion\UseIt\Services\UsageService;
use ThomasBrillion\UseIt\Support\ModelResolver;

trait CanUseIt
{
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
     * @param  string|Feature  $feature
     * @param  int  $amount
     * @param  array  $meta
     * @return Model|bool
     * @throws Exception
     */
    public function try(string|Feature $feature, int $amount, array $meta = []): Model|bool
    {
        return (new FeatureService($this))->try($feature, $amount, $meta);
    }

    /**
     * @param  string|Feature  $feature
     * @param  int|null  $amount
     * @return bool
     * @throws Exception
     */
    public function canUseFeature(string|Feature $feature, int $amount = null): bool
    {
        return (new FeatureService($this))->canUse($feature, $amount);
    }

    /**
     * @param  string|Feature  $feature
     * @return Collection
     * @throws Exception
     */
    public function getConsumableUsagesOfFeature(string|Feature $feature): Collection
    {
        $feature = (new FeatureService($this))->resolveFeature($feature);

        return (new UsageService($this))->getConsumableUsages($feature);
    }

    /**
     * @param  string|Feature  $feature
     * @return Collection
     * @throws Exception
     */
    public function getAllUsagesOfFeature(string|Feature $feature): Collection
    {
        $feature = (new FeatureService($this))->resolveFeature($feature);

        return (new UsageService($this))->getAllUsages($feature);
    }

    /**
     * @param  string|Feature  $feature
     * @return UsageInterface|null
     * @throws Exception
     */
    public function getCurrentUsageOfFeature(string|Feature $feature): ?UsageInterface
    {
        $feature = (new FeatureService($this))->resolveFeature($feature);

        return (new UsageService($this))->getConsumableUsages($feature)->first();
    }

    /**
     * @param  string|Feature  $feature
     * @param  string|DateTime|null  $startDate
     * @param  string|DateTime|null  $endDate
     * @return array
     * @throws Exception
     */
    public function getConsumptionsOfFeature(
        string|Feature $feature,
        string|DateTime $startDate = null,
        string|DateTime $endDate = null
    ): array {
        $feature = (new FeatureService($this))->resolveFeature($feature);

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
