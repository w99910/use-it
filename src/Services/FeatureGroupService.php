<?php

namespace ThomasBrillion\UseIt\Services;

use DateTime;
use Exception;
use ThomasBrillion\UseIt\Interfaces\Actions\CanUseFeatureGroup;
use ThomasBrillion\UseIt\Interfaces\Models\FeatureGroupInterface;
use ThomasBrillion\UseIt\Interfaces\Models\FeatureInterface;
use ThomasBrillion\UseIt\Support\ModelResolver;

class FeatureGroupService
{
    protected ?CanUseFeatureGroup $creator = null;

    public static function featureGroupQuery()
    {
        return (new (ModelResolver::getFeatureGroupModel()))->query();
    }

    public static function create(
        string $name,
        string $description,
        array $meta = [],
    ) {
        return static::featureGroupQuery()->create([
            'name' => $name,
            'description' => $description,
            'meta' => $meta,
        ]);
    }

    public static function findFeatureGroup(string $name)
    {
        return static::featureGroupQuery()->firstWhere('name', $name);
    }

    public static function resolveFeatureGroup(string|FeatureGroupInterface $featureGroup)
    {

        if (is_string($featureGroup)) {
            $featureGroup = static::findFeatureGroup($featureGroup);
            if (! $featureGroup) {
                throw new Exception('Feature Group not found', 404);
            }
        }

        return $featureGroup;
    }

    public static function addFeature(string|FeatureGroupInterface $featureGroup, string|FeatureInterface $feature)
    {
        $featureGroup = static::resolveFeatureGroup($featureGroup);
        $feature = FeatureService::resolveFeature($feature);

        $featureGroup->features()->attach($feature->getId());
    }

    public static function addFeatures(string|FeatureGroupInterface $featureGroup, array $features)
    {
        $featureGroup = static::resolveFeatureGroup($featureGroup);
        foreach ($features as $feature) {
            if (is_string($feature) || $feature instanceof FeatureInterface) {
                static::addFeature($featureGroup, $feature);
            }
        }
    }

    public static function removeFeature(string|FeatureGroupInterface $featureGroup, string|FeatureInterface $feature)
    {
        $featureGroup = static::resolveFeatureGroup($featureGroup);
        $feature = FeatureService::resolveFeature($feature);

        $featureGroup->features()->detach($feature->getId());
    }

    public static function removeFeatures(string|FeatureGroupInterface $featureGroup, array $features)
    {
        $featureGroup = static::resolveFeatureGroup($featureGroup);
        foreach ($features as $feature) {
            if (is_string($feature) || $feature instanceof FeatureInterface) {
                static::removeFeature($featureGroup, $feature);
            }
        }
    }

    public static function hasFeature(string|FeatureGroupInterface $featureGroup, string|FeatureInterface $feature)
    {
        $featureGroup = static::resolveFeatureGroup($featureGroup);
        $feature = FeatureService::resolveFeature($feature);

        return $featureGroup->features()->where('name', $feature->name)->exists();
    }

    public static function of(CanUseFeatureGroup $creator)
    {
        $instance = new static();
        $instance->creator = $creator;

        return $instance;
    }

    public function hasFeatureGroup(
        string|FeatureGroupInterface $featureGroup,
    ) {
        $featureGroup = static::resolveFeatureGroup($featureGroup);

        return $this->creator->featureGroups()->where('name', $featureGroup->name)->exists();
    }

    public function grantFeatureGroup(
        string|FeatureGroupInterface $featureGroup,
        ?DateTime $expireAt = null,
        ?int $total = null,
        int $level = 0,
        array $meta = []
    ) {
        $featureGroup = static::resolveFeatureGroup($featureGroup);

        if ($this->hasFeatureGroup($featureGroup)) {
            throw new Exception('Feature Group is already granted to the creator');
        }

        $featureService = FeatureService::of($this->creator);

        foreach ($featureGroup->features as $feature) {
            if (! ($feature instanceof FeatureInterface)) {
                continue;
            }
            $featureService->grantFeature($feature, $expireAt, $total, $level, $meta);
        }

        $this->creator->featureGroups()->attach($featureGroup->getId());
    }

    public function revokeFeatureGroup(
        string|FeatureGroupInterface $featureGroup
    ) {
        $featureGroup = static::resolveFeatureGroup($featureGroup);

        if (! $this->hasFeatureGroup($featureGroup)) {
            throw new Exception('Feature Group is not granted to the creator');
        }

        $featureService = FeatureService::of($this->creator);


        foreach ($featureGroup->features as $feature) {
            if (! ($feature instanceof FeatureInterface)) {
                continue;
            }
            $featureService->revokeToFeature($feature);
        }

        $this->creator->featureGroups()->detach($featureGroup->getId());
    }
}
