<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use ThomasBrillion\UseIt\Interfaces\Actions\CanUseFeature;
use ThomasBrillion\UseIt\Interfaces\Actions\CanUseFeatureGroup;
use ThomasBrillion\UseIt\Support\ModelResolver;
use ThomasBrillion\UseIt\Traits\CanUseIt;

class User extends Model implements CanUseFeatureGroup
{
    use CanUseIt;

    protected $table = 'use_it_users';

    public function featureGroups(): BelongsToMany
    {
        if (!method_exists($this, 'belongsToMany')) {
            throw new Exception('belongsToMany method not found', 404);
        }
        return $this->belongsToMany(ModelResolver::getFeatureGroupModel(), 'use_it_feature_group_users');
    }
}
