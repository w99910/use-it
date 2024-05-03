<?php

namespace ThomasBrillion\UseIt\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ThomasBrillion\UseIt\Support\Enums\FeatureType;

/**
 * @property int|string id
 * @property string $name
 * @property string $description
 * @property FeatureType $type
 * @property int|null $quantity
 */
class Feature extends Model
{
    protected $table = 'use_it_features';

    protected $fillable = [
        'name',
        'description',
        'type', // ability or quantity
        'quantity',
        'meta',
        'disabled',
    ];

    protected $casts = [
        'type' => FeatureType::class,
        'meta' => 'json',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(Usage::class);
    }

    public function abilities(): HasMany
    {
        return $this->hasMany(Ability::class);
    }
}
