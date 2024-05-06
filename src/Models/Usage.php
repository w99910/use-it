<?php

namespace ThomasBrillion\UseIt\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use ThomasBrillion\UseIt\Interfaces\Models\UsageInterface;
use ThomasBrillion\UseIt\Support\ModelResolver;

/**
 * @property int|string $id
 * @property int|string $name
 * @property DateTime $expire_at
 * @property int $total
 * @property int $level
 * @property int $spend
 */
class Usage extends Model implements UsageInterface
{
    protected $table = 'use_it_usages';

    protected $fillable = [
        'feature_id', 'creator_id', 'creator_type', 'name', 'total', 'spend', 'level', 'expire_at', 'meta',
    ];

    protected $casts = [
        'expire_at' => 'datetime',
        'meta' => 'json',
    ];

    public function consumptions(): HasMany
    {
        return $this->hasMany(ModelResolver::getConsumptionModel());
    }

    public function creator(): MorphTo
    {
        return $this->morphTo();
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(ModelResolver::getFeatureModel());
    }

    public function getId(): string|int
    {
        return $this->id;
    }

    public function getName(): string|int
    {
        return $this->name;
    }

    public function getSpend(): int
    {
        return $this->spend;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getExpiredAt(): DateTime
    {
        return $this->expire_at;
    }

    public function consume(int $updatedAmount): static
    {
        $this->spend = $updatedAmount;
        $this->save();
        return $this;
    }
}
