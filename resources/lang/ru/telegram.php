<?php

return [
	'invitation'				=> 'Для того чтобы подписаться на рассылку отправьте код подтверждения',
	'not_subscribed'			=> 'Вы не подписаны на бота',
	'successfully_unsubscribed' => 'Вы успешно отписались от рассылки',
	
	'notisset'					=> "Вы подписались на бота.\nОтмена подписки: /cancel",
	'isset'						=> "Вы уже подписаны на бота.\nОтмена подписки: /cancel",
	'invalid_code'				=> 'Неверный код',
	
	'order'						=> "Новый заказ!\nID: :id\n\nКонтакты:\nИмя: :fio\nТелефон: :phone\nTelegram: :username\n\n:products",
    
    'status'                    => [
        'processed' => 'Ваш заказ #:id обработан',
        'canceled'  => 'Ваш заказ #:id отменен'
    ],
    
    'text_required'             => 'Введите текст запроса',
    'text_max'                  => 'Максимальная длина :max символов',
    
    'request_send'              => 'Ваша заявка отправлена на обработку',
    
    'new_request'			    => "Новая заявка!\n\nID: :id\nЛогин: :username\nИмя: :fio\n\n:note",
    
    'actions'				    => [
        'approved'  => 'Одобрить',
        'rejected'  => 'Отказать'
    ],
    
    'request_approved'              => 'Вашу заявку одобрено',
    'request_rejected'              => 'В вашей заявке отказано',
    
    'request_alredy_approved'       => 'Заявка уже одобрена',
    'request_alredy_rejected'       => 'В заявке уже отказано',
    
    'request_not_found'             => 'Заявка не найдена',
    
    'request_processed'             => 'Заявка обработана',
    
    'request_being_processed'       => 'Ваша заявка на обработке',
    
    'access_is_denied'              => 'Доступ запрещен',
    
    'select_category'               => "Выберите категорию ⤵️",
    'select_subcategory'            => "Выберите подкатегорию ⤵️",
];
