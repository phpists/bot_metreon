<?php

namespace App\Admin\Controllers;

use App\Models\Orders;
use App\Models\OrderProducts;

use App\Models\Products;
use App\Models\Clients;

use App\Admin\Controllers\MyAdminController;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

use Encore\Admin\Layout\Content;

use App\Helpers\StringHelper;

use DB;

use App\Helpers\CurlHelper;
use Telegram\Bot\Api;

class OrdersController extends MyAdminController {
	
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Заказы';
	
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(){
        $grid = new Grid(new Orders());
        
        $grid->column('id'				, __('ID'));
        
        $grid->column('created_at'		, __('admin.orders.created_at'));
        
        $grid->column('status'			, __('admin.orders.status.label'))->display(function($status){
			return __('admin.orders.status.'.$status);
		});
		//->sortable();
        
        $grid->column('name'			, __('admin.orders.name'));
        //->sortable();
        
        $grid->column('phone'			, __('admin.orders.phone'))->display(function($phone){
			if($phone){
				return '<a href="tel:+'.$phone.'" target="_blank">+'.$phone.'</a>';
			}
			
			return '-';
		});
        
        $grid->column('amount'			, __('admin.orders.amount'))->display(function($amount){
			if($amount){
				return $amount.' '.__('admin.products.rub');
			}
			
			return '-';
		});
		
        $model = $grid->model();
        
        $model->orderBy('created_at', 'desc');
        
		$grid->actions(function($actions){
			//$tools->disableDelete();
			$actions->disableView();
			//$tools->disableList();
		});
		
		$grid->filter(function($filter){
			$filter->between('created_at'	, __('admin.orders.created_at'))->datetime();
			
			$filter->equal('status'			, __('admin.orders.status.label'))->radio([
				null		=> __('admin.orders.status.all'), 
                
				'new'		=> __('admin.orders.status.new'), 
				'processed' => __('admin.orders.status.processed'),
				'canceled'	=> __('admin.orders.status.canceled')
			]);
			
			$filter->like('name'			, __('admin.orders.name'));
			$filter->like('phone'			, __('admin.orders.phone'));
		});
		
        return $grid;
    }
	
    protected function detail($id){
		header('Location: /orders/'.$id.'/edit');
		return;
		//return redirect('/login');
	}
	
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(){
        $form = new Form(new Orders());
        
        $this->configure($form);
		
		$id = $this->_id;
        
        $form->hidden('chat_id');
        
        $form->tab(__('admin.orders.info')		, function($form) use ($id){
			if($id){
				$form->datetime('updated_at', __('admin.orders.updated_at'))->default(date('Y-m-d H:i:s'));
			}
			
			$form->radio('status'			, __('admin.orders.status.label'))
					->options([
						'new'		=> __('admin.orders.status.new'), 
						'processed' => __('admin.orders.status.processed'),
						'canceled'	=> __('admin.orders.status.canceled')
					])
					->default('new')
					->rules('required');
			
			$form->text('name'				, __('admin.orders.name'))->rules('required|min:2|max:100');
			$form->text('username'			, __('admin.orders.username'))->rules('required|min:2|max:30');
			
			$form->text('phone'				, __('admin.orders.phone'))->rules('max:25');
			$form->text('amount'			, __('admin.orders.amount'))->help(__('admin.products.uah'));
			
			$form->file('file'				, __('admin.orders.invoice'))->move('invoice')->removable()->uniqueName()->downloadable();
		});
		
		$form->tab(__('admin.orders.products')	, function($form) use ($id){
			$form->hasMany('products', '', function($form){
				$form->select('product_id'	, __('admin.orders.product'))
						->options(Products::get()->pluck('name', 'id')->toArray())
						->rules('required');
				
				$form->text('count'			, __('admin.orders.count'));
				
				$form->text('price'			, __('admin.orders.price'));
				$form->text('amount'		, __('admin.orders.amount'));
			});
		});
		
		// callback before save
		$form->saving(function(Form $form) use ($id) {
			if(!$id){
				$id = $form->model()->id;
			}
			
			$form->name			= trim($form->name);
			
			if($id){
				$order 	= Orders::query()->where('id', $id)->first();
				
				if($order->status != $form->status){
					$answer = "";
					
					if($form->status == 'processed'){
						$answer = __('telegram.status.processed', ['id' => $id]);
					}
					
					if($form->status == 'canceled'){
						$answer = __('telegram.status.canceled', ['id' => $id]);
					}
					
					if($answer){
						$key = env('TELEGRAM_TOKEN', '');
						
						if($key){
							$telegram = new Api($key);
							
							$telegram->sendMessage([
								'chat_id'		=> $order->chat_id, 
								'text'			=> $answer
							]);
						}
					}
				}
			}
		});
		
        return $form;
    }
}
