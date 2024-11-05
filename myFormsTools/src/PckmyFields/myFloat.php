<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myFloat.
 */

namespace Gimi\myFormsTools\PckmyFields;
 

class myFloat extends myInt  {
/** @ignore */
protected  $decimali='',$separatore='.';


	/**
	  *
      * 
	  * @param	 string $nome E' il nome del campo
	  * @param	 string $valore Valore da assegnare all'attributo 'value'
	  * @param	 string $classe e' la classe css da utilizzare
	  */
  public function __construct($nome,$valore='', $classe='') {
    $this->set_regExp('^(([\+\-]{0,1})[0-9]*([\\.\\,]{0,1}[0-9]*))$',"deve essere un numero");
    myText::__construct($nome,$valore,$classe);
    $this->set_decimali(0);
	$this->set_MyType('MyFloat');
  }


 /**
	* Setta il numero di cifre decimali accettabili
  * @param	integer $decimali
  */
   public function set_decimali($decimali){
   $this->decimali=$decimali;
   $maxl1=$minl2=0;
   if (!$decimali)$this->set_regExp('^[\+\-]{0,1}[0-9]+([\\.\\,]{0,1}[0-9]+)?$',"deve essere un numero");
			else  $this->set_regExp('^[\+\-]{0,1}[0-9]+([\\.\\,]{0,1}[0-9]{1,'.$decimali.'})?$',array("deve essere un numero con al più %1% cifre decimali",$decimali));
   if ($this->max) $maxl1=strlen(intval($this->max))+1+$this->decimali;
   if ($this->min) $minl2=strlen(intval($this->min))+1+$this->decimali+intval($this->min<0);
   if(!$maxl1) $maxl1=$minl2;
   if($maxl1>0) $this->set_maxlength(max($maxl1,$minl2));
   return $this;
  }

    public function set_max($max) {
   		$this->max=$max;
   		$dec=strlen(strstr($max,$this->separatore))-1;
   		$this->set_decimali(max($dec,$this->decimali));
   		return $this;
   }

    public function set_min($min) {
   	    $this->min=$min;
	 	$dec=strlen(strstr($min,$this->separatore))-1;
	 	$this->set_decimali(max($dec,$this->decimali));
	 	return $this;
	}


	 public function set_value($valore) {
	  $val=str_replace(',','.',$valore);
	  if (strlen($val) && $this->decimali && !preg_match("#{$this->controllo_regExp['exp']}#",$val))
	  						parent::set_value(str_replace('.',$this->separatore,sprintf('%01.'.$this->decimali.'f',$val)));
	  			     else   parent::set_value(str_replace('.',$this->separatore,$val));
	 return $this;
	}


	function &get_value() {
	  if (parent::get_value()!=0) $this->set_value(parent::get_value());
	  return parent::get_value();
	}

	/**
	 * Imposta il segno separatore dei decimali, sono accettati solo punto o virgola
	 * altrimenti non ha effetto
	 *
	 * @param ,|. $separatore
	 */
	 public function set_separatore($separatore='.') {
		if ($separatore=='.' || $separatore==',') {
			$this->separatore=$separatore;
			$this->set_decimali($this->decimali);
		}
		return $this;
	}

	/**
	 * Restituisce il separature corrente
	 *
	 * @return ,|.
	 */
	 public function get_separatore() {
		return $this->separatore;
	}

 /**
	* Restituisce il valore del campo in lettere
  *  @param  int    $decimali numero di cifre decimali in forma letterale, se negativo si aggiungono sempre decimali anche se non significativi
  *  @param  string $separatore_decimali indica la parola per il simbolo di interpunzione
  *  @param  bool $numero e' il numero da convertire, se omesso si usa il valore corrente dell'oggetto
  *  @return string
  */
    public function inlettere ($decimali=2,$separatore_decimali='virgola',$numero='') {
	 if ($numero==='') $n=(float) trim(str_replace(array(",",'.'),array($this->separatore,$this->separatore),$this->get_value()));
				 else  $n=(float) $numero;
	 if (!$separatore_decimali) $separatore_decimali='virgola';
	 if ($n<0) {
			   $segno='-';
			   $n=abs($n);
			  }
	 $v=explode($this->separatore,round($numero,abs($decimali)));
	 if ($v[1] || $decimali<0) return $segno.myInt::inlettere($v[0]).$separatore_decimali.myInt::inlettere($v[1]);
	 					  else return $segno.myInt::inlettere($v[0]);
	}

	protected function html5Settings($attrs=array()){
	    myField::html5Settings($attrs);
	}

	 public function get_html(){
		$this->get_value();
		$this->html5Settings();
		return parent::get_html();
	}

}