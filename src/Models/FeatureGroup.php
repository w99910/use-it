<?php

namespace ThomasBrillion\UseIt\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use ThomasBrillion\UseIt\Interfaces\Models\FeatureGroupInterface;

/**
 * @property int|string id
 * @property string $name
 * @property string $description
 * @property array $meta
 */
class FeatureGroup extends Model implements FeatureGroupInterface
{
    protected $table = 'use_it_feature_groups';

    protected $fillable = [
        'name',
        'description',
        'meta',
    ];


    protected $casts = [
        'meta' => 'json',
    ];

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'use_it_feature_group_feature', 'feature_group_id', 'feature_id');
    }

    public function getId(): string|int
    {
        return $this->id;
    }
}
