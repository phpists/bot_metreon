<?php

namespace App\Helpers;

use App\Helpers\Helper;
use DB;

class StringHelper extends Helper {
    
    static function pass($count_ch = 10){
        $pass = '';
        $accepted = '0987654321zyxwvutsrqponmlkjihgfedcba';  // доступный набор символов в пароле
        
        srand(((int)((double)microtime()*1000000))); // меняем начальное число генератора случайных чисел
        
        for($i=0; $i<=$count_ch; $i++) { //формируем случайный символ для каждой составляющей пароля
            $random = rand(0, (strlen($accepted) -1)); // берем случайный индекс из строки $accepted
            $pass .= $accepted[$random] ; // дописываем в конец генерируемого пароля полученный символ
        }
        
        return $pass;
    }
    
    static function validateDate($date, $format = 'd/m/Y'){
		$d = \DateTime::createFromFormat($format, $date);
		
		$validate = $d && $d->format($format) == $date;
		
		if(!$validate){
			$format = 'd.m.Y';
			
			$d = \DateTime::createFromFormat($format, $date);
			
			return $d && $d->format($format) == $date;
		}
		
		return true;
    }
    
    static function convertDate($date, $from = 'd/m/Y', $to = 'd/m/Y'){
		$d = \DateTime::createFromFormat($from, $date);
		
		if(!$d){
			return false;
		}
		
		return $d->format($to);
	}
    
    static function GetDaysBetween($date1, $date2){
        return floor((strtotime($date2)-strtotime($date1))/86400);
    }
    
    static function phone($number, $format = '[1] [(3)] 3-2-2'){
        $plus = ($number[0] == '+'); // есть ли +
        $number = preg_replace('/\D/', '', $number); // убираем все знаки кроме цифр
        
        $len = array_sum(preg_split('/\D/', $format)); // получаем сумму чисел из $format
        
        $params = array_reverse(str_split($number)); // разбиваем $number на цифры и переворачиваем массив
        $params += array_fill(0, $len, 0); // забиваем пустаты предыдущего массива нулями
        
        //var_dump($params);exit;
        
        $format = strrev(preg_replace_callback('/(\d)/', function($m){return str_repeat('d%', $m[1]);}, $format)); // делаем форматированную строку и переворачиваем её
        
        $format = call_user_func_array('sprintf', array_merge(array($format), $params)); // заполняем строку цирами
        $format = ($plus ? '+' : '').strrev($format); // возвращаем строку в нормальное положение и прилепляем + обратно, если он был
        
        if(preg_match_all('/\[(.*?)\]/', $format, $match)){
            $c = count($match[0]);
            
            for($i = 0; $i < $c; $i++){
                if(!(int)preg_replace('/\D/', '', $match[1][$i])){
                    $format = str_replace($match[0][$i], '', $format);
                }
            }
        }
        
        return strtr(trim($format), array('[' => '', ']' => '')); // вырезаем знаки необязательности
    }
    
    static function url_title($str, $separator = 'underscore', $lowercase = true){
        if($separator == 'dash'){
            $search = '_';
            $replace = '-';
        }else{
            $search = '-';
            $replace = '_';
        }
        
        $trans = array(
            '&\#\d+?;' => '',
            '&\S+?;' => '',
            '\s+' => $replace,
            '[^a-z0-9\-\._]' => '',
            $replace . '+' => $replace,
            $replace . '$' => $replace,
            '^'.$replace => $replace,
            '\.+$' => ''
        );
        
        $translit = array(
          "а" => "a",
          "б" => "b",
          "в" => "v",
          "г" => "g",
          "д" => "d",
          "е" => "e",
          "ж" => "zh",
          "з" => "z",
          "і" => "i",
          "и" => "i",
          "й" => "y",
          "к" => "k",
          "л" => "l",
          "м" => "m",
          "н" => "n",
          "о" => "o",
          "п" => "p",
          "р" => "r",
          "с" => "s",
          "т" => "t",
          "у" => "u",
          "ф" => "f",
          "х" => "h",
          "ц" => "c",
          "ч" => "ch",
          "ш" => "sh",
          "щ" => "sch",
          "ъ" => "",
          "ы" => "y",
          "ь" => "",
          "э" => "e",
          "ю" => "yu",
          "я" => "ya",
          "ї" => "yi",
          "А" => "a",
          "Б" => "b",
          "В" => "v",
          "Г" => "g",
          "Д" => "d",
          "Е" => "e",
          "Ж" => "zh",
          "З" => "z",
          "І" => "i",
          "И" => "i",
          "Й" => "y",
          "К" => "k",
          "Л" => "l",
          "М" => "m",
          "Н" => "n",
          "О" => "o",
          "П" => "p",
          "Р" => "r",
          "С" => "s",
          "Т" => "t",
          "У" => "u",
          "Ф" => "f",
          "Х" => "h",
          "Ц" => "c",
          "Ч" => "ch",
          "Ш" => "sh",
          "Щ" => "sch",
          "Ъ" => "",
          "Ы" => "y",
          "Ь" => "",
          "Э" => "e",
          "Ю" => "yu",
          "Я" => "ya",
          "Ї" => "yi",
         // " " => "_",
          "," => "",
          "." => "-"
        );
        
        $str = strtr($str, $translit);
        $str = strip_tags($str);
        
        foreach($trans as $key => $val){
            $str = preg_replace("#" . $key . "#i", $val, $str);
        }
        
        if($lowercase === true){
            $str = strtolower($str);
        }
        
        $str = trim(stripslashes($str));
        $str = preg_replace('/-$||^-/', '', $str);
        
        return preg_replace('/-{2,}/', '-', $str);
    }
    
    static function uuid($unique = false){
        $uuid = sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0fff ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
        
        if($unique){
            $check = DB::table('orders')->where('uid', $uuid)->count() > 0;
            
            if(!$check){
                return $uuid;
            }
            
            return self::uuid(true);
        }else{
            return $uuid;
        }
    }
}
