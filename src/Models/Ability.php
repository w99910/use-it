<?php

namespace ThomasBrillion\UseIt\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int|string $id
 * @property DateTime $expire_at
 */
class Ability extends Model
{
    protected $table = 'use_it_abilities';

    protected $fillable = [
        'feature_id', 'creator_id', 'creator_type', 'name', 'expire_at', 'meta',
    ];

    protected $casts = [
        'expire_at' => 'datetime',
        'meta' => 'json'
    ];

    public function creator(): MorphTo
    {
        return $this->morphTo();
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }
}
