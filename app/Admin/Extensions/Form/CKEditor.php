<?php

namespace App\Admin\Extensions\Form;

use Encore\Admin\Form\Field;

class CKEditor extends Field{
	
    public static $js_old = [
        '/vendor/ckeditor/ckeditor.js',
        '/vendor/ckeditor/adapters/jquery.js',
        '/vendor/ckeditor/config.js',
    ];
    
    public static $js = [];
	
    protected $view = 'admin.ckeditor';
	
    public function render(){
		//$class = str_replace(" ", ".", $this->getElementClassString());
		
       // $this->script = "$('textarea.{$class}').ckeditor();";
		
        return parent::render();
    }
}
