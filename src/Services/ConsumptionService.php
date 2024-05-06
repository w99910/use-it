<?php

namespace ThomasBrillion\UseIt\Services;

use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use ThomasBrillion\UseIt\Interfaces\Actions\CanConsumeUsage;
use ThomasBrillion\UseIt\Interfaces\Models\ConsumptionInterface;
use ThomasBrillion\UseIt\Interfaces\Models\UsageInterface;

class ConsumptionService
{
    public function __construct(protected CanConsumeUsage $consumer)
    {

    }

    /**
     * @param  UsageInterface  $usage
     * @param  int  $amount
     * @param  array|null  $meta
     * @return Model|ConsumptionInterface
     * @throws Exception
     */
    public function create(UsageInterface $usage, int $amount, array $meta = null): Model|ConsumptionInterface
    {
        $this->canConsume($usage, $amount);

        $consumption = $this->consumer->consumptions()->create([
            'usage_id' => $usage->getId(),
            'amount' => $amount,
            'meta' => $meta,
        ]);
        $usage->consume($usage->getSpend() + $amount);

        return $consumption;
    }

    /**
     * @param  UsageInterface  $usage
     * @param  int  $amount
     * @return bool
     * @throws Exception
     */
    public function canConsume(UsageInterface $usage, int $amount): bool
    {
        // if usage is expired or exceed amount when it is consumed
        if (new DateTime() > $usage->getExpiredAt()) {
            throw new Exception('Usage is expired', 401);
        }

        if ($usage->getTotal() > 0 && $usage->getSpend() + $amount > $usage->getTotal()) {
            throw new Exception('Usage is out of limit', 429);
        }

        return true;
    }

    public function getConsumptionsOfUsage(UsageInterface $usage): Collection
    {
        return $this->consumer->consumptions()->where('usage_id', $usage->getId())->get();
    }

    public function getConsumptionsOfUsageBetween(
        UsageInterface $usage,
        DateTime|Carbon $startTime,
        DateTime|Carbon $endTime
    ): Collection {
        return $this->consumer->consumptions()
            ->where('usage_id', $usage->getId())
            ->whereBetween('created_at', [$startTime, $endTime])
            ->get();
    }
}
