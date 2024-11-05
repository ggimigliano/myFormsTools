<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\PckmyForms\myJQMyFormTabs.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\PckmyForms;



use Gimi\myFormsTools\PckmyJQuery\myJQueryUI;
use Gimi\myFormsTools\PckmyJQuery\Pckgeneric\myJQTabs;

                                                
 

/**
 * Estensione di {@link myJQTabs} specifica per myForms
 */
	
class myJQMyFormTabs extends myJQTabs implements myJQMyFormSezioni{
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
		$this->selected=$titolo;
	}

	/**
 * @ignore 
 */protected function create_tab($titolo) {
		$i=self::id_titolo($titolo);
#		$url='//'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
#		if($_SERVER['QUERY_STRING']) $url.='?'.$_SERVER['QUERY_STRING'];
		return "<li><a href=\"#$i\">$titolo</a></li>";
	}
	
   /**
 * @ignore 
 */  function get_panel($titolo,&$v) {
		$i=self::id_titolo($titolo);
		return "<div id='$i'><a name='$i'></a>{$v}</div>";
	}
	
/**
 * @ignore 
 */	function get_html(){
		foreach (array_keys($this->items) as $titolo) $title.=$this->create_tab($titolo);
		return "<div id='".str_replace('#','',$this->get_id())."'>
				<ul>$title</ul>
				{$this->html}
				</div>".myJQueryUI::get_html();
				;
	}
	
}