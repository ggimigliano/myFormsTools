<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myBottone.
 */

namespace Gimi\myFormsTools\PckmyFields;
 
class myBottone extends myPulsante {
/**
  * 
	  *
	  * @param	 string $nome E' il nome del campo
	  * @param	 string $valore Valore da assegnare come default
	  * @param	 string $classe e' la classe css da utilizzare
	  */

		   public function __construct($nome,$valore="",$classe=''){
				myPulsante::__construct($nome,$valore,$classe);
				$this->set_attributo('type','button');
				$this->set_MyType('MyBottone');
		  }

}