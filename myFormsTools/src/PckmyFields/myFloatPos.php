<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myFloatPos.
 */

namespace Gimi\myFormsTools\PckmyFields;

 
class myFloatPos extends myFloat  {
	  /**
      * 
	  * @param	 string $nome E' il nome del campo
	  * @param	 string $valore Valore da assegnare come default
	  * @param	 string $classe e' la classe css da utilizzare
	  */

   public function __construct($nome,$valore='', $classe='') {
	 myFloat::__construct($nome,$valore,$classe);
	 $this->set_min(0);
	 $this->set_MyType('MyFloatPos');
  }



/*
function set_max($max) {
	 parent::set_max($max);
	 $this->set_maxlength(strlen($this->max)+1+$this->decimali);
}

*/



}