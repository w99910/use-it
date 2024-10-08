<?php

namespace ThomasBrillion\UseIt\Services;

use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use ThomasBrillion\UseIt\Interfaces\Actions\CanCreateUsage;
use ThomasBrillion\UseIt\Interfaces\Models\ConsumptionInterface;
use ThomasBrillion\UseIt\Interfaces\Models\FeatureInterface;
use ThomasBrillion\UseIt\Interfaces\Models\UsageInterface;
use ThomasBrillion\UseIt\Support\Enums\FeatureType;

class UsageService
{
    public function __construct(protected CanCreateUsage $creator)
    {
    }

    /**
     * @param  FeatureInterface  $feature
     * @param  DateTime  $expire_at
     * @param  int|null  $total
     * @param  int  $level
     * @param  array  $meta
     * @return Model|UsageInterface
     * @throws Exception
     */
    public function create(
        FeatureInterface $feature,
        DateTime $expire_at,
        int $total = null,
        int $level = 0,
        array $meta = []
    ): Model|UsageInterface {
        if ($feature->getType() !== FeatureType::Quantity) {
            throw new Exception('Feature should be quantity type', 422);
        }

        if (! $total) {
            throw new Exception('Please specify total to create usage', 422);
        }

        return $this->creator->usages()->create([
            'feature_id' => $feature->getId(),
            'name' => $feature->getName(),
            'total' => $total,
            'spend' => 0,
            'level' => $level,
            'expire_at' => $expire_at,
            'meta' => $meta,
        ]);
    }

    /**
     * Getting all usages including expired or invalid usages.
     * @param  FeatureInterface  $feature
     * @return Collection
     * @throws Exception
     */
    public function getAllUsagesOf(FeatureInterface $feature): Collection
    {
        if ($feature->getType() !== FeatureType::Quantity) {
            throw new Exception('Feature must be quantity type');
        }

        return $this->creator->usages()
            ->where('feature_id', $feature->getId())
            ->get();
    }

    /**
     * @param  FeatureInterface  $feature
     * @return Collection
     * @throws Exception
     */
    public function getConsumableUsagesOf(FeatureInterface $feature): Collection
    {
        if ($feature->getType() !== FeatureType::Quantity) {
            throw new Exception('Feature must be quantity type');
        }

        $usages = $this->creator->usages()
            ->where('feature_id', $feature->getId())
            ->where('expire_at', '>', new DateTime())
            ->orderByDesc('level')
            ->get();

        // whereColumn doesn't support in mongodb eloquent builder.
        return $usages->filter(fn ($usage) => $usage->total > $usage->spend);
    }

    /**
     * @param  FeatureInterface  $feature
     * @param  int  $amount
     * @param  array  $meta
     * @param  bool  $dryTest
     * @return bool|ConsumptionInterface
     * @throws Exception
     */
    public function try(
        FeatureInterface $feature,
        int $amount,
        array $meta = [],
        bool $dryTest = false
    ): bool|ConsumptionInterface {
        $usages = $this->getConsumableUsagesOf($feature);

        if ($usages->isEmpty()) {
            return false;
        }

        $consumptionService = new ConsumptionService($this->creator);

        foreach ($usages as $usage) {
            try {
                if ($dryTest) {
                    return $consumptionService->canConsume($usage, $amount);
                }

                return $consumptionService->create($usage, $amount, $meta);
            } catch (Exception $exception) {
                continue;
            }
        }

        return false;
    }

    public function update(FeatureInterface $feature, int $total = null, int $spend = null, DateTime $expire_at = null, array $meta = [])
    {
        $usage = $this->creator->usages()->firstWhere('feature_id', $feature->getId());

        if (! $usage) {
            throw new Exception('Usage not found', 404);
        }

        if ($total) {
            $usage->total = $total;
        }

        if ($spend) {
            $usage->spend = $spend;
        }

        if ($expire_at) {
            $usage->expire_at = $expire_at;
        }

        if (! empty($meta)) {
            $usage->meta = $meta;
        }

        return $usage->save();
    }

    public function list(bool $valid = true)
    {
        $query = $this->creator->usages();

        if ($valid) {
            $query->where('expire_at', '>', new DateTime());
        }

        $usages = $query->orderByDesc('level')->get();

        if ($valid) {
            $usages = $usages->filter(fn ($usage) => $usage->total > $usage->spend);
        }

        return $usages;
    }
}
