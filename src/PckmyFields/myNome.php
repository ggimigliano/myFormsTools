<?php 
/**
 * Contains Gimi\myFormsTools\PckmyFields\myNome.
 */

namespace Gimi\myFormsTools\PckmyFields;


 
class myNome extends myText  {
	/** @ignore */
	protected function &formattaNome($valore) {
	  $valore=str_replace("o'","�",$valore);
	  $valore=str_replace("a'","�",$valore);
	  $valore=str_replace("e'","�",$valore);
	  $valore=str_replace("i'","�",$valore);
	  $valore=str_replace("u'","�",$valore);
	  $valore=str_replace("'","`",$valore);
	  $valore=$this->rimuovi_spazi_doppi($valore);
	  $valore=trim(str_replace("`","'",ucwords(strtolower($valore))));
	  return $valore;
	}


	function &get_value() {
	  return $this->formattaNome(parent::get_value());
	}

	 public function set_value($valore) {
	  return parent::set_value($this->formattaNome($valore));
	}


	/**
	  *
  	  * 
  	  * @param	 string $nome E' il nome del campo
	  * @param	 string $valore Valore da assegnare come default
	  * @param	 string $classe e' la classe css da utilizzare
	  */
	   public function __construct ($nome,$valore='',$classe='') {
		parent::__construct($nome,$valore,$classe);
		$this->set_MyType('MyNome');
		$this->set_regExp('^[A-Z|a-z| |\\|\'|`|������]*$'," formalmente errato");
		$this->set_minlength(2);
	  }

}

?>