<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myBoolean.
 */

namespace Gimi\myFormsTools\PckmyFields;


 
class myBoolean extends myCheck {
/** @ignore */
protected $cosa_trasmettere='S',$addhidden=true;


	/**
    *  
	  * @param	 string $nome E' il nome del campo
	  * @param	 string $valore Valore da assegnare all'attributo 'value'
	  * @param	 string $classe e' la classe css da utilizzare
	  */
	  public function __construct($nome,$valore='',$classe='') {
				parent::__construct($nome,$valore);
				if ($classe) $this->set_attributo('class',$classe);
				$this->set_MyType('MyBoolean');
				$this->set_attributo('type','checkbox');
		  }


	/**
	 * Imposta il vaslore da trasmettere quando check
	 *
	 * @param string $cosa_trasmettere
	 */
	  public function set_check_value($cosa_trasmettere){
	 	if($cosa_trasmettere) $this->cosa_trasmettere=$cosa_trasmettere;
	 	return $this;
	 }


	  public function set_value($valore) {
	 	 	//echo $this->get_name()." => $valore</br>";
	 	   $this->set_unchecked();
	 	   if ("$valore"!=="{$this->cosa_trasmettere}")  $this->set_unchecked();
										  		    else $this->set_checked();
		   return parent::set_value($this->cosa_trasmettere);	 		
	 }


	
	 function &get_value() {
	  if ($this->checked) return $this->attributi['value'];
	 }



  	protected function get_errore_diviso_singolo() {
  	 $errore=parent::get_errore_diviso_singolo();
  	 if ($errore) return $errore;
	 if ($this->notnull && !$this->get_checked()) return "deve essere selezionato";
  }


}