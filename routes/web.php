<?php

use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], 'webhook/telegram', [
	'uses' => 'TelegramController@WebHook'
]);
