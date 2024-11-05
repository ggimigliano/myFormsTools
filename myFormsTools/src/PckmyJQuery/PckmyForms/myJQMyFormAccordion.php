<?php
/**
 * Contains Gimi\myFormsTools\PckmyForms\myJQMyFormAccordion.
 */
                                                
namespace Gimi\myFormsTools\PckmyForms;


use Gimi\myFormsTools\PckmyJQuery\Pckgeneric\myJQAccordion;
use Gimi\myFormsTools\PckmyJQuery\PckmyForms\myJQMyFormSezioni;
use Gimi\myFormsTools\PckmyJQuery\myJQueryUI;
                                                
 

/**
 * Estensione di {@link myJQAccordion} specifica per myForms
 */
	
class myJQMyFormAccordion extends myJQAccordion implements myJQMyFormSezioni {
/**
 * @ignore 
 */
	protected $html;	
	 
	/**
 * @ignore 
 */function set_content($html){
		$this->html=$html;
	}
	
/**
 * @ignore 
 */function set_selected($titolo){
		$this->active=$titolo;
	}
	
/**
 * @ignore 
 */function get_panel($titolo,&$v) {
		return $this->create_tab($titolo)."\n".$this->create_panel($titolo,$v) ;
	}
	
/**
 * @ignore 
 */function get_html(){
		return "<div id='".str_replace('#','',$this->get_id())."'>
				{$this->html}
				</div>".myJQueryUI::get_html();
				;
	}

}