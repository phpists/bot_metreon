<?php

namespace App\Admin\Extensions\Form;

use Encore\Admin\Form\Field;

class Country extends Field{
	
    public static $js	= [
        '/js/countrySelect.min.js',
    ];
    
    public static $css	= [
		'/css/countrySelect.css',
    ];
	
    protected $view = 'admin.country';
	
    public function render(){
		$class = str_replace(" ", ".", $this->getElementClassString());
		
        $this->script = "$('input.{$class}').countrySelect();";
		
        return parent::render();
    }
}
