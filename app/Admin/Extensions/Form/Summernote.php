<?php

namespace App\Admin\Extensions\Form;

use Encore\Admin\Form\Field;

class Summernote extends Field{
	
	public static $css = [
		//'/vendor/laravel-admin-ext/summernote/dist/summernote-lite.css',
		'/vendor/laravel-admin-ext/summernote/dist/summernote.css',
		'/css/summernote-addition.css'
    ];
	
    public static $js = [
		//'/vendor/laravel-admin-ext/summernote/dist/summernote-lite.js',
		'/vendor/laravel-admin-ext/summernote/dist/summernote.min.js',
		'/js/summernote.init.js'
    ];
	
    protected $view = 'admin.ckeditor';
	
    public function render(){
		$class = str_replace(" ", ".", $this->getElementClassString());
		
        //$this->script = "$('textarea.{$class}').summernote();";
		
		$this->script = "";
		$this->script .= "var textarea = $('textarea.{$class}');";
		$this->script .= "if(textarea.length > 0){";
			$this->script .= "$.each(textarea, function(i, el){";
				$this->script .= "setEditor($(el));";
			$this->script .= "});";
		$this->script .= "};";
		
        return parent::render();
    }
}
