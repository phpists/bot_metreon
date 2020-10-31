<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AuthController as BaseAuthController;

use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class AuthController extends BaseAuthController{
	
	public function logout(Request $request){
		if(Admin::guard()->check()){
			$this->guard()->logout();
			$request->session()->invalidate();
		}
		
		return redirect('/auth/login');
	}
}
