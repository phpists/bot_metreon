<?php

namespace App\Admin\Controllers;

use App\Models\Category;

use App\Admin\Controllers\MyAdminController;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

use Encore\Admin\Layout\Content;

use App\Helpers\StringHelper;

use DB;

class CategoryController extends MyAdminController {
	
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Категорії';
	
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(){
        $grid = new Grid(new Category());
        
        $grid->column('id'				, __('ID'));
        
        //$grid->column('created_at'		, __('admin.category.created_at'));
        //$grid->column('updated_at'		, __('admin.category.updated_at'));
        
        $grid->column('sort'			, __('admin.category.sort'))->sortable();
        
        $grid->column('public'			, __('admin.public'))->display(function($public){
			$public = (int)$public;
			
			return $public > 0 ? '<i class="fa fa-check" style="color:green;" aria-hidden="true"></i>' : '<i class="fa fa-times" style="color:red;" aria-hidden="true"></i>';
		});
        
        $grid->column('name'			, __('admin.category.name'))->sortable();
        
        $model = $grid->model();
        
		$grid->actions(function($actions){
			//$tools->disableDelete();
			$actions->disableView();
			//$tools->disableList();
		});
		
		$grid->filter(function($filter){
			//$filter->between('created_at'	, __('admin.Category.created_at'))->datetime();
			
			$filter->like('name'			, __('admin.Category.name'));
		});
		
		$model->orderBy('sort', 'asc');
		
        return $grid;
    }
	
    protected function detail($id){
		header('Location: /category/'.$id.'/edit');
		return;
		//return redirect('/login');
	}
	
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(){
        $form = new Form(new Category());
        
        $this->configure($form);
		
		$id = $this->_id;
        
        $form->tab(__('admin.category.info')		, function($form) use ($id){
			if($id){
				$form->datetime('updated_at', __('admin.Category.updated_at'))->default(date('Y-m-d H:i:s'));
			}
			
			$form->switch('public'		, __('admin.public'));
			
			$form->decimal('sort'		, __('admin.category.sort'));
			$form->text('name'			, __('admin.category.name'))->rules('required|min:3|max:100');
			
			//$form->display('created_at', 'Created At');
			//$form->display('updated_at', 'Updated At');
		});
		
		if($this->_edit){
			if(!$this->_update){}
		}
		
		// callback before save
		$form->saving(function (Form $form){
			$form->name			= trim($form->name);
			$form->sort			= (int)trim($form->sort);
			
			if($form->sort < 1){
				$count = DB::table('category')->count();
				$count++;
				
				$form->sort = $count;
			}
		});
		
        return $form;
    }
}
