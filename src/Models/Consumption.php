<?php

namespace ThomasBrillion\UseIt\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Consumption extends Model
{
    protected $table = 'use_it_consumptions';

    protected $fillable = [
        'consumer_id',
        'consumer_type',
        'usage_id',
        'amount',
    ];

    public function usage(): BelongsTo
    {
        return $this->belongsTo(Usage::class);
    }

    public function consumer(): MorphTo
    {
        return $this->morphTo();
    }
}
