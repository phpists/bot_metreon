<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Orders;
use App\Models\Products;

class OrderProducts extends Model{
	
    protected $table	= 'order_products';
    
    public $timestamps	= false;
    
    protected $fillable = [
		'order_id',
		'product_id',
		'count',
		'price',
		'amount'
	];
	
	public function order(){
		return $this->belongsTo(Orders::class, 'order_id');
	}
    
    public function product(){
		return $this->belongsTo(Products::class, 'product_id');
	}
}
