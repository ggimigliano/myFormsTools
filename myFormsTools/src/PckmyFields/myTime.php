<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myTime.
 */

namespace Gimi\myFormsTools\PckmyFields;

use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQInputMask;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQTimePicker;




/**
	* Setta il valore del campo espresso in minuti
* @param	string $orario
 */
	
class myTime extends myOrario  {
/**
 * @ignore
 */
protected $minCal,$maxCal;    
    
/**
  *
  * 
  * @param	 string $nome E' il nome del campo
  * @param	 string $valore Valore da assegnare come default ACCETTATE SIA IN FORMATO hh:mm:ss e hh.mm
  * @param	 string $classe e' la classe css da utilizzare
  */
   public function __construct($nome,$valore='',$classe='') {
		myText::__construct($nome,$valore,$classe);
		$this->set_attributo('maxlength','8');
		$this->set_attributo('size','8');
		$this->set_regExp('^[0-9]{1,2}[\.|\-|\:]{1}[0-9]{1,2}([\.|\-|\:]{1}[0-9]{1,2})?$',' errato, formati corretti hh.mm oppure hh.mm.ss');
		$this->set_MyType('MyTime');
		$this->is_numeric(false);
  }
  
  

   public function set_mask($stato = true) {
      $this->add_myJQuery(new myJQTimePicker())->usa_slider();
      $myJQInputMask=new myJQInputMask("#{$this->get_id()}");
      $myJQInputMask->set_mask(">9:<9:<9",'',array('<'=>"012345",'>'=>'012'));
      $this->add_myJQuery($myJQInputMask);
      return $this;
  }
  
  private function reformat($valore){
      $n=array();
      $valore=preg_replace('#[\.\-]#',':',$valore);
      foreach(explode(':',$valore) as $v) if ($v!=='') $n[]=sprintf('%02d',$v);
      while (count($n)<3) $n[]='00';
      if (is_array($n)) $valore=implode(":",$n);
      return $valore;
  }
  
   public function set_max($max,$anche_calendario=true){
      if($anche_calendario) $this->maxCal=$this->reformat($max);
      return parent::set_max($this->reformat($max));
  }
  
   public function set_min($min,$anche_calendario=true){
      if($anche_calendario) $this->minCal=$this->reformat($min);
      return parent::set_min($this->reformat($min));
  }
  
    public function set_value($valore) {
       if ($valore && isset($this->controllo_regExp['exp']) && preg_match("#{$this->controllo_regExp['exp']}#",$valore)) $valore=$this->reformat($valore);
	  return myText::set_value($valore);
    }
    
    public function get_min($calendario=false) {
       if($calendario) return $this->minCal;
                  else return parent::get_min();
   } 

    public function get_max($calendario=false) {
       if($calendario) return $this->maxCal;
            else return parent::get_max();
   }
   
   
  function &get_value() {
	 $valore=myText::get_value();
	 if ($valore) $valore=$this->reformat($valore);
	 return $valore;
}

/** @ignore*/
  function &get_value_DB() {
	 return $this->get_value();
  }


  protected function get_errore_diviso_singolo() {
      $v=explode(':',$this->get_value());
      if ($v[0]>23 || $v[1]>59 || $v[2]>59) return " non è un'ora valida";
	  return parent::get_errore_diviso_singolo();
      }
}