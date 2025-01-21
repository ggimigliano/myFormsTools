<?

/**
 * Contains Gimi\myFormsTools\PckmyFields\myCodiceFiscale.
 */

namespace Gimi\myFormsTools\PckmyFields;
 
use Gimi\myFormsTools\PckmyArrayObject\myArrayObject;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQInputMask;

class myCodiceFiscale extends myText  {
/** @ignore */
protected  $restrizioni_check=array(true,true),
		   $mesi=array(1=>'A',2=>'B',3=>'C',4=>'D',5=>'E',6=>'H',7=>'L',8=>'M',9=>'P',10=>'R',11=>'S',12=>'T'),
		   $omocodie=array('L','M','N','P','Q','R','S','T','U','V'),
		   $omocodibili=array(14,13,12,10,9,7,6) ;


	  /**
	  * 
	  * @param	 string $nome E' il nome del campo
	  * @param	 string $valore Valore da assegnare come default
	  * @param	 string $classe e' la classe css da utilizzare
	  */
  public function __construct ($nome,$valore='',$classe='') {
     parent::__construct($nome,$valore,$classe);
	$this->set_MyType('MyCodiceFiscale');
	$this->set_regExp('^[A-Z|a-z|0-9]*$'," formalmente errato");
	$this->set_MaiMin('maiuscolo');
	$this->set_minlength(16);
	$this->set_maxlength(16);
  }




      /**
	  * Calcola Il codice fiscale, se  i parametri immessi sono errati non restituisce alcun messaggio d'errore
	  *
	  * @param	 string $cognome anche minuscolo o con apici etc.
	  * @param	 string $nome anche minuscolo o con apici etc.
	  * @param	 string $nascita e' la data di nascita nel formato mydate
	  * @param	 'F'|'M' $sesso
	  * @param	 string $comune e' il codice di 4 caratteri del comune
	  * @return  string
	  */

   public function calcolaCF($cognome,$nome,$nascita,$sesso,$comune) {
  	$d=new myDate('',$nascita);
  	$anno=$d->get_parte(2);
  	$mese=$d->get_parte(1);
  	$giorno=$d->get_parte(0);
  	$sesso=$sesso[0];

	$valori=array("ò"=>"o","à"=>"a","è"=>"e","é"=>"e","ì"=>"i","ù"=>"u");
    $cf=$this->cf_cognome($this->formattaNome(strtr($cognome,$valori)));
	$cf=$cf.$this->cf_nome($this->formattaNome(strtr($nome,$valori)));

	$cf=$cf.substr($anno,-2);
	$cf=$cf.$this->mesi[(int) $mese];
	if(strtoupper($sesso)=='F')  $giorno=$giorno+40;
	$cf=$cf.sprintf("%02d",$giorno).strtoupper(substr($comune,0,4));
	return $cf.$this->cin($cf);
	}


	 public function set_mask($stato = true) {
		$myJQMask=new myJQInputMask("#{$this->get_id()}");
		$myJQMask->set_mask("aaaaaa**a**a***a");
		$this->add_myJQuery($myJQMask);
		return $this;
	}

    function &get_value() {Return $this->formattaNome(parent::get_value());}

	 public function set_value($valore) {return  parent::set_value($this->formattaNome($valore));}


   /** @ignore*/
	function &formattaNome($valore) {
	  $valore=strtoupper($valore);
	  for ($i=0;$i<strlen($valore);$i++) if (!preg_match('#^[A-Z0-9]$#S',$valore[$i])) $valore[$i]=' ';
	  $valore=str_replace(' ','',$valore);
	  return $valore;
	}


	/**
	 * Permette di rendere il check_errore piu' o meno restrittivo
	 *
	 * @param boolean $primi11      //verifica formale sui primi 11 caratteri se true si attiva
	 * @param boolean $dodicesimo   //calcolo del cin se true si attiva
	 */
    public function set_restrizioni_check($primi11=true,$dodicesimo=true){
   	 $this->restrizioni_check=array($primi11,$dodicesimo);
   	 return $this;
   }


    public function is_omocodico($val='') {
   	 if(func_num_args()==0) $val=$this->get_value();
   	 foreach($this->omocodibili as $pos)
   	 		if($pos<strlen($val) &&
   	 		   in_array($val[$pos], $this->omocodie)) return true;
   	 return false;
   }



   /**
    * Confronta il codice fiscale con quello calcolato, se il codice fiscale passato e' omocodico la verifica diventa
    * piu' debole
    *
    * @param	 string $cognome anche minuscolo o con apici etc.
    * @param	 string $nome anche minuscolo o con apici etc.
    * @param	 string $nascita e' la data di nascita nel formato mydate
    * @param	 'F'|'M' $sesso
    * @param	 string $comune e' il codice di 4 caratteri del comune
    * @param     string $cf codice fiscale da testare se omesso usa il valore dell'istanza
    * @return  boolean
    */

    public function confronta_calcolatoCF($cognome,$nome,$nascita,$sesso,$comune='',$cf='') {
   	if(!$cf)  $cf=strtoupper($this->get_value());
   	if(trim((string) $comune))
   			   { $N=15; //il CIN non può essere oggetto di verifica omocodibilità
   			     if(!$this->is_omocodico()) $N=16; //ma se non è omocodico ok
   				}
   			else{$comune='H501';
   				 $N=11;
   				 }
    $calcolato=$this->calcolaCF($cognome, $nome, $nascita, $sesso, $comune);
    $N=min($N,strlen($cf));
   	for($i=0;$i<$N;$i++)
   				if($calcolato[$i]!=$cf[$i])
   					{
   					if(!in_array($i, $this->omocodibili) //se non è omocodibile
   							    ||
   					  preg_match('/^[0-9]{1}$/S',$cf[$i]) //oppure nel valore da testare c'è già  un numero
   					            ||
   				 	  !in_array($cf[$i], $this->omocodie) //oppure nel valore da testare non una lettera da omocodia
   					) {
   					   return false;
   					  }
   					}
   	return true;
    }

  /** @ignore*/
    public function get_errore_diviso() {
	 if ($errore=parent::get_errore_diviso()) return $errore;
	 $valore=strtoupper($this->get_value());
	 if ($valore && $this->restrizioni_check[1] && $this->cin(substr($valore,0,15))!=$valore[15]) return " formalmente errato (CIN)";
	 if ($valore && $this->restrizioni_check[0])
	    {
	    //se l'i-esimo carattere non è negli omocodibili e non è una lettera
	    for($i=0;$i<strlen($valore)-1;$i++)
	    	if(!in_array($i, $this->omocodibili)
	             &&
	           !preg_match('#^[A-Z]{1}$#S',$valore[$i])) return " formalmente errato";

	  	$ms=array_flip($this->mesi);
	 	//mese deve avere il nono carattere scelto tra ".implode(',',$this->mesi);
	  	if (!$ms[substr($valore,8,1)])  return " formalmente errato";

	  	//Lettera del codice catastale Z (esteri) oppure da A-L
	  	if (!preg_match('#^[ABCDEFGHILMZ]$#',substr($valore,11,1)))  return " formalmente errato";

	  	//se l'i-esimo carattere è negli omocodibili e non è numerico ne una lettera delle omocodiche errore
	 	for($i=0;$i<strlen($valore)-1;$i++)
	    	if(in_array($i, $this->omocodibili)
	             &&
	           !preg_match('#^[0-9]{1}$#S',$valore[$i])
	 	          &&
	 	       !in_array($valore[$i],$this->omocodie)) return " formalmente errato";

	 	if($this->is_omocodico()) return;
	 	$decund=(int)substr($valore,9,2);
	 	// giorno nascita  deve avere la coppia costituita da decimo ed undicesimo carattere compresa tra 01 e 31 per gli uomini oppure tra 41 e 71 per le donne";
	 	if ($decund<1 || ($decund>31 && $decund<41) || $decund>71) return " formalmente errato";

		$decund=(int)substr($valore,12,3);
	 	// Codice stato >0
	 	if ($decund==0) return " formalmente errato";
	 	}

	}




/** @ignore*/
 private function prime_n_cons($st,$n){
	 $vocali=array('A'=>'A','E'=>'E','I'=>'I','O'=>'O','U'=>'U');
	 $out='';
	 for ($i=0;$i<strlen($st);$i++){
	  							 if(!isset($vocali[$st[$i]]))
											  {
												$out=$out.$st[$i];
												if(strlen($out)==$n) break;
											  }
								  }
	return $out;
 }


/** @ignore*/
 private  function prime_n_vocali($st,$n){
	$vocali=array('A'=>'A','E'=>'E','I'=>'I','O'=>'O','U'=>'U');
	$out='';
    for ($i=0;$i<strlen($st);$i++){
								 if(isset($vocali[$st[$i]]))
												  {
													$out=$out.$st[$i];
													if(strlen($out)==$n) break;
												  }
							  }
	 return $out;
	}


/** @ignore*/
  private function cf_cognome($cognome) {
  	$cognome=$this->formattaNome($cognome);
    $cf=$this->prime_n_cons($cognome,3);
	if(strlen($cf)<3)  $cf=$cf.$this->prime_n_vocali($cognome,3-strlen($cf));
	while(strlen($cf)<3) $cf=$cf.'X';
	return $cf;
 }


/** @ignore*/
 private function cf_nome($nome) {
 	  $nome=$this->formattaNome($nome);
	  $cf=$this->prime_n_cons($nome,4);
	  if(strlen($cf)==4) $cf=$cf[0].$cf[2].$cf[3];
	  if(strlen($cf)<3)  $cf=$cf.$this->prime_n_vocali($nome,3-strlen($cf));
	  while(strlen($cf)<3) $cf=$cf.'X';
 	  return $cf;
 }


/**
 * Restituisce estrae dal codice fiscale dato informazioni come data di nascita, sesso e codice comune
 * un myArrayObject con ('data'=>myDate,'sesso'=>M|F, 'comune'=>codice comune)
 *
 * @param string $cf
 * @return myArrayObject
 */
 public static function decodeCF($cf) {
  	$cf=strtoupper($cf);
  	$mesi=array(1=>'A',2=>'B',3=>'C',4=>'D',5=>'E',6=>'H',7=>'L',8=>'M',9=>'P',10=>'R',11=>'S',12=>'T');
  	$omocodie=array('L','M','N','P','Q','R','S','T','U','V');
	$omodecode=array_flip($omocodie);
	foreach (array(14,13,12,10,9,7,6) as $pos)
	 				 if(isset($omodecode[$cf[$pos]])) $cf[$pos]=$omodecode[$cf[$pos]];

	$mesi=array_flip($mesi);
	$mese=$mesi[$cf[8]];
	$anno='19'.substr($cf,6,2);
	$giorno=substr($cf,9,2);
	if($giorno<=31) $sesso='M';
			   else {$sesso='F';
					 $giorno-=40;
				    }
	$d=new myDate('',"$giorno/$mese/$anno");
	if ($d->errore()) return null;
	return new myArrayObject(array('data'=>$d,'sesso'=>$sesso,'comune'=>substr($cf,11,4)));

   }


 /** @ignore*/
 private function CIN ($cfparziale) {
	 $pari=array('0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','A'=>'0','B'=>'1','C'=>'2','D'=>'3','E'=>'4','F'=>'5','G'=>'6','H'=>'7','I'=>'8','J'=>'9','K'=>'10','L'=>'11','M'=>'12','N'=>'13','O'=>'14','P'=>'15','Q'=>'16','R'=>'17','S'=>'18','T'=>'19','U'=>'20','V'=>'21','W'=>'22','X'=>'23','Y'=>'24','Z'=>'25');
	 $dispari=array('0'=>'1','1'=>'0','2'=>'5','3'=>'7','4'=>'9','5'=>'13','6'=>'15','7'=>'17','8'=>'19','9'=>'21','A'=>'1','B'=>'0','C'=>'5','D'=>'7','E'=>'9','F'=>'13','G'=>'15','H'=>'17','I'=>'19','J'=>'21','K'=>'2','L'=>'4','M'=>'18','N'=>'20','O'=>'11','P'=>'3','Q'=>'6','R'=>'8','S'=>'12','T'=>'14','U'=>'16','V'=>'10','W'=>'22','X'=>'25','Y'=>'24','Z'=>'23');
	 $cin=array('0'=>'A','1'=>'B','2'=>'C','3'=>'D','4'=>'E','5'=>'F','6'=>'G','7'=>'H','8'=>'I','9'=>'J','10'=>'K','11'=>'L','12'=>'M','13'=>'N','14'=>'O','15'=>'P','16'=>'Q','17'=>'R','18'=>'S','19'=>'T','20'=>'U','21'=>'V','22'=>'W','23'=>'X','24'=>'Y','25'=>'Z');
	 $somma=0;
	 for ($i=0;$i<strlen($cfparziale);$i++)	if($i%2!=0) $somma+=$pari[$cfparziale[$i]];
													else $somma+=$dispari[$cfparziale[$i]];
	return $cin[$somma%26];
	 }
}



?>