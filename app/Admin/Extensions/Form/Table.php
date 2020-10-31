<?php

namespace App\Admin\Extensions\Form;

use Encore\Admin\Admin;
use Encore\Admin\Form\NestedForm;
use App\Admin\Extensions\Form\HasMany;
//use Encore\Admin\Form\Field\HasMany;

class Table extends HasMany {
	
    /**
     * @var string
     */
    protected $viewMode = 'table';
    
    /**
     * Table constructor.
     *
     * @param string $column
     * @param array  $arguments
     */
    public function __construct($column, $arguments = []){
        $this->column = $column;

        if (count($arguments) == 1) {
            $this->label = $this->formatLabel();
            $this->builder = $arguments[0];
        }

        if (count($arguments) == 2) {
            list($this->label, $this->builder) = $arguments;
        }
    }
    
    /**
     * @return array
     */
    protected function buildRelatedForms(){
//        if (is_null($this->form)) {
//            return [];
//        }

        $forms = [];

        if ($values = old($this->column)) {
            foreach ($values as $key => $data) {
                if ($data[NestedForm::REMOVE_FLAG_NAME] == 1) {
                    continue;
                }

                $forms[$key] = $this->buildNestedForm($this->column, $this->builder, $key)->fill($data);
            }
        } else {
            foreach ($this->value as $key => $data) {
                if (isset($data['pivot'])) {
                    $data = array_merge($data, $data['pivot']);
                }
                $forms[$key] = $this->buildNestedForm($this->column, $this->builder, $key)->fill($data);
            }
        }

        return $forms;
    }
    
    public function prepare($input){
        $form = $this->buildNestedForm($this->column, $this->builder);

        $prepare = $form->prepare($input);

        return collect($prepare)->reject(function ($item) {
            return $item[NestedForm::REMOVE_FLAG_NAME] == 1;
        })->map(function ($item) {
            unset($item[NestedForm::REMOVE_FLAG_NAME]);

            return $item;
        })->toArray();
    }
    
    protected function getKeyName(){
        if (is_null($this->form)) {
            return;
        }

        return 'id';
    }
    
    protected function buildNestedForm($column, \Closure $builder, $key = null){
        $form = new NestedForm($column);

        $form->setForm($this->form)
            ->setKey($key);

        call_user_func($builder, $form);

        $form->hidden(NestedForm::REMOVE_FLAG_NAME)->default(0)->addElementClass(NestedForm::REMOVE_FLAG_CLASS);

        return $form;
    }
    
    /**
     * Setup default template script.
     *
     * @param string $templateScript
     *
     * @return void
     */
    protected function setupScriptForTableView($templateScript){
        $removeClass = NestedForm::REMOVE_FLAG_CLASS;
        $defaultKey = NestedForm::DEFAULT_KEY_NAME;

        /**
         * When add a new sub form, replace all element key in new sub form.
         *
         * @example comments[new___key__][title]  => comments[new_{index}][title]
         *
         * {count} is increment number of current sub form count.
         */
        $script = <<<EOT
var index = 0;alert('#has-many-{$this->column}');
$('#has-many-{$this->column}').on('click', '.add', function () {

    var tpl = $('template.{$this->column}-tpl');

    index++;

    var template = tpl.html().replace(/{$defaultKey}/g, index);
    $('.has-many-{$this->column}-forms').append(template);
    {$templateScript}
    return false;
});

$('#has-many-{$this->column}').on('click', '.remove', function () {
    $(this).closest('.has-many-{$this->column}-form').hide();
    $(this).closest('.has-many-{$this->column}-form').find('.$removeClass').val(1);
    return false;
});

EOT;

        //Admin::script($script);
        //Admin::js('/js/items.js');
    }
    
    public function render(){
        return $this->renderTable();
    }
}
