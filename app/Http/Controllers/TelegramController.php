<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Helpers\CurlHelper;
use App\Helpers\ImageHelper;

use Telegram\Bot\Api;
use TelegramBot\Api\BotApi;

use App\Models\Contents;
use App\Models\Category;
use App\Models\Products;
use App\Models\Messages;

use App\Models\Orders;
use App\Models\OrderProducts;

use App\Models\Clients;
use App\Models\Admins;

use Darryldecode\Cart\Cart;

class TelegramController extends Controller{
	
    public function WebHook(Request $request){
		$disabled = (int)env('disabled', 0);
		
		if($disabled){
			return false;
		}
		
        $key = env('TELEGRAM_TOKEN', '');
		
		if(!$key){
			return false;
		}
        
        $time = time();
		$dir = CACHE_PATH.'/data';
		
		if(!is_dir($dir)){
			mkdir($dir);
		}
        
        $telegram = new Api($key);
        
        $result = $telegram->getWebhookUpdates();
        
        file_put_contents($dir.'/'.$time.'.result', print_r($result, true));
        
        $text		= isset($result["message"]["text"]) ? $result["message"]["text"] : "";
        $chat_id	= isset($result["message"]["chat"]["id"]) ? $result["message"]["chat"]["id"] : 0;
        $username	= isset($result["message"]["from"]["username"]) ? $result["message"]["from"]["username"] : "";
        
        $client     = Clients::query()->where('chat_id', $chat_id)->first();
        
        //Клавиатура
        $keyboard	= [
			[]
		];
		
		if($text || (!$text && !isset($result["callback_query"]) && !isset($result["inline_query"]))){
			$message = Messages::query()->where('chat_id', $chat_id)->first();
			
			if($message){
				if($text == '/start' || $text == '/cancel'){
					Messages::query()->where('chat_id', $chat_id)->delete();
					
					$message = null;
				}else{
                    if($message->type == "code"){
                        $this->codeVerification($result, $text, $chat_id, $username);
                        $message = null;
                    }
                    
                    if($message->type == "request"){
                        $this->commandRequest($telegram, $result, $text, $chat_id, $client);
                        
                        $message    = null;
                        $text       = null;
                    }
                }
			}
		}
		
		if($text){
			if($text == '/start'){
                Messages::query()->where('chat_id', $chat_id)->delete();
                
				$this->commandStart($telegram, $chat_id);
			}elseif($text == '/subscribe'){
                $this->commandSubscribe($telegram, $chat_id);
            }elseif($text == '/cancel'){
                $this->commandCancel($telegram, $chat_id);
            }else{
                if(!$client){
                    $telegram->sendMessage([
                        'chat_id'		=> $chat_id, 
                        'text'			=> __('telegram.access_is_denied')
                    ]);
                    
                    return false;
                }
                
                if($client->status == 'new'){
                    $telegram->sendMessage([
                        'chat_id'		=> $chat_id, 
                        'text'			=> __('telegram.request_being_processed')
                    ]);
                    
                    return false;
                }
                
                if($client->status == 'rejected'){
                    $telegram->sendMessage([
                        'chat_id'		=> $chat_id, 
                        'text'			=> __('telegram.request_rejected')
                    ]);
                    
                    return false;
                }
                
                if($text == "Кошик"){
                    $this->commandCart($telegram, $chat_id);
                }
                
                if($text == "Оформити"){
                    $this->commandOrder($telegram, $chat_id, $result);
                }
                
                if($text == "Очистити"){
                    \Cart::session($chat_id);
                    \Cart::clear();
                    
                    $answer = "🛒 Кошик очищено\nПовертаємось у Головне меню ↩️";
                    
                    $keyboard	= [
                        []
                    ];
                    
                    $reply_markup = $telegram->replyKeyboardMarkup([
                        'keyboard'			=> $keyboard, 
                        'resize_keyboard'	=> true, 
                        'one_time_keyboard'	=> true
                    ]);
                    
                    $telegram->sendMessage([
                        'chat_id'		=> $chat_id, 
                        'text'			=> $answer,
                        'reply_markup'	=> $reply_markup
                    ]);
                    
                    return;
                }
            }
		}
		
		if(isset($result["callback_query"])){
			$chat_id	= $result["callback_query"]["from"]["id"];
			
			$command	= $result["callback_query"]["data"];
			$command	= explode('-', $command);
			
			if(isset($command[1])){
				$command[1]	= explode("#", $command[1]);
				
				$id			= (int)$command[1][0];
				$hash		= isset($command[1][1]) ? $command[1][1] : "";
				$command	= $command[0];
				
				$answer = [
					'callback_query_id'  => $result["callback_query"]["id"],
					'text'               => 'ok',
					'show_alert'         => false
				];
				
				$telegram->answerCallbackQuery($answer);
				
				if($id > 0){
					if($command == 'product'){
						$product = Products::query()
											->leftJoin('category', 'category.id', '=', 'products.cat_id')
											->where('products.public', '1')
											->where('products.id', $id)
											->select(DB::raw('products.*'), 'category.name as cat_name')
											->first();
						
						if($product){
						}
						
						if(!$product){
							$answer = "Товар не знайдено";
							
							$reply_markup = $telegram->replyKeyboardHide([
								'hide_keyboard' => true,
								'selective'     => false,
							]);
							
							$telegram->sendMessage([
								'chat_id'				=> $chat_id, 
								'text'					=> $answer,
								'reply_markup'			=> $reply_markup,
								'reply_to_message_id'	=> $result["callback_query"]["inline_message_id"]
							]);
							
							//
							
							$answer = "Повертаємось у Головне меню ↩️";
							
							$keyboard	= [
								[
								]
							];
							
							$reply_markup = $telegram->replyKeyboardMarkup([
								'keyboard'			=> $keyboard, 
								'resize_keyboard'	=> true, 
								'one_time_keyboard'	=> true
							]);
							
							$telegram->sendMessage([
								'chat_id'		=> $chat_id, 
								'text'			=> $answer,
								'reply_markup'	=> $reply_markup
							]);
						}
					}
                    
                    if($command == 'approved'){
                        $this->commandApproved($telegram, $result, $chat_id, $id);
                    }
                    
					if($command == 'rejected'){
                        $this->commandRejected($telegram, $result, $chat_id, $id);
                    }
                    
					if($command == 'data'){
						$hash = explode("&", $hash);
						
						$params = [];
						
						foreach($hash as $h){
							$h = explode("=", $h);
							
							if(isset($h[1])){
								$params[$h[0]] = $h[1];
							}
						}
					}
				}
			}else{
				$command	= $command[0];
				
				$answer = [
					'callback_query_id'  => $result["callback_query"]["id"],
					'text'               => 'ok',
					'show_alert'         => false
				];
				
				$telegram->answerCallbackQuery($answer);
				
				if($command == 'menu'){
					$chat_id	= $result["callback_query"]["from"]["id"];
					
					$this->commandMenu($telegram, $chat_id, false);
					
					return;
				}
				
				if($command == 'cart'){
					$chat_id	= $result["callback_query"]["from"]["id"];
					
					$this->commandCart($telegram, $chat_id, false);
					
					return;
				}
				
				if($command == 'start'){
					$chat_id	= $result["callback_query"]["from"]["id"];
					
					$keyboard	= [
						[
						]
					];
					
					$reply_markup = $telegram->replyKeyboardMarkup([
						'keyboard'			=> $keyboard, 
						'resize_keyboard'	=> true, 
						'one_time_keyboard'	=> false
					]);
					
					$answer = "Повертаємось у Головне меню ↩️";
					
					$telegram->sendMessage([
						'chat_id'		=> $chat_id, 
						'text'			=> $answer,
						'reply_markup'	=> $reply_markup
					]);
					
					$answer = "";
					
					$tmp = Contents::query()->where('key', 'introductory-message')->where('public', '1')->first();
					
					if($tmp){
						$answer = trim($tmp->text);
					}
					
					$telegram->sendMessage([
						'chat_id'		=> $chat_id, 
						'text'			=> $answer,
						'reply_markup'	=> $reply_markup
					]);
					
					return;
				}
			}
		}
		
		if(isset($result["inline_query"])){
			$answer = [
				'inline_query_id'		=> $result["inline_query"]["id"], 
				'results'             	=> [],
				'cache_time'           	=> 0,
				'is_personal'          	=> true,
				'next_offset'          	=> '',
				'switch_pm_text'       	=> '',
				'switch_pm_parameter'  	=> ''
			];
			
			$command	= $result["inline_query"]["query"];
			
			if($command){
                
			}
			
			$answer['results'] = json_encode($answer['results']);
			
			$this->sendMessage($answer, "answerInlineQuery", true, false);
			return;
		}
		
        return response()->json([], 200);
    }
    
    function commandStart(&$telegram, $chat_id){
        //Клавиатура
        $keyboard	= [
			[]
		];
        
        $reply_markup = $telegram->replyKeyboardMarkup([
            'keyboard'			=> $keyboard, 
            'resize_keyboard'	=> true, 
            'one_time_keyboard'	=> false
        ]);
        
        //
        
        $answer = "";
        
        $tmp = Contents::query()->where('key', 'introductory-message')->where('public', '1')->first();
        
        if($tmp){
            $answer = trim($tmp->text);
        }
        
        $telegram->sendMessage([
            'chat_id'		=> $chat_id, 
            'text'			=> $answer,
            'reply_markup'	=> $reply_markup
        ]);
        
        //
        
        $id = md5(time().'-'.$chat_id.'-code');
        
        Messages::create([
            "id"			=> $id,
            "product_id"	=> 0,
            "message_id"	=> 0,
            "chat_id"		=> $chat_id,
            "date"			=> "",
            "type"			=> "request"
        ]);
    }
    
    function commandRequest(&$telegram, $result, $text, $chat_id, $client){
        $text = preg_replace('/[^a-zA-Zа-яА-ЯіІёЁъЪєЄїЇ0-9\:\-\(\)\.\, ]/ui', '', $text);
		$text = trim($text);
        
        if(!$text){
            $telegram->sendMessage([
                'chat_id'		=> $chat_id, 
                'text'			=> __('telegram.text_required')
            ]);
            
            return false;
        }
        
        $len = mb_strlen($text);
        
        if($len > 1000){
            $telegram->sendMessage([
                'chat_id'		=> $chat_id, 
                'text'			=> __('telegram.text_max', ['max' => 1000])
            ]);
            
            return false;
        }
        
        $username = isset($result["message"]["from"]["username"]) ? $result["message"]["from"]["username"] : "";
        
        $fio = [];
        
        $first_name = isset($result["message"]["from"]["first_name"]) ? $result["message"]["from"]["first_name"] : "";
        $last_name  = isset($result["message"]["from"]["last_name"]) ? $result["message"]["from"]["last_name"] : "";
        
        if($last_name){
            $fio[] = $last_name;
        }
        
        if($first_name){
            $fio[] = $first_name;
        }
        
        if(!$client){
            $client = Clients::create([
                'chat_id'   => $chat_id,
                'username'  => $username,
                'name'      => implode(" ", $fio),
                'note'      => $text
            ]);
        }else{
            $client->note       = $text;
            
            if($username){
                $client->username   = $username;
            }
            
            if($fio){
                $client->name       = implode(" ", $fio);
            }
            
            $client->save();
        }
        
        Messages::query()->where('chat_id', $chat_id)->delete();
        
        $telegram->sendMessage([
            'chat_id'		=> $chat_id, 
            'text'			=> __('telegram.request_send')
        ]);
        
        //
        
        $chats = DB::table('admins')->where('notify', 1)->get();
        
        if(count($chats)){
			$message = trans('telegram.new_request', [
                'id'        => $client->id,
                'username'  => $username,
                'fio'       => $client->name,
                'note'      => $text
            ]);
            
            $keyboard = [
                [
                    [
                        "text"			=> __('telegram.actions.approved'),
                        "callback_data"	=> 'approved-'.$client->id
                    ],
                    [
                        "text"			=> __('telegram.actions.rejected'),
                        "callback_data"	=> 'rejected-'.$client->id
                    ]
                ]
            ];
            
            $inline_keyboard = json_encode([
                'inline_keyboard'	=> $keyboard
            ]);
			
			foreach($chats as $item){
				$this->sendMessage(
					[
						'chat_id'		=> $item->chat_id,
						'text'			=> $message,
						'parse_mode'	=> 'Markdown',
                        'reply_markup'	=> $inline_keyboard
					],
					"sendMessage", 
					true, 
					true
				);
			}
		}
    }
    
    function commandApproved(&$telegram, $result, $chat_id, $client_id){
        $client     = Clients::query()->where('id', $client_id)->first();
        
        if(!$client){
            $telegram->sendMessage([
                'chat_id'		=> $chat_id, 
                'text'			=> __('telegram.request_not_found')
            ]);
            
            return false;
        }
        
        if($client->status == 'approved'){
            $telegram->sendMessage([
                'chat_id'		=> $chat_id, 
                'text'			=> __('telegram.request_alredy_approved')
            ]);
            
            return false;
        }
        
        if($client->status == 'rejected'){
            $telegram->sendMessage([
                'chat_id'		=> $chat_id, 
                'text'			=> __('telegram.request_alredy_rejected')
            ]);
            
            return false;
        }
        
        $client->status = 'approved';
        $client->save();
        
        $telegram->sendMessage([
            'chat_id'		=> $chat_id, 
            'text'			=> __('telegram.request_processed')
        ]);
        
        //
        
        $telegram->sendMessage([
            'chat_id'		=> $client->chat_id, 
            'text'			=> __('telegram.request_approved')
        ]);
    }
    
    function commandRejected(&$telegram, $result, $chat_id, $client_id){
        $client     = Clients::query()->where('id', $client_id)->first();
        
        if(!$client){
            $telegram->sendMessage([
                'chat_id'		=> $chat_id, 
                'text'			=> __('telegram.request_not_found')
            ]);
            
            return false;
        }
        
        if($client->status == 'approved'){
            $telegram->sendMessage([
                'chat_id'		=> $chat_id, 
                'text'			=> __('telegram.request_alredy_approved')
            ]);
            
            return false;
        }
        
        if($client->status == 'rejected'){
            $telegram->sendMessage([
                'chat_id'		=> $chat_id, 
                'text'			=> __('telegram.request_alredy_rejected')
            ]);
            
            return false;
        }
        
        $client->status = 'rejected';
        $client->save();
        
        $telegram->sendMessage([
            'chat_id'		=> $chat_id, 
            'text'			=> __('telegram.request_processed')
        ]);
        
        //
        
        $telegram->sendMessage([
            'chat_id'		=> $client->chat_id, 
            'text'			=> __('telegram.request_rejected')
        ]);
    }
    
    function commandMenu(&$telegram, $chat_id, $hide_keyboard = true, $mini = false){
		if($hide_keyboard){
			$reply_markup = $telegram->replyKeyboardHide([
				'hide_keyboard' => true,
				'selective'     => false,
			]);
		}
		
		//
		
		$answer = "";
		
		$tmp = Contents::query()->where('key', 'menu-text')->where('public', '1')->first();
		
		if($tmp){
			$answer = trim($tmp->text);
		}
		
		if($hide_keyboard){
			$telegram->sendMessage([
				'chat_id'		=> $chat_id, 
				'text'			=> $answer,
				'reply_markup'	=> $reply_markup
			]);
		}else{
			$telegram->sendMessage([
				'chat_id'		=> $chat_id, 
				'text'			=> $answer
			]);
		}
		
		//
		
		$items = [];
		
		$cat = Category::query()->where('category.public', '1')
								->orderBy('category.sort', 'asc')
								->select(
									DB::raw('category.*'), 
									DB::raw('(SELECT COUNT(`products`.`id`) FROM `products` WHERE `products`.`cat_id` = `category`.`id` AND `products`.`public` = 1) as `count_products`')
								)
								->get();
		
		if(count($cat)){
			foreach($cat as $item){
				$item->count_products = (int)$item->count_products;
				
				if(!$item->count_products){
					continue;
				}
				
				$items[] = [
					[
						"text"								=> $item->name,
						"switch_inline_query_current_chat"	=> 'cat-'.$item->id
					]
				];
			}
		}
		
		if($mini){
			$items[] = [
				[
					"text"								=> "↩️ Головне меню",
					"callback_data"						=> "start"
				]
			];
		}else{
			$items[] = [
				[
					"text"								=> "↩️ Головна",
					"callback_data"						=> "start"
				],
				[
					"text"								=> "🛒 Корзина",
					"callback_data"						=> "cart"
				]
			];
		}
		
		$answer = "Оберіть потрібне ⤵️";
		
		$inline_keyboard = json_encode([
			'inline_keyboard'	=> $items
		]);
		
		$this->sendMessage(
			[
				'chat_id'		=> $chat_id, 
				'text'			=> $answer,
				'parse_mode'	=> 'Markdown',
				'reply_markup'	=> $inline_keyboard
			]
		);
	}
	
	function commandCart(&$telegram, $chat_id){
		\Cart::session($chat_id);
		
		$cart = \Cart::getContent();
		
		$answer = "🛒 Кошик";
		
		if(count($cart)){
			$keyboard	= [
				[
					["text" => "Оформити"],
					["text" => "Очистити"]
				],
				[
					["text" => "Головна"]
				]
			];
			
			$reply_markup = $telegram->replyKeyboardMarkup([
				'keyboard'			=> $keyboard, 
				'resize_keyboard'	=> true, 
				'one_time_keyboard'	=> false
			]);
			
			//
			
			$amount = 0;
			
			foreach($cart as $item){
				$amount += ($item->quantity * $item->price);
				
				$answer .= "\n\n";
				$answer .= "#\n";
				$answer .= "👉 ".$item->quantity." шт X ".$item->name."\n";
				$answer .= "💰 Сума: ".($item->quantity * $item->price)." грн\n";
				$answer .= "\n";
			}
			
			$answer .= "--\n";
			$answer .= "💳 Всього: ".$amount." грн";
			
			$telegram->sendMessage([
				'chat_id'		=> $chat_id, 
				'text'			=> $answer,
				'reply_markup'	=> $reply_markup
			]);
		}else{
			$answer .= "\nЩе порожньо, спершу оберіть товар";
			
			$items = [];
			
			$items[] = [
				[
					"text"								=> "↩️ Головне меню",
					"callback_data"						=> "start"
				]
			];
			
			$inline_keyboard = json_encode([
				'inline_keyboard'	=> $items
			]);
			
			$this->sendMessage(
				[
					'chat_id'		=> $chat_id, 
					'text'			=> $answer,
					'parse_mode'	=> 'Markdown',
					'reply_markup'	=> $inline_keyboard
				]
			);
		}
	}
    
    function commandOrder(&$telegram, $chat_id, $result){
		\Cart::session($chat_id);
		
		$cart = \Cart::getContent();
		
		if(count($cart)){
			$total = 0;
			
			foreach($cart as $item){
				$total += ($item->quantity * $item->price);
			}
			
            $order_insert = [
				"status"	=> "new",
				"amount"	=> $total,
				"chat_id"	=> $chat_id
			];
            
			$order = Orders::create($order_insert);
            
			foreach($cart as $item){
				$insert = [
                    "order_id"		=> $order->id,
                    "product_id"	=> $item->id,
                    "count"			=> $item->quantity,
                    "price"			=> $item->price,
                    "amount"		=> ($item->quantity * $item->price)
                ];
                
                OrderProducts::create($insert);
			}
			
			\Cart::clear();
			
			$client = Clients::query()->where('chat_id', $chat_id)->first();
			
			if(!$client){
				$client = Clients::create([
					'chat_id' => $chat_id
				]);
			}
			
			$upd = false;
			
			if(isset($result["message"]["from"]["username"])){
				if($result["message"]["from"]["username"] && $result["message"]["from"]["username"] != $client->username){
					$client->username = $result["message"]["from"]["username"];
					
					$upd = true;
				}
			}
			
			if(isset($result["message"]["from"]["first_name"])){
				if($result["message"]["from"]["first_name"] && $result["message"]["from"]["first_name"] != $client->name){
					$client->name = $result["message"]["from"]["first_name"];
					
					$upd = true;
				}
			}
			
			if($upd){
				$client->save();
			}
			
			$fio = $client->name;
			
			$answer		= "";
			$keyboard	= [
				[
					["text" => "Відміна"]
				]
			];
			
			$type		= "";
			
			if(!$fio){
				$answer = "Введіть ім'я ⌨️⤵️";
				
				$type	= "name";
			}else{
				$answer = "Записуємо вас як ".$fio."? 😎";
				
				$keyboard	= [
					[
						["text" => "Так"],
						["text" => "Введу вручну"]
					],
					[
						["text" => "Відміна"]
					]
				];
				
				$type	= "confirm_name";
			}
			
			if($keyboard){
				$reply_markup = $telegram->replyKeyboardMarkup([
					'keyboard'			=> $keyboard, 
					'resize_keyboard'	=> true, 
					'one_time_keyboard'	=> false
				]);
			}else{
				$reply_markup = $telegram->replyKeyboardHide([
					'hide_keyboard' => true,
					'selective'     => false,
				]);
			}
			
			//
			
			$message = $this->sendMessage([
				'chat_id'		=> $chat_id, 
				'text'			=> $answer,
				'reply_markup'	=> $reply_markup
			]);
			
			if($type){
				$id = md5($order->id.'-'.$chat_id.'-'.$message['result']['date']);
				
				Messages::create([
					"id"			=> $id,
					"product_id"	=> 0,
					"message_id"	=> $message['result']['message_id'],
					"chat_id"		=> $chat_id,
					"date"			=> $message['result']['date'],
					"type"			=> $type,
					"data"			=> $order->id
				]);
			}
			
			return;
		}
		
		$answer = "🛒 Кошик порожній";
		
		$items = [];
		
		$items[] = [
			[
				"text"								=> "↩️ Головне меню",
				"callback_data"						=> "start"
			]
		];
		
		$inline_keyboard = json_encode([
			'inline_keyboard'	=> $items
		]);
		
		$this->sendMessage(
			[
				'chat_id'		=> $chat_id, 
				'text'			=> $answer,
				'parse_mode'	=> 'Markdown',
				'reply_markup'	=> $inline_keyboard
			]
		);
	}
	
	//
	
	// кількість
	function commandCount(&$telegram, $chat_id, $id, $params){
		//$packaging = ProductsPackaging::query()->where('product_id', $id)->where('packaging_id', $params['packaging'])->first();
		
		$items = [[], []];
		
		$params['count'] = 1;
		
		$items[0][0] = [
			"text"			=> 1,
			"callback_data"	=> 'data-'.$id.'#'.http_build_query($params)
		];
		
		$params['count'] = 2;
		
		$items[0][1] = [
			"text"			=> 2,
			"callback_data"	=> 'data-'.$id.'#'.http_build_query($params)
		];
		
		$params['count'] = 3;
		
		$items[1][0] = [
			"text"			=> 3,
			"callback_data"	=> 'data-'.$id.'#'.http_build_query($params)
		];
		
		$params['count'] = 4;
		
		$items[1][1] = [
			"text"			=> 4,
			"callback_data"	=> 'data-'.$id.'#'.http_build_query($params)
		];
		
		$items[] = [
			[
				"text"			=> "↩️ До товарів",
				"callback_data"	=> 'menu'
			]
		];
		
		$inline_keyboard = json_encode([
			'inline_keyboard'	=> $items
		]);
		
		$this->sendMessage([
			'chat_id'				=> $chat_id, 
			'text'					=> "Оберіть кількість ⤵️",
			'reply_markup'			=> $inline_keyboard
		]);
	}
	
	// додавання в корзину
	function commandAdd(&$telegram, $chat_id, $id, $params){
		\Cart::session($chat_id);
		
		$product = Products::query()
							->leftJoin('category', 'category.id', '=', 'products.cat_id')
							->where('products.id', $id)
							->select(
								DB::raw('products.*'), 
								'category.name as cat_name'
							)
							->first();
		
		if(\Cart::get($product->id)){
			\Cart::update($product->id, array(
				'name'				=> $product->name,
				'price'				=> $product->price,
				'quantity'			=> $params['count'],
				'attributes'		=> array(
				)
			));
		}else{
			\Cart::add(array(
				'id'				=> $product->id,
				'name'				=> $product->name,
				'price'				=> $product->price,
				'quantity'			=> $params['count'],
				'attributes'		=> array(
				)
			));
		}
		
		//
		
		$answer = "Додано у 🛒";
		
		$reply_markup = $telegram->replyKeyboardHide([
			'hide_keyboard' => true,
			'selective'     => false,
		]);
		
		$telegram->sendMessage([
			'chat_id'		=> $chat_id, 
			'text'			=> $answer,
			'reply_markup'	=> $reply_markup
		]);
		
		sleep(1);
		
		$answer = "Повертаємось у Головне меню ↩️";
		
		$keyboard	= [
			[]
		];
		
		$reply_markup = $telegram->replyKeyboardMarkup([
			'keyboard'			=> $keyboard, 
			'resize_keyboard'	=> true, 
			'one_time_keyboard'	=> true
		]);
		
		$telegram->sendMessage([
			'chat_id'		=> $chat_id, 
			'text'			=> $answer,
			'reply_markup'	=> $reply_markup
		]);
	}
	
	// name
    
    function commandGetName(&$telegram, $chat_id, $text, $result, &$message){
		if($text == "Відміна"){
			DB::table('orders')->where('id', $message->data)->delete();
			
			DB::table('messages')->where('id', $message->id)->delete();
			
			return false;
		}
		
		$text = preg_replace('/[^a-zA-Zа-яА-ЯіІёЁъЪєЄїЇ]/ui', '', $text);
		$len = mb_strlen($text);
		
		if($len < 2){
			$answer = "Ім'я має містити мінімум 2 символи\nВведіть ім'я ⌨️⤵️";
			
			$keyboard	= [
				[
					["text" => "Відміна"]
				]
			];
			
			$message = $this->sendMessage([
				'chat_id'		=> $chat_id, 
				'text'			=> $answer,
				'reply_markup'	=> $reply_markup
			]);
			
			return true;
		}
		
		if($len > 30){
			$answer = "Дозволено введення до 30-ти символів\nВведіть ім'я ⌨️⤵️";
			
			$keyboard	= [
				[
					["text" => "Відміна"]
				]
			];
			
			$message = $this->sendMessage([
				'chat_id'		=> $chat_id, 
				'text'			=> $answer,
				'reply_markup'	=> $reply_markup
			]);
			
			return true;
		}
		
		$client = Clients::query()->where('chat_id', $chat_id)->first();
		
		$client->name = $text;
		$client->save();
		
		$order = Orders::query()->where('id', $message->data)->first();
		
		$client->name = $text;
		$client->save();
		
		DB::table('messages')->where('id', $message->id)->delete();
		
		//
		
		$this->sendAccepted($telegram, $chat_id);
		
		//
		
		$this->startGetSurname($telegram, $chat_id, $text, $result, $order->id);
		
		return true;
	}
	
	function commandConfirmName(&$telegram, $chat_id, $text, $result, &$message){
		$order_id = $message->data;
		
		if($text == "Так"){
			$order 	= Orders::query()->where('id', $order_id)->first();
			$client = Clients::query()->where('chat_id', $chat_id)->first();
			
			$order->name = $client->name.' '.$client->surname;
			$order->save();
			
			DB::table('messages')->where('id', $message->id)->delete();
			
			$this->startConfirmPhone($telegram, $chat_id, $text, $result, $order_id);
		}
		
		if($text == "Введу вручну"){
			DB::table('messages')->where('id', $message->id)->delete();
			
			$answer = "Введіть ім'я ⌨️⤵️";
			
			$keyboard	= [
				[
					["text" => "Відміна"]
				]
			];
			
			$reply_markup = $telegram->replyKeyboardMarkup([
				'keyboard'			=> $keyboard, 
				'resize_keyboard'	=> true, 
				'one_time_keyboard'	=> true
			]);
			
			$message = $this->sendMessage([
				'chat_id'		=> $chat_id, 
				'text'			=> $answer,
				'reply_markup'	=> $reply_markup
			]);
			
			$id = md5($order_id.'-'.$chat_id.'-'.$message['result']['date']);
			
			Messages::create([
				"id"			=> $id,
				"product_id"	=> 0,
				"message_id"	=> $message['result']['message_id'],
				"chat_id"		=> $chat_id,
				"date"			=> $message['result']['date'],
				"type"			=> "name",
				"data"			=> $order_id
			]);
		}
		
		if($text == "Відміна"){
			DB::table('orders')->where('id', $order_id)->delete();
			
			DB::table('messages')->where('id', $message->id)->delete();
			
			return false;
		}
		
		return true;
	}
	
	// phone
	
	function startConfirmPhone(&$telegram, $chat_id, $text, $result, $order_id){
		$answer = "Дозволяєте взяти ваш номер телефону з профілю?";
		
		$client = Clients::query()->where('chat_id', $chat_id)->first();
		
		if($client){
			if($client->phone){
				$answer .= " (".$client->phone.")";
			}
		}
		
		$keyboard	= [
			[
				["text" => "Так", "request_contact" => true],
				["text" => "Введу вручну"]
			],
			[
				["text" => "Відміна"]
			]
		];
		
		$reply_markup = $telegram->replyKeyboardMarkup([
			'keyboard'			=> $keyboard, 
			'resize_keyboard'	=> true, 
			'one_time_keyboard'	=> true
		]);
		
		$message = $this->sendMessage([
			'chat_id'		=> $chat_id, 
			'text'			=> $answer,
			'reply_markup'	=> $reply_markup
		]);
		
		$id = md5($order_id.'-'.$chat_id.'-'.$message['result']['date']);
		
		Messages::create([
			"id"			=> $id,
			"product_id"	=> 0,
			"message_id"	=> $message['result']['message_id'],
			"chat_id"		=> $chat_id,
			"date"			=> $message['result']['date'],
			"type"			=> "confirm_phone",
			"data"			=> $order_id
		]);
	}
	
	function commandConfirmPhone(&$telegram, $chat_id, $text, $result, &$message){
		if($text == "Відміна"){
			DB::table('orders')->where('id', $message->data)->delete();
			
			DB::table('messages')->where('id', $message->id)->delete();
			
			return false;
		}
		
		$order = Orders::query()->where('id', $message->data)->first();
		
		if($text == "Так"){
			DB::table('messages')->where('id', $message->id)->delete();
			
			$client = Clients::query()->where('chat_id', $chat_id)->first();
			
			if($client->phone){
				$order->phone = $client->phone;
				$order->save();
				
				$this->sendAccepted($telegram, $chat_id);
				
				$this->commandCheckDelivery($telegram, $chat_id, $text, $result, $order->id);
			}
		}
		
		if($text == "Введу вручну"){
			DB::table('messages')->where('id', $message->id)->delete();
			
			$answer = "Введіть номер ⌨️⤵️";
			
			$keyboard	= [
				[
					["text" => "Відміна"]
				]
			];
			
			$reply_markup = $telegram->replyKeyboardMarkup([
				'keyboard'			=> $keyboard, 
				'resize_keyboard'	=> true, 
				'one_time_keyboard'	=> true
			]);
			
			$message = $this->sendMessage([
				'chat_id'		=> $chat_id, 
				'text'			=> $answer,
				'reply_markup'	=> $reply_markup
			]);
			
			$id = md5($order->id.'-'.$chat_id.'-'.$message['result']['date']);
			
			Messages::create([
				"id"			=> $id,
				"product_id"	=> 0,
				"message_id"	=> $message['result']['message_id'],
				"chat_id"		=> $chat_id,
				"date"			=> $message['result']['date'],
				"type"			=> "phone",
				"data"			=> $order->id
			]);
		}
		
		if(!$text && isset($result["message"]["contact"])){
			$text = $result["message"]["contact"]["phone_number"];
			
			$this->commandGetPhone($telegram, $chat_id, $text, $result, $message);
		}
		
		//
		
		return true;
	}
	
	function commandGetPhone(&$telegram, $chat_id, $text, $result, &$message){
		if($text == "Відміна"){
			DB::table('orders')->where('id', $message->data)->delete();
			
			DB::table('messages')->where('id', $message->id)->delete();
			
			return false;
		}
		
		$text 	= (string)preg_replace('/[^0-9]/', '', $text); 
		$len	= strlen($text);
		
		if($len < 10){
			$answer = "Номер має містити мінімум 2 цифр\nВведіть номер ⌨️⤵️";
			
			$keyboard	= [
				[
					["text" => "Відміна"]
				]
			];
			
			$reply_markup = $telegram->replyKeyboardMarkup([
				'keyboard'			=> $keyboard, 
				'resize_keyboard'	=> true, 
				'one_time_keyboard'	=> true
			]);
			
			$message = $this->sendMessage([
				'chat_id'		=> $chat_id, 
				'text'			=> $answer,
				'reply_markup'	=> $reply_markup
			]);
			
			return true;
		}
		
		if($len > 13){
			$answer = "Номер має містити до 13-ти цифр\nВведіть номер ⌨️⤵️";
			
			$keyboard	= [
				[
					["text" => "Відміна"]
				]
			];
			
			$reply_markup = $telegram->replyKeyboardMarkup([
				'keyboard'			=> $keyboard, 
				'resize_keyboard'	=> true, 
				'one_time_keyboard'	=> true
			]);
			
			$message = $this->sendMessage([
				'chat_id'		=> $chat_id, 
				'text'			=> $answer,
				'reply_markup'	=> $reply_markup
			]);
			
			return true;
		}
		
		if($text[0] == "0"){
			$text = "38".$text;
		}
		
		if($text[0] == "8"){
			$text = "3".$text;
		}
		
		$client = Clients::query()->where('chat_id', $chat_id)->first();
		
		$client->phone = $text;
		$client->save();
		
		$order = Orders::query()->where('id', $message->data)->first();
		
		$order->phone = $text;
		$order->save();
		
		DB::table('messages')->where('id', $message->id)->delete();
		
		//
		
		$this->sendAccepted($telegram, $chat_id);
		
		//
		
		//$this->startConfirmAddress($telegram, $chat_id, $text, $result, $order->id);
		$this->commandCheckDelivery($telegram, $chat_id, $text, $result, $order->id);
		
		return true;
	}
	
	//
	
	function startConfirm(&$telegram, $chat_id, $text, $result, $order_id){
		$answer = "";
		
		$tmp = Contents::query()->where('key', 'thank')->where('public', '1')->first();
		
		if($tmp){
			$answer = trim($tmp->text);
		}
		
		if($answer){
			$answer .= "\n";
		}
		
		$keyboard	= [];
		
		$reply_markup = $telegram->replyKeyboardMarkup([
			'keyboard'			=> $keyboard, 
			'resize_keyboard'	=> true, 
			'one_time_keyboard'	=> true
		]);
		
		$message = $this->sendMessage([
			'chat_id'		=> $chat_id, 
			'text'			=> $answer,
			'reply_markup'	=> $reply_markup
		]);
        
        //
		
		$products = "";
		
		$tmp = OrderProducts::query()
						->join('products', 'products.id', '=', 'order_products.product_id')
						->where('order_products.order_id', $order_id)
						->select(
							'products.name', 
							'order_products.count', 
							'order_products.price', 
							'order_products.amount'
						)
						->get();
		
		if(count($tmp)){
			foreach($tmp as $item){
				if($products){
					$products .= "\n\n";
				}
				
				$products .= "#\n";
				$products .= "👉 ".$item->count." шт X ".$item->name."\n";
				$products .= "💰 Сума: ".$item->amount." грн\n";
				//$products .= "\n";
			}
		}
		
		$products .= "--\n";
		$products .= "💳 Всього: ".$order->amount." грн";
		
		$this->sendMessages([
			'id' 			=> $order_id,
			
			'fio' 			=> $order->name,
			'phone' 		=> $order->phone,
			'username' 		=> ($order->username ? '@'.$order->username : '-'),
			
			'note' 			=> $text,
			'amount' 		=> $order->amount,
			
			'products' 		=> $products
		], 'order');
	}
	
	//
    
    function sendAccepted(&$telegram, $chat_id){
		$answer = "Прийнято, йдемо далі 👀";
		
		$reply_markup = $telegram->replyKeyboardHide([
			'hide_keyboard' => true,
			'selective'     => false,
		]);
		
		$message = $this->sendMessage([
			'chat_id'		=> $chat_id, 
			'text'			=> $answer,
			'reply_markup'	=> $reply_markup
		]);
		
		sleep(1);
	}
    
    //
    
    function commandSubscribe(&$telegram, $chat_id){
        $id = md5(time().'-'.$chat_id.'-code');
        
        Messages::create([
            "id"			=> $id,
            "product_id"	=> 0,
            "message_id"	=> 0,
            "chat_id"		=> $chat_id,
            "date"			=> "",
            "type"			=> "code"
        ]);
        
        $this->sendMessage(
            [
                'chat_id'		=> $chat_id,
                'text'			=> trans('telegram.invitation'),
                'parse_mode'	=> 'Markdown',
            ],
            "sendMessage", 
            true, 
            true
        );
    }
    
    function commandCancel(&$telegram, $chat_id){
        Messages::query()->where('chat_id', $chat_id)->delete();
        
        $admin = Admins::query()->where('chat_id', $chat_id)->first();
        
        if(!$admin){
            $this->sendMessage(
                [
                    'chat_id'		=> $chat_id,
                    'text'			=> trans('telegram.not_subscribed'),
                    'parse_mode'	=> 'Markdown',
                ],
                "sendMessage", 
                true, 
                true
            );
        }else{
            Admins::query()->where('id', $admin->id)->delete();
            
            $this->sendMessage(
                [
                    'chat_id'		=> $chat_id,
                    'text'			=> trans('telegram.successfully_unsubscribed'),
                    'parse_mode'	=> 'Markdown',
                ],
                "sendMessage", 
                true, 
                true
            );
        }
    }
    
    public function codeVerification($data, $text, $chat_id, $username){
        $code = env('TELEGRAM_CODE', '');
        
        if($code && $text == $code){
            Messages::query()->where('chat_id', $chat_id)->delete();
            
            $admin = Admins::query()->where('chat_id', $chat_id)->first();
            
            if(!$admin){
                Admins::query()->create([
                    'chat_id'   => $chat_id,
                    'username'  => $username
                ]);
                
                $this->sendMessage(
                    [
                        'chat_id'		=> $chat_id,
                        'text'			=> trans('telegram.notisset'),
                        'parse_mode'	=> 'Markdown',
                    ],
                    "sendMessage", 
                    true, 
                    true
                );
            }else{
                $this->sendMessage(
                    [
                        'chat_id'		=> $chat_id,
                        'text'			=> trans('telegram.isset'),
                        'parse_mode'	=> 'Markdown',
                    ],
                    "sendMessage", 
                    true, 
                    true
                );
            }
        }else{
            $this->sendMessage(
                [
                    'chat_id'		=> $chat_id,
                    'text'			=> trans('telegram.invalid_code'),
                    'parse_mode'	=> 'Markdown',
                ],
                "sendMessage", 
                true, 
                true
            );
        }
    }
    
    //
    
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
    
    private function sendMessages($data, $key, $all = true){
        if($all){
            $chats = DB::table('admins')->get();
        }else{
            $chats = DB::table('admins')->where('notify', 1)->get();
        }
		
		if(count($chats)){
			$message = trans('telegram.'.$key, $data);
			
			foreach($chats as $item){
				$this->sendMessage(
					[
						'chat_id'		=> $item->chat_id,
						'text'			=> $message,
						'parse_mode'	=> 'Markdown',
					],
					"sendMessage", 
					true, 
					true
				);
			}
		}
	}
}
