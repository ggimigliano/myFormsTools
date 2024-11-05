<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\Pckgeneric\myJQPanelOver.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\Pckgeneric;



use Gimi\myFormsTools\PckmyJQuery\myJQuery;
                                                
class myJQPanelOver extends myJQuery {

/**
 * @ignore 
 */protected $cover_id='myJQPanelOver',$cover_color='gray',$cover_html='';

/**
 * @ignore 
 */protected static $used_ids=array();


/**
 * Imposta la coperture
 * @param string $id dell'elemento coprente
 * @param string $html html dell'elemento coprente se non e' già  presente nella pagina
 * @param string $color colore dello sfondo
 */
	 public function set_cover($id='',$html='',$color='gray') {
		$this->cover_id=($id?$id:"#_".spl_object_hash($this));
		$this->cover_html=$html;
		$this->cover_color=$color;
		return $this;
	}

	
/**
	 * Restituisce un html per l'elemento coprente standard buono per un'altezza minima di 60px
	 * @return string
	 */
	 public function get_generic_panel(){
		return '<img src="'.self::$percorsoMyForm.'/icone/loader_blu.gif"  style="margin-left:45%;margin-right:45%;width:10%" >
				';
	}

	
/**
	 * Restituisce una map JS con i show() e hide() per l'elemento coprente
	 * @return string
	 */
	 public function get_code($ns=''){
		if($this->cover_html && !self::$used_ids[$this->cover_id])
				{ self::$used_ids[$this->cover_id]=true;
				 $id=preg_replace('/^#/','',$this->cover_id);
				 $html=addslashes("<div id='{$id}_inside'>".str_replace(array("\r","\n","\t"),array(' ',' ',' '),$this->cover_html)."</div><div id='{$id}' style='background:{$this->cover_color};margin:0;padding;0;border:0;display:none;-ms-filter:\"progid:DXImageTransform.Microsoft.Alpha(opacity=5)\";-moz-opacity: 0.05;opacity: 0.05'></div>");
				 $html="{$this->JQvar()}('body').append('$html');
				         ";
				}

		return "{show:function()
				 		{ var self=this;
				 		if(this.__handler===null)
				 			{{$html}
				 			 this.__handler=function(){
                                						 var tab={$this->JQid()};
                                						 {$this->JQvar()}('{$this->cover_id}').css({'left':tab.offset().left,'top':tab.offset().top,'width':tab.width(),'height':tab.height(),'position':'absolute','z-index':".(static::$max_z_index).",display:'block'}) ;
                                					     
                                						 var rel=$(window).scrollTop()+tab.offset().top+($(window).height()-tab.offset().top)/2-{$this->JQvar()}('{$this->cover_id}_inside').height()/2;
                                						 rel=Math.min(Math.max(tab.offset().top,rel),tab.offset().top+tab.height()-{$this->JQvar()}('{$this->cover_id}_inside').height());
                        						         {$this->JQvar()}('{$this->cover_id}_inside').css({'left':tab.offset().left,'top':rel,'width':tab.width(),'position':'absolute','z-index':".(static::$max_z_index+1).",display:'block'}) ;
                        						           
                                						}
                            
				 			 {$this->JQvar()}(document).ready(function(){self.__handler();});
				 			 {$this->JQvar()}(window).bind('resize',self.__handler);
						     {$this->JQvar()}(window).bind('scroll',self.__handler);
							}
						},
				 hide:function(){
				 		 {$this->JQvar()}(window).unbind('resize',this.__handler);
				 		 {$this->JQvar()}(window).unbind('scroll',this.__handler);
				 		 {$this->JQvar()}('{$this->cover_id}').remove();
				 		 {$this->JQvar()}('{$this->cover_id}_inside').remove();
						},
				__handler:null
				}
			   ";
	}



}