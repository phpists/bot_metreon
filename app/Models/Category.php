<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\SubCategory;

class Category extends Model{
	
    protected $table	= 'category';
    
    public $timestamps	= false;
    
    protected $fillable = [
		'name',
		'sort',
		'public'
	];
	
	public function subcategory(){
		return $this->hasMany(SubCategory::class, 'cat_id');
	}
}
