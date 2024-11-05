<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myOra.
 */

namespace Gimi\myFormsTools\PckmyFields;

use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQTimePicker;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQInputMask;

 
class myOra extends myOrario  {
/** @ignore */
protected $anche_secondi=true,$frac=1,$secondi=null;



	/**
	  * Costruttore di classe, permette di memorizzare una generica ora nell'ambito  delle 24 ore giornaliere
	  * si interfaccia con il DB memorizzando il valore espresso in HH:MM:00, (specifico per mysql)
	  *
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
	$this->set_regExp('^[0-9]{1,2}([\.\:\,]{1}[0-9]{1,2})?$',' errato, formato corretto hh.mm');
	$this->set_MyType('MyOra');
 
  }
  
  
  /**
   * Setta la proprietà di inserire fittiziamente anche i secondi quando salva su DB
   *
   * @param boolean $abilita disabilita/abilita tale proprietà
   */
   public function set_secs_mode($abilita=false) {
  	 $this->anche_secondi=$abilita;
  	 return $this;
  }

  /**
   * Setta secondi come parametro fisso 
   * @param int $secs
   * @return myOra
   */
   public function set_seconds($secs) {
      $this->secondi=$secs;
      return $this;
  }

   /**
   * Restituisce lo stato proprietà di inserire anche i secondi quando salva su DB
   *
   * @return  boolean stato della proprieta
   */
   public function get_secs_mode($disabilita=true) {
  	 return $this->anche_secondi;
  }

  /**
   * Imposta il numero di minuti nelle porzioni es se 15
   * accetterà solo i querti d'ora es: XX:00 XX:15 XX:30 XX:45
   * @param int $minuti Frazione espressa in minuti
   */
   public function set_frazioni_ora($minuti){
      $this->frac=$minuti;
      return $this;
  }
  
  
  /**
   * Restituisce il numero di frazioni d'ra accettate espresse in minuti
   * @return number
   */
   public function get_frazioni_ora(){
      return $this->frac;
  }
  
  
  
   public function set_max($max) {
      $this->max=$max;
      return $this;
  }
  
   public function set_min($min) {
      $this->min=$min;
      return $this;
  }
  
  
   public function get_Html(){
      if($this->is_hidden()) $this->myJQueries=array();
      return parent::get_Html();
  }
  
   public function set_mask($stato = true) {
      $this->add_myJQuery(new myJQTimePicker())->usa_slider();
      $myJQInputMask=new myJQInputMask("#{$this->get_id()}");
      $myJQInputMask->set_mask(">9:<9",'',array('<'=>"012345",'>'=>'012'));
      $this->set_style('text-align', 'left');
   // $myJQInputMask->add_code("{$myJQInputMask->JQid()}.css('width',(parseFloat({$myJQInputMask->JQvar()}('body').css('font-size')*3 +20))+'px!important');");
      $myJQInputMask->add_code("{$myJQInputMask->JQid()}.css('border-right','-1px').width(myJQCalcWidth({$myJQInputMask->JQid()},'88:88')+24);");
     
      $this->add_myJQuery($myJQInputMask);
      return $this;
  }
  
  
  protected function get_errore_diviso_singolo() {
  	// if(!preg_match('#^[0-9]{1,2}([\\.\\:\\,]{1}[0-9]{1,2})?$#',$this->get_value())) return  "non è in formato corretto";
  	 $v=explode(':',$this->get_value());
  	 if (($v[1] % $this->frac)!==0) return array(" deve avere il numero di minuti multiplo di %1%",$this->frac);
	 if ($v[1]<0 || $v[1]>59 ) return " deve avere il numero di minuti compreso tra 0 e 59";
	 if ($v[0]<0 || $v[0]>23 ) return " deve avere l'ora compresa tra 0 e 23";
	 
	 return parent::get_errore_diviso_singolo();
	}
	
/** @ignore*/
  function &get_value_DB() {
	    $v=explode(':',$this->get_value());
	    if (count($v)==2) $v[]='00';
	    if($this->secondi!==null) $v[2]=sprintf('%02s',$this->secondi);
	    if (method_exists($this,'get_secs_mode') && !$this->get_secs_mode()) unset($v[2]);
	    return implode(':',$v);
	}	
	
}