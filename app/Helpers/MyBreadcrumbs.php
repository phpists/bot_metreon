<?php

namespace App\Helpers;

class MyBreadcrumbs {
    
    protected static $_links = array();
    
    public function __construct(){}
    
    public static function push($uri, $title){
        $uri = trim($uri, '/');
        self::$_links[$uri] = $title;
    }
    
    public static function clear(){
        self::$_links = array();
    }
    
    public static function render(){
		$html = '<nav class="nav-page" aria-label="breadcrumb" itemscope="" itemtype="http://schema.org/BreadcrumbList">';
			$html .= '<ul>';
			
			if(self::$_links){
				$i = 1;
				
				$count = count(self::$_links);
				
				if($count > 1){
					foreach(self::$_links as $uri => $title){
						if($i > 1){
							if($i != $count){
								$html .= '<li itemscope="" itemprop="itemListElement" itemtype="http://schema.org/ListItem">';
									$html .= '<a href="'.url("/".$uri).'" itemprop="item">';
										$html .= '<span itemprop="name">'.$title.'</span>';
									$html .= '</a>';
									$html .= '<meta itemprop="position" content="'.$i.'">';
								$html .= '</li>';
							}else{
								$html .= '<li aria-current="page">';
									$html .= '<div>';
										$html .= '<span>'.$title.'</span>';
									$html .= '</div>';
								$html .= '</li>';
							}
						}else{
							$html .= '<li itemscope="" itemprop="itemListElement" itemtype="http://schema.org/ListItem">';
								$html .= '<a href="'.url("/".$uri).'" itemprop="item">';
									$html .= '<span itemprop="name">'.$title.'</span>';
								$html .= '</a>';
								$html .= '<meta itemprop="position" content="'.$i.'">';
							$html .= '</li>';
						}
						
						$i++;
					}
				}else{
					$vals = array_values(self::$_links);
					
					$html .= '<li aria-current="page">';
						$html .= '<div>';
							$html .= '<span>'.$vals[0].'</span>';
						$html .= '</div>';
					$html .= '</li>';
				}
			}
			
			$html .= '</ul>';
        $html .= '</nav>';
        
        self::$_links = array();
        
        return $html;
    }
}
