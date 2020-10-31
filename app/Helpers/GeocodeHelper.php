<?php

namespace App\Helpers;

use App\Helpers\Helper;

class GeocodeHelper extends Helper{
    
    public $request_url         = "https://maps.google.com/maps/api/geocode/json?";
    
    public $sensor          	= "false";
    public $language            = "en";
    
    public $status          	= false;
    public $response            = false;
    public $country         	= false;
    public $country_short       = false;
    public $country_short_small	= true;
    public $region         		= false;
    public $region_short        = false;
    public $region_preference 	= null;
    public $city           		= false;
    public $area            	= false;
    public $address        		= false;
    public $street          	= false;
    public $house           	= false;
    public $zipcode         	= false;
    public $lat             	= false;
    public $lng             	= false;
    public $location_type       = false;
    
    public $_poxy = null;
    public $_key = null;
    
    public function __construct(){
        
    }
    
    public function setProxy($proxy = null){
        if($proxy != null){
            $this->_poxy = $proxy;
        }
    }
    
    public function setKey($key = null){
        if($key != null){
            $this->_key = $key;
        }
    }
    
    public function query($address = null, $coordinates = null){
        if(!function_exists('curl_init')){
            return false;
        }
        
        if($address != null){
            $query = 'address='.urlencode(stripslashes($address)).'&language='.$this->language.'&sensor='.$this->sensor;
        }elseif($coordinates != null){
            $query = 'latlng='.$coordinates.'&language='.$this->language.'&sensor='.$this->sensor;
        }else{
            return;
        }
        
        if($this->_key != null){
            $query .= '&key='.$this->_key;
        }
        
        if($this->region_preference != null){
            $this->region_preference = strtolower($this->region_preference);
            
            $query .= '&region='.$this->region_preference;
        }
        
        $req = curl_init($this->request_url.$query);
        
        curl_setopt($req, CURLOPT_URL, $this->request_url.$query);
        curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($req, CURLOPT_TIMEOUT, 3);
        //curl_setopt($req, CURLOPT_CONNECTTIMEOUT, 2);
        
        if($this->_poxy != null){
            curl_setopt($req, CURLOPT_PROXY, $this->_poxy);
        }
        
        $this->response = curl_exec($req);
        
        $this->response = json_decode($this->response);
        
        curl_close($req);
        
        //$this->status = $this->response->results['status'];
        
        if(!empty($this->response)){
            if(array_key_exists('status', $this->response) && $this->response->status == 'OK'){
                $short = '';
                
                if($this->region_preference != null){
                    $short = $this->get_component('country', 'short_name');
                    
                    if($short){
                        if($this->country_short_small){
                            $short = strtolower($short);
                            
                            if($short != $this->region_preference){
                                return false;
                            }
                        }else{
                            $tmp_short = strtolower($short);
                            
                            if($tmp_short != $this->region_preference){
                                return false;
                            }
                        }
                    }
                }
                
                $region = '';
                if($this->region){
                    $region = $this->get_component('administrative_area_level_1');
                }
                
                $city = '';
                if($this->city){
                    $city = $this->get_component('locality');
                    
                    if(!$city){
                        $city = $this->get_component('administrative_area_level_3');
                    }
                    
                    if(!$city){
                        $city = $this->get_component('postal_town');
                    }
                }
                
                $area = '';
                if($this->area){
                        $area = $this->get_component('sublocality_level_1');
                }
                
                $street = '';
                if($this->street){
                        $street = $this->get_component('route');
                }
                
                $house = '';
                if($this->house){
                        $house = $this->get_component('premise');
                }
                
                $country = '';
                if($this->country){
                    $country = $this->get_component('country');
                }
                
                if($this->country_short && $short == null){
                    $short = $this->get_component('country', 'short_name');
                    
                    if($short && $this->country_short_small){
                        $short = strtolower($short);
                    }
                }
                
                $zip = '';
                if($this->zipcode){
                    $zip = $this->get_component('postal_code');
                }
                
                return (object)array(
                    'address'       => $this->response->results[0]->formatted_address,
                    'country'       => $country,
                    'country_short' => $short,
                    'region'        => $region,
                    'city'      => $city,
                    'area'      => $area,
                    'street'        => $street,
                    'house'     => $house,
                    'zip'       => $zip,
                    'lat'       => $this->response->results[0]->geometry->location->lat,
                    'lng'       => $this->response->results[0]->geometry->location->lng,
                    'place_id'      => $this->response->results[0]->place_id
                );
            }
        }
        
        return false;
    }
    
    function get_component($type, $key = 'long_name'){
        foreach($this->response->results[0]->address_components as $k => $found){
            if(in_array($type, $found->types)){
                return $found->{$key};
            }
        }
        
        return false;
    }
    
    function place_info($place_id){
        $url = 'https://maps.googleapis.com/maps/api/place/details/json';
        
        $url .= '?placeid='.$place_id;
        
        if($this->_key != null){
            $url .= '&key='.$this->_key;
        }
        
        $req = curl_init($url);
        
        curl_setopt($req, CURLOPT_URL, $url);
        curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($req, CURLOPT_TIMEOUT, 3);
        curl_setopt($req, CURLOPT_CONNECTTIMEOUT, 2);
        
        if($this->_poxy != null){
            curl_setopt($req, CURLOPT_PROXY, $this->_poxy);
        }
        
        $this->response = curl_exec($req);
        
        echo $this->response;
        echo "\n";
        
        $this->response = json_decode($this->response);
    
        print_r($this->response);
        echo "\n";
        
        curl_close($req);
        
        if(!empty($this->response)){
            if(array_key_exists('status', $this->response) && $this->response->status == 'OK'){
                return $this->response;
            }
        }
        
        return false;
    }
}
