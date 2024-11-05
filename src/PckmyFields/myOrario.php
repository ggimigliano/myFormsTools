<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myOrario.
 */
 
namespace Gimi\myFormsTools\PckmyFields;




class myOrario extends myInt  {
/**
  * Costruttore di classe, permette di memorizzare un generico orario positivo o negativo o maggiore di 24 ore
  * si interfaccia con il DB memorizzando il valore espresso in minuti
  * 
  * @param	 string $nome E' il nome del campo
  * @param	 string $valore Valore da assegnare come default ACCETTATE SIA IN FORMATO hh:mm e hh.mm
  * @param	 string $classe e' la classe css da utilizzare
  */
   public function __construct($nome,$valore='',$classe='') {
	myInt::__construct($nome,$valore,$classe);
	$this->is_numeric(false);
	$this->set_attributo('maxlength','5');
	$this->set_attributo('size','5');
	$this->set_regExp('^[\+|\-]{0,1}[0-9]{1,2}([\.\:\,]{1}[0-9]{1,2})?$',' errato, formato corretto hh.mm');
	$this->set_MyType('MyOrario');
  }
  
  
  
   public function get_Html(){
        return myText::get_html();
  }
  
  /**
   * 
   * @param '0'|'1'|'2' $quale  0=ore 1=minuti 2=secondi
   * @return int
   */
   public function get_parte($quale){
        $v=explode(':',$this->get_value());
        return $v[$quale]; 
  }

/**
 * @ignore
 */
  function &get_value_DB(){
      $out=$this->get_minuti();
      return $out;
  }

   public function set_value($valore) {
  	myInt::set_value($valore);
    if ($this->get_errore_diviso_singolo()) return $this;
	if (trim((string) $valore)!==''){
	            $segno='';
				if ($valore[0]=='-') {$segno=$valore[0];
									  $valore=substr($valore,1);
									 }
				if (strlen($valore)==4 && preg_match('#^[0-9]+$#S',$valore)) $valore=substr($valore,0,2).':'.substr($valore,2,2);
				
	 			$n=array();
	 			foreach(preg_split('/\\:|\\.|\\,/',$valore) as $v) if ($v!=='' && preg_match('#^[0-9]+$#S',$v)) $n[]=sprintf('%02d',$v);
	 																			           			       else $n[]=$v;
	 			unset($n[2]);
	 			if (preg_match('#^[0-9]+$#S',$n[0]) && !isset($n[1])) $n[1]='00';
				$valore=implode(":",$n);
				if ($segno) $valore=$segno.$valore;
			  }
	return parent::set_value($valore);
  }

  function &get_value() {
	 $valore=parent::get_value();
	 if (strlen(trim((string) $valore))=='') return '';
	 if (strlen($valore)==4 && preg_match('#^[0-9]+$#S',$valore)) $valore=substr($valore,0,2).':'.substr($valore,2,2);
	 $n=array();
	 foreach(preg_split('/\\:|\\.|\\,/',$valore) as $v) if ($v!=='' && preg_match('#^[0-9]+$#S',$v)) $n[]=sprintf('%02d',$v);
	 																					 else $n[]=$v;
	 unset($n[2]);
	 if (preg_match('#^[0-9]+$#S',$n[0]) && !isset($n[1])) $n[1]='00';
	 $valore=implode(":",$n);
	 $this->attributi['value']=$valore;
	 /*if (preg_match('/(\d{2}):(\d{2})[:(\d{2})]/i',$valore))
	 				{
					foreach(explode(':',$valore) as $v) if ($v!=='') $n[]=sprintf('%02d',$v);
					unset($n[2]);
					$valore=implode(":",$n);
				  }*/
	return $valore;
	}



  /**
	* Ritorna il valore in minuti
	* @param	string $orario se omesso usa il valore memorizzato nel campo
	* @return  int numero_minuti
  */
    public function get_minuti($orario='') {
	 if ($orario=='') $orario=$this->get_value();
	 $v=preg_split('/\\.|\\:|\\,/',$orario);
	 return  ($v[0]<0?-1:1)*(abs($v[0])*60+$v[1]);
  }


 /**
	* Setta il valore del campo espresso in minuti
* @param	string $orario
 */
   public function set_minuti($orario) {
      $n=array();
	  $n[]=sprintf('%02d',(int)($orario/60));
	  $n[]=sprintf('%02d',abs((int) $orario) % 60);
	  return parent::set_value(implode(':',$n));
 }


  protected function get_errore_diviso_singolo() {
     $errore=parent::get_errore_diviso_singolo();
  	 if ($errore) return $errore;
	 $v=explode(':',$this->get_value());
	 if (isset($v[1]) && ($v[1]<0 || $v[1]>59 )) return " deve avere i minuti compresi tra 0 e 59";
  }
  
  

}