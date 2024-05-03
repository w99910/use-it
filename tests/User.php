<?php

use Illuminate\Database\Eloquent\Model;
use ThomasBrillion\UseIt\Interfaces\CanUseFeature;
use ThomasBrillion\UseIt\Traits\CanUseIt;

class User extends Model implements CanUseFeature
{
    use CanUseIt;
    protected $table = 'use_it_users';
}
