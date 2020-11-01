<?php

namespace App\Admin\Controllers;

use App\Models\Admins;

use App\Admin\Controllers\MyAdminController;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

use Encore\Admin\Layout\Content;

use App\Helpers\StringHelper;

use DB;

class AdminsController extends MyAdminController {
	
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Админы';
	
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(){
        $grid = new Grid(new Admins());
        
        $grid->column('id'				, __('ID'));
        
        $grid->column('created_at'		, __('admin.admins.created_at'));
        
        $grid->column('name'			, __('admin.admins.name'));
		
		$grid->column('username'		, __('admin.admins.username'))->display(function($username){
			if($username){
				return '<a href="http://t.me/'.$username.'" target="_blank">@'.$username.'</a>';
			}
			
			return '-';
		});
		
        $grid->column('notify'			, __('admin.admins.notify'))->display(function($notify){
			$notify = (int)$notify;
			
			return $notify > 0 ? '<i class="fa fa-check" style="color:green;" aria-hidden="true"></i>' : '<i class="fa fa-times" style="color:red;" aria-hidden="true"></i>';
		});
        
        $model = $grid->model();
        
		$grid->actions(function($actions){
			//$tools->disableDelete();
			$actions->disableView();
			//$tools->disableList();
		});
		
		$grid->filter(function($filter){
			$filter->like('name'			, __('admin.admins.name'));
			$filter->like('username'		, __('admin.admins.username'));
		});
		
        return $grid;
    }
	
    protected function detail($id){
		header('Location: /admins/'.$id.'/edit');
		return;
	}
	
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(){
        $form = new Form(new Admins());
        
        $this->configure($form);
		
		$id = $this->_id;
        
        $form->text('name'			, __('admin.admins.name'))->rules('max:100');
        
        $form->text('username'		, __('admin.admins.username'))->rules('max:30');
        
        $form->decimal('chat_id'	, __('admin.admins.chat_id'));
        
        $form->switch('notify'		, __('admin.admins.notify'));
		
		// callback before save
		$form->saving(function (Form $form){
			$form->name			= trim($form->name);
		});
		
        return $form;
    }
}
