<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\myJQueryMyField\myJQGoogleAddress.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\myJQueryMyField;


use Gimi\myFormsTools\PckmyFields\myField;
use Gimi\myFormsTools\PckmyJQuery\myJQueryMyField;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQAutocomplete;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQAutocompleteUtilsStatic;
                                                


/**
 *
 * Classe  produrre effetto autocompletamento di indirizzi e/o comuni basato su google
 * <code>
 *  $f=new form() 
 * 	$f->add_campo(new mySelect('provincia'..  //selettore Provincia=>codice_istat
 *  $f->add_campo(new myText('comune'));
 *  $f->add_campo(new myText('indirizzo'));
 *  $f->add_campo(new myCAP('cap');
 *  $f->campo('comune')
 *  		->add_myJQuery(new myJQGoogleAddress())
 *  		->set_provincia_input($f->campo('provincia'),true,true) //usa in input provincia, ma non il valore ma il testo visualizzato
 *  		->set_formato_value('{#COMUNE#}')				//inserisce nel campo il nome del comune
 *  		->set_formato_label('{#COMUNE#} - {PROVINCIA}'); //mostra i comuni trovati e la relativa provincia (in alcuni casi google non filtra bene)
 *  $f->campo('indirizzo')
 *  		->add_myJQuery(new myJQGoogleAddress())
 *  		->set_provincia_input($f->campo('provincia'),true,true) //usa in input provincia, ma non il valore ma il testo visualizzato
 *   		->set_comune_input($f->campo('comune'),true,false) 	//usa in input il valore del comune
 *   		->set_cap($f->campo('cap'),false,false,'function(val){return val*2}'); //valorizza il campo cap con un cap pari al doppio di quello trovato
 * </code>
 *
 * @see http://jqueryui.com/demos/autocomplete/
 * @see https://developers.google.com/maps/documentation/javascript/geocoding
 */
	
class myJQGoogleAddress extends myJQueryMyField {
    /**
     * @ignore 
     */
	private   $building_code=array('js'=>'','usa_titolo'=>''),$val_input=array(array('STATO'=>'Italia')), $campi_output=array(),$campi_input=array();
	/**
	 * @ignore
	 */
	protected static $conta_ist=0;
    /**
     * @ignore 
     */
    protected $scroll=null,$istanza,$sorgente,$hmax,$css_font_default='{font-size:11px}',$css_font_class=array('.ui-autocomplete'),$myField;
	
	
	/**
 * @ignore 
 */static protected function  init(&$widget) {
		$widget='myAddresspicker';
	}

	/**
 * @ignore 
 */function get_common_defaults(){
		return array();
	}
	
	/**
 * @ignore 
 */function set_common_defaults(array $defaults){
	}
	

	/**
 * @ignore 
 */function build_input_code($id){
     		$campo=each($this->building_code);
     		$campo=$campo[1];
     		$js=$this->building_code['js'];
     		$usa_titolo=$this->building_code['usa_titolo'];
     		if(!$js) $js="function(val){return val}";
     		if(!is_object($campo)) $id=$campo;
     		if(!$usa_titolo) $codice="\$('#$id').val()";//qui no ;
					else{
						if(!is_object($campo)) 
							 {
					    	  $codice="\$('#$id option:selected').text()";
							 }			  
						else {
							  if($campo->estende('MySelect',true)) $codice="\$('#$id option:selected').text()";
							  								 else  $codice="\$('#$id').val()";
							  }
					   }	  								 	 	
			return "function(JQ){var \$=JQ; return ($js)($codice)}";											
	}
	
	/**
 * @ignore 
 */function build_output_code($id){
         //	self::add_src("//maps.google.com/maps/api/js?sensor=false"); posizionati qui evitano rischio di essere ignorati
        $campo=each($this->building_code);
		$campo=$campo[1];
		$usa_titolo=(isset($this->building_code['usa_titolo']) && $this->building_code['usa_titolo']?$this->building_code['usa_titolo']:'');
		$js=        (isset($this->building_code['js']) && $this->building_code['js']?$this->building_code['js']:'');
		if(!$js) $js="function(val){return val}";
		if(!is_object($campo)) $id=$campo; 
		if(!$usa_titolo) $codice="\$('#$id').val(valore);";
			else{if(!is_object($campo)) 
						{
						  $codice="\$('#$id').text(valore);";
						}
					else{if($campo->estende('MySelect',true)) $codice="\$('#$id').text(valore);";
						}
				}	
				
		$codice="
				 $codice
				 if(\$('#$id option'))	 {myEventManager.fireAll('$id');/*myEventManager.fire('focus','$id');myEventManager.fire('change','$id');myEventManager.fire('blur','$id');*/}
								   else  {myEventManager.fireAll('$id');/*myEventManager.fire('focus','$id');myEventManager.fire('keypress','$id');myEventManager.fire('blur','$id');*/}
				";		
		//elseif($campo[0]->estende('MySelect',true))
			
		return "function (valore,valori,JQ){var \$=JQ;
										   var valore=($js)(valore); 
									 	   $codice
										  }";
	}
			
	/**
 * @ignore 
 */function set_istance_defaults(){
        $m=array();
		if(preg_match('@([a-z]{2}\-[A-Z]{2})@',$_SERVER['HTTP_ACCEPT_LANGUAGE'],$m)) $this->lingua=$m[1];
		if(!$this->istanza)	$this->istanza=++self::$conta_ist;
		$open=$this->get_istance_defaults('open');
		if($this->scroll===null) $this->set_altezza_scroll('');
		if(!isset($open->opzioni['icona'])) $this->set_indicatore_attesa('');
		if(!isset($open->opzioni['css'])) $open->opzioni['css']='';
		foreach ($this->css_font_class as $class) $open->opzioni['css'].=self::get_add_style($class.$this->css_font_default,true);
		self::add_src("jquery/myJQGoogleAddress/jquery.ui.myAddresspicker.js");
		
		
		//$jq=self::$identificatore;
		if($this->campi_input) {$JQINFields=array();
								foreach ($this->campi_input as  $campi){ 
												$this->building_code=$campi;
												$campo=each($campi);
												$ids=self::quote_keys(myJQAutocompleteUtilsStatic::estrai_ids(
																											array($campo[0]=>$campo[1]),
																											array($this,'build_input_code')
																											)
																		);
												foreach ($ids as $kk=>$vv) $JQINFields[]=array($kk=>$vv);
												}
								$this->set_option('JQINFields',$JQINFields);
								}
		if($this->val_input) 	{
								 $this->set_option('JQINVals',$this->val_input);
								}						
		if($this->campi_output)	{$JQOUTFields=array();
								foreach ($this->campi_output as $campi){ 
												$this->building_code=$campi;
												$campo=each($campi);
												$ids=self::quote_keys(myJQAutocompleteUtilsStatic::estrai_ids(
																											array($campo[0]=>$campo[1]),
																											array($this,'build_output_code')
																											)
																		);
												foreach ($ids as $kk=>$vv) $JQOUTFields[]=array($kk=>$vv);
												}
								$this->set_option('JQOUTFields',$JQOUTFields);
								}
	}
	

	/**
	 * Imposta il formato della label da visualizzare, di default e' 
	 * {#STRADA#}{,#CIVICO#} {- #COMUNE#} {(#provincia#)}
	 * che sarebbe la strada il civico (preceduto da virgola) il comune (preceduto dal trattino) la sigla della provincia(tra parentesi tonde)  
	 * {#STRADA#} e' la via
	 * {#CIVICO#} e' il civico
	 * {#COMUNE#} e' il comune
	 * {#STATO#}  e' il nome dello Stato
	 * {#PROVINCIA#} e' il nome della provincia
	 * {#provincia#} e' la targa
	 * {#CAP#} e' il cap
	 * 
	 * @param string $f
	 * @return myJQGoogleAddress
	 */	
	 public function set_formato_label($f){
		$f=trim((string) $f);
		if(strpos($f,'function')!==0) $f="function () {return '".str_replace("'","\\'",$f)."';}";
		$this->formato_label=$f;
		return $this;	
	}
	
	/**
	 * Imposta il formato del dato da inserire nel campo, di default e'
	 * {#STRADA#}{,#CIVICO#} 
	 * che sarebbe la strada il civico (preceduto da virgola) 
	 * {#STRADA#} e' la via
	 * {#CIVICO#} e' il civico
	 * {#COMUNE#} e' il comune
	 * {#STATO#}  e' il nome dello Stato
	 * {#PROVINCIA#} e' il nome della provincia
	 * {#provincia#} e' la targa
	 * {#CAP#} e' il cap
	 *
	 * @param string $f
	 * @return myJQGoogleAddress
	 */
	 public function set_formato_value($f){
		$f=trim((string) $f);
		if(strpos($f,'function')!==0) $f="function () {return '".str_replace("'","\\'",$f)."';}";
		$this->formato_value=$f;
		return $this;
	}
	
	/**
	 * Imposta i campi obbligatori da estrarre, di default array('STRADA')
	 * I parametri sono quelli dei segnaposti senza #
	 *
	 * @param array $f
	 * @return myJQGoogleAddress
	 */
	 public function set_campi_necessari(array $pars){
		foreach ($pars as $v) if (!is_array($v)) {$pars=array($pars);break;}
		$this->___indispensabili=$pars;
		return $this;
	}
	
	/**
	 * @ignore
	 */
	 public function __set($k,$v){
		//echo "$k,$v<br>";
		switch ($k) {
			case 'formato_value':if(strpos(trim((string) $v),'function')===false) return $this->set_formato_value($v);
			case 'formato_label':if(strpos(trim((string) $v),'function')===false) return $this->set_formato_label($v);
		}
		//echo "*<br>";
		parent::__set($k, $v);
	}
	

	/**
	 * Imposta un campo da usare in input o output per la targa
	 * @param myField|string $campo e' un oggetto o l'id del campo html
	 * @param boolean $input se true si usa in input altrimenti in output
	 * @param boolean $usa_titolo se true usa il titolo del campo e non il valore (es. il testo evidenziato in un menu a tendina e non il valore associato)
	 * @param string $js eventuale funziona anonima javascript per lavorare il valore ottenuto/da inserire dal campo se usato in ouput e' possibile usare la variabile valori['STATO'],valori['PROVINCIA'] ecc
	 * @return myJQGoogleAddress
	 */
	 public function set_campo_targa($campo,$input=true,$usa_titolo=false,$js='') {
		if($input) $this->campi_input[]=array('provincia'=>$campo,'js'=>$js,'usa_titolo'=>$usa_titolo);
			else   $this->campi_output[]=array('provincia'=>$campo,'js'=>$js);
		return $this;
	}

	/**
	 * Imposta un campo da usare in input o output per il civico
	 * @param myField|string $campo e' un oggetto o l'id del campo html
	 * @param boolean $input se true si usa in input altrimenti in output
	 * @param boolean $usa_titolo se true usa il titolo del campo e non il valore (es. il testo evidenziato in un menu a tendina e non il valore associato)
	 * @param string $js eventuale funziona anonima javascript per lavorare il valore ottenuto/da inserire dal campo se usato in ouput e' possibile usare la variabile valori['STATO'],valori['PROVINCIA'] ecc 
	 * @return myJQGoogleAddress
	 */
	 public function set_campo_civico($campo,$input=true,$usa_titolo=false,$js='') {
		if($input) $this->campi_input[]=array('CIVICO'=>$campo,'js'=>$js,'usa_titolo'=>$usa_titolo);
		else   $this->campi_output[]=array('CIVICO'=>$campo,'js'=>$js);
		return $this;
	}
	
	/**
	 * Imposta un campo da usare in input o output per il comune
	 * @param myField|string $campo e' un oggetto o l'id del campo html
	 * @param boolean $input se true si usa in input altrimenti in output
	 * @param boolean $usa_titolo se true usa il titolo del campo e non il valore (es. il testo evidenziato in un menu a tendina e non il valore associato)
	 * @param string $js eventuale funziona anonima javascript per lavorare il valore ottenuto/da inserire dal campo se usato in ouput e' possibile usare la variabile valori['STATO'],valori['PROVINCIA'] ecc
	 * @return myJQGoogleAddress
	 */
	 public function set_campo_comune($campo,$input=true,$usa_titolo=false,$js='') {
		if($input) $this->campi_input[]=array('COMUNE'=>$campo,'js'=>$js,'usa_titolo'=>$usa_titolo);
			else   $this->campi_output[]=array('COMUNE'=>$campo,'js'=>$js);
		return $this;
	}
	
	/**
	 * Imposta un campo da usare in input o output per la provincia
	 * @param myField|string $campo e' un oggetto o l'id del campo html
	 * @param boolean $input se true si usa in input altrimenti in output
	 * @param boolean $usa_titolo se true usa il titolo del campo e non il valore (es. il testo evidenziato in un menu a tendina e non il valore associato)
	 * @param string $js eventuale funziona anonima javascript per lavorare il valore ottenuto/da inserire dal campo se usato in ouput e' possibile usare la variabile valori['STATO'],valori['PROVINCIA'] ecc
	 * @return myJQGoogleAddress
	 */
	 public function set_campo_provincia($campo,$input=true,$usa_titolo=false,$js='') {
		if($input) $this->campi_input[]=array('PROVINCIA'=>$campo,'js'=>$js,'usa_titolo'=>$usa_titolo);
			else   $this->campi_output[]=array('PROVINCIA'=>$campo,'js'=>$js);
		return $this;
	}
	
	/**
	 * Imposta un campo da usare in input o output per lo Stato
	 * @param myField|string $campo e' un oggetto o l'id del campo html
	 * @param boolean $input se true si usa in input altrimenti in output
	 * @param boolean $usa_titolo se true usa il titolo del campo e non il valore (es. il testo evidenziato in un menu a tendina e non il valore associato)
	 * @param string $js eventuale funziona anonima javascript per lavorare il valore ottenuto/da inserire dal campo se usato in ouput e' possibile usare la variabile valori['STATO'],valori['PROVINCIA'] ecc
	 * @return myJQGoogleAddress
	 */
	 public function set_campo_stato($campo,$input=true,$usa_titolo=false,$js='') {
		if($input) $this->campi_input[]=array('STATO'=>$campo,'js'=>$js,'usa_titolo'=>$usa_titolo);
			else   $this->campi_output[]=array('STATO'=>$campo,'js'=>$js);
		return $this;
	}
	
	/**
	 * Imposta un campo da usare in input o output per il CAP
	 * @param myField|string $campo e' un oggetto o l'id del campo html
	 * @param boolean $input se true si usa in input altrimenti in output
	 * @param boolean $usa_titolo se true usa il titolo del campo e non il valore (es. il testo evidenziato in un menu a tendina e non il valore associato)
	 * @param string $js eventuale funziona anonima javascript per lavorare il valore ottenuto/da inserire dal campo se usato in ouput e' possibile usare la variabile valori['STATO'],valori['PROVINCIA'] ecc
	 * @return myJQGoogleAddress
	 */
	 public function set_campo_cap($campo,$input=false,$usa_titolo=false,$js='') {
		if($input) $this->campi_input[]=array('CAP'=>$campo,'js'=>$js,'usa_titolo'=>$usa_titolo);
			else   $this->campi_output[]=array('CAP'=>$campo,'js'=>$js);
		return $this;
	}
	
	
	/**
	 * Imposta un campo da usare in input o output per il CAP
	 * @param myField|string $campo e' un oggetto o l'id del campo html
	 * @param boolean $input se true si usa in input altrimenti in output
	 * @param boolean $usa_titolo se true usa il titolo del campo e non il valore (es. il testo evidenziato in un menu a tendina e non il valore associato)
	 * @param string $js  funziona anonima javascript per lavorare il valore da inserire dal campo se usato in ouput e' possibile usare la variabile valori['STATO'],valori['PROVINCIA'] ecc
	 * @return myJQGoogleAddress
	 */
	 public function set_campo_out($campo,$js) {
	    $this->campi_output[]=array("_{$campo->get_id()}"=>$campo,'js'=>$js);
		return $this;
	}
	
/**
 * Imposta un valore di default per targa
 * @param string $valore
 * @return myJQGoogleAddress
 */
	 public function set_val_targa($valore){
	    $this->val_input[]=array('provincia'=>$valore);
		return $this;
	}


	/**
	 * Imposta un valore di default per civico
	 * @param string $valore
	 * @return myJQGoogleAddress
	 */
	 public function set_val_civico($valore){
		$this->val_input[]=array('CIVICO'=>$valore);
		return $this;
	}
	

	/**
	 * Imposta un valore di default per comune
	 * @param string $valore
	 * @return myJQGoogleAddress
	 */
	 public function set_val_comune($valore){
	    $this->val_input[]=array('COMUNE'=>$valore);
		return $this;
	}
	

	/**
	 * Imposta un valore di default per provincia
	 * @param string $valore
	 * @return myJQGoogleAddress
	 */
	 public function set_val_provincia($valore){
	    $this->val_input[]=array('PROVINCIA'=>$valore);
		return $this;
	}
	

	/**
	 * Imposta un valore di default per Stato
	 * @param string $valore
	 * @return myJQGoogleAddress
	 */
	 public function set_val_stato($valore){
	    $this->val_input[]=array('STATO'=>$valore);
		return $this;
	}
	  

	/**
	 * Imposta un valore di default per CAP
	 * @param string $valore
	 * @return myJQGoogleAddress
	 */
	 public function set_val_cap($valore){
	    $this->val_input[]=array('CAP'=>$valore);
		return $this;
	}
	
	
	/**
	 *
	 * Imposta i n. di caratteri dopo cui la scelta si attiva
	 * @param int $caratteri
	 */
	public function set_tiping($caratteri=2){
		$this->minLength=$caratteri;
		return $this;
	}
	
	
	/**
	 * Impone altezza massima alla finestra di scroll
	 * @param int $px pixels
	 * @return myJQAutocomplete
	 */
	 public function set_altezza_scroll($px){
		$this->scroll=$px;
		$this->set_event('open',myJQAutocompleteUtilsStatic::altezza_scroll_code($px),'scroll');
		return $this;
	}
	
	
	/**
	 * Cosa deve comparire durante l'attesa di aggiornamento
	 * @param string $tag e' la url dell'icona da visualizzare o semplocemente l'html da visualizzare
	 */
	 public function set_indicatore_attesa($url=''){
		$this->set_event('open',myJQAutocompleteUtilsStatic::indicatore_attesa_code($url),'icona');
		return $this;
	}
	
	
	
}