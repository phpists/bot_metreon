<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contents extends Model{
	
    protected $table    = 'contents';
    
    public $timestamps  = true;
    
    protected $fillable = [
        'created_at',
        'updated_at',
		'key',
		'public',
		'description',
		'text'
	];
}
