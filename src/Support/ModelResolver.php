<?php

namespace ThomasBrillion\UseIt\Support;

use ThomasBrillion\UseIt\Interfaces\Models\AbilityInterface;
use ThomasBrillion\UseIt\Interfaces\Models\ConsumptionInterface;
use ThomasBrillion\UseIt\Interfaces\Models\FeatureInterface;
use ThomasBrillion\UseIt\Interfaces\Models\UsageInterface;
use ThomasBrillion\UseIt\Models\Ability;
use ThomasBrillion\UseIt\Models\Consumption;
use ThomasBrillion\UseIt\Models\Feature;
use ThomasBrillion\UseIt\Models\FeatureGroup;
use ThomasBrillion\UseIt\Models\Usage;

class ModelResolver
{
    protected array $models = [
        'feature' => Feature::class,
        'feature-group' => FeatureGroup::class,
        'ability' => Ability::class,
        'usage' => Usage::class,
        'consumption' => Consumption::class,
    ];

    protected static ?self $instance = null;

    /**
     * @throws \Exception
     */
    protected function __construct()
    {
        if (function_exists('config')) {
            $defaultModels = config('use-it.models');

            foreach ($defaultModels as $name => $model) {
                $this->resolveModel($name, $model);
            }
        }
    }

    /**
     * @throws \Exception
     */
    protected function resolveModel(string $name, string $model): void
    {
        if (!array_key_exists($name, $this->models)) {
            throw new \Exception("$name is not found. Only " . implode(
                ',',
                array_keys($this->models)
            ) . " are supported.");
        }

        if ($name === 'feature' && !new $model() instanceof FeatureInterface) {
            throw new \Exception("$model class must implement ThomasBrillion\UseIt\Interfaces\Models\FeatureInterface");
        }

        if ($name === 'ability' && !new $model() instanceof AbilityInterface) {
            throw new \Exception("$model class must implement ThomasBrillion\UseIt\Interfaces\Models\AbilityInterface");
        }

        if ($name === 'usage' && !new $model() instanceof UsageInterface) {
            throw new \Exception("$model class must implement ThomasBrillion\UseIt\Interfaces\Models\UsageInterface");
        }

        if ($name === 'consumption' && !new $model() instanceof ConsumptionInterface) {
            throw new \Exception("$model class must implement ThomasBrillion\UseIt\Interfaces\Models\ConsumptionInterface");
        }

        $this->models[$name] = $model;
    }

    /**
     * @return ModelResolver
     */
    protected static function getInstance(): ModelResolver
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @param  string  $name
     * @param  string  $model
     * @return void
     * @throws \Exception
     */
    public static function registerModel(string $name, string $model): void
    {
        $instance = static::getInstance();

        $instance->resolveModel($name, $model);
    }

    /**
     * @return string
     */
    public static function getFeatureModel(): string
    {
        return static::getInstance()->models['feature'];
    }

    /**
     * @return string
     */
    public static function getFeatureGroupModel(): string
    {
        return static::getInstance()->models['feature-group'];
    }

    /**
     * @return string
     */
    public static function getUsageModel(): string
    {
        return static::getInstance()->models['usage'];
    }

    /**
     * @return string
     */
    public static function getAbilityModel(): string
    {
        return static::getInstance()->models['ability'];
    }

    /**
     * @return string
     */
    public static function getConsumptionModel(): string
    {
        return static::getInstance()->models['consumption'];
    }
}
