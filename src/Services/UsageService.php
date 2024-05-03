<?php

namespace ThomasBrillion\UseIt\Services;

use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use ThomasBrillion\UseIt\Interfaces\CanCreateUsage;
use ThomasBrillion\UseIt\Models\Consumption;
use ThomasBrillion\UseIt\Models\Feature;
use ThomasBrillion\UseIt\Models\Usage;
use ThomasBrillion\UseIt\Support\Enums\FeatureType;

class UsageService
{
    public function __construct(protected CanCreateUsage $creator)
    {
    }

    /**
     * @param  Feature  $feature
     * @param  DateTime  $expire_at
     * @param  array  $meta
     * @return Model|Usage
     * @throws Exception
     */
    public function create(Feature $feature, DateTime $expire_at, array $meta = []): Model|Usage
    {
        if ($feature->type !== FeatureType::Quantity) {
            throw new Exception('Feature should be quantity type', 401);
        }

        return $this->creator->usages()->create([
            'feature_id' => $feature->id,
            'name' => $feature->name,
            'total' => $feature->quantity,
            'spend' => 0,
            'level' => 0,
            'expire_at' => $expire_at,
            'meta' => $meta,
        ]);
    }

    /**
     * @param  Feature  $feature
     * @return Collection
     * @throws Exception
     */
    public function getConsumableUsages(Feature $feature): Collection
    {
        if ($feature->type !== FeatureType::Quantity) {
            throw new Exception('Feature must be quantity type');
        }

        return $this->creator->usages()
            ->where('feature_id', $feature->id)
            ->where('expire_at', '>', new DateTime())
            ->whereColumn('total', '>', 'spend')
            ->orderByDesc('level')
            ->get();
    }

    /**
     * @param  Feature  $feature
     * @param  int  $amount
     * @param  array  $meta
     * @return false|Consumption
     * @throws Exception
     */
    public function try(Feature $feature, int $amount, array $meta = []): bool|Consumption
    {
        $usages = $this->getConsumableUsages($feature);

        if ($usages->isEmpty()) {
            throw new Exception('Cannot find usages for the feature', 404);
        }

        $consumptionService = new ConsumptionService($this->creator);

        foreach ($usages as $usage) {
            try {
                return $consumptionService->create($usage, $amount, $meta);
            } catch (Exception $exception) {
                continue;
            }
        }

        return false;
    }
}
