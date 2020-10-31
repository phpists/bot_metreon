<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;

class User extends Authenticatable{

    protected $fillable = [
        'email',
        'newemail',
        'password',
        'name',
        'surname',
        'photo',
        'city',
        'status',
        'phone',
        'code',
    ];

    protected $table = 'users';
}
