<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clients extends Model{
	
    protected $table	= 'clients';
    
    public $timestamps	= false;
    
    protected $fillable = [
		'chat_id',
		'name',
		'username',
		'phone',
		'address'
	];
}
