<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myPassword.
 */

namespace Gimi\myFormsTools\PckmyFields;




class myPassword extends myText  {
	/**
	  * 
	  * 
	  * @param	 string $nome E' il nome del campo
	  * @param	 string $valore Valore da assegnare come default
	  * @param	 string $classe e' la classe css da utilizzare
	  */
	   public function __construct($nome,$valore='',$classe='') {
				myText::__construct($nome,$valore,$classe);
				$this->set_attributo('type','password');
				$this->set_MyType('MyPassword');
				$this->rimuovi_spazi_doppi=false;
		  }
		  
		  
	/** @ignore */
    protected function _get_html_show($pars, $tag = 'span', $content = NULL){
		   return '';
		  }
}