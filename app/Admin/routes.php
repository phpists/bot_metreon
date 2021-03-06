<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->get('/auth/logout', 'AuthController@logout')->name('admin.logout');
    $router->get('/clients/{id}/approved', 'ClientsController@approved')->name('admin.approved')->where('id', '[0-9]+');
    
    $router->resource('category'			, CategoryController::class);
    $router->resource('products'			, ProductsController::class);
    $router->resource('clients'				, ClientsController::class);
    $router->resource('admins'				, AdminsController::class);
    $router->resource('contents'			, ContentsController::class);
    $router->resource('orders'				, OrdersController::class);
});
