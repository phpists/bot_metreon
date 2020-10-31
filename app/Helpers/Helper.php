<?php

namespace App\Helpers;

class Helper {
    
    function call($method, $params = null){
        if(method_exists($this, $method)){
            if($params){
                if(!is_array($params)){
                    return $this->{$method}($params);
                }else{
                    return $this->{$method}(...$params);
                }
            }
        }
        
        return false;
    }
    
}