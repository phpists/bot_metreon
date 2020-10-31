<?php

namespace App\Helpers;

class MyPaginator {
    
    protected $_total_rows = 0;
    protected $_per_page = 0;
    protected $_cur_page = 0;
    
    protected $_count_show_pages = 4;
    
    protected $_path = '/';
    
    protected $_qs = true;
    
    protected $_query = array();
    
    public function __construct($count, $limit, $curPage){
        $this->_total_rows = $count;
        $this->_per_page = $limit;
        $this->_cur_page = $curPage;
    }
    
    public function setCountShowPages($count = 4){
        $this->_count_show_pages = $count;
        
        return $this;
    }
    
    public function queryStringResult($qs = true){
        $this->_qs = $qs;
        
        return $this;
    }
    
    public function setPath($path = '/'){
        $this->_path = $path;
        
        return $this;
    }
    
    public function setQuery($data){
        if(isset($data['page'])){
            unset($data['page']);
        }
        
        $this->_query = http_build_query($data);
        
        return $this;
    }
    
    public function getCurrent(){
        return (int)$this->_cur_page;
    }
    
    protected $_first_num = 0;
    
    public function getFirst(){
        return $this->_first_num;
    }
    
    protected $_num_pages = 0;
    
    public function calcNumPages(){
        $this->_num_pages = ceil($this->_total_rows / $this->_per_page);
        
        return $this;
    }
    
    public function getNumPages(){
        return $this->_num_pages < 1 ? 1 : $this->_num_pages;
    }
    
    public function build(){
        $this->calcNumPages();
        
        if($this->_num_pages <= 1){
            return;
        }
        
        if($this->_cur_page > $this->_num_pages){
            $this->_cur_page = $this->_num_pages;
        }
        
        $start = (($this->_cur_page - $this->_count_show_pages) > 0) ? $this->_cur_page - ($this->_num_pages - 1) : 1;
		$end = (($this->_cur_page + $this->_count_show_pages) < $this->_num_pages) ? $this->_cur_page + $this->_num_pages : $this->_num_pages;
        
        $this->_path = '/'.trim($this->_path, '/');
        
        $output = array(
            'first' => '',
            'prev'  => '',
            'links' => array(),
            'next'  => '',
            'last'  => '',
        );
        
        //if($this->_num_pages > $this->_cur_page && $this->_cur_page > $this->_count_show_pages){
        if($this->_num_pages > $this->_cur_page && $this->_cur_page > 1){
            $output['first'] = $this->_path;
            
            if($this->_query){
                $output['first'] .= '?'.$this->_query;
            }
        }
        
        if($this->_cur_page > 1){
            if(!$this->_qs){
                if(($this->_cur_page - 1) > 1){
                    $output['prev'] = $this->_path.'/page-'.($this->_cur_page - 1);
                    
                    if($this->_query){
                        $output['prev'] .= '?'.$this->_query;
                    }
                }else{
                    $output['prev'] = $this->_path;
                    
                    if($this->_query){
                        $output['prev'] .= '?'.$this->_query;
                    }
                }
            }else{
                if(($this->_cur_page - 1) > 1){
                    $output['prev'] = $this->_path.'?page='.($this->_cur_page - 1);
                    
                    if($this->_query){
                        $output['prev'] .= '&'.$this->_query;
                    }
                }else{
                    $output['prev'] = $this->_path;
                    
                    if($this->_query){
                        $output['prev'] .= '?'.$this->_query;
                    }
                }
            }
        }
        
        if($this->_cur_page < $this->_num_pages){
            if(!$this->_qs){
                $output['next'] = $this->_path.'/page-'.($this->_cur_page + 1);
                
                if($this->_query){
                    $output['next'] .= '?'.$this->_query;
                }
            }else{
                $output['next'] = $this->_path.'?page='.($this->_cur_page + 1);
                
                if($this->_query){
                    $output['next'] .= '&'.$this->_query;
                }
            }
        }
        
        $count_prev = 0;
        
        if($this->_cur_page > 3){
			$count_prev = $this->_cur_page - 2;
		}elseif($this->_cur_page > 2){
			$count_prev = $this->_cur_page - 1;
		}
		
		if($count_prev > 0){
			for($count_prev; $count_prev <= $this->_cur_page; $count_prev++){
				if(!$this->_qs){
					$output['links'][$count_prev] = $this->_path.'/page-'.$count_prev;
					
					if($this->_query){
						$output['links'][$count_prev] .= '?'.$this->_query;
					}
				}else{
					$output['links'][$count_prev] = $this->_path.'?page='.$count_prev;
					
					if($this->_query){
						$output['links'][$count_prev] .= '&'.$this->_query;
					}
				}
			}
		}
        
        $i = 0;
        
        for($p = $this->_cur_page; $p <= $this->_num_pages; $p++){
            if($i == $this->_count_show_pages){
                break;
            }
            
            if($p > 1){
                if(!$this->_qs){
                    $output['links'][$p] = $this->_path.'/page-'.$p;
                    
                    if($this->_query){
                        $output['links'][$p] .= '?'.$this->_query;
                    }
                }else{
                    $output['links'][$p] = $this->_path.'?page='.$p;
                    
                    if($this->_query){
                        $output['links'][$p] .= '&'.$this->_query;
                    }
                }
            }else{
                $output['links'][$p] = $this->_path;
                
                if($this->_query){
                    $output['links'][$p] .= '?'.$this->_query;
                }
            }
            
            $i++;
        }
        
        if($this->_num_pages > $this->_count_show_pages && $this->_num_pages != $this->_cur_page && !isset($output['links'][$this->_num_pages])){
            if(!$this->_qs){
                $output['last'] = $this->_path.'/page-'.$this->_num_pages;
                
                if($this->_query){
                    $output['last'] .= '?'.$this->_query;
                }
            }else{
                $output['last'] = $this->_path.'?page='.$this->_num_pages;
                
                if($this->_query){
                    $output['last'] .= '&'.$this->_query;
                }
            }
        }
        
        if(!$output['links']){
            return;
        }
        
        //var_dump($output);exit;
        
        return $output;
    }
    
    public function render(){
        $result = $this->build();
        
        if($result){
			$current = $this->getCurrent();
			
            $html = '<div class="pagination"><ul>';
            
            if($result['prev'] && $current > 1){
                //$html .= '<li data-n="1"><a href="'.$result["prev"].'" rel="prev">Назад</a></li>';
            }else{
                //$html .= '<li><span>Назад</span></li>';
            }
            
            if($result['first']){
                $html .= '<li data-n="1"><a href="'.$result["first"].'">1</a></li>';
                
                if($current > $this->_count_show_pages){
					$html .= '<li>...</li>';
				}
            }
            
            foreach($result['links'] as $p => $item){
                if($current != $p){
                    $html .= '<li data-n="'.$p.'"><a href="'.$item.'">'.$p.'</a></li>';
                }else{
                    $html .= '<li data-n="'.$p.'"><span>'.$p.'</span></li>';
                }
            }
            
            if($result['last']){
                $html .= '<li>...</li>';
                $html .= '<li data-n="'.$this->getNumPages().'"><a href="'.$result["last"].'">'.$this->getNumPages().'</a></li>';
            }
            
            if($result['next']){
                //$html .= '<li data-n="'.($current + 1).'"><a href="'.$result["next"].'" rel="next">Вперед</a></li>';
            }
            
            $html .= '</ul></div>';
            
            return $html;
        }
    }
}
