<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\myJQueryMyField.
 */

namespace Gimi\myFormsTools\PckmyJQuery;


 

/**
 *
 * Classe di utilità per produrre codice facilmente utilizzabile con il metodo {@link myField::add_myJQuery()}
 */
	
abstract class myJQueryMyField extends myJQueryUI{
    /**
     * @ignore
     */
    protected $myField,$items;
    
    /**
     * @ignore
     */
    static protected function init( &$widget) {$widget='myJQueryMyField';}
    /**
     * @ignore
     */function set_istance_defaults() {}
    /**
     * @ignore
     */ function get_common_defaults() {return array();}
    /**
     * @ignore
     */ function set_common_defaults(array $commons) {}
    
	/** @ignore */
	  public function application(&$myField) {//NO FINAL
	        
    		$this->myField=&$myField;
    		$class=get_class($this);
    		$class::init($this->widget);
    		$this->set_id("#".$myField->get_id());
    		if(!$this->widget) throw new \Exception("Valorizzare il parametro di $class::init(&\$widget) con il nome della classe jQuery es datepicker, autocomplete button");
    		if(!isset(self::$css_fonts[get_called_class()]) && $this->css_font_default) $this->set_css_font($this->css_font_default);
	   }

	   
	  

	/** @ignore */
	 public function get_id($punto=true){
	    if(isset($this->myField) && method_exists($this->myField, 'get_id')) return '#'.$this->myField->get_id();
	               else return parent::get_id($punto);
	}



}