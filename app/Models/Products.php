<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

use App\Models\Category;
use App\Models\SubCategory;

class Products extends Model{
	
    protected $table	= 'products';
    
    public $timestamps	= false;
    
    protected $fillable = [
		'name',
		'text',
		'image',
		'public',
		'cat_id',
		'sub_id',
		'price'
	];
	
	public function category(){
		return $this->belongsTo(Category::class, 'cat_id', 'id');
	}
	
	public function subcategory(){
		return $this->belongsTo(SubCategory::class, 'sub_id');
	}
}
