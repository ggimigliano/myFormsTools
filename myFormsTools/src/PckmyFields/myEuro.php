<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myEuro.
 */

namespace Gimi\myFormsTools\PckmyFields;


use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQInputMask;


 
class myEuro extends myFloat  {
  /**
	  *
      * 
	  * @param	 string $nome E' il nome del campo
	  * @param	 string $valore Valore da assegnare come default
	  * @param	 string $classe e' la classe css da utilizzare
	  */

   public function __construct($nome,$valore='', $classe='') {
  	myFloat::__construct($nome,'',$classe);
  	$this->set_decimali(2);
  	$this->set_value($valore);
	$this->set_MyType('MyEuro');
	$this->set_style("text-align","right");
  }

  /*
  function &get_value(){
  	 if($this->separatore=',')
  }
*/
   public function set_mask($stato = true){
  	$this->mask=new myJQInputMask("#{$this->get_id()}");
	return $this;
  }

  /**
	* Restituisce il valore del campo in lettere
   *  @param  string $separatore_decimali indica il simbolo di separazione tra interi e decimali(in forma numerica) di default '/'
   *  @param  float $numero e' il numero da convertire, se omesso si usa il valore corrente dell'oggetto
   *  @return string
   */                  
    public function inlettere ($separatore_decimali='/',$numero='',$null='') {
	 if ($numero==='') $n=(float) trim(str_replace(array(",",'.'),array($this->separatore,$this->separatore),$this->get_value()));
				 else  $n=(float) $numero;
	 if ($n<0) {
			   $segno='-';
			   $n=abs($n);
			  }
	 $v=explode('.',sprintf("%01.2f",$numero,2));
	 return $segno.myInt::inlettere($v[0]).$separatore_decimali.$v[1];
	}

	 public function set_value($x) {
		if($this->mask) {
				if($this->separatore=='.')  $migliaia="'";
						 			   else $migliaia='.';
				$x=trim(str_replace($migliaia,'',$x));
		}
		return parent::set_value($x);
	}

	 public function get_html(){
	    if($this->get_value())
        	    {$v=explode($this->separatore,$this->get_value());
        	     while (strlen($v[1])<2) $v[1]="0{$v[1]}";
        	     $this->set_value("{$v[0]}{$this->separatore}{$v[1]}");
        	    }
		if(!$this->mask) return parent::get_html();
				{$l=strlen(max(intval(abs($this->get_max())), intval(abs($this->get_min()))));
				 $m="{$this->separatore}99";
				 if($this->separatore=='.')  $migliaia="'";
				 						else $migliaia='.';
				 for ($i=0;$i<$l;$i++) {
				                  $punti=0;
				 				  if($i>0 && $i%3==0)
				 				  					{$m=$migliaia.$m;
				 				  					 $punti++;
				 				  					}
				 				  $m="9$m";
				 				 }
				 if($this->get_min()<0) {
				 		$this->set_maxlength($l+$punti+4,$l+$punti+4);
				 		$this->mask->set_mask("~$m",'_',array('~'=>'+ -'));
				 		}
				 	else {
				 		$this->set_maxlength($l+$punti+3,$l+$punti+3);
				 		$this->mask->set_mask($m);
				 		}
				 $this->add_myJQuery($this->mask);
				 return parent::get_html();
				}

	}	
	
	
	function &get_xml(){
	    if($this->get_value())
        	    {$v=explode($this->separatore,$this->get_value());
        	     while (strlen($v[1])<2) $v[1]="0{$v[1]}";
        	     $this->set_value("{$v[0]}{$this->separatore}{$v[1]}");
        	    }
		return parent::get_xml();
	}
	
}