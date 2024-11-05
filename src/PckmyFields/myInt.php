<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myInt.
 */

namespace Gimi\myFormsTools\PckmyFields;

 

use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQInputMask;


class myInt extends myText  {
/** @ignore */
protected $min,$max,$numerico=true,$zeri=false,$multiplodi=null;

    
    /**
      * 
      * @param	 string $nome E' il nome del campo
      * @param	 string $valore Valore da assegnare come default
      * @param	 string $classe e' la classe css da utilizzare
      */
       public function __construct($nome,$valore='',$classe='') {
    	myText::__construct($nome,$valore,$classe);
    	$this->set_regExp('^[\+|\-]{0,1}[0-9]*$','deve essere un numero intero');
        $this->set_MyType('MyInt');
        $this->min='';
    	$this->max='';
    	$this->set_style("text-align","right");
      }
    
      /**
       * forza un cero numero di zeri davanti al numero inserito,
       * agisce solo dopo verifica errore() quindi set_minlength continua ad essere valido
       * es: se su un campo da 8 cifresi imposta una lunghezza minima di 5 e l'utente ne inserisce 3 da comunque errore
       * se invece ne mette 6 le rimanenti 2 vengono copert con zeri davanti
       */
       public function set_zerofill($stato=true){$this->zeri=$stato;$this->is_numeric(false);}
    
     /**
    	* Setta il minimo valore numericamente accettabile
    	* @param	integer $min
      */
    
       public function set_min($min) {
     	 $this->min=$min;
    	 return $this;
     }
     
     /**
      * Imposta il numero accettabile come multiplo di.. es se 5 accetta valori come 5,10,15,20,25
      * @param int $multiplo
      * @return myInt
      */
      public function set_multiplo_di($multiplo){
         $this->multiplodi;
         return $this;
     }
     
     
     protected function html5Settings($attrs=array()){
         $masked=false;
         foreach ($this->myJQueries as $ist)
                       if ($ist instanceof myJQInputMask) $masked=true; 
         if(!$masked) $attrs['type']='number';
         if($this->multiplodi) $attrs['step']=$this->multiplodi;
         myField::html5Settings($attrs);
     }
    
      public function get_Html(){
        $this->html5Settings();
        if(!$this->is_hidden() && $this->masked && $this->minlength==$this->maxlength) { 
             if($this->min>=0 ) 
                            {
                                $myJQInputMask=new myJQInputMask("#{$this->get_id()}");
                                $myJQInputMask->set_mask(str_repeat('9', $this->maxlength));
                                $this->add_myJQuery($myJQInputMask);
                            }
             elseif($this->max<0)
                            {
                                $myJQInputMask=new myJQInputMask("#{$this->get_id()}");
                                $myJQInputMask->set_mask('-'.str_repeat('9', $this->maxlength-1));
                                $this->add_myJQuery($myJQInputMask);
                            }                    
            elseif($this->min<0 && $this->max>0 )
                            {
                                $myJQInputMask=new myJQInputMask("#{$this->get_id()}");
                                $myJQInputMask->set_mask('+'.str_repeat('9', $this->maxlength-1),array('~'=>'+-0123456789'));
                                $this->add_myJQuery($myJQInputMask);
                            }               
        }                
        return parent::get_Html();                
     }

      /**
    	* Setta il massimo valore numericamente accettabile
        * @param	integer $max
        */
      public function set_max($max) {
     	 $this->max=$max;
     	 $this->set_maxlength(max(strlen((string)$this->max),strlen((string)$this->min)));
    	 return $this;
     }
    
     function &get_value(){
         if (strlen(parent::get_value()) && $this->zeri)
     	 			 $v=sprintf("%0{$this->maxlength}s",parent::get_value());
     	 		else $v=parent::get_value();
     	 return $v;		
     }

 

      public function errore(){
     	if($this->zeri) $this->set_value(parent::get_value());
        $errore=parent::errore();
        if(!$errore && $this->get_notnull() && $this->multiplodi && (intval($this->get_value())%$this->multiplodi)!==0)
                                           return array ("deve essere un multiplo di %1%",$this->multiplodi);
        if($this->zeri) $this->set_value($this->get_value());
        return $errore;
     }

    
     protected function get_errore_diviso_singolo() {
         $errore=parent::get_errore_diviso_singolo();
     	 if ($errore) return $errore;
     	 $x=trim((string) $this->get_value_html());
     	 if (!strlen($x) && !$this->notnull) return;
     	 if (strlen($x) && strlen((string) $this->min) && $x<$this->min) return array('non può essere minore di %1%',$this->min);
     	 if (strlen($x) && strlen((string) $this->max) && $x>$this->max) return array('non può essere maggiore di %1%',$this->max);
     }


      public function get_js_chk($js = ''){
  		$js="valore=trim(''+document.getElementById('{$this->get_id()}'));";
  		if ($this->notnull)
		    $js.="if(strlen(valore)==0) return \"{$this->trasl('non può essere nullo')}\";";

		if ($this->controllo_regExp['exp']) {
		   $this->controllo_regExp['exp']=stripslashes($this->controllo_regExp['exp']);
		   $js.="if(valore!='' && !valore.search(/{$this->controllo_regExp['exp']}/)) return \"{$this->controllo_regExp['mess']}\";";
		}


		If (strlen((string) $this->max)>0)
		   	$js.="if (valore!='' && parseFloat(valore)>{$this->max}) return \"{$this->trasl('non può essere maggiore di %1%',array('%1%'=>$this->max))}\";";

		if (strlen((string) $this->min)>0)
			$js.="if (valore!='' && parseFloat(valore)<{$this->min}) return \"{$this->trasl('non può essere minore di %1%',array('%1%'=>$this->min))}\";";

		if (strlen((string) $this->maxlength)>0)
		   	$js.="if (valore!='' && strlen(valore)>{$this->maxlength}) return \"{$this->trasl('non può contenere più di %1% caratteri',array('%1%'=>$this->maxlength))}\";";

		if (strlen((string) $this->minlength)>0)
			$js.="if (valore!='' && strlen(valore)<{$this->minlength}) return \"{$this->trasl('deve contenere almeno %1% caratteri',array('%1%'=>$this->minlength))}\";";
		return $js;
  	}

    
    
    
    /** @ignore*/
      public function lettere_decine($n){
    	 $numeri=array('zero','uno','due','tre','quattro','cinque','sei','sette',
    					'otto','nove','dieci','undici','dodici','tredici','quattordici',
    					'quindici','sedici','diciassette','diciotto','diciannove',
    					  20=>'venti',30=>'trenta',40=>'quaranta',50=>'cinquanta',
    					 60=>'sessanta',70=>'settanta',80=>'ottanta',90=>'novanta');
    	 $vocali=array('a'=>1,'e'=>1,'i'=>1,'o'=>1,'u'=>1);
    	 if($numeri[$n]!=null) return $numeri[$n];
    					 else  { //20<=$n<=99
    							  $st=$n.'';
    							  $unita=$numeri[$st[1]];
    							  $decine=$numeri[10*$st[0]];
    							  if($vocali[$unita[0]]==1) $decine=substr($decine,0,strlen($decine)-1);
    							  return $decine.$unita;
    						  }
      }
    
     /** @ignore*/
       public function lettere_centinaia ($n){
    	 $numeri=array(100=>'cento',200=>'duecento',300=>'trecento',400=>'quattrocento',500=>'cinquecento',
    						600=>'seicento',700=>'settecento',800=>'ottocento',900=>'novecento');
    	 $vocali=array('o'=>1);
    	 if($numeri[$n]!=null) return $numeri[$n];
    			elseif($n<100)  return self::lettere_decine($n);
    					  else {$st=$n.'';
    						  $centinaia=$numeri[$st[0]*100];
    						  $decine=self::lettere_decine(substr($st,1)+0);
    						  if($vocali[$decine[0]]==1) $centinaia=substr($centinaia,0,strlen($centinaia)-1);
    										 return $centinaia.$decine;
    							 }
      }
    
      /** @ignore*/
       public function lettere_n ($n) {
    	 $grandezze=array('|0|1|2|3|'=>1,
    					'|4|5|6|'=>array('cifre'=>3,'singolare'=>'mille','plurale'=>'mila'),
    					'|7|8|9|'=>array('cifre'=>6,'singolare'=>'unmilione','plurale'=>'milioni'),
    					'|9|10|11|'=>array('cifre'=>9,'singolare'=>'unmiliardo','plurale'=>'miliardi')
    					);
    	 foreach ($grandezze as $k=>$v) if (strpos($k,'|'.strlen($n).'|')!==false) break;
    	 if(!is_array($v)) return self::lettere_centinaia($n);
    				 else {  $st=$n.'';
    						 $primaParte=substr($st,0,strlen($st)-$v['cifre'])+0;
    						 if($primaParte==1) $risultato=$v['singolare'];
    								      else  $risultato=myInt::lettere_n($primaParte).$v['plurale'];
    						 $secondaParte=substr($st,-$v['cifre'])+0;
    						 if($secondaParte>0) $risultato.=myInt::lettere_n($secondaParte);
    						 return $risultato;
    					}
     }
    
    
     /**
    	* Restituisce il valore del campo in lettere
     *  @param  int $numero e' il numero da convertire, se omesso si usa il valore corrente dell'oggetto
     *  @return string
     */
        public function inlettere ($numero='') {
    	 if ($numero==='') $n=(int) str_replace(',','.',$this->get_value());
    				 else  $n=(int) $numero;
    	 if ($n<0) {
    			   $segno='-';
    			   $n=abs($n);
    			  }
    	 $v=explode('.',sprintf('%01.2f',$n));
    	 return $segno.self::lettere_n($v[0]);
    	}
    
    
      /**
         * Converts a number to its roman numeral representation
         *
         * @param  integer $num         An integer between 0 and 3999
         *                              inclusive that should be converted
         *                              to a roman numeral integers higher than
         *                              3999 are supported from version 0.1.2
         *           Note:
         *           For an accurate result the integer shouldn't be higher
         *           than 5 999 999. Higher integers are still converted but
         *           they do not reflect an historically correct Roman Numeral.
         *
         * @param  bool    $html        Enable html overscore required for
         *                              integers over 3999. default true
         * @return string  $roman The corresponding roman numeral
         */
         public function inRomani($num='',$html = true)
        {   if ($num==='') $num=$this->get_value();
        	if ($num < 0)   return '';
            $num = (int) $num;
    
            $conv = array( 10 => array('X', 'C', 'M'), 5 => array('V', 'L', 'D'), 1 => array('I', 'X', 'C') );
            $roman = '';
    
            $digit = (int) ($num / 1000);
            $num  -= $digit * 1000;
            while ($digit > 0) {
                				$roman .= 'M';
                				$digit--;
            					}
    
            for ($i = 2; $i >= 0; $i--) {
                $power = pow(10, $i);
                $digit = (int) ($num / $power);
                $num -= $digit * $power;
                if (($digit == 9) || ($digit == 4))  $roman .= $conv[1][$i] . $conv[$digit+1][$i];
                  else {
                   	 	if ($digit >= 5)
                   	 		{
                        	$roman .= $conv[5][$i];
                        	$digit -= 5;
                    		}
                     	while ($digit > 0)
                     		{
                        	$roman .= $conv[1][$i];
                        	$digit--;
                    		}
                		}
            }
    
            /*
             * Preparing the conversion of big integers over 3999.
             * One of the systems used by the Romans  to represent 4000 and
             * bigger numbers was to add an overscore on the numerals.
             * Because of the non ansi equivalent if the html output option
             * is true we will return the overline in the html code if false
             * we will return a _ to represent the overscore to convert from
             * numeral to arabic we will always expect the _ as a
             * representation of the html overscore.
             */
            if ($html == true) {
                $over = '<span style="text-decoration:overline;">';
                $overe = '</span>';
            } elseif ($html == false) {
                $over = '_';
                $overe = '';
            }
    
            /*
             * Replacing the previously produced multiple MM with the
             * relevant numeral e.g. for 1 000 000 the roman numeral is _M
             * (overscore on the M) for 900 000 is _C_M (overscore on both
             * the C and the M) We initially set the replace to AFS which
             * will be later replaced with the M.
             *
             * 500 000 is   _D (overscore D) in Roman Numeral
             * 400 000 is _C_D (overscore on both C and D) in Roman Numeral
             * 100 000 is   _C (overscore C) in Roman Numeral
             *  90 000 is _X_C (overscore on both X and C) in Roman Numeral
             *  50 000 is   _L (overscore L) in Roman Numeral
             *  40 000 is _X_L (overscore on both X and L) in Roman Numeral
             *  10 000 is   _X (overscore X) in Roman Numeral
             *   5 000 is   _V (overscore V) in Roman Numeral
             *   4 000 is M _V (overscore on the V only) in Roman Numeral
             *
             * For an accurate result the integer shouldn't be higher then
             * 5 999 999. Higher integers are still converted but they do not
             * reflect an historically correct Roman Numeral.
             */
            $roman = str_replace(str_repeat('M', 1000), $over.'AFS'.$overe, $roman);
            $roman = str_replace(str_repeat('M', 900),  $over.'C'.$overe.$over.'AFS'.$overe, $roman);
            $roman = str_replace(str_repeat('M', 500),  $over.'D'.$overe, $roman);
            $roman = str_replace(str_repeat('M', 400),  $over.'C'.$overe.$over.'D'.$overe, $roman);
            $roman = str_replace(str_repeat('M', 100),  $over.'C'.$overe, $roman);
            $roman = str_replace(str_repeat('M', 90),   $over.'X'.$overe.$over.'C'.$overe, $roman);
            $roman = str_replace(str_repeat('M', 50),   $over.'L'.$overe, $roman);
            $roman = str_replace(str_repeat('M', 40),   $over.'X'.$overe.$over.'L'.$overe, $roman);
            $roman = str_replace(str_repeat('M', 10),   $over.'X'.$overe, $roman);
            $roman = str_replace(str_repeat('M', 5),    $over.'V'.$overe, $roman);
            $roman = str_replace(str_repeat('M', 4),'M'.$over.'V'.$overe, $roman);
    
            /*
             * Replacing AFS with M used in both 1 000 000
             * and 900 000
             */
            $roman = str_replace('AFS', 'M', $roman);
    
            /*
             * Make HTML output more readable by combining span tags
             * where possible.
             */
            if ($html == true)  $roman = str_replace($overe.$over, '', $roman);
            return $roman;
        }
    
    
    
      /**
       * Restituisce il valore dell'oggetto in forma ordinale: primo, secondo ..
       *
       * @param int $numero	        se omesso restituisce l'ordinale del valore memorizzato nell'istanza
       * @param boolean $genere     se true il genere e' maschile (es. primo) altrimenti femminile (prima)
       * @param boolean $singolare  se true e' singolare (es. primo,prima) altrimenti plurale (primi,prime)
       * @return string
       */
       public static function ordinale ($numero=null,$genere=true,$singolare=true) {
      	 $numero=(int) $numero;
    
      	 if($numero<=0) return '';
    
      	 $numeri=array(1=>'prim','second','terz','quart','quint','sest','settim','ottav','non','decim');
      	 if ($numero<=10)  $ordinale=$numeri[$numero];
      	 			 else {
      	 			 	   $ordinale=myInt::inlettere($numero);
      	 			       $numero=(string) $numero;
      	 			       if ($ordinale[strlen($ordinale)-2]!='e') $ordinale=substr($ordinale,0,-1);
      	 			       if ($numero[strlen($numero)-1]=='3') $ordinale.='e';
      	 			 	   $ordinale.='esim';
      	 			     }
      	 if ($genere)
      	 		{
      	 		 if($singolare)  $ordinale.='o';
      	 		 		  else   $ordinale.='i';
      	 		}
      	 	else {
      	 		if($singolare) $ordinale.='a';
      	 		 	    else   $ordinale.='e';
      	 		}
      	 return $ordinale;
      }


}