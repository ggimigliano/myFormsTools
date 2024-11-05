<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\Data.
 */

namespace Gimi\myFormsTools\PckmyFields;


 

/** @ignore */
	
class Data {
/** @ignore */
public $formato,$mask,$value,$errore;

   public function __construct($formato="gma",$val='') {
 	  $formato=strtolower($formato);
 	  $this->errore='';
	  if (strlen($formato)!=3 || strpos($formato,'g')===false
			|| strpos($formato,'m')===false || strpos($formato,'a')===false
			)  $this->errore="Formato errato";
		 else {$this->formato=$formato;
				 $this->mask[0]=$formato[0];
				 $this->mask[1]=$formato[1];
				 $this->mask[2]=$formato[2];
				 $this->mask=array_flip($this->mask);
				 $this->check_value($val);
				 $this->set_value($val);
				 }
	 }


	  public function get_expr () {
	     $x='';
    	 if ($this->formato[0]=='g' || $this->formato[0]=='m') $x.='[0-9]{1,2}[/.-]';
    	 if ($this->formato[0]=='a') $x.='[0-9]{4}[/.-]';
    	 $x.='[0-9]{1,2}[/.-]';
    	 if ($this->formato[2]=='g' || $this->formato[2]=='m') $x.='[0-9]{1,2}';
    	 if ($this->formato[2]=='a') $x.='[0-9]{4}';
    	 return "^$x\$|^[0-9]{8}\$";
	}

	 public function set_value($val){
		if (preg_match('/[0-9]{8}/S',$val)) $val="$val[0]$val[1]/$val[2]$val[3]/$val[4]$val[5]$val[6]$val[7]";
	  	$this->value=str_replace(array('\\','.','-'),array('/','/','/'),$val);
	  	return $this;
	  }

	 public function check_value($val='') {
	 $this->errore='';
	 if ($val=='') return $val;
	 if (!preg_match("#{$this->get_expr()}#S",$val)) $this->errore=" è scorretto, il formato data ammesso è ".$this->get_format();
		 else {
				if (preg_match('/[0-9]{8,8}/S',$val)) $val="$val[0]$val[1]/$val[2]$val[3]/$val[4]$val[5]$val[6]$val[7]";
				$val=preg_split('~[/.-]~',str_replace('\\','-',$val));
				$ok=@checkdate ((int) $val[$this->mask['m']],(int) $val[$this->mask['g']],(int) $val[$this->mask['a']]);
				if (!$ok) $this->errore="Data errata";
				}
	}


	 public function anglo($x) {
	  if ($x=='g') return 'd';
	  if ($x=='m') return 'm';
	  if ($x=='a') return 'Y';
	}





	 public function get_format () {
	 $x=$this->formato[0].'/'.$this->formato[1].'/'.$this->formato[2];
	 $x=str_replace('g','gg',$x);
	 $x=str_replace('m','mm',$x);
	 return str_replace('a','aaaa',$x);
	}


	  public function get_value_formatted($formato='gma',$sep='/') {
	  if (!$this->value) return '';
	  $val=explode("/",$this->value);
	  $x=$mask=array();
	  $mask[0]=$formato[0];
	  $mask[1]=$formato[1];
	  $mask[2]=$formato[2];
	  foreach ($mask as $va) $x[]=sprintf('%02d',$val[$this->mask[$va]]);
	  return implode($sep,$x);
	}

	 public function check ($data) {
		$this->check_value($data);
		return $this->errore;
	}

}