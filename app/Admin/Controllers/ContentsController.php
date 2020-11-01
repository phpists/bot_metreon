<?php

namespace App\Admin\Controllers;

use App\Models\Contents;

use Encore\Admin\Controllers\AdminController;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ContentsController extends AdminController {
	
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Контент';
	
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(){
        $grid = new Grid(new Contents());
        
        $grid->column('id'				, __('ID'));
        
        $grid->column('created_at'		, __('admin.contents.created_at'));
        $grid->column('updated_at'		, __('admin.contents.updated_at'));
        
        $grid->column('public'			, __('admin.public'))->display(function($public){
			$public = (int)$public;
			
			return $public > 0 ? '<i class="fa fa-check" style="color:green;" aria-hidden="true"></i>' : '<i class="fa fa-times" style="color:red;" aria-hidden="true"></i>';
		});
        
        $grid->column('key'				, __('admin.contents.key'))->sortable();
        $grid->column('description'		, __('admin.contents.description'))->sortable();
        
        //$model = $grid->model();
        
        return $grid;
    }
	
    protected function detail($id){
        $show = new Show(Contents::findOrFail($id));
        
        $show->field('id'               , __('Id'));
        
        $show->field('key'              , __('admin.contents.key'));
        $show->field('description'      , __('admin.contents.description'));
        $show->field('content'          , __('admin.contents.text'));
        
        return $show;
    }
	
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(){
        $form = new Form(new Contents());
        
        //$this->configure($form);
		
		$id = (int)request()->segment(2);
        
        $form->switch('public'			, __('admin.public'));
        
        $form->text('key'				, __('admin.contents.key'))->rules('required|min:3|max:30');
        $form->text('description'		, __('admin.contents.description'))->rules('max:200');
        
        if($id == 6){
            $form->summernote('text'    , __('admin.contents.text'));
        }else{
            $form->textarea('text'      , __('admin.contents.text'));
        }
        
        return $form;
    }
}
