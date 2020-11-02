<?php

namespace App\Admin\Controllers;

use App\Models\Clients;

use App\Admin\Controllers\MyAdminController;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

use Encore\Admin\Layout\Content;

use App\Helpers\StringHelper;

use DB;

class ClientsController extends MyAdminController {
	
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Клиенты';
	
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(){
        $grid = new Grid(new Clients());
        
        $grid->column('id'				, __('ID'));
        
        $grid->column('created_at'		, __('admin.clients.created_at'));
        
        $grid->column('name'			, __('admin.clients.name'));
		
		$grid->column('username'		, __('admin.clients.username'))->display(function($username){
			if($username){
				return '<a href="http://t.me/'.$username.'" target="_blank">@'.$username.'</a>';
			}
			
			return '-';
		});
		
		$grid->column('phone'			, __('admin.clients.phone'))->display(function($phone){
			if($phone){
				return '<a href="tel:+'.$phone.'" target="_blank">+'.$phone.'</a>';
			}
			
			return '-';
		});
		
		$grid->column('address'			, __('admin.clients.address'));
		
        $grid->column('status'			, __('admin.clients.status.label'))->display(function($status){
            if($status){
                return __('admin.clients.status.'.$status);
            }
            
            return '-';
        });
        
        $model = $grid->model();
        
		$grid->actions(function($actions){
			//$tools->disableDelete();
			$actions->disableView();
			//$tools->disableList();
		});
		
		$grid->filter(function($filter){
			$filter->like('name'			, __('admin.clients.name'));
			$filter->like('username'		, __('admin.clients.username'));
			$filter->like('phone'			, __('admin.clients.phone'));
			$filter->like('address'			, __('admin.clients.address'));
            
            $filter->equal('status'			, __('admin.clients.status.label'))->radio([
                null        => __('admin.filter-all'), 
                'new'       => __('admin.clients.status.new'),
                'approved'  => __('admin.clients.status.approved'),
                'rejected'  => __('admin.clients.status.rejected')
            ]);
		});
		
        return $grid;
    }
	
    protected function detail($id){
		header('Location: /clients/'.$id.'/edit');
		return;
	}
	
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(){
        $form = new Form(new Clients());
        
        $this->configure($form);
		
		$id = $this->_id;
        
        $form->text('name'			, __('admin.clients.name'))->rules('required|min:2|max:100');
        
        $form->text('username'		, __('admin.clients.username'))->rules('max:30');
        
        $form->text('phone'			, __('admin.clients.phone'))->rules('max:21');
		
		$form->text('address'		, __('admin.clients.address'))->rules('max:200');
        
        $form->decimal('chat_id'	, __('admin.clients.chat_id'));
        
        $form->radio('status'       , __('admin.clients.status.label'))
						->options([
							'new'       => __('admin.clients.status.new'),
                            'approved'  => __('admin.clients.status.approved'),
                            'rejected'  => __('admin.clients.status.rejected')
						])
						->default('new')
						->rules('required');
		
        $form->text('note'		    , __('admin.clients.note'));
        
		// callback before save
		$form->saving(function (Form $form){
			$form->name			= trim($form->name);
		});
		
        return $form;
    }
}
