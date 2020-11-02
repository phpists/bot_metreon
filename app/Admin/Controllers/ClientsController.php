<?php

namespace App\Admin\Controllers;

use App\Models\Clients;
use App\Models\Category;

use App\Admin\Controllers\MyAdminController;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

use Encore\Admin\Layout\Content;

use App\Helpers\StringHelper;
use App\Helpers\CurlHelper;

use DB;

use Illuminate\Http\Request;

class ClientsController extends MyAdminController {
	
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Клиенты';
	
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(){
        $grid = new Grid(new Clients());
        
        $grid->column('id'				, __('ID'));
        
        $grid->column('created_at'		, __('admin.clients.created_at'));
        
        $grid->column('name'			, __('admin.clients.name'));
		
		$grid->column('username'		, __('admin.clients.username'))->display(function($username){
			if($username){
				return '<a href="http://t.me/'.$username.'" target="_blank">@'.$username.'</a>';
			}
			
			return '-';
		});
		
		$grid->column('phone'			, __('admin.clients.phone'))->display(function($phone){
			if($phone){
				return '<a href="tel:+'.$phone.'" target="_blank">+'.$phone.'</a>';
			}
			
			return '-';
		});
		
		//$grid->column('address'			, __('admin.clients.address'));
		
        $grid->column('status'			, __('admin.clients.status.label'))->display(function($status){
            if($status){
                if($status == 'new'){
                    return __('admin.clients.status.new')."<br><a href=\"/clients/".$this->id."/approved\">".__('admin.clients.approved_btn')."</a>";
                }else{
                    return __('admin.clients.status.'.$status);
                }
            }
            
            return '-';
        });
        
        $model = $grid->model();
        
		$grid->actions(function($actions){
			//$tools->disableDelete();
			$actions->disableView();
			//$tools->disableList();
		});
		
		$grid->filter(function($filter){
			$filter->like('name'			, __('admin.clients.name'));
			$filter->like('username'		, __('admin.clients.username'));
			$filter->like('phone'			, __('admin.clients.phone'));
			//$filter->like('address'			, __('admin.clients.address'));
            
            $filter->equal('status'			, __('admin.clients.status.label'))->radio([
                null        => __('admin.filter-all'), 
                'new'       => __('admin.clients.status.new'),
                'approved'  => __('admin.clients.status.approved'),
                'rejected'  => __('admin.clients.status.rejected')
            ]);
		});
		
        return $grid;
    }
	
    protected function detail($id){
		header('Location: /clients/'.$id.'/edit');
		return;
	}
    
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(){
        $form = new Form(new Clients());
        
        $this->configure($form);
		
		$id = $this->_id;
        
        $form->text('name'			, __('admin.clients.name'))->rules('required|min:2|max:100');
        
        $form->text('username'		, __('admin.clients.username'))->rules('max:30');
        
        $form->text('phone'			, __('admin.clients.phone'))->rules('max:21');
		
		//$form->text('address'		, __('admin.clients.address'))->rules('max:200');
        
        $form->decimal('chat_id'	, __('admin.clients.chat_id'));
        
        $form->radio('status'       , __('admin.clients.status.label'))
						->options([
							'new'       => __('admin.clients.status.new'),
                            'approved'  => __('admin.clients.status.approved'),
                            'rejected'  => __('admin.clients.status.rejected')
						])
						->default('new')
						->rules('required');
		
        $form->text('note'		    , __('admin.clients.note'));
        
		// callback before save
		$form->saving(function (Form $form){
			$form->name			= trim($form->name);
            $form->username		= trim($form->username);
            //$form->phone		= trim($form->phone);
            
            $form->phone 	    = (string)preg_replace('/[^0-9]/', '', $form->phone);
            
            if($form->chat_id && $form->status != "new"){
                $client = Clients::query()->where('chat_id', $form->chat_id)->first();
                
                if($client){
                    if($form->status != $client->status){
                        $this->sendMessage([
                            'chat_id'		=> $form->chat_id, 
                            'text'			=> __('telegram.request_'.$form->status)
                        ]);
                    }
                }else{
                    $this->sendMessage([
                        'chat_id'		=> $form->chat_id, 
                        'text'			=> __('telegram.request_'.$form->status)
                    ]);
                }
                
                if($form->status == "approved"){
                    $this->commandCategoryList($form->chat_id);
                }
            }
		});
        
        $form->saved(function(Form $form){
            
        });
		
        return $form;
    }
    
    function approved(Request $request, $id){
        $client = Clients::query()->where('id', $id)->first();
        
        if($client){
            $client->status = 'approved';
            $client->save();
            
            $this->sendMessage([
                'chat_id'		=> $client->chat_id, 
                'text'			=> __('telegram.request_approved')
            ]);
            
            $this->commandCategoryList($client->chat_id);
        }
        
        header("Location: /clients");
        return;
    }
    
    function commandCategoryList($chat_id){
        $items = [];
		
		$cat = Category::query()->where('category.public', '1')
								->orderBy('category.sort', 'asc')
								->select(
									DB::raw('category.*'), 
                                    DB::raw('(SELECT COUNT(`subcategory`.`id`) FROM `subcategory` WHERE `subcategory`.`cat_id` = `category`.`id` AND `subcategory`.`public` = 1) as `count_sub`'),
									DB::raw('(SELECT COUNT(`products`.`id`) FROM `products` WHERE `products`.`cat_id` = `category`.`id` AND `products`.`public` = 1) as `count_products`')
								)
								->get();
		
		if(count($cat)){
			foreach($cat as $item){
				$item->count_sub        = (int)$item->count_sub;
				$item->count_products   = (int)$item->count_products;
                
				if(!$item->count_sub && !$item->count_products){
					continue;
				}
				
                if($item->count_sub){
                    $items[] = [
                        [
                            "text"								=> $item->name,
                            "callback_data"                     => 'cat-'.$item->id
                        ]
                    ];
                }else{
                    $items[] = [
                        [
                            "text"								=> $item->name,
                            "switch_inline_query_current_chat"	=> 'cat-'.$item->id
                        ]
                    ];
                }
			}
		}
        
		$inline_keyboard = json_encode([
			'inline_keyboard'	=> $items
		]);
		
		$this->sendMessage(
			[
				'chat_id'		=> $chat_id, 
				'text'			=> __('telegram.select_category'),
				'parse_mode'	=> 'Markdown',
				'reply_markup'	=> $inline_keyboard
			]
		);
    }
    
    private function sendMessage($send, $method = "sendMessage", $post = true, $json = true){
		$key = env('TELEGRAM_TOKEN', '');
		
		if(!$key){
			return false;
		}
		
        $url = "https://api.telegram.org/bot".$key."/".$method;
        
        CurlHelper::setUrl($url);
		CurlHelper::setTimeout(10);
		CurlHelper::post($post);
		CurlHelper::setData($send, false);
		CurlHelper::json($json);
				
		$result = CurlHelper::request(false);
        
        return $result;
    }
}
