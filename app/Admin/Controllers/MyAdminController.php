<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;

use Encore\Admin\Layout\Content;

class MyAdminController extends AdminController {
	
    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id){}
	
	protected $_id		= 0;
	protected $_action	= '';
	protected $_method	= '';
	protected $_post	= false;
	
	protected $_save	= false;
	protected $_edit	= false;
	protected $_update	= false;
	
    protected function configure($form){
		$this->_post	= isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] == 'POST' : false;
		
		if($this->_post){
			$this->_method	= isset($_POST['_method']) ? $_POST['_method'] : 'POST';
		}else{
			$this->_method	= 'GET';
		}
		
		if($form->isCreating()){
			$this->_save = true;
		}else{
			if($form->isEditing()){
				$this->_edit = true;
				
				$this->_id = (int)request()->segment(2);
			}else{
				if($this->_post){
					$this->_edit	= true;
					$this->_update	= true;
					
					$this->_id = (int)request()->segment(2);
				}
			}
		}
		
		if(false){
			echo"id:<br>";
			var_dump($this->_id);
			
			echo"method:<br>";
			var_dump($this->_method);
			
			echo"post:<br>";
			var_dump($this->_post);
			
			echo"_POST:<br>";
			var_dump($_POST);
			
			echo"_SERVER:<br>";
			var_dump($_SERVER);
			
			exit;
		}
		
    }
    
    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function edit($id, Content $content){
        return $content
            ->title($this->title())
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body($this->form($id, 'edit')->edit($id));
    }
    
    /**
     * Create interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function create(Content $content){
        return $content
            ->title($this->title())
            ->description($this->description['create'] ?? trans('admin.create'))
            ->body($this->form(0, 'create'));
    }
}
