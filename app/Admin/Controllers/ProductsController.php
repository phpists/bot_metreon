<?php

namespace App\Admin\Controllers;

use App\Models\Products;

use App\Models\Category;
use App\Models\SubCategory;

use App\Admin\Controllers\MyAdminController;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

use Encore\Admin\Layout\Content;

use App\Helpers\StringHelper;

use DB;

class ProductsController extends MyAdminController {
	
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Товары';
	
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(){
        $grid = new Grid(new Products());
        
        $grid->column('id'				, __('ID'));
        
        //$grid->column('sort'			, __('admin.products.sort'));
        
        $grid->column('public'			, __('admin.public'))->display(function($public){
			$public = (int)$public;
			
			return $public > 0 ? '<i class="fa fa-check" style="color:green;" aria-hidden="true"></i>' : '<i class="fa fa-times" style="color:red;" aria-hidden="true"></i>';
		});
        
        $grid->column('name'			, __('admin.products.name'));
        
        $grid->column('price'			, __('admin.products.price'))->display(function($price){
            if($price){
                return $price.' '.__('admin.products.rub');
            }
            
            return '-';
        });
        
        $grid->column('category'		, __('admin.products.category'))->display(function($category){
			if($category){
				if(is_array($category)){
					return $category['name'];
				}
				
				return $category->name;
			}
			
			return '-';
		});
		
		$grid->column('subcategory'		, __('admin.products.subcategory'))->display(function($subcategory){
			if($subcategory){
				if(is_array($subcategory)){
					return $subcategory['name'];
				}
				
				return $subcategory->name;
			}
			
			return '-';
		});
		
		$grid->column('image'			, __('admin.products.image'))->image();
        
        $model = $grid->model();
        
        //$model->orderBy('products.sort', 'asc');
        
		$grid->actions(function($actions){
			//$tools->disableDelete();
			$actions->disableView();
			//$tools->disableList();
		});
		
		$grid->filter(function($filter){
			//$filter->between('created_at'	, __('admin.products.created_at'))->datetime();
			
			$filter->like('name'			, __('admin.products.name'));
			
			$filter->equal('cat_id'			, __('admin.products.category'))->radio(([null => __('admin.filter-all')] + Category::all()->pluck('name', 'id')->toArray()));
			$filter->equal('sub_id'			, __('admin.products.subcategory'))->radio(([null => __('admin.filter-all')] + SubCategory::all()->pluck('name', 'id')->toArray()));
			
			$filter->equal('public'			, __('admin.public'))->radio([null => __('admin.no'), 1 => __('admin.yes')]);
		});
		
        return $grid;
    }
	
    protected function detail($id){
		header('Location: /products/'.$id.'/edit');
		return;
	}
	
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(){
        $form = new Form(new Products());
        
        $this->configure($form);
		
		$id = $this->_id;
        
        $form->tab(__('admin.products.info')		, function($form) use ($id){
			if($id){
				$form->datetime('updated_at', __('admin.products.updated_at'))->default(date('Y-m-d H:i:s'));
			}else{
				$form->datetime('created_at', __('admin.products.created_at'))->default(date('Y-m-d H:i:s'));
			}
			
			$form->switch('public'		, __('admin.public'));
			//$form->decimal('sort'		, __('admin.products.sort'));
			
			$form->text('name'			, __('admin.products.name'))->rules('required|min:3|max:100');
			
			$form->decimal('price'		, __('admin.products.price'))->help(__('admin.products.rub'));
			
			$category = Category::orderBy('sort', 'asc')->get()->pluck('name', 'id')->toArray();
			
			$form->select('cat_id'		, __('admin.products.category'))->options(($category ? ([0 => ''] + $category) : []))->rules('required');
			
			$subcategory = [];
			
			$tmp = SubCategory::orderBy('sort', 'asc')->get();
			
			if(count($tmp)){
				foreach($tmp as $item){
					$subcategory[] = '('.$category[$item->cat_id].') '.$item->name;
				}
			}
			
			$form->select('sub_id'		, __('admin.products.subcategory'))->options(($subcategory ? ([0 => ''] + $subcategory) : []))->rules('required');
			
			$form->image('image'		, __('admin.products.image'))->removable();
		});
		
		// callback before save
		$form->saving(function (Form $form){
			$form->name			= trim($form->name);
			$form->sort			= (int)trim($form->sort);
			
			/*
			if($form->sort < 1){
				$count = DB::table('products')->count();
				$count++;
				
				$form->sort = $count;
			}
			*/
		});
		
        return $form;
    }
}
