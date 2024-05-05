<?php

namespace ThomasBrillion\UseIt\Services;

use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use ThomasBrillion\UseIt\Interfaces\Actions\CanConsumeUsage;
use ThomasBrillion\UseIt\Models\Consumption;
use ThomasBrillion\UseIt\Models\Usage;

class ConsumptionService
{
    public function __construct(protected CanConsumeUsage $consumer)
    {

    }

    /**
     * @param  Usage  $usage
     * @param  int  $amount
     * @param  array|null  $meta
     * @return Model|Consumption
     * @throws Exception
     */
    public function create(Usage $usage, int $amount, array $meta = null): Model|Consumption
    {
        $this->canConsume($usage, $amount);

        $consumption = $this->consumer->consumptions()->create([
            'usage_id' => $usage->id,
            'amount' => $amount,
            'meta' => $meta,
        ]);
        $usage->spend += $amount;
        $usage->save();

        return $consumption;
    }

    /**
     * @param  Usage  $usage
     * @param  int  $amount
     * @return true
     * @throws Exception
     */
    public function canConsume(Usage $usage, int $amount): bool
    {
        // if usage is expired or exceed amount when it is consumed
        if (new DateTime() > $usage->expire_at) {
            throw new Exception('Usage is expired', 401);
        }

        if ($usage->total > 0 && $usage->spend + $amount > $usage->total) {
            throw new Exception('Usage is out of limit', 429);
        }

        return true;
    }

    public function getConsumptionsOfUsage(Usage $usage): Collection
    {
        return $this->consumer->consumptions()->where('usage_id', $usage->id)->get();
    }

    public function getConsumptionsOfUsageBetween(
        Usage $usage,
        DateTime|Carbon $startTime,
        DateTime|Carbon $endTime
    ): Collection {
        return $this->consumer->consumptions()
            ->where('usage_id', $usage->id)
            ->whereBetween('created_at', [$startTime, $endTime])
            ->get();
    }
}
