<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Category;

class SubCategory extends Model{
	
    protected $table	= 'subcategory';
    
    public $timestamps	= false;
    
    protected $fillable = [
		'cat_id',
		'name',
		'sort',
		'public'
	];
	
	public function category(){
		return $this->belongsTo(Category::class, 'cat_id');
	}
}
