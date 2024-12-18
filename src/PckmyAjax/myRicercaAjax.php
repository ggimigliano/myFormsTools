<?php
/**
 * Contains Gimi\myFormsTools\PckmyAjax\myRicercaAjax.
 */

namespace Gimi\myFormsTools\PckmyAjax;



use Gimi\myFormsTools\PckmyFields\myText;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQAutocomplete;



/**
 * 
*
 * Classe che permette, utilizzando Ajax, di mostrare all'utente una serie di dati recuperati da un altro script,
 *  variabili a seconda di quello che e' stato inserito.
 * <code>
 * 		$ric=new MyRicercaAjax( new myCognome('Prova',$Prova)  );
 * 		$ric->set_opzioni('dati.php',3);
 * 		//Esempio di utilizzo di un metodo non esistente in questa classe ma esistente nell'oggetto (myCognome) utilizzato nell'istanza
 * 		$ric->set_tooltip("Metodo non appartenente a MyRicercaAjax ma ereditiato da myCognome ");
 *		echo $ric->get_html();
 *
 * 		//Lo script che viene richiamato per ottenere i dati deve ritornarli sotto forma di un elenco non ordinato
 * 		$lista="<ul class='lista_ul'>";
 * 			$lista.="<li id='valore'>dato</li>";
 * 	 	$lista.="</ul>";
 * 		echo $lista;
 * </code>
 */
	
class myRicercaAjax extends myRicercaAjaxModel  {

    	/**
    	 *
    	* @param	 MyText $myText E' il campo su cui applicare la ricerca Ajax (puo' anche essere un campo esteso da MyText)
    	*/
    	 public function __construct($myText){
    		return parent::__construct($myText);
    	}
    	
    	
    	
    	 public function get_myAutocompleter(){
    	    if(!$this->myAutocompleter) $this->myAutocompleter=new myJQAutocomplete();
    		return $this->myAutocompleter;
    	}

       public function set_campo_input($nome,&$campo){
      		$this->get_myAutocompleter()->set_campo_input($nome, $campo);
      		return $this;
      }
    
       public function set_campo_output(&$campo){
      		$this->campo_output=$campo;
      		$this->get_myAutocompleter()->set_campo_output($campo);
      		return $this;
      }
    
      /**
       *
       * Imposta i n. di caratteri dopo cui la scelta si attiva
       * @param int $caratteri
       */
       public function set_tiping($caratteri=2){
          	$this->get_myAutocompleter()->set_tiping($caratteri);
      		return $this;
      }
    
       public function get_titolo(){
      		return $this->myText->get_value();
      }

	/**
	 * Imposta il testo della riga di chiusura della pagina,
	 * se omesso non verrà mostrato quindi sarà  impossibile chiudere la finistra senza effettuare una scelta
	 *
	 * @deprecated Non funziona piu'
	 */
	 public function set_messaggio_chiusura() {
    	
    }

/**
 * Funzione obbligatoria, va utilizzata dopo l'istanza della classe
 *
 * @param string $script E' lo script da cui devo andare a recuperare i dati da visualizzare all'utente
 * @param integer $car Indica dopo quanti caratteri devo visualizzare l'aiuto (ignorato se script e' nullo)
 * @param string $campoId (Opzionale) Da utilizzare se si ha la necessità di utilizzare una eventuale scelta dell'id
 * @param string $parametri Parametri da inviare in post in aggiunta
 * @param string $parametri Eventuali altri parametri da valorizzare tramite js es $parametriJS=array('pippo'=>'documento.getElementById('ciccio').value');
 *
 */
	 public function set_opzioni($script,$car=3,$campoId="",$parametri=array(),$parametrijs=array()){
		if(!$this->myAutocompleter) $this->myAutocompleter=new myJQAutocomplete();
  		if (!$script) return;
	    $this->myAutocompleter->set_tiping($car);
	    $this->set_campo_output($campoId);
	    $this->myAutocompleter->set_source($script,$campoId,$parametri,$parametrijs);
	}

	


	 public function set_onselect_function($function) {
		if(!$this->myAutocompleter)  {echo "usare set_onselect_function() solo prima di set_opzioni()";exit;}
		$this->myAutocompleter->set_onselect_function($function);
	}

	/** @ignore */
	/*
	protected function js_update($campoId){
		if ($this->function) $f="{$this->function}(text,li);";
		if ($this->chiudi[0])
							{  if (!$this->chiudi[1]) $messaggio=($this->trasl($this->chiudi[0]));
												else  $messaggio=($this->chiudi[0]);
							   $extra="if(text.value==\"$messaggio\")
							   					{
							   					   document.getElementById('{$this->get_id()}').value=li.id;
							   					   return;
							   					}";
							}
		return 	"function onSelectedMyAjax_{$this->id}(text, li) {
									document.getElementById('$campoId').value=li.id;
		                        	$extra
    								$f
							}

				";

	}
*/

	 public function set_security(\Gimi\myFormsTools\PckmyPlugins\mySecurizer $s){
	    $this->myText->set_security($s);
	    return $this;
	}
	
	 public function set_never_security(){
	    $this->myText->set_never_security();
	    return $this;
	}

	 public function set_showonly() {
		$this->myText->set_showonly();
		$this->showonly=true;
	}


/**
 * Funzione che mi permette di costruire l'HTML del campo
 * @return l'HTML
 */

	function &get_Html(){
		if (!$this->showonly) $this->myText->add_myJQuery($this->get_myAutocompleter());
	    $html= $this->myText->get_html();
	    return $html;
	}

	
	/**
	 * Impone altezza massima alla finestra di scroll
	 * @param int $px pixels
	 */
	 public function set_altezza_scroll($px){
		if(!$this->myAutocompleter) $this->myAutocompleter=new myJQAutocomplete();
  		$this->myAutocompleter->set_altezza_scroll($px);
  		return $this;
	}
	

	/**
	 * Cosa deve comparire durante l'attesa di aggiornamento
	 * @param string $tag e' la urla dell'icona da visualizzare o semplocemente l'html da visualizzare
	 */
	 public function set_indicatore_attesa($html){
		if(!$this->myAutocompleter) $this->myAutocompleter=new myJQAutocomplete();
  		$this->myAutocompleter->set_indicatore_attesa($html);
	 return $this;
	}

	/**
	 * Imposta la facoltatività del tag, se facoltativo @see get_xml non restituirà nulla 
	 * altrimenti si visualizza il tag vuoto <tag />
	 *
	 * @param boolean facoltativo  se true l'xml si vede se false non viene mostrato
	 */
    public function set_xml_facoltativo($facoltativo=true){
  	  $this->myText->set_xml_facoltativo($facoltativo);
  	  return $this;
   }


/**
 *  Funzione privata che permette di utilizzare metodi non esistenti in questa classe, ma esistenti
 *  nell'oggetto istanziato (es. potrei, invece di istanziare questa classe su un generico oggetto
  * myText su uno più specifico myCognome ed utilizzare metodi specifici di questo oggetto) 
 */
    public function __call($metodo, $attributi){
		return @call_user_func_array( array($this->myText,$metodo) , $attributi );
    }

     /** @ignore*/
      public function __get($var)
        {  
    	return $this->myText->$var;
        }

      /** @ignore*/
     public function __clone() {
    	$this->myText=clone($this->myText);
    	return $this;
    }


    /** @ignore*/
    public function clonami() {
		 if (PHP_VERSION >= 5) return clone($this);
						 else  return $this;
	}



}