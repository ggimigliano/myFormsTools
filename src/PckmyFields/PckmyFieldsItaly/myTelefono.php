<?
/**
 * Contains Gimi\myFormsTools\PckmyFields\myFieldsItaly\myTelefono.
 */

namespace Gimi\myFormsTools\PckmyFields\myFieldsItaly;

 
use Gimi\myFormsTools\PckmyFields\myField;
use Gimi\myFormsTools\PckmyFields\myText;


class myTelefono extends myText  {
/** @ignore */
protected $divisore; 
/**
  * Accetta stringhe numeriche intere o con al max un seperatore che puo' essere . - / in tal caso la prima parte (prefisso)
  * compresso tra i 2 e 4 caratteri e che inizi per 3,0,8,7, la lunghezza complessiva del numero non puo' comunque superare gli 11 caratteri,
  * come da piano di numerazione nazionale
  *
  * 
  * @param	 string $nome E' il nome del campo
  * @param	 string $valore Valore da assegnare come default
  * @param	 string $classe e' la classe css da utilizzare
  */
   public function __construct($nome,$valore='',$classe='') {
      parent::__construct($nome,$valore,$classe);
	    $this->set_regexp("^[0387]{1}[0-9]{1,3}[\/\.\-]{0,1}[0-9]{4,9}\$","non è in formato corretto");
	    $this->set_minlength(6);
	    $this->set_maxlength(11+1,11+1);
		$this->set_MyType('MyTelefono');
  }


 /**
  * Imposta la caratteristica di numerazione mobilr, accetterà quindi numeri di telefono con prefisso esattamente di 3 cifre che inizia con 3
  * di lunghezza complessiva compresa tra 9 e 11 caratteri
  */
   public function set_mobile() {
  	  $this->set_regexp("^3[2-9]{1}[0-9]{1}[\/\.\-]{0,1}[0-9]{6,8}\$","non è conforme alla numerazione di telefonia mobile");
  	  $this->set_minlength(9);
	  $this->set_maxlength(11+1,11+1);
	  return $this;

  }


   /**
   * Imposta la caratteristica di numerazione fissa, accetterà quindi numeri di telefono con prefisso da 2 a 4 cifre che inizia con 0
   * di lunghezza complessiva compresa tra 6 e 11 caratteri
   * @param array $prefissi_speciali e' l'elenco di prefissi aggiuntivi ammessi
   */
   public function set_fisso($prefissi_speciali=array()) {
  	 $prefissi_speciali[]='0[1-9]{1}[0-9]{0,2}';
     $this->set_regexp("^(".implode('|',$prefissi_speciali).")[\/\.\-]{0,1}[0-9]{4,9}\$","non è conforme alla numerazione di telefonia fissa");
  	 $this->set_minlength(6);
	 $this->set_maxlength(11+1,11+1);
	return $this;
  }


   protected function get_errore_diviso_singolo(){
  	if ($errore=parent::get_errore_diviso_singolo()) return $errore;
  	$valore=$this->get_value();
  	if ($valore && !preg_match('#[\/\.\-]#',$valore))
  						   {
  							if(strlen(trim((string) $valore))>=$this->get_maxlength()) return array("non può essere composto da più di %1% cifre ",$this->get_maxlength()-1);
  							//if(strlen(trim((string) $this->get_value()))<=$this->get_minlength()) return "non può essere composto da meno di ".($this->get_minlength()-1)." cifre ";
  						   }

  }


  /**
   * Imposta la caratteristica di numerazione internazionale, accetterà quindi
   * +39.6416539758
   * +39/6416539758
   * +39-6416539758
   * +39-6-416539758
   * 
   * @param array $prefissi_speciali e' l'elenco di prefissi aggiuntivi ammessi
   */
   public function set_internazionale($prefissi_speciali=array()) {
      $prefissi_speciali[]='\+[0-9]{1,3}';
      $this->set_regexp("^(".implode('|',$prefissi_speciali).')[\/\.\-]{0,1}([0-9]{1,3}[\/\.\-]{0,1})?[0-9]{2,4}[\/\.\-]{0,1}[0-9]{3,9}$',"non è conforme alla numerazione di telefonia internazionale");
	  $this->set_minlength(3+4+1);
	  $this->set_maxlength(10+1+3+1+3+1+1,10+1+3+1+1);
	  return $this;
  }


   public function set_value($valore) {
  	  if (!$this->divisore) $valori=array($valore);
  	  				   else {$valore=preg_replace('~\s+'.$this->divisore.'\s+~',$this->divisore,$valore);
  	  			 		  	 $valori=explode($this->divisore,$valore);
  	  				   		}
  	  foreach ($valori as &$valore){
  	        $valore=strtr($valore,array('.'=>'-',' '=>'-'));
  	        if (strpos($valore,'/')!==false &&
  	        	strpos($valore,'-')!==false)  $valore=str_replace('/','-',$valore);
  			if (substr_count($valore,'-')==1) $valore=str_replace('-','/',$valore);
  	  		}
  	   return parent::set_value(implode(''.$this->divisore,$valori));
  }

  protected function html5Settings($attrs=array()){
      $attrs['type']='tel';
      myField::html5Settings($attrs);
  }

}


?>