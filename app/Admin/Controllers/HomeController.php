<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;

use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;

use Encore\Admin\Facades\Admin;

use Illuminate\Routing\Router;

class HomeController extends Controller{
	
    public function index(Content $content){
		if(Admin::guard()->check()){
			return redirect('/products');
		}
		
		return redirect('/auth/login');
    }
}
