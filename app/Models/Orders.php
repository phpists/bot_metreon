<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\OrderProducts;

class Orders extends Model{
	
    protected $table	= 'orders';
    
    public $timestamps	= false;
    
    protected $fillable = [
        'created_at',
        'updated_at',
		'status',
		'name',
		'phone',
		'amount',
		'chat_id',
		'client_id',
		'file'
	];
	
	public function products(){
		return $this->hasMany(OrderProducts::class, 'order_id');
	}
}
