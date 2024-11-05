<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\Pckgeneric\myJQTabs.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\Pckgeneric;



use Gimi\myFormsTools\PckmyJQuery\myJQueryUI;
                                                
class myJQTabs extends myJQueryUI {


 
/**
     * @ignore
     */function set_common_defaults(array $commons){}
 
/**
     * @ignore
     */function get_common_defaults(){ return array();}
 
/**
     * @ignore
     */function set_istance_defaults(){}
 


/**
 * @ignore 
 */
 public static function init(&$widget) {
		$widget='tabs';
		
//self::add_src(self::$percorsoMyForm."jquery/ui/jquery.ui.tabs.js");
	}




/**
 * @ignore 
 */	static function id_titolo($titolo){
		return "mt_".str_replace('=','_',base64_encode($titolo));
	}


/**
 * @ignore 
 */	protected function create_tab($titolo) {
		$i=self::id_titolo($titolo);
		return "<li><a href=\"#$i\">$titolo</a></li>";
	}


/**
 * @ignore 
 */	protected function create_panel($titolo,&$v) {
		$i=self::id_titolo($titolo);
		return "<div id='$i'><a name='$i'></a>{$v}</div>";
	}



/**
 * @ignore 
 */	function get_html(){
		if($this->items)
		foreach ($this->items as $titolo=>$v)
					{$html.=$this->create_panel($titolo,$v);
					 $title.=$this->create_tab($titolo);
					}
		return myJQueryUI::get_html().
				"<div id='".str_replace('#','',$this->get_id())."'>
				<ul>$title</ul>
				$html
				</div>"
				;
	}


/**
 * Aggiunge un elemento da visualizzare
 * @param string $titolo
 * @param string $html
 */
	 public function add_item($titolo,$html){
		$this->items["$titolo"]=$html;
		return $this;
	}


/**
 * Aggiunge piu' elementi contemporaneamente.
 * @param array $items array associativo del tipo titolo=>html
 */
	 public function add_items(array $items){
		foreach ($items as $k=>$v) $this->add_item($k, $v);
		return $this;
	}
}