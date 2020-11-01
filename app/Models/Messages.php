<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Messages extends Model{
	
    protected $table    = 'messages';
    
    public $timestamps  = false;
    
    protected $fillable = [
		'id',
		'product_id',
		'message_id',
		'chat_id',
		'date',
		'type',
		'data'
	];
}
