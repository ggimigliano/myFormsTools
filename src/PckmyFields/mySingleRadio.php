<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\mySingleRadio.
 */

namespace Gimi\myFormsTools\PckmyFields;






/**
* Implementa una singola opzioni di myRadio
*/
	
class mySingleRadio extends myCheck {
public $valore;

	  public function __construct($nome,$valore='',$classe='') {
				myCheck::__construct($nome,$valore,$classe);
				$this->set_attributo('type','radio');
		  }


	   public function set_value($valore) {
			  $this->valore=$valore;
			  return parent::set_value($valore);
		  }

	   function &get_value() {
			  return $this->valore;
	  }


	 /**
	* Non attiva in questa classe
		*/
	  public function set_autotab () {return $this;  }
}