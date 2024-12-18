<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myField.
 */

namespace Gimi\myFormsTools\PckmyFields;


use Gimi\myFormsTools\myDizionario;
use Gimi\myFormsTools\PckmyForms\myForm;
use Gimi\myFormsTools\PckmyJQuery\myJQuery;
use Gimi\myFormsTools\PckmyPlugins\mySecurizer;
use Gimi\myFormsTools\PckmyUtils\myCharset;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQDatepicker;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQInputMask;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQTimePicker;

class myField extends myTag{
/**
 @ignore */
public $Richiede_tag_label=true, $Prevede_label=true,$con_js=true,$usaDIV=false,$myFields   ;
/**
 * @ignore
 */protected
     $html5done=false,$notnull=null, $controllo_regExp=null, $maxlength,$minlength,$min,$max,
	 $MyType='',$encode=array('errore'=>null,'label'=>''),
	 $Formula='', $autotab=false, $javascript=false,
	 $Metodo_ridefinito,
	 $myJQueries=array(),$secure,
	 $traduzioni,$dizionario,$numerico=false,$notrasl,$xml=array('tag'=>null,'case'=>'','header'=>''),$abilitaXML=true,$facoltativoXML=false;
/**
 * @ignore
 */protected static $dizionari=array(),$lingua='it',$usaHTML5=false;

/**
 * @ignore
 */private $myFormContainer=array('myForm'=>null,'campo'=>'');

/**
 * @ignore
 */ public function offsetSet($offset, $value) {
 		if (!$offset)  return $this->set_value($value);
 				  else return $this->set_attributo($offset, $value);
    }

/**
 * @ignore
 */public function offsetGet($offset) {
	  	if (!$offset)  return $this->get_value();
 				  else return $this->attributi[$offset];
    }
    
    static function &changeCase(&$val,$case='l') {
        if(is_callable('mb_strtolower') && strtoupper(self::get_charset())!='ISO-8859-1') return ($case=='l'?mb_strtolower($val):mb_strtoupper($val));
        return ($case=='l'?strtolower($val):strtoupper($val)); 
    }

     public function set_added($campo,$form) {
        $this->myFormContainer['campo']=$campo;
        $this->myFormContainer['myForm']=$form;
    }
    
    
     public function set_security(mySecurizer $s){
        if($this->secure!==false) $this->secure=$s;
        return $this;
    }
    
     public function set_never_security(){
        if( $this->secure)  $this->secure->unset_key($this->get_name());
        $this->secure=false;
        return $this;
    }
    
    static function &charset_encode($testo,&$testoaddr=null) {
        static $done=array();
        if($testoaddr!==null) $testo=&$testoaddr;
        if(self::get_charset()!='UTF-8') return $testo;
        if(in_array($testo, $done))      return $testo;
        $enc=iconv("ISO-8859-1", self::get_charset()."//TRANSLIT", $testo);
        $done[]=$enc;
        return $enc;
    }
    
    /**
     * 
     * @param boolean $status se true si aggiungono attrributi din vincoli di non null ecc dell'HTML5
     */
 static function usaHTML5($status=true){
     self::$usaHTML5=$status;
 }   
    
    /**
     * @ignore
     */
 protected function html5Settings($attrs=array()){
      if(!self::$usaHTML5) return;
      if($this->get_notnull()) $attrs['required']='required';
      if($this->maxlength!==null && $this->maxlength!=='') $attrs['maxlength']=$this->maxlength;
      if($this->minlength!==null && $this->minlength!=='') $attrs['minlength']=$this->minlength;
      if($this->min!==null && $this->min!=='') $attrs['min']=$this->min;
      if($this->max!==null && $this->max!=='') $attrs['max']=$this->max;
      if(is_array($this->controllo_regExp)) $attrs['pattern']=$this->controllo_regExp['exp'];
      if($attrs && !$this->html5done) { 
                   foreach ($attrs as $k=>$v) $jq.="document.getElementById('{$this->get_id()}').$k=\"".str_replace('"',"+'\"'+",$v)."\";";
                   //$j->add_code($jq.";");
                   $this->html5done="<script type='text/javascript'>
                                            //<!--        
                                            try { $jq } catch(e) {};
                                            //-->
                                      </script>";
                 }
  }

     /**
     * Invocato quando questo campo viene aggiunto ad una myForm
     * con add_campo, eventualmente da ridefinire
     * 
     * 
     * @param string $nome
     * @param myForm $myForm
     */
     public function __onAdded($nome,$myForm){

    }
    

    /**
     * Restituisce il puntatore all'eventuale myForm nel quale  e'  contenuto il campo
     *
     * @return myForm
     */
    public function get_myFormContainer() {
        return $this->myFormContainer['myForm'];
    }
    
    /**
     * Restituisce il nome con cui il campo è inserito nella myForm
     */
    public function get_id_in_myFormContainer() {
         return $this->myFormContainer['campo'];
    }

  /**
	 *
	 * 
	* @param	 string $nome E' il nome del campo
	* @param	 array|string $valore Valore da assegnare come defaulto array (non assoc) di valori nel caso di campo multivalori
	*/
   public function __construct($nome='',$valore='')
	{self::$id_istanza++;
     $this->id_istance=self::$id_istanza;
     static $MyField;
	 $this->myFields= &$MyField;
	 $this->dizionario=is_array($MyField) && key_exists('dizionari',$MyField)?$MyField['dizionari']:null;
	 self::AbsPath();
	
	 if (self::$autorecode && !empty($_POST) && !self::$autorecoded) self::recodePOST();
	 if(self::$lingua!='it') $this->set_lingua(self::$lingua);
	 if(!isset($this->myFields['static'])) $this->myFields['static']=array();
	 if(!isset($this->myFields['campo__istanze'])) $this->myFields['campo__istanze']=array();
	 if(!isset($this->myFields['myForms'])) $this->myFields['myForms']=array();
  //  if ($nome=='LIVELLO_AUTH') {echo "<pre>$nome<br />";print_r($this->myFields['campo__istanze']);		  }
     if(!isset($this->myFields['names'])) $this->myFields['names']=0;
     if(!$nome)  $nome='myField_'.(++$this->myFields['names']);
	 if (is_array($nome))  $this->set_attributo($nome);
					  else {$this->set_name($nome);
							$this->set_value($valore);
						  }
	//  if ($nome=='LIVELLO_AUTH') {print_r($this->myFields['campo__istanze']);								echo "<hr>";}
	}


 	/** @ignore*/
	 public static function AbsPath(){
	if (!isset($_SERVER['myFields']['PathSito']) || !$_SERVER['myFields']['PathSito'])
		{
	    $_SERVER['myFields']['PathSito']=str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']);
	    if(substr($_SERVER['DOCUMENT_ROOT'],-1)!='/') $_SERVER['myFields']['PathSito'].='/';
		$_SERVER['myFields']['MyFormsPath']=str_replace('//','/',str_ireplace($_SERVER['myFields']['PathSito'],'',str_replace('\\','/',dirname(__FILE__)))).'/';
		}
	}


	/**
	 * Restituisce tag per il carivcamento di js di utilità
	 *
	 */
 	 public function get_js_common($conTag=true) {
 	    $js="try {if (typeof myEventManager== 'undefined') throw '';} catch (err){document.write( '<'+'script type=\"text\/javascript\" src=\"".str_replace('/','/',"/".self::get_MyFormsPath()."js/common.min.js")."\">'+'<\/script'+'>');}";
 	    if(!$conTag) return $js;
 	    
 		return "<script type=\"text/javascript\">
 		             //<!--
 		             $js
 		             //-->
 		             </script>
 		";
 		//	return "<script  type=\"text/javascript\" src=\"/".self::get_MyFormsPath()."js/common.min.js\"></script>";
 	}


	 public function set_abilitazione_js($stato=false) {
		$this->con_js=$stato;
		return $this;
	}

/**
	* Imposta i percorsi da far usare al package, ove non riesca a reperirli in automatico
 * @param	 string $PathSito E' la path assoluta del sito es "c:\wwwwpublic\htdocs"
 * @param	 string $MyFormsPath la path assoluta delle myforms rispetto alla root del sito esl "/librerie/myform"
 */
  public static function setAbsPath($PathSito='',$MyFormsPath='') {
	 $MyFormsPath=preg_replace('|^/|','',$MyFormsPath);
	 if($PathSito) $_SERVER['myFields']['PathSito']=str_replace('//','/',"$PathSito/");
	 if($MyFormsPath) $_SERVER['myFields']['MyFormsPath']=$MyFormsPath.'/';
  }

  
 

  public static function get_PathSito() {
	 myField::AbsPath();
	 return $_SERVER['myFields']['PathSito'];
  }

  /**
   * Restituisce un booleano che dice se contiene piu' valori contemporaneamente
   * @return boolean
   */
   public function isMultiple(){
  	return false;
  }

  public static function get_MyFormsPath() {
	 myField::AbsPath();
	 return $_SERVER['myFields']['MyFormsPath'];
  }

  /**
   * Dice se e' numerico questo campo
   *
   * @param boolean $numerico se omesso restituisce il fatto che sia numerico, se valorizzato setta la caratteristica
   * @return boolean
   */
   public function is_numeric($numerico='')
  		{if ($numerico!=='') $this->numerico=$numerico;
  		 return $this->numerico;
  		}


  /**
   * Imposta una lingua da usare (carica dizionario predefinito dalle myForms)
   *
   * @param string(2) $lingua codice lingua
   */
   
   public function set_lingua($lingua='en',$logErrori=false) {
  	 if (!$lingua) return ;
  	 if($lingua!=self::$lingua)   self::$dizionari=$this->dizionario=array();
  	 self::$lingua=$lingua;
  	 $this->dizionario[$lingua]=null;
  	 $this->add_dizionario(new myDizionario($lingua),$lingua);
  	 $this->dizionario[$lingua]->log_errori($logErrori);
  	 return $this;
  }

 /**
  * Aggiunge un dizionario alternativo (da usare solo dopo myForm::set_lingua)
  * @throws \Exception se non usato dopo @see myForm::set_lingua()
  * @param myDizionario $dizionario
  * @param string(2) $lingua eventuale codice lingua
  */
   public function add_dizionario($dizionario,$lingua='') {
      if( $dizionario instanceof myDizionario) {
                if ($this->dizionario) {
  							           if (!$lingua) $this->dizionario[spl_object_hash($dizionario)]=$dizionario;
  								                else $this->dizionario[$lingua]=$dizionario;
  								        self::$dizionari=array_merge(self::$dizionari,$this->dizionario);
  				              		   }
  					else throw new \Exception("add_dizionario solo dopo set_lingua");
                }
 
	return $this;
  }
  
  
   public function get_dizionario() {
      return array_shift($this->get_dizionari());
  }
  		
  
  /**
   *
   * Restituisce l'elenco di tutti i dizionari attivi.
   * @return array
   */
   public function get_dizionari() {
      return self::$dizionari;
      if(!$this->dizionario) $this->dizionario=self::$dizionari;
    	 if (!$this->dizionario)  return array();
    	        elseif (!is_array($this->dizionario)) return array($this->dizionario);
    	               else return $this->dizionario;
  }



  
  /** @ignore */
    public function trasl($messaggio,$parole='',$encode=false) {
     if (!$this->get_dizionari() || $this->notrasl) {
  	 						   if (!$parole) return $messaggio;
  	 									else return strtr($messaggio,$parole);
  	 						 }
	 
	 foreach ($this->get_dizionari() as $dizionario)
          	 	if($dizionario instanceof myDizionario)
          	 	       {  $esito=null;
	                      $log=$dizionario->log_errori(false);
          	 	          $tradotto=$dizionario->trasl($messaggio,$parole,$esito);
          	 	          $dizionario->log_errori($log);
        	              if($esito===-1) break;
          	 			  if ($esito && $tradotto) return $tradotto;
          	 			  $dizionario->trasl($messaggio,$parole,$esito);
          	 			}
    
	return $messaggio;
  }

  


  /**
	* Restituisce un messaggio se l'attributo value non soddisfa condizioni di notnull,maxlength,minlength
	  *  @return string
	  */
	  public function Errore() {
		$errore=$this->get_errore_diviso();
		if (!is_array($errore)) return $this->trasl($errore);
						  else { $mess=array_shift($errore);
						         $messaggi=array();
						  		 foreach($errore as $i=>&$v) $messaggi['%'.($i+1).'%']=&$v;
						  		 return $this->trasl($mess,$messaggi);
							   }
  	}


  	 public function get_js_chk($js=''){
  	    if(!$js) $js="
                  		valore=trim(''+document.getElementById('{$this->get_id()}').value);
                  		";
  //		$js.="alert(valore+'>');";
  		if ($this->notnull)
		       $js.="if(strlen(valore)==0) return \"{$this->trasl('non può essere nullo')}\";\n";
		  else $js.="if(strlen(valore)==0)  return ;\n";

		if (isset($this->controllo_regExp['exp'])) {
		   $exp=$this->controllo_regExp['exp'];
		   $js.="if(valore!='' && valore.match(/{$exp}/)==null) return \"{$this->trasl($this->controllo_regExp['mess'])}\";\n";
		}

		If (strlen(intval($this->max))>0)
		   	$js.="if (valore!='' && valore>\"{$this->max}\") return \"{$this->trasl('non può essere maggiore di %1%',array('%1%'=>$this->max))}\";\n";

		if (strlen(intval($this->min))>0)
			$js.="if (valore!='' && valore<\"{$this->min}\") return \"{$this->trasl('non può essere minore di %1%',array('%1%'=>$this->min))}\";\n";

		if (strlen(intval($this->maxlength))>0)
		   	$js.="if (valore!='' && strlen(valore)>{$this->maxlength}) return \"{$this->trasl('non può contenere più di %1% caratteri',array('%1%'=>$this->maxlength))}\";\n";

		if (strlen(intval($this->minlength))>0)
			$js.="if (valore!='' && strlen(valore)<{$this->minlength}) return \"{$this->trasl('deve contenere almeno %1% caratteri',array('%1%'=>$this->minlength))}\";\n";

		//$js.="alert(valore+'ok');";
		return $js;
  	}


  	 public function get_errore_diviso(){
  		return $this->get_errore_diviso_singolo();
  	}

  	/*
  	 * @ignore
  	 */
  	protected function get_errore_diviso_singolo() {
  		$valore=$this->get_value();

  		$nullo=false;
  		if(is_array($valore)) $nullo= ($valore==array()|| $valore==array(''));
  		  		   elseif(is_object($valore)) return '';
  		  		        else $nullo= trim((string) $valore)==='';

  		if (!$this->notnull && $nullo) return '';
  		if ($this->notnull  && $nullo) return 'non può essere nullo';
  	

		if (is_scalar($valore) && !$nullo){
			if(isset($this->controllo_regExp['exp']) && !preg_match("~{$this->controllo_regExp['exp']}~",$valore)) return $this->controllo_regExp['mess'];
			If ($this->maxlength && strlen($valore)>$this->maxlength) return array('non può contenere più di %1% caratteri',$this->maxlength);
			if ($valore && $this->minlength && strlen($valore)<$this->minlength) return array('deve contenere almeno %1% caratteri',$this->minlength);
		}
  	}




  /**
   *  restituisce true se l'oggetto e' sottoclasse del parametro
   *
   *  @param string $classe nome della classe
   *  @param boolean $oppure_e se true restituisce true anche se l'oggetto e' istanza di $classe
   *  @return boolean
   */
   public function Estende($classe,$oppure_e=false) {
     $classe="Gimi\\myFormsTools\\PckmyFields\\$classe";
     if($oppure_e && $this instanceof $classe) return true;
  	 return is_subclass_of($this,$classe);
  }


  /**@ignore**/
  protected function jsAutotab() {
  	if (!$this->autotab || $this->myFields['static']['autotab']) return '';
  	$this->myFields['static']['autotab']=1;
  	return '<script type="text/javascript">
//<!--
document.onkeyup = checkKeycode;
var keycode;
//-->
</script>';
  }


  /**
	* Setta la autotabulazione del campo*/
   public function set_autotab () {
	 $this->autotab=true;
 	 return $this;
  }

  /** @ignore*/
   public function my_js_documentwrite($html,$quote=true) {
  	  $tags=explode('>',$html);
  	  $out=$testo='';
  	  for ($i=0;$i<count($tags)-1;$i++) {
  	  	  $testo.=$tags[$i].'>';
  	  	  if(strlen($testo)>50)  {$out.='document.write("'.($quote?str_replace('"','\\"',$testo):$testo)."\");\n";
  	  	  						  $testo='';
  	  	  						 }
      	  }
  	  $testo.=$tags[count($tags)-1];
  	  return $out.'document.write("'.($quote?str_replace('"','\\"',$testo):$testo)."\");\n";
  	}



  /**
	* Restituisce il nome del tipo del campo
  *
  * @return string
  */
  public function get_MyType() {return $this->MyType; }


  /** @ignore*/
  public function set_MyType($nome) {  $nome[0]=strtolower($nome[0]);
   										$this->MyType=$nome; 
   									   }


  /** @ignore*/
  public function get_value_html() {
  $valore=$this->get_value();
  if (is_array($valore))	return count($valore);
  if ($valore==='' || is_array($valore)) return '';
  return myTag::htmlentities(myTag::nonhtmlentities($valore));
}



  public function get_formula() {
 	return trim((string) $this->Formula);
 }

  public function set_formula($formula) {
	if (trim((string) $formula)!=='') $this->Formula=$formula;
	return $this;
 }

  public function unset_formula() {
     $this->Formula='';
     return $this;
 }

/*
  *
  * @return string
  */
 function &get_value() {
 	 return $this->attributi['value'];
 }


 /**
	* Restituisce l'attributo 'name'
	* @return string
  */
  public function get_name() {if(isset($this->attributi['name']))  return $this->attributi['name']; }



 /**
  *
  * Restituisce il valore del minimo impostato
  * @return int
  */
  public function get_min(){return $this->min;}

 /**
  *
  * Restituisce il valore del massimo impostato
  * @return int
  */
  public function get_max(){return $this->max;}

   /**
	* Elimina lo showonly
	**/
   public function unset_showonly() {
  	/*$this->Prevede_label=true;
  	$this->Richiede_tag_label=true;*/
    if($this->is_hidden()) return;  
  	unset($this->Metodo_ridefinito['get_Html']['metodo']);
  	unset($this->Metodo_ridefinito['get_Html']['parametri']);
  	return $this;
  }


  /**
	* Imposta lo showonly
	* @param  boolean	 $campo_nascosto se true si aggiunge un campo nascosto con il valore del campo se falso si scrive solo la descrizione (default e' true)
	* @param  array      $attributi array associativo con eventuali attributi class,id,style per lo span con il campo visualizzato 
   */
   public function set_showonly($campo_nascosto=true,$attributi=array()) {
  	/*$this->Prevede_label=true;
  	$this->Richiede_tag_label=true;*/
    if($this->is_hidden()) return;  
  	$this->Metodo_ridefinito['get_Html']['metodo']='_get_html_show';
  	$this->Metodo_ridefinito['get_Html']['parametri']=array('campo'=>$campo_nascosto,'attributi'=>$attributi);
  	return $this;
  }

  /**
   * Stato showonly
   *
   * @return '0'|'1'|'2' = non attivo | campo nascosto | senza campo nascosto
   */

   public function get_showonly() {
      if($this->Metodo_ridefinito && $this->Metodo_ridefinito['get_Html']) {
                              	    if (isset($this->Metodo_ridefinito['get_Html']['parametri']['campo'])) return 1;
                              		if ($this->Metodo_ridefinito['get_Html']<>'') return 2;
                                  }
      return 0;
  }


   /**
	* Disattiva lo set_hidden
	**/
   public function unset_hidden() {
   if (isset($this->Metodo_ridefinito['get_Html']['metodo']))
  			{
  			$this->Prevede_label=$this->Metodo_ridefinito['get_Html']['set_hidden'][0];
  			$this->Richiede_tag_label=$this->Metodo_ridefinito['get_Html']['set_hidden'][1];
  			}
  	unset($this->Metodo_ridefinito['get_Html']['metodo']);
  	unset($this->Metodo_ridefinito['get_Html']['parametri']);
  	$this->Prevede_label=true;
  	$this->Richiede_tag_label=true;
  	return $this;
  }


  /**
	* Imposta il campo come nescosto
  */
   public function set_hidden() {
    $this->Metodo_ridefinito['get_Html']['metodo']='_get_html_hidden';
  	$this->Metodo_ridefinito['get_Html']['set_hidden']=array($this->Prevede_label, $this->Richiede_tag_label);
  	$this->Metodo_ridefinito['get_Html']['parametri']=array();
  	$this->Prevede_label=false;
  	$this->Richiede_tag_label=false;
  
	return $this;
  }


  /**
   * Restituisce true se hidden abilitato
   *
   * @return boolean
   */
   public function is_hidden() {
      return (isset($this->Metodo_ridefinito['get_Html']['metodo']) &&
              $this->Metodo_ridefinito['get_Html']['metodo']=='_get_html_hidden') ||
  		  // $this->get_showonly()===1 || showonly non è nascosto
  		   $this->Estende('myHidden',true);
  }

  /** @ignore */
   protected function _get_html_hidden($pars){
     if (!isset($this->attributi['disabled'])){
   	 	$x=new myHidden($this->get_name(),$this->get_value());
   	 	if($this->secure) $x->set_security($this->secure);
   	 	$x->set_attributo('id',$this->get_id());
   	 	return $x->get_html();
   	 }
   }



	/**
	 * abilita/disabilita l'xml di quel campo
	 *
	 * @param boolean abilita se true l'xml si vede se false non viene mostrato
	 */
    public function set_abilita_xml($abilita=false){
  	  $this->abilitaXML=$abilita;
  	  return $this;
   }


	/**
	 * Imposta la facoltatività del tag, se facoltativo @see get_xml non restituirà nulla
	 * altrimenti si visualizza il tag vuoto <tag />
	 *
	 * @param boolean facoltativo  se true l'xml si vede se false non viene mostrato
	 */
    public function set_xml_facoltativo($facoltativo=true){
  	  $this->facoltativoXML=$facoltativo;
  	  return $this;
   }


 	/**
 	 * restituisce il nome del tag XML corrente
 	 * @param boolean $complex se false restituisce il tag; true restituisce un array associatico con chiavi 'tag'=> 'ridefinito'=>boolean che dice se impostato usando @see set_parametri_xml
 	 * @return string
 	 */
	public function get_xml_tag($complex=false){
		$tag=($this->xml['']?$this->xml['']:$this->get_name());
   		$tag=explode('[',$tag,2);
		$tag=$tag[0];
		if(!$complex) return $tag;
				else  return array('tag'=>$tag,'ridefinito'=>$this->xml['']!='');
	}



	/**
	 * Imposta eventuali variazioni sull'XML prodotto da @see get_xml
	 *
	 * @param string $tagName e' il tag name che verrà usato nell'xml al posto del nome del campo (se omesso si usa il nome del campo)
	 * @param array $attributi è l'array associativo che indica la trascodifica degli attributi da visualizzare, la chiave è il nome dell'attributo HTNL il valore e' l'attributo XML da usare
	 * 							puo' anche non essere associativo, in quel caso indica gli attributi da visualizzare
	 */
	public function set_parametri_xml($tagName='',$attributi=array()){
		if ($tagName) $this->xml['']=trim((string) $tagName);

		foreach ($attributi as $k=>&$v)
					if(is_int($k)) $this->xml[$v]=$v;
							  elseif(strlen($k)>0) $this->xml[$k]=$v;
		return $this;
	}


	/**
	 * Funzione estendibile per modificare il valore prodotto dall'XML m di default e' un alias di @see get_html
	 *
	 * @return string
	 */
   function &get_xml_value()	{
   	  return $this->get_value();
   }


  /**
	* Restituisce il campo in XML iin UTF-8 pronto per la visualizzazione, di default gli attributi non vengono presi in considerazione ed il nome del campo diventa il tag che lo racchiude mentre il contenuto e' il valore restituito da @see get_xml per personalizzare gli attributi usare @see set_attributi_xml
   *  @return string
   */
   function &get_xml() {
   	    if(!$this->abilitaXML) return;
   	    if(is_array($this->xml))
   	    	foreach ($this->xml as $nome=>$usare)
   	    		if ($nome && trim((string) $this->parametri[$nome])) $parametri.=' '.strtolower($usare)."=\"".(\Gimi\myFormsTools\PckmyUtils\PHP8::htmlspecialchars($this->parametri[$nome],null,'UTF-8')).'"';

   		$tag=$this->get_xml_tag(false);
        if(strlen($v=$this->get_xml_value()))
        								{if(strpos($v,'&')===false && strpos($v,'<')===false)
        											 return "<$tag$parametri>".myCharset::utf8_encode($v)."</$tag>";
        										else return "<$tag$parametri>\n<![CDATA[\n".myCharset::utf8_encode($v)."\n]]>\n</$tag>";

        								}
   								 	elseif(!$this->facoltativoXML) return  "<$tag$parametri />";

  }

  /** @ignore */
  protected function _get_html_show_obj($pars,$tag='span',$content=null){
                 
    $t=new myTag($tag,$this->Metodo_ridefinito['get_Html']['parametri']['attributi'],($content===null?$this->get_value():$content));
  	if(!isset($this->Metodo_ridefinito['get_Html']['parametri']['attributi']['id']) || !isset($this->Metodo_ridefinito['get_Html']['parametri']['attributi']['id'])) $t->set_attributo('id',$this->attributi['id'].'_showonly');
  	if(isset($this->attributi['class']) && !isset($this->Metodo_ridefinito['get_Html']['parametri']['attributi']['class'])) $t->set_attributo('class',$this->attributi['class']);
  	if(isset($this->attributi['style']) && !isset($this->Metodo_ridefinito['get_Html']['parametri']['attributi']['style'])) $t->set_attributo('style',$this->attributi['style']);
  	return $t;
  }
  
	

  /** @ignore */
  protected function _get_html_show($pars,$tag='span',$content=null){
   	 if (isset($pars['campo']) && $pars['campo'] && (!isset($this->get_attributo['disabled']) || !$this->get_attributo['disabled'])) $out=$this->_get_html_hidden($pars);
   	 $t=$this->_get_html_show_obj($pars,$tag,$content);
   	 return $t.$out;
   }



 /**
	* Restituisce l'attributo 'id'
	* @return string
  */
  public function get_id() {  return $this->attributi['id']; }



/** @ignore*/
 function &get_value_DB() {  return $this->get_value(); }


 /**
  * Imposta l'attributo 'value'
  *
  * @param	 array|string $valore Valore da assegnare come defaulto array (non assoc) di valori nel caso di campo multivalori
  */
  public function set_value($valore) {
  $val=array();
  if (is_array($valore)) foreach ($valore as $v) $val[]=stripslashes((string)$v);
  					else{ $valore=(string) $valore;
  						  if(trim((string) $valore)==='') $val='';
							else $val=stripslashes(trim((string) $valore));
  					    }
 $this->attributi['value']=$val;
 return $this;
 }



 /**
	* Setta l'attributo 'name'
 * @param string $nome
 * @param boolean $cambia_id se false l'id del campo non viene riassegnato
  */
   public function set_name($nome,$cambia_id=true) {
	 if (trim((string) $nome)==='') return $this;
	 $this->attributi['name']=$nome;
	 $nome=explode('[',$nome,2);
	 $nome=$nome[0];
	 if(!isset($this->myFields['campo__istanze'][strtolower($nome)])) $this->myFields['campo__istanze'][strtolower($nome)]=0;
	 if($cambia_id) $this->set_id($nome.(($this->myFields['campo__istanze'][strtolower($nome)])?'_'.$this->myFields['campo__istanze'][strtolower($nome)]:""));
	 $this->myFields['campo__istanze'][strtolower($nome)]++;
	 return $this;
 }



 /**
	* Setta l'attributo 'id'
  * @param string $id
  */
  public function set_id($id) {
 	$this->attributi['id']=$id;
 	return $this;
 }

   public function set_attributo ($primo,$valore='',$filtrato=true) {
     if (is_array($primo))
					 foreach ($primo as $nome=>$valore)
										  {if ($filtrato && strtolower($nome)=='name') $this->set_name($valore);
										  elseif($filtrato && strtolower($nome)=='value') $this->set_value($valore);
												  else parent::set_attributo($nome,$valore);
										  }
				          elseif ($valore!==null)
				                         {if ($filtrato && strtolower($primo)=='name') $this->set_name($valore);
				                                elseif($filtrato && strtolower($primo)=='value') $this->set_value($valore);
													  else parent::set_attributo($primo,$valore);
										 }
 return $this;
 }



 /**
	* Setta la caratteristica di notnull
  * @param	 boolean $var se true=> not null	false=> null
  */
   public function set_notnull($var=true) {  $this->notnull=$var; return $this;}


 /**
	* Restituisce la caratteristica di notnull
  * @param	 boolean $var se true=> notnull	false=> nullable
  */
	 public function get_notnull() {  return $this->notnull; }



  /**
	* Setta l'espressione regolare da usare nel metodo Errore
  * @param	string $expr e' l'espressione regolare
  * @param	string $messaggio è il messaggio da restituire se value non e' confermato da $expr
  */
   public function set_regExp($expr,$messaggio='formalmente errato') {
	  $this->controllo_regExp['exp']=$expr;
	  $this->controllo_regExp['mess']=$messaggio;
	  return $this;
  }

  
   public function get_regExp() {
     return  $this->controllo_regExp;
  }

	/**
	* Setta la caratteristica di readonly
	* @param	bool $var se vero imposta readonly altrimenti lo toglie (default=true)
	*/
	 public function set_readonly($var=true){
 		 if ($var==false) $this->unset_attributo('readonly');
		 	 		else $this->set_attributo('readonly','readonly');
		return $this;
	}


	/**
	* Setta la caratteristica di disabled
	*/
	 public function set_disabled($var=true){ 
	               if ($var==false) $this->unset_attributo('disabled');
		 	 		           else  $this->set_attributo('disabled','disabled'); 
	               return $this;
	           }


	 /**
	* Setta il Tooltip del campo
	* @param	string $testo e' il testo
	*/
	 public function set_Tooltip($testo) {
		$this->set_attributo('title',$testo);
		return $this;
	// $this->setjs('this.title=\''.addslashes(myTag::htmlentities(myTag::nonhtmlentities(strip_tags($testo)))).'\'','onmouseover');
 	}


 	/**
	* Restituisc il Tooltip del campo
 	 * @return	string 
 	 */
 	 public function get_Tooltip () {
 	    if(!isset($this->attributi['title']) || $this->attributi['title']=='') return '';
 	   return  $this->attributi['title'];
 	}
 	

 	 public function get_myJQueries(){
 		return (array) $this->myJQueries;
 	}



 	 public function add_myJQuery(myJQuery $myJQ) {
 		if(method_exists($myJQ, 'application')) $myJQ->application($this);
 		if(method_exists($myJQ, 'get_widget')) $k=$myJQ->get_widget();
 										  else $k=spl_object_hash($myJQ);
 		unset($this->myJQueries[$k]);
 		return $this->myJQueries[$k]=$myJQ;
 	}

	/**
	 * @ignore
	 */
 	protected function &send_html($html){
 	    $js='';
 	    $noMask=$this->get_showonly() || isset($this->attributi['readonly']);
 	    if($this->myJQueries && 
 	     (!isset($this->myFields['CONTESTO']) || 
 	      $this->myFields['CONTESTO']!='myForm::calcolo_dipendenze'))
 	                     foreach ($this->myJQueries as $myjq) {
 					                  if($noMask && ( $myjq instanceof myJQInputMask ||
             					                      $myjq instanceof myJQDatepicker ||
             					                      $myjq instanceof myJQTimePicker 
 					                                 )) continue;
 					                  $js.=$myjq->get_html();
 	                                  }
 	      
 	    if($js) $html.="<!-- JS+ -->".$js."<!-- JS- -->";
 	    $html.=$this->html5done;
 		return $html;
 	}


	/**
	* Restituisce il campo in html pronto per la visualizzazione
	 *  @return string
	 */
	  public function get_Html() {
	 	//if(!$this->myFields['static']['common']) {$jsCommon=$this->get_js_common();$this->myFields['static']['common']=true;}
	 	$jsCommon=$this->get_js_common();
		$get_html=isset($this->Metodo_ridefinito['get_Html']['metodo'])?$this->Metodo_ridefinito['get_Html']['metodo']:null; if ($get_html!='') return $jsCommon.$this->$get_html($this->Metodo_ridefinito['get_Html']['parametri']);
	 	return $this->send_html((!$this->con_js?'':$jsCommon.$this->jsAutotab()).'<input '.$this->stringa_attributi().' />');
	 }


}