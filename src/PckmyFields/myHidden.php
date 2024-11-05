<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myHidden.
 */

namespace Gimi\myFormsTools\PckmyFields;





class myHidden extends myField  {
/**
  @ignore */
protected $errore;



/**
  *
  * 
  * @param	 string $nome E' il nome del campo
  * @param	 string $valore Valore da assegnare come default
  * @param	 string $classe e' la classe css da utilizzare
  */
	 public function __construct($nome,$valore='',$classe='') {
		myField::__construct($nome,$valore);
		$this->set_attributo('type','hidden'); 
		$this->set_MyType('MyHidden');
		$this->Richiede_tag_label=false;
		$this->Prevede_label=false;
		$this->numerico=null;
  }

/*  function unsecure(mySecurizer $s){
  	$this->secure=$s;
  	$val=$this->get_value().'';
  	if($val!=='')
  			{
  			if(!$this->secure->is_secure($this->get_name(),$val))
  						{
  						 $this->errore="Errore interno";
  						 return $this;
  						}
		  	$this->set_value($s->unsecure($this->get_name(),$val));
		  	}
		return $this;
  }
  */




   public function get_js_chk($js = ''){}


/**
  * In questo campo non si verificano mai errori
  */
  protected function get_errore_diviso_singolo() {
  	   /*if($this->notnull &&
  	   	   $this->secure &&
  	   	   !$this->secure->is_secure($this->get_name(),$this->get_value())) return "Errore interno, riprovare prego";
  	   */
  		return null;
  }


 /**
	* Non attiva in questa classe
	*/
  public function set_autotab () {return $this;}


  public function get_Html () {
    if($this->secure) return $this->send_html("<input value='{$this->secure->encode($this->get_name(),$this->get_value())}' ".$this->stringa_attributi(array('type','name','class','id'),false)." />");
                 else return $this->send_html("<input ".$this->stringa_attributi(array('type','name','value','class','id'),false)." />");
 }

}