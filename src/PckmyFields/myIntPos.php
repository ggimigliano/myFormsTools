<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myIntPos.
 */

namespace Gimi\myFormsTools\PckmyFields;


 

class myIntPos extends myInt  {
  /**
  * 
  *
  * @param	 string $nome E' il nome del campo
  * @param	 string $valore Valore da assegnare come default
  * @param	 string $classe e' la classe css da utilizzare
  */
   public function __construct($nome,$valore='',$classe='') {
	myInt::__construct($nome,$valore,$classe);
	$this->set_regExp('^[\+]{0,1}[0-9]*$','deve essere un numero intero positivo');
	$this->set_MyType('MyIntPos');
	$this->set_min(0);
  }

}