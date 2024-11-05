<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\Pckgeneric\myJQAccordion.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\Pckgeneric;



use Gimi\myFormsTools\PckmyJQuery\myJQueryUI;
                                                
class myJQAccordion extends myJQTabs {

/**
 * @ignore 
 */
protected $items,$css_font_class=array('.ui-accordion'),$css_font_default='{font-size:12px}';

/**
 * @ignore
 */
public $autoHeight; 
//introdotta in modo da essere ignorata

 
/**
 * @ignore 
 */ static function init(&$widget) {
		$widget='accordion';
		
//self::add_src(self::$percorsoMyForm."jquery/ui/jquery.ui.accordion.js");
	}


/**
 * @ignore 
 */	protected function create_tab($titolo) {
		return "<h3><a href=\"#\">$titolo</a></h3>";
	}

	
	 public function set_istance_defaults() {
	 $this->heightStyle='content';
	}
	

/**
 * @ignore 
 */	protected function create_panel($titolo,&$v) {
		return "<div>{$v}</div>";
	}



/**
 * @ignore 
 */function get_html(){
		if($this->items)
		 foreach ($this->items as $titolo=>$v)
					{$html.=$this->create_tab($titolo)."\n".
							$this->create_panel($titolo,$v);
					}
		return myJQueryUI::get_html().
				"<div id='".str_replace('#','',$this->get_id())."'>
					$html
				</div>"
				;
	}

}