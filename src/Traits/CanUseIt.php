<?php

namespace ThomasBrillion\UseIt\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use ThomasBrillion\UseIt\Models\Ability;
use ThomasBrillion\UseIt\Models\Consumption;
use ThomasBrillion\UseIt\Models\Feature;
use ThomasBrillion\UseIt\Models\Usage;
use ThomasBrillion\UseIt\Services\FeatureService;

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

        return $this->morphMany(Ability::class, 'creator');
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

        return $this->morphMany(Usage::class, 'creator');
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

        return $this->morphMany(Consumption::class, 'consumer');
    }

    /**
     * @param  Feature  $feature
     * @param  int  $amount
     * @param  array  $meta
     * @return false|Model
     * @throws Exception
     */
    public function try(Feature $feature, int $amount, array $meta = []): Model|bool
    {
        return (new FeatureService($this))->try($feature, $amount, $meta);
    }
}
