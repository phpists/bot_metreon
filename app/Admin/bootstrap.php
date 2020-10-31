<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

use Encore\Admin\Form;

//use App\Admin\Extensions\Form\CKEditor;
use App\Admin\Extensions\Form\Summernote;

//use App\Admin\Extensions\Form\HasMany;
//use App\Admin\Extensions\Form\Table;
//use App\Admin\Extensions\Form\Country;

Form::forget(['map', 'editor']);

//Form::extend('ckeditor'	, CKEditor::class);
Form::extend('summernote', Summernote::class);

//Form::extend('tabs'		, HasMany::class);
//Form::extend('items'	, Table::class);
//Form::extend('country'	, Country::class);

Admin::css('/css/admin.css');
//Admin::js('/js/items.js');
