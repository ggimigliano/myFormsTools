<?php
namespace Gimi\myFormsTools\PckmyTables;

use Gimi\myFormsTools\PckmyFields\mySelect;
use Gimi\myFormsTools\myDizionario;
use Gimi\myFormsTools\PckmyFields\myLink;
use Gimi\myFormsTools\PckmyFields\myHidden;

class myTablePager
{protected $format,$dizionario,$reload,$corrente,$prossima_pagina,$altre,$rxpag,$rxpager,$altre_pagine;
 protected $actions=array();
    public function __construct($class,$reload,$corrente,$rxpag, $prossima_pagina,$altre_pagine)
        {
         $this->reload=$reload;
         $this->corrente=$corrente;
         $this->prossima_pagina=$prossima_pagina;
         $this->altre_pagine=$altre_pagine;
         $this->rxpag=$rxpag;
         $this->set_opzioni_rigexpag(array(10,25,50,100),$class);
         $this->set_dizionario(new myDizionario('IT'));
         $this->set_format();
         $this->modifica_pagina_corrente(function($x){return "<span style='font-weight:bold'>{$x}</span>";});
        }
        
     public function isLastPage(){
         return !$this->altre_pagine && !$this->prossima_pagina;
        }
     
    public function set_dizionario(myDizionario $diz){
         $this->dizionario=$diz;
         return $this;
        }
        
    public function getHTMLNavigator() {
    	$pages=array();
        if($this->corrente==1  && !$this->prossima_pagina) return '';
        if($this->corrente==1) $pages=array($this->corrente=>$this->corrente);
        
        if($this->corrente==2) $pages=array('|<'=>1,$this->corrente-1=>$this->corrente-1,$this->corrente=>$this->corrente);
             elseif($this->corrente>2) $pages=array('|<'=>1,'<<'=>$this->corrente-2,$this->corrente-1=>$this->corrente-1,$this->corrente=>$this->corrente);
        if($this->corrente>=1 && $this->prossima_pagina) $pages[$this->corrente+1]=$this->corrente+1;
        
        if($this->prossima_pagina && !($this->corrente && !$this->altre_pagine)) $pages['>>']=$this->corrente+2;
        if($this->altre_pagine) $pages['>|']=-1;
        
        $out='';
        foreach ($pages as $k=>$v) $out.=$this->build_link($v, $k).'&nbsp;&nbsp;';
        return $out; 
        }
        
    public function set_opzioni_rigexpag($opzioni,$class){
            $this->rxpager=(new mySelect('MyRxPage',$this->rxpag,array_combine($opzioni, $opzioni)))->set_reloadjs($this->reload);
            return $this;
        }
        
    /**
     * 
     * @param string $format "{navigator} mostra su {pager} righe per pagina"
     */    
    public function set_format($format="{navigator} {pager} righe per pagina") {
       $this->format=$format;
       return $this;
    }
    
    
    public function modifica_pagina_corrente($func){
            $this->actions['current']=$func;
            return $this;
    }
    
    public function modifica_pagina_ultima($func){
        $this->actions['>|']=$func;
        return $this;
    }
    
    public function modifica_pagina_prima($func){
        $this->actions['|<']=$func;
        return $this;
    }
    
    public function modifica_pagina_succ($func){
        $this->actions['>>']=$func;
        return $this;
    }
    
    public function modifica_pagina_prec($func){
        $this->actions['<<']=$func;
        return $this;
    }
    
    public function build_link($pag,$txt){
         $url='#';
         
         if($pag==$this->corrente) $txt=$this->actions['current']($txt);
                    elseif(isset($this->actions[$txt])) $txt=$this->actions[$txt]($txt);
         if($this->reload=='parametro' || $this->reload=='completo' || $this->reload=='azzera')
                            {$url=$_SERVER['REQUEST_URI'];
                             if($this->reload!='azzera' && false===strpos($url,'?'))    $url=strstr($url,'?',true); 
                             if(false!==strpos($url,'&MyPage='))                        $url=strstr($url,'&MyPage=',true);
                             if(false!==strpos($url,"&{$this->rxpager->get_name()}="))  $url=strstr($url,"&{$this->rxpager->get_name()}=",true);
                             if(false===strpos($url,'?')) $url.="?MyPage=$pag&{$this->rxpager->get_name()}={$this->rxpager->get_value()}#pager";
                                                    else  $url.="&MyPage=$pag&{$this->rxpager->get_name()}={$this->rxpager->get_value()}#pager";
                            }
                             
        $link=new myLink($url,$pag==-1?$this->dizionario->trasl("Vai all'ultima pagina"):$this->dizionario->trasl("Vai a pagina")." $pag",$txt);
        $link->set_attributo('data-page',$pag);
        if($this->reload=='submit')
                            {
                                $js="(function (me){var page = document.createElement('input');
                                                    page.type = 'hidden';
                                                    page.name = 'MyPage';
                                                    page.value= me.dataset.page;
                                                    me.closest('form').appendChild(page);
                                                    me.closest('form').submit();
                                                    return false;})(this)";
                                $link->set_js($js);
                                $link->set_js($js,'keypressed');
                            }
                                
        return $link;
    }
    
    public function __toString(){
        return ($this->reload=='submit' && isset($_POST['MyPage'])?new myHidden('MyPage',$_POST['MyPage']):'').
                    str_replace(array("{navigator}","{pager}", "righe per pagina"),
                          array($this->getHTMLNavigator(),$this->rxpager,$this->dizionario->trasl("righe per pagina")),
                          $this->format    
                         );
     }
}

?>