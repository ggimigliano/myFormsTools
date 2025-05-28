<?php
/**
 * Contains Gimi\myFormsTools\Pckgeneric\myJQDialog.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\Pckgeneric;



use Gimi\myFormsTools\PckmyJQuery\myJQuery;
use Gimi\myFormsTools\PckmyJQuery\myJQueryUI;
use Gimi\myFormsTools\PckmyFields\myField;
                                                
class myJQDialog extends myJQueryUI {
 
/**
  * @ignore
  */ 
 protected $icona,$html; 

  public function set_istance_defaults(){
 
 }
 
  public function set_common_defaults(array $commons){}

  public function get_common_defaults(){return array();}
 
 
  public function get_js_show(){
        return "{$this->myJQVarName()}.dialog('option','positon',{my:'center', at: 'center',of:window}).dialog('open')";
 }
 

/**
 * @ignore 
 */
  public function __set($var,$val){
		if($var=='height' && self::isMSIE()) $this->add_common_code(myJQuery::get_add_style(".ui-dialog-content {height:{$val}px !important}",true));
		return parent::__set($var,$val);
	}



/**
 * @ignore 
 */static protected function init( &$widget) {
		$widget='dialog';
	}
	
	
/**
	 * Aggiunge un icona
	 * @param help|important|info o altro percorso  $icona
	 */
	 public function set_icona($icona){
	 if(!strpos($icona, '.')) $icona=self::$percorsoMyForm."jquery/myJQUtils/images/$icona.gif";
	 $this->icona=$icona;
	 return $this;
	}



	
/**
	 * Permette di aggiungere html o testo
	 * @param html/string $testo
	 */
	public function set_html($html){
	 $this->html=(str_replace(array('\'',"\n","\r","\t",'/'),array('\\\'',' ',' ',' ','\/'),str_replace('\\','\\\\',$html)));
	 return $this;
	}
	
	

	
  public function prepara_codice(){
             if(!isset($this->modal)) $this->modal=true;
             if(!$this->autoOpen) $this->autoOpen=false;
             if(!$this->resizable) $this->resizable=false;
             if(!isset($this->closeOnEscape)) $this->closeOnEscape=true;
             
             if($this->html) {
                                 if($this->icona)  $this->html="<img src=\"{$this->icona}\" style=\"float:left;padding-right:8px\"/>".trim((string) $this->html).'<br style="clear:both" />';
                                 $id=substr($this->get_id(),1);
                                 if(strpos( $this->html,'<div')!==false ||
                                     strpos( $this->html,'<p')!==false ||
                                     strpos( $this->html,'<table')!==false) $tag='div';
                                     else $tag='span';
                                     $this->add_code(self::$identificatore."('body').append('<$tag id=\"{$id}\" style=\"display:none\">$this->html</$tag>');",-1);
                             }
             if($this->modal) {if(!$this->open) $this->open='function(){}';
                             $this->open="function (type, data) { {$this->JQvar()}('.ui-dialog').css('z-index', ".(static::$max_z_index+1).");  {$this->JQvar()}('.ui-widget-overlay').css('z-index', ".(static::$max_z_index).");  ({$this->open}) (type, data); }";
                             }
 
 parent::prepara_codice();
 }
}