<?php

namespace App\Helpers;

use App\Helpers\Helper;
use Intervention\Image\ImageManager;

class ImageHelper extends Helper {
    
    private static $_instance = null;
    
    private static $_manager = null;
    
    static public function getInstance(){
        if(is_null(self::$_instance)){
            self::$_instance = new self();
            
            //self::$_manager = new ImageManager(array('driver' => 'imagick'));
        }
        
        return self::$_instance;
    }
    
    static function auto($fileInput, $portrait, $album){
		$hash = md5(json_encode(array_merge($portrait, $album)));
		
		if(is_null(self::$_manager)){
            self::$_manager = new ImageManager(array('driver' => 'gd'));
        }
        
        $fileInput = trim($fileInput, '/');
        
        if(!$fileInput){
            return $cap;
        }
        
        if(!file_exists(ROOT.'/'.$fileInput)){
            return $cap;
        }
        
        $pathinfo = pathinfo($fileInput);
        
        $file_path = ROOT.'/'.$fileInput;
        
        $size = getimagesize($file_path);
        
        $orig_width		= $size[0];
        $orig_height	= $size[1];
        $mime			= explode('/', $size['mime'])[1];
        
        if($orig_width == $portrait[0] && $orig_height == $portrait[1]){
            return '/'.$fileInput;
        }
        
        if($orig_width == $album[0] && $orig_height == $album[1]){
            return '/'.$fileInput;
        }
        
        $dir = $pathinfo["dirname"].'/'.$hash.'/';
        
        if(!is_dir(ROOT.'/'.$dir)){
            if(!mkdir(ROOT.'/'.$dir)){
                return '/'.$fileInput;
            }
        }
        
        $namefile = md5($pathinfo["filename"]).'.'.$pathinfo["extension"];
        
        if(!file_exists(ROOT.'/'.$dir.$namefile)){
			// width > height: альбомная
			// width < height: портретная
			// width = height: квадрат малевича
			
			if($orig_width > $orig_height){
				$orientatin = 1;
				
				$new_width	= $album[0];
				$new_height	= $album[1];
			}elseif($orig_width < $orig_height){
				$orientatin = 2;
				
				$new_width	= $portrait[0];
				$new_height	= $portrait[1];
			}else{
				$orientatin = 3;
				
				$new_width	= $portrait[0];
				$new_height	= $portrait[0];
			}
			
			if($orientatin > 2){
				// open an image file
                $img = self::$_manager->make($file_path);
                
                // crop image instance
                $img->crop($new_width, $new_height, $x, $y);
                
                // save image in desired format
                $img->save(ROOT.'/'.$dir.$namefile);
			}else{
				$width = $orig_width;
                $height = $orig_height;
                
                # taller
                if ($height > $new_height) {
                    $width = ($new_height / $height) * $width;
                    $height = $new_height;
                }
                
                # wider
                if ($width > $new_width) {
                    $height = ($new_width / $width) * $height;
                    $width = $new_width;
                }
                
                if($mime == 'jpeg'){
                    $image_p = \imagecreatetruecolor($new_width, $new_height);
                    
                    // установка фона
                    $black = \imagecolorallocate($image_p, 255, 255, 255);
                    \imagefill($image_p, 0, 0, $black);
                    
                    $image = \imagecreatefromjpeg($file_path);
                    
                    $dst_x = 0;
                    $dst_y = 0;
                    
                    if($new_width > $width){
                        $dst_x = ($new_width - $width) / 2;
                    }
                    
                    if($new_height > $height){
                        $dst_y = ($new_height - $height) / 2;
                    }
                    
                    \imagecopyresampled($image_p, $image, $dst_x, $dst_y, 0, 0, $width, $height, $orig_width, $orig_height);
                    \imagedestroy($image);
                    
                    //imagecopyresized($image_p, $image, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);
                    
                    \imagejpeg($image_p, ROOT.'/'.$dir.$namefile, 100);
                    \imagedestroy($image_p);
                }elseif($mime == 'png'){
                    $image_p = \imagecreatetruecolor($new_width, $new_height);
                    
                    // установка фона
                    $black = \imagecolorallocate($image_p, 255, 255, 255);
                    \imagefill($image_p, 0, 0, $black);
                    
                    $image = \imagecreatefrompng($file_path);
                    
                    //ImageAlphaBlending($img, true);
                    //ImageSaveAlpha($img, true);
                    
                    $dst_x = 0;
                    $dst_y = 0;
                    
                    if($new_width > $width){
                        $dst_x = ($new_width - $width) / 2;
                    }
                    
                    if($new_height > $height){
                        $dst_y = ($new_height - $height) / 2;
                    }
                    
                    \imagecopyresampled($image_p, $image, $dst_x, $dst_y, 0, 0, $width, $height, $orig_width, $orig_height);
                    \imagedestroy($image);
                    
                    \imagejpeg($image_p, ROOT.'/'.$dir.$namefile, 100);
                    \imagedestroy($image_p);
                }else{
                    return '/'.$fileInput;
                }
			}
		}
        
        return '/'.$dir.$namefile;
	}
    
    static function thumb($fileInput, $new_width = 100, $new_height = 100, $type = 'resize', $x = 0, $y = 0, $cap = '/img/logo-cap.png'){
        if(is_null(self::$_manager)){
            self::$_manager = new ImageManager(array('driver' => 'gd'));
        }
        
        $fileInput = trim($fileInput, '/');
        
        if(!$fileInput){
            return $cap;
        }
        
        if(!file_exists(ROOT.'/'.$fileInput)){
            return $cap;
        }
        
        $pathinfo = pathinfo($fileInput);
        
        $file_path = ROOT.'/'.$fileInput;
        
        $size = getimagesize($file_path);
        
        $orig_width = $size[0];
        $orig_height = $size[1];
        $mime = explode('/', $size['mime'])[1];
        
        if($orig_width == $new_width && $orig_height == $new_height){
            return '/'.$fileInput;
        }
        
        $dir = $pathinfo["dirname"].'/'.$new_width.'-'.$new_height.'-'.$type.'/';
        
        if(!is_dir(ROOT.'/'.$dir)){
            if(!mkdir(ROOT.'/'.$dir)){
                return '/'.$fileInput;
            }
        }
        
        $namefile = md5($pathinfo["filename"]).'.'.$pathinfo["extension"];
        
        if(!file_exists(ROOT.'/'.$dir.$namefile)){
            if($type == 'crop'){
                // open an image file
                $img = self::$_manager->make($file_path);
                
                // crop image instance
                $img->crop($new_width, $new_height, $x, $y);
                
                // save image in desired format
                $img->save(ROOT.'/'.$dir.$namefile);
            }elseif($type == 'pad'){
                $width = $orig_width;
                $height = $orig_height;
                
                # taller
                if ($height > $new_height) {
                    $width = ($new_height / $height) * $width;
                    $height = $new_height;
                }
                
                # wider
                if ($width > $new_width) {
                    $height = ($new_width / $width) * $height;
                    $width = $new_width;
                }
                
                if($mime == 'jpeg'){
                    $image_p = \imagecreatetruecolor($new_width, $new_height);
                    
                    // установка фона
                    $black = \imagecolorallocate($image_p, 0, 0, 0);
                    \imagefill($image_p, 0, 0, $black);
                    
                    $image = \imagecreatefromjpeg($file_path);
                    
                    $dst_x = 0;
                    $dst_y = 0;
                    
                    if($new_width > $width){
                        $dst_x = ($new_width - $width) / 2;
                    }
                    
                    if($new_height > $height){
                        $dst_y = ($new_height - $height) / 2;
                    }
                    
                    \imagecopyresampled($image_p, $image, $dst_x, $dst_y, 0, 0, $width, $height, $orig_width, $orig_height);
                    \imagedestroy($image);
                    
                    //imagecopyresized($image_p, $image, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);
                    
                    \imagejpeg($image_p, ROOT.'/'.$dir.$namefile, 100);
                    \imagedestroy($image_p);
                }elseif($mime == 'png'){
                    $image_p = \imagecreatetruecolor($new_width, $new_height);
                    
                    // установка фона
                    $black = \imagecolorallocate($image_p, 0, 0, 0);
                    \imagefill($image_p, 0, 0, $black);
                    
                    $image = \imagecreatefrompng($file_path);
                    
                    //ImageAlphaBlending($img, true);
                    //ImageSaveAlpha($img, true);
                    
                    $dst_x = 0;
                    $dst_y = 0;
                    
                    if($new_width > $width){
                        $dst_x = ($new_width - $width) / 2;
                    }
                    
                    if($new_height > $height){
                        $dst_y = ($new_height - $height) / 2;
                    }
                    
                    \imagecopyresampled($image_p, $image, $dst_x, $dst_y, 0, 0, $width, $height, $orig_width, $orig_height);
                    \imagedestroy($image);
                    
                    \imagejpeg($image_p, ROOT.'/'.$dir.$namefile, 100);
                    \imagedestroy($image_p);
                }else{
                    return '/'.$fileInput;
                }
                
                //$img->resize(ceil($width), $height);
            }else{
                // open an image file
                $img = self::$_manager->make($file_path);
                
                // resize image instance
                $img->resize($new_width, $new_height);
                
                // save image in desired format
                $img->save(ROOT.'/'.$dir.$namefile);
            }
            
            // insert a watermark
            //$img->insert('public/watermark.png');
        }
        
        return '/'.$dir.$namefile;
    }
}
