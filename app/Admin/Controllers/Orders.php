<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\OrderProducts;

class Orders extends Model{
	
    protected $table	= 'orders';
    
    public $timestamps	= true;
    
    protected $fillable = [
		'status',
		'name',
		'phone',
		'amount',
		'address',
		'delivery',
		'note',
		'chat_id',
		'paid',
		'payment'
	];
	
	public function products(){
		return $this->hasMany(OrderProducts::class, 'order_id');
	}
}
