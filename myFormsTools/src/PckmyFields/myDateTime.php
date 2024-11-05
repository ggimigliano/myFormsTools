<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myDateTime.
 */

namespace Gimi\myFormsTools\PckmyFields;


use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQInputMask;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQTimePicker;
 
class myDateTime extends myText	{
    /**
     * @ignore
     */
 protected $is_hidden=true,$autovalued=false,$dateLimit=array(),$timeLimit=array(),$minCal,$maxCal;

  public function set_value($valore) {
     if ($valore) {
                   $valore=explode(' ',trim((string) $valore));
                   if(count($valore)==2) { $d=new myDate('',$valore[0]);
                                           $h=new myTime('',$valore[1]);
                                           
                                           if(!$d->set_notnull()->errore() &&
                                               !$h->set_notnull()->errore()) 
                                                    {$valore[0]=$d->get_formatted();
                                                     $valore[1]=$h->get_value();
                                                    }
                                         }
                                      
                   parent::set_value(implode(' ',$valore));                      
                  }
		   else {$this->autovalued=true;
		         return parent::set_value(date("Y-m-d H:i:s"));
		        }
	return $this;	        
  }

  
  
  
  /**
  * 
  * @param	 string $nome E' il nome del campo
  * @param	 string $valore Valore da assegnare all'attributo 'value', SE NULL VIENE IMPOSTATO ALLA DATA DI SISTEMA
  * @param	 string $classe e' la classe css da utilizzare
  */
   public function __construct($nome,$valore='',$classe='') {
	 myText::__construct($nome,$valore,$classe);
	 $this->set_MyType('MyDateTime');
	 $this->set_minlength(19)->set_maxlength(19);
	 $this->dateLimit=new myDate('');
	 $this->dateLimit->set_notnull();
	 $this->timeLimit=new myTime('');
	 $this->timeLimit->set_notnull();
	 $this->set_hidden(true);
  }

  /**
  * In questo campo non si verificano mai errori
  */
  protected function get_errore_diviso_singolo() {
      if($this->get_value() || $this->get_notnull()) 
                { if(!$this->get_parte(0,true) || 
                      !$this->get_parte(1,true)) return "formalmente errato";
                
                   $errore=$this->dateLimit->set_value($this->get_parte(0,true)->get_value())->errore();
                   if($errore) return $errore;
                   
                   if($this->get_parte(0,true)->get_value()==$this->dateLimit->get_min() && 
                      $this->get_parte(1,true)->get_value()<$this->timeLimit->get_min())         
                                                    return array("non può essere antecedente al %1% alle %2%",$this->get_parte(0,true)->get_value(),$this->timeLimit->get_min());
                      
                   if($this->get_parte(0,true)->get_value()==$this->dateLimit->get_max() && 
                      $this->get_parte(1,true)->get_value()>$this->timeLimit->get_max())
                                                    return array("non può essere successivo al %1% alle %2%",$this->get_parte(0,true)->get_value(),$this->timeLimit->get_max());
                }
  }

  
  /**
   * Restituiesce la parte del valore contenuto nel mydate
   *
   * @param  '0'|'1' $quale se 0=>data 1=>ora
   * @param  boolean $oggetto se falso restituisce la parte come stringa, alttrimenti come oggetto myDate|myOrario a seconda della parte scelta
   * @return myField|string
   */
   public function get_parte($quale,$oggetto=false) {
  	$v=explode(' ',$this->get_value());
  	if (!$oggetto) return $v[$quale];
  	if ($quale==0) return (new myDate(''))->set_value($v[0]);
  	     elseif(count($v)>=2) return (new myTime(''))->set_value($v[1]);
  }
  
 
  
   public function is_hidden(){
      return $this->is_hidden;
  }
  
  /**
   * @param boolean $stato
   */
   public function set_hidden($stato=true){
      if($stato==true) {
                    $this->Richiede_tag_label=false;
                    $this->Prevede_label=false;
                    $this->is_hidden=true;
                 }
             else {
                     $this->Richiede_tag_label=true;
                     $this->Prevede_label=true;
                     $this->is_hidden=false;
                 } 
      return $this;
  }
  
  
   public function unset_hidden(){
        return $this->set_hidden(false);
  }
  
  
   public function set_max($max,$ancheCalendario=true){
      $t=new myDateTime('',$max);
      if($t->get_parte(0,true) && $t->get_parte(1,true)) { 
            $this->dateLimit->set_max($t->get_parte(0,true)->get_value());
            $this->timeLimit->set_max($t->get_parte(1,true)->get_value());
            if($ancheCalendario) $this->maxCal=$t->get_parte(0,true)->get_formatted().' '.$t->get_parte(1,true)->get_value();
            }
      return $this;
  }
  
  
   public function set_min($min,$ancheCalendario=true){
      $t=new myDateTime('',$min);
      if($t->get_parte(0) && $t->get_parte(1)){
            $this->dateLimit->set_min($t->get_parte(0,true)->get_value());
            $this->timeLimit->set_min($t->get_parte(1,true)->get_value());
            if($ancheCalendario) $this->minCal=$t->get_parte(0,true)->get_formatted().' '.$t->get_parte(1,true)->get_value();
            }
      return $this;
  }
  
   public function get_max($calendario=false){
      if($calendario) return $this->maxCal;
      return $this->dateLimit->get_max().' '.$this->timeLimit->get_max();
  }

   public function get_min($calendario=false){
      if($calendario) return $this->minCal;
      return $this->dateLimit->get_min().' '.$this->timeLimit->get_min();
  }
  
  /**
   * NON DISPONIBILE PER QUESTA CLASSE
   */
   public function set_molteplicita($max = 0, $separaQuota = ';', $maxlengthComplessiva = '', $grafica = true, $textarea = false){}
  
   public function get_html(){
     if($this->is_hidden()) {
                             $this->set_attributo('type','hidden');
                                 if($this->secure) return $this->send_html("<input value='{$this->secure->encode($this->get_name(),$this->get_value())}' ".$this->stringa_attributi(array('type','name','class','id'),false)." />");
                                              else return $this->send_html("<input ".$this->stringa_attributi(array('type','name','value','class','id'),false)." />");
                            }
    if($this->autovalued) $this->set_value('');
    if(!$this->masked) return  parent::get_Html();
        
     $this->add_myJQuery(new myJQTimePicker())->usa_slider();
     $myJQInputMask=new myJQInputMask("#{$this->get_id()}");
     $myJQInputMask->set_mask("a9/b9/9999 c9:d9:d9",'',array('a'=>"0123",'b'=>'01','c'=>'012','d'=>'012345'));
     $myJQInputMask->add_code("{$myJQInputMask->JQid()}.css('border-right','-1px').width(myJQCalcWidth({$myJQInputMask->JQid()},'88/88/8888 88:88:88')+24);");
     
     $this->add_myJQuery($myJQInputMask);
    
     $valore=$this->get_value();
     if($valore) {
         $valore=explode(' ',$valore);
         if(count($valore)==2) { $d=new myDate('',$valore[0]);
                                 $h=new myTime('',$valore[1]);
                                 $this->attributi['value']=$d->get_value().' '.$h->get_value();
                              }
     }
     $out=parent::get_html();
     if($valore) $this->set_value(implode(' ',$valore));
     return $out;
  }

}