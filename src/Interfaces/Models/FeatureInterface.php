<?php

namespace ThomasBrillion\UseIt\Interfaces\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use ThomasBrillion\UseIt\Support\Enums\FeatureType;

interface FeatureInterface
{
    public function usages(): HasMany;

    public function abilities(): HasMany;

    public function getId(): string|int;

    public function getName(): string;

    public function getType(): FeatureType;

    public function isDisabled(): bool;

    public function toggleDisability(): bool;
}
