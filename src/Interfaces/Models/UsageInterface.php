<?php

namespace ThomasBrillion\UseIt\Interfaces\Models;

use DateTime;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface UsageInterface
{
    public function creator(): MorphTo;

    public function feature(): BelongsTo;

    public function getId(): string|int;

    public function getName(): string|int;

    public function getSpend(): int;

    public function getTotal(): int;

    public function getLevel(): int;

    public function getExpiredAt(): DateTime;

    public function consume(int $updatedAmount): static;
}
