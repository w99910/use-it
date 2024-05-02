<?php

use Illuminate\Database\Eloquent\Model;
use ThomasBrillion\UseIt\Interfaces\CanUseFeature;
use ThomasBrillion\UseIt\Traits\CanUseIt;

class User extends Model implements CanUseFeature
{
    protected $table = 'use_it_users';
    
    use CanUseIt;
}
