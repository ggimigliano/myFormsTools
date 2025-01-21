<?
/**
 * Contains Gimi\myFormsTools\PckmyFields\myArrayObject.
 */

namespace Gimi\myFormsTools\PckmyFields;


 

class myPIVA extends \Gimi\myFormsTools\PckmyFields\myInt{
/** @ignore */
protected $restrizioni_check=array(true,true);


/**
  * 
  *
  * @param	 string $nome E' il nome del campo
  * @param	 string $valore Valore da assegnare come default
  * @param	 string $classe e' la classe css da utilizzare
  */
   public function __construct($nome,$valore='',$classe='') {
     
	parent::__construct($nome,$valore,$classe);
	$this->min=0;
	//$this->set_regExp('^[0-9]{11}$','deve essere un numero intero positivo');

	$this->set_MyType('MyPIVA');
	$this->set_minlength(11);
	$this->set_maxlength(11,11);
	$this->set_zerofill(true);
  }



  /**
	* Permette di rendere il check_errore piu' o meno restrittivo
	*
	* @param boolean $verificaUfficio   //verifica che sia coerente la parte relativa al numero ufficio iva se true si attiva
	* @param boolean $calcoloCin        //calcolo del cin se true si attiva
    */
   public function set_restrizioni_check($verificaUfficio=true,$calcoloCin=true){
   	 $this->restrizioni_check=array($verificaUfficio,$calcoloCin);
   	 return $this;
   }
   
   
    public function set_value($valore) {
       $val=$valore;
       parent::set_value($val);
       if (!parent::get_errore_diviso() && 
           (trim((string) $valore)!=='' && ($this->restrizioni_check[0] || $this->restrizioni_check[1]) && !$this->verifica_formale())
           )  parent::set_value(sprintf("%011s",$val));
              else   parent::set_value($val);
       return $this;
   }
   



   public function get_errore_diviso() {
   	if ($errore=parent::get_errore_diviso()) return $errore;
   	$valore=strtoupper($this->get_value());
   	if ($valore && ($this->restrizioni_check[0] || $this->restrizioni_check[1])) return $this->verifica_formale();
   }

  /** @ignore */
  protected function verifica_formale()
	 {  $pi=$this->get_value();
		$ufficio=substr($pi,7,3);
		if ($this->restrizioni_check[0] && $ufficio>121 && $ufficio!=888) return "non è coerente nelle tre cifre a partire dalla settima";
		if (!$this->restrizioni_check[1]) return;
        $s = 0;
        for($i = 0; $i <= 9; $i += 2 ) $s += ord($pi[$i]) - ord('0');
        for($i = 1; $i <= 9; $i += 2 ){
               $c = 2*( ord($pi[$i]) - ord('0') );
               if( $c > 9 )  $c = $c - 9;
               $s += $c;
     		   }

        if ( ( 10 - $s%10 )%10 != ord($pi[10]) - ord('0') ) return "non è coerente con nell'ultima cifra";
	}


}


?>