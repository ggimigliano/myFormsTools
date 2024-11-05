<?

/**
 * Contains Gimi\myFormsTools\PckmyFields\myFieldsItaly\myCognome.
 */

namespace Gimi\myFormsTools\PckmyFields\myFieldsItaly;


 

class myCognome extends myNome  {

/** @ignore*/
protected function &formattaNome($valore) {
  $valore=str_replace("ò","o'",$valore);
  $valore=str_replace("à","a'",$valore);
  $valore=str_replace("è","e'",$valore);
  $valore=str_replace("é","e'",$valore);
  $valore=str_replace("i'","ì",$valore);
  $valore=str_replace("ù","u'",$valore);
  $valore=str_replace("`","'",$valore);
  $valore=$this->rimuovi_spazi_doppi($valore);
  $valore=str_replace(array("' "," '"),array("'","'"),$valore);
  $valore=trim(ucwords(strtolower($valore)));
  $valore=explode("'",$valore);
  foreach ($valore as &$val) $val=trim(ucfirst($val));
  $valore=implode("'",$valore);
  return $valore;
}



  /**
	  *
	  * 
	  * @param	 string $nome E' il nome del campo
	  * @param	 string $valore Valore da assegnare come default
	  * @param	 string $classe e' la classe css da utilizzare
	  */
   public function __construct($nome,$valore='',$classe='') {
	parent::__construct($nome,$valore,$classe);
	$this->set_MyType('MyCognome');
	$this->set_regExp('^[A-Z|a-z|ì| |\\\'|`]*$'," formalmente errato");
	$this->set_minlength(2);
  }

}


?>