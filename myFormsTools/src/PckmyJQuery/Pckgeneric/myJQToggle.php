<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\myJQToggle.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\Pckgeneric;



use Gimi\myFormsTools\PckmyJQuery\myJQueryMyField;

                                                


/**
 * @ignore
 */
	
class myJQToggle extends myJQueryMyField {
	public function set_istance_defaults() {
		self::add_src(self::$percorsoMyForm."jquery/itoggle/jquery.itoggle.js");
		self::add_css(self::$percorsoMyForm."jquery/itoggle/itoggle.css");
	}

	static protected function init(&$widget){
		$widget='itoggle';
	}
	
}