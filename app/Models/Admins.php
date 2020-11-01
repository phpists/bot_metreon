<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admins extends Model{
	
    protected $table	= 'admins';
    
    public $timestamps	= true;
    
    protected $fillable = [
		'chat_id',
		'name',
		'username',
		'created_at',
		'updated_at',
        'notify'
	];
}
