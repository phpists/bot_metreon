<?php

namespace App\Helpers;

use App\Helpers\Helper;

class CurlHelper extends Helper {
    
    static private $_url = '';
    
    static function setUrl($url){
		self::$_url = $url;
    }
    
    static private $_timeout = 0;
    
    static function setTimeout($time){
        self::$_timeout = $time;
    }
    
    static private $_connecttimeout = 0;
    
    static function setConnectTimeout($time){
        self::$_connecttimeout = $time;
    }
    
    static private $_json = false;
    
    static function json($json = false){
		self::$_json = $json;
    }
    
    static private $_post = false;
    
    static function post($post = false){
		self::$_post = $post;
    }
    
    static private $_data = array();
    static private $_data_json = false;
    
    static function setData($data = null, $json = false){
		self::$_data = $data;
		
		self::$_data_json = $json;
    }
    
    static private $_auth_data = '';
    
    static function auth($data = ''){
		self::$_auth_data = $data;
    }
    
    static private $_headers = [];
    
    static function setHeaders($data){
		self::$_headers = $data;
    }
    
    static private $_cookie_file = '';
    
    static function cookieFile($file){
		self::$_cookie_file = $file;
    }
    
    static private $_new_session = false;
    
    static function newSession($param){
		self::$_new_session = $param;
    }
    
    static function save($filepath){
        if(self::$_url == null){
            return false;
        }
        
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, self::$_url);
        
        if(self::$_timeout > 0){
            curl_setopt($curl, CURLOPT_TIMEOUT, self::$_timeout);
        }
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        /*** Create a new file */
        //$fp = fopen($filepath, 'w');
        
        /*** Ask cURL to write the contents to a file */
        //curl_setopt($curl, CURLOPT_FILE, $fp);
        
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        
        curl_setopt($curl, CURLOPT_HEADER, false);
        
        /*** Execute the cURL session */
        $out	= curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
        $code	= curl_getinfo($curl, CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
        
        /*** Close cURL session and file */
        curl_close($curl);
        
        if($code != 200 && $code != 204){
            return false;
        }
        
        //fclose($fp);
        
        if(file_put_contents($filepath, $out)){
            $size = (int)filesize($filepath);
            
            return $size > 0;
        }
        
        return false;
    }
    
    static private $_gzip = false;
    
    static function gzip($param){
		self::$_gzip = $param;
    }
    
    static private $_file = '';
    
    static function file($file){
		self::$_file = $file;
    }
    
    static private $_rowdata = '';
    
    static function data($data){
		self::$_rowdata = $data;
    }
    
    static function request($debug = false){
        if(self::$_url == null){
            return false;
        }
        
        $curl = curl_init();
        
        if(!self::$_post && self::$_data != null){
            self::$_url .= '?'.http_build_query(self::$_data);
            
            self::$_data = null;
        }
        
        if($debug){
			echo"\nURL:\n";
			echo self::$_url;
			echo "\n";
		}
        
        curl_setopt($curl, CURLOPT_URL, self::$_url);
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        if(self::$_timeout > 0){
            curl_setopt($curl, CURLOPT_TIMEOUT, self::$_timeout);
        }
        
        if(self::$_connecttimeout > 0){
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, self::$_connecttimeout);
        }
        
        if($debug){
			echo"\nSEND:\n";
			print_r(self::$_data);
			echo "\n";
		}
        
        if(self::$_file){
			self::$_post = false;
			
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($curl, CURLOPT_POST, true);
			
			curl_setopt($curl, CURLOPT_POSTFIELDS, [
				'file' => curl_file_create(self::$_file)
			]);
			
			//curl_setopt($curl, CURLOPT_SAFE_UPLOAD, false);
            
            self::$_file = null;
		}
        
        if(self::$_post){
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POST, true);
            
            if(self::$_rowdata){
				if($debug){
					echo"\nROW:\n";
					print_r(self::$_rowdata);
					echo"\n";
				}
				
				curl_setopt($curl, CURLOPT_POSTFIELDS, self::$_rowdata);
				
				self::$_rowdata = null;
			}else{
				curl_setopt($curl, CURLOPT_POSTFIELDS, ((self::$_data_json) ? json_encode(self::$_data) : http_build_query(self::$_data)));
				
				self::$_data = null;
			}
        }
        
        if(self::$_auth_data){
			if($debug){
				echo"\nAUTH: ".self::$_auth_data."\n";
			}
			
            curl_setopt($curl, CURLOPT_USERPWD, self::$_auth_data);
            
            self::$_auth_data = '';
		}
		
		if(self::$_gzip){
			curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
			
			self::$_headers[] = 'accept-encoding:gzip';
			
			self::$_gzip = false;
		}
        
        if(self::$_headers){
			curl_setopt($curl, CURLOPT_HTTPHEADER, self::$_headers);
			
			self::$_headers = [];
		}
		
		if(self::$_new_session){
			curl_setopt($curl, CURLOPT_COOKIESESSION, true);
			
			self::$_new_session = false;
		}
		
		if(self::$_cookie_file){
			curl_setopt($curl, CURLOPT_COOKIEFILE, self::$_cookie_file);
			curl_setopt($curl, CURLOPT_COOKIEJAR, self::$_cookie_file);
			
			self::$_cookie_file = '';
		}
        
        curl_setopt($curl, CURLOPT_HEADER, false);
        
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        
        $out	= curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
        $code	= curl_getinfo($curl, CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
        
        curl_close($curl); #Завершаем сеанс cURL
        
        if($debug){
			echo"\nRESPONSE:\n";
			print_r($out);
			echo"\n";
			
			echo"\nCODE: ".$code."\n";
			
		//	print_r(json_decode($out, true));
		//	exit;
		}
        
        if($code != 200 && $code != 204){
            if($out == null){
                return false;
            }
            
            if(self::$_json){
                return json_decode($out, true);
            }
            
            return false;
        }
        
        if($out == null){
            return false;
        }
        
        if(self::$_json){
			self::$_json = false;
			
            return json_decode($out, true);
        }
        
        return $out;
    }
}
