<?php

namespace ThomasBrillion\UseIt\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ThomasBrillion\UseIt\Interfaces\Models\FeatureInterface;
use ThomasBrillion\UseIt\Support\Enums\FeatureType;
use ThomasBrillion\UseIt\Support\ModelResolver;

/**
 * @property int|string id
 * @property string $name
 * @property string $description
 * @property FeatureType $type
 * @property int|null $quantity
 * @property bool $disabled
 */
class Feature extends Model implements FeatureInterface
{
    protected $table = 'use_it_features';

    protected $fillable = [
        'name',
        'description',
        'type', // ability or quantity
        'meta',
        'disabled',
        'total',
        'expire_in_seconds',
        'level',
    ];

    protected $casts = [
        'type' => FeatureType::class,
        'meta' => 'json',
        'disabled' => 'bool',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(ModelResolver::getUsageModel());
    }

    public function abilities(): HasMany
    {
        return $this->hasMany(ModelResolver::getAbilityModel());
    }

    public function getId(): string|int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): FeatureType
    {
        return $this->type;
    }

    public function isDisabled(): bool
    {
        return $this->disabled !== null && $this->disabled;
    }

    public function toggleDisability(): bool
    {
        $this->disabled = ! $this->disabled;
        $this->save();

        return $this->disabled;
    }
}
