<?php

namespace ThomasBrillion\UseIt\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// get top usages
// get usage for days

/**
 * @property int|string $id
 * @property DateTime $expire_at
 * @property int $total
 * @property int $spend
 */
class Usage extends Model
{
    protected $table = 'use_it_usages';

    protected $fillable = [
        'feature_id', 'creator_id', 'creator_type', 'name', 'total', 'spend', 'level', 'expire_at', 'meta',
    ];

    protected $casts = [
        'expire_at' => 'datetime',
        'meta' => 'json'
    ];

    public function consumptions(): HasMany
    {
        return $this->hasMany(Consumption::class);
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }
}
