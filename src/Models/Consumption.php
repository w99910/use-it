<?php

namespace ThomasBrillion\UseIt\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use ThomasBrillion\UseIt\Interfaces\Models\ConsumptionInterface;
use ThomasBrillion\UseIt\Support\ModelResolver;

class Consumption extends Model implements ConsumptionInterface
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
        return $this->belongsTo(ModelResolver::getUsageModel());
    }

    public function consumer(): MorphTo
    {
        return $this->morphTo();
    }
}
