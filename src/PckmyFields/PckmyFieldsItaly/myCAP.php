<?
/**
 * Contains Gimi\myFormsTools\PckmyFields\myFieldsItaly\myArrayObject.
 */

namespace Gimi\myFormsTools\PckmyFields\PckmyFieldsItaly;

 

use Gimi\myFormsTools\PckmyFields\myInt;
use Gimi\myFormsTools\PckmyFields\myField;

class myCAP extends myInt{
/** @ignore **/
protected $generici=array('15100',  '60100',  '70100',  '24100',  '40100',  '25100',  '09100',  '95100', '44100',
 						  '50100',  '71100',  '47100',  '16100',  '19100',  '57100',  '98100',  '20100', '41100',
 						  '80100',  '35100',  '90100',  '43100',  '06100',  '61100',  '65100',  '29100', '56100',
                          '48100',  '89100',  '42100',  '47521', '47900',  '00100',  '84100',  '74100',  '10100', '38100',
 						  '34100',  '30100',  '28900',  '37100',  '47500');

/**
  * 
  *
  * @param	 string $nome E' il nome del campo
  * @param	 string $valore Valore da assegnare come default
  * @param	 string $classe e' la classe css da utilizzare
  */
   public function __construct ($nome,$valore='',$classe='') {
	parent::__construct($nome,$valore,$classe);
	$this->min=0;
	$this->set_minlength(5);
	$this->set_maxlength(5,5);
	$this->set_min(10);
	$this->set_max(98999);
	$this->set_MyType('MyCAP');
	$this->set_zerofill(true);
	$this->set_restrizioni_check(true);
  }



  protected function html5Settings($attrs=array()){
      myField::html5Settings($attrs);
  }

   public function set_value($valore) {
      $val=$valore;
	  if (strlen($val) && !preg_match("#[0-9]{2}[01589]{1}[0-9]{2}#",$val))  parent::set_value(sprintf("%05s",$val));
	  									 		  	 	            else   parent::set_value($val);
	 return $this;
	}


	function &get_value() {
	  if (parent::get_value()!=0) $this->set_value(parent::get_value());
	  return parent::get_value();
	}

	/**
	 * Permette di rendere il check_errore piu' o meno restrittivo
	 *
	 * @param boolean $capGenerici      //Impedisce l'inserimento di cap come 00100 di default e' già  restrittivo
	 */
    public function set_restrizioni_check($capGenerici=true){
   	 $this->restrizioni_check=array($capGenerici);
   	 return $this;
   }


    public function get_Errore_diviso(){
   		if($this->restrizioni_check[0] && in_array($this->get_value(),$this->generici))  return array("non è un CAP valido");
		if($errore=parent::get_Errore_diviso()) return $errore;
		if ($this->notnull && $this->get_value()) {
			if(parent::get_value()<$this->min) return array('non può essere minore di %1%',sprintf("%05s",$this->min));
			$v=sprintf("%05s",parent::get_value());

			
			if(($v[2]>=2 && $v[2]<=7) && $v[2]!=5) {//cap valido 47522 provincia di cesena
				
				return array("non è un CAP valido");
			}
		}
	}

}




?>