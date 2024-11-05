<?php
/**
 * Contains Gimi\myFormsTools\PckmyForms\myForm.
 */

namespace Gimi\myFormsTools\PckmyForms;


use Gimi\myFormsTools\myDizionario;
use Gimi\myFormsTools\PckmyArrayObject\myGroupFields;
use Gimi\myFormsTools\PckmyAutoloaders\myAutoloader;
use Gimi\myFormsTools\PckmyAutoloaders\myAutoloaders;
use Gimi\myFormsTools\PckmyFields\myDate;
use Gimi\myFormsTools\PckmyFields\myField;
use Gimi\myFormsTools\PckmyFields\myOra;
use Gimi\myFormsTools\PckmyFields\myTime;
use Gimi\myFormsTools\PckmyFields\myUploadText;
use Gimi\myFormsTools\PckmyJQuery\myJQuery;
use Gimi\myFormsTools\PckmyJQuery\PckmyForms\myJQMyFormDialog;
use Gimi\myFormsTools\PckmyJQuery\PckmyForms\myJQMyFormSezioni;
use Gimi\myFormsTools\PckmyJQuery\PckmyForms\myJQMyFormTabs;
use Gimi\myFormsTools\PckmyPlugins\myEventManager;
use Gimi\myFormsTools\PckmyPlugins\mySecurizer;
use Gimi\myFormsTools\PckmySessions\mySessions;
use Gimi\myFormsTools\PckmyFields\myTag;




/**
 *  Questa classe permette la costruzione e gestione facilitata di Form; si basa su myFields
 *  
 */
	
class myForm  extends myEventManager implements \ArrayAccess {
/** @ignore */
protected  static $encodingOn=array('label'=>false,'messages'=>false),
           $lingua='it',$dizionari=array(),$docAll=0,$internalCache='',$Gruppi=array(),$erroreForzato=array(),$id_istanza,$prec_info,$salta_get_html=array(),$salta_get_xml=array(),$static=array(),
		   $dipendenza_campi=array('js_dipendenze_campo'=>array('allFunc'=>array(),'funzioni'=>array(),'dipendenza'=>array()),
                                   'daClonare'=>array(),
                                   'relazioni'=>array(),
                                   'info'=>array()
                                   );
/** @ignore */
protected  	$docOn=false,$refreshCaches=false,$prefix,$secure,$ultimoErrore, $JQTab,$JQDialog, $eventManager=array(),$colonneOriginali,$campi=array(),$classe_label, $classe_valori,$classe_righe,  $ordine, $titles, $labels, $mostra_label,  $extra_html_label, $extra_html_valori,
 			$pref_html='tr', $muovi_lables=false,$orizzontale=false,$id_istance,$prefisso_nascoste,$magic_attr=array(),
  			$facoltativoXML=false,$myMultiForm=null,$myForms=array(), $autotab=false, $debug=false, $pre_post_label=array(), $pre_post_campo=array(),$dizionario=array(),$isShowonly=false,
  			$xml=array('tag'=>null,'case'=>'','header'=>'');

/** @ignore */
 	 public function __destruct() {
  	  if($this->refreshCaches) myAutoloader::clean_server_caches();
  	  IF(isset(self::$dipendenza_campi['daClonare']) && is_object(self::$dipendenza_campi['daClonare']) &&
  	  	 self::$dipendenza_campi['daClonare']->get_id_istanza()==$this->id_istance) {
                			  		unset(self::$dipendenza_campi['js_dipendenze_campo']['allFunc'][$this->id_istance]);
                			  		unset(self::$dipendenza_campi['relazioni'][$this->id_istance]);
                			  		unset(self::$dipendenza_campi['info'][$this->id_istance]);
                  	 			 }
  	 unset(self::$dipendenza_campi['daClonare']);
     if(($this->docOn || self::$docAll) &&
	        (!class_exists('myAutoloaders',false) || myAutoloaders::get_debugging())) self::build_documentation_properties($this,$this->get_campi(false), $this->docOn | self::$docAll);

    }
    
    
    /** @ignore*/
     public function __call($m,$v){ 
        $m=strtolower($m);
	    $classInfo=new \ReflectionClass(get_class($this));
	 //   echo get_class($this),'=>',$m,'<br>';
	    while($classInfo) {
	        $class=$classInfo->getName();
	        if(strtolower($class)==$m)  { $pars=array();
                                	        for ($i=0;$i<count($v);$i++) $pars[]="\$v[".$i."]";
                                	   //     echo get_class($this),'=>',$m, "return $class::__construct(".implode(',',$pars).");<br>";
                                	        eval("return $class::__construct(".implode(',',$pars).");");
                                	        }
	        $classInfo=$classInfo->getParentClass();
	    }
        foreach ($this->campi as &$campo)  if (method_exists($campo,$m)) return call_user_func_array(array($campo, $m),$v);
        return parent::__call($m,$v);
    }
    
    
    /**
     * Le myForm sono dotate di metodi magici queste operazioni
     * <code>
     * $myform->campo('nomecampo')->set_min(1);
     * $myform->campo('nomecampo')->set_value(1);
     * //Possono anche essere scritte
     * $myform->nomecampo->set_min(1);
     * $myform->nomecampo=1; //solo per il set_value
     * </code>
     * Usando questa opzione viene creata automaticamente la documentazione di tutti i campi dell'istanza  
     * in questo modo gli editor sono in grado di interpretarla suggerendo i metodi dei vari campi
     * 
     * @param string $crea_back se true una copia del file viene salvata con estensione bak
     */
     public function set_auto_doc_attributi($crea_back=false){
        $this->docOn=1;        
        if($crea_back) $this->docOn+=2;
        return $this;
    }
    
    
    /**
     * Se impostata viene creata automaticamente la documentazione di tutti i campi di tutte le myForm istanziate
     * @see myForm::set_auto_doc_attributi
     * @param string $crea_back se true una copia del file viene salvata con estensione bak
     */
     public static function set_auto_doc_tutti_attributi($crea_back=false){
        static $first_call;
        self::$docAll=1;
        if($crea_back) self::$docAll+=2;
        if(!$first_call) {$first_call=true;
                          myMultiForm_DB::set_auto_doc_tutti_attributi($crea_back);
                         }
    }
    
    
    /**
     * @ignore
     */
      public static function build_documentation_properties($obj,$props,$save=false){
        static $testi,$done; 
        $class=$className=get_class($obj);
        for($i=1;$i<strlen($class);$i++) 
            if(strtolower($class[0])==$class[0] && 
               strtolower($class[$i-1])==$class[$i-1] &&
               strtoupper($class[$i])==$class[$i] &&
               strtolower($class[$i+1])==$class[$i+1]) $className=str_replace($class[$i]," ".$class[$i], $className);
        $className=$class." ".str_replace('_',' ',$className); 
                                    
        $refl=new \ReflectionClass($class);
        $start=$refl->getStartLine()-1;
        $file=$refl->getFileName();
        if(isset($done[$class]) || strpos($file,__DIR__)===0 || !is_file($file)) return;
        
        $done[$class]=true;
        foreach ($_SERVER as $k=>$v) if($v && preg_match('/_USER$/',$k)) {$user=" * @author $v\r\n"; break;}
        if($save>1 && !is_file("$file.bak")) copy($file,"$file.bak");
        if(!isset($testi[$file])) $testi[$file]=file($file);
        
        $testo=&$testi[$file];
        if(!$refl->getDocComment())  $myComment="/"."**\r\n * Class $className\r\n\r\n$user";
                                else{$start--;
                                     while(trim((string) $testo[$start])=='*' || trim((string) $testo[$start])=='' || strpos($testo[$start],'*'.'/')!==false)  {unset($testo[$start]);$start--;}
                                     while(preg_match('#\*\s+@property\s+[a-zA-Z]+\s+[a-zA-Z]+\s+#S', $testo[$start])) {unset($testo[$start]);$start--;}
                                     $start=$refl->getStartLine()-1;
                                    }
        foreach ($props as $nome=>$campo) $myComment.=" * @property ".get_class($campo)." $nome ".(method_exists($obj,'get_label')?$obj->get_label($nome):"")."\r\n";
        $myComment.="*"."/\r\n\r\n";
        $testo[$start]=$myComment.$testo[$start];
        file_put_contents($file, implode("",$testo),LOCK_EX);
    }
  	
/** 
 * Metodo eventualmente riscrivibile invocato dalla myForms::dispatchEvents() al rilascio dell'istanza
 */ function onClose(){}
	
/** 
 * Metodo eventualmente riscrivibile invocato dalla myForms::dispatchEvents() all'avvio dell'istanza
 */ function onStart(){}

   /***
    * Metodo eventualmente riscrivibile invocato dalla myForms::add_campo()
    * @param string $nomeCampo
    * @param myForm $form
    */
     public function onAddedField($nomeCampo,$form){}

  	
    /** @ignore */
     public function __get($x){  if(!is_object($this->campo($x))) return isset($this->magic_attr[$x])?$this->magic_attr[$x]:null;
  	                                     else return $this->campo($x);	
  	                  }
  	
  	/** @ignore */
  	 public function __set($x,$v){  if(!is_object($this->campo($x))) $this->magic_attr[$x]=$v;
  	                                       else $this->campo($x)->set_value($v);
  	                         return $v;
  	                      }
  	
    /** @ignore */
     public function set_no_html(){$this->__destruct();}

    /** @ignore */
    public function offsetSet($offset, $value):void {$this->add_campo($value,$offset);}
    
    /** @ignore */
    public function offsetExists($offset):bool {
        return is_object($this->get_campo($offset));
    }
    /** @ignore */
    public function offsetUnset($offset):void {
    	$this->unset_campo($offset);
    }
    /** @ignore */
    public function offsetGet($offset):mixed {
        return $this->get_campo($offset);
    }
    /** @ignore */
     public function set_myMultiform($myMultiForm) {
     	if (!$myMultiForm) $this->myMultiForm=null;
    				  else $this->myMultiForm=$myMultiForm;
    }

    /**
     * Metodo eventualmente riscrivibile invocato dalla myForms::dispatchEvents() su errori generati da myForms::check_errore()
     */
   public function onUserError(&$error) {
  	foreach ($this->eventManager as &$mng) $mng->onUserError($error,$this);
  }

  /**
   * Metodo eventualmente riscrivibile invocato dalla myForms::dispatchEvents() su errori generati da myForms::salva() o myForms::elimina() 
   */
   public function onInternalError(&$error) {
  	foreach ($this->eventManager as &$mng) $mng->onInternalError($error,$this);
  }


  /**
     * Imposta un eventManager EventManager.
     * @throws \Exception se  $mng non ha metodi pubblici onUserError e onInternalError
     * @param myEventManager $mng
     */
     public function add_myEventManager(myEventManager $mng,$propaga=true){
    	if(!method_exists($mng, 'onUserError') &&
    	   !method_exists($mng, 'onInternalError')
    	   ) throw new \Exception("set_EventManager deve passare un'istanza di EventManager o sottoclasse che abbia i metodi pubblici onUserError e onInternalError");
    	$this->eventManager[]=$mng;
    }


    
/**
 * Definisce un gruppo di campi che verranno divisi per grupppo quando si usa le myForms::get_html_completo()
 * @param string $testo   descrizione del gruppo
 * @param string $dal_campo
 * @param string $al_campo
 * @return myForm
 */
     public function set_gruppo_campi($testo,  $dal_campo, $al_campo=''){
    	foreach ($this->get_campi() as $campo) {
    		if($campo===$dal_campo) $attivo=true;
    		if($attivo) {
    					 self::$Gruppi[spl_object_hash($campo)]=array('titolo'=>$testo,'tipo'=>($campo===$dal_campo?'I':($campo===$al_campo?'F':'X')));
    					}
    		if($campo===$al_campo) break;
    	}
    	return $this;
    }

    
    
    
      public function __construct() {
          if(!$this->id_istance) {
		  	parent::__construct();
		  	$f=new myField('');
		  	if(self::$lingua!='it') 
		  	           {
		  	             $this->set_lingua(self::$lingua);
		  	             $f->set_lingua(self::$lingua);
		  	           }
		  	if(!self::$erroreForzato &&
        		   (!$_POST && !$_POST && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH']>=myUploadText::calcola_max_upload())
        			  ){
		  	            $_POST=array('');
		  	            self::$erroreForzato['errore_puro']=self::$erroreForzato['errore_tipo']='I dati trasmessi eccedono il massimo consentito, verificare la dimensione dei file uploadati';
		  	           }	
		  	           
		    $this->myForms=&$f->myFields['myForms'];
		    $this->id_istance=$f->get_id_istanza();

		    if (!isset($this->myForms['usafile'])) $this->myForms['usafile']=true;
			$this->mostra_label=true;
		  }
	}

	
	/**
	 * Se impostato la form viene visualizzata con DIV invece di TABLE
	 *  
	 * si consiglia di usarla in abbinamento a myForm::set_classi()
	 * es. dopo aver definito nei css
	 * 
	 * .colonna_labels{float:left;width:60%;}
	 * .colonna_campi{float:left;width:39%;}
	 * 
	 * <code>
	 *  $f=new myForm();
	 *  $f->set_usaDIV()->set_classi('colonna_labels','colonna_campi');
	 *  ...
	 * </code>
	 * 
	 * @param boolean $usare
	 * @return myForm
	 */
	 public function set_usaDIV($usare=true){
		if($usare) $this->set_pref_html_field('div');
			  else $this->set_pref_html_field('td');
		return $this;
	}

	
	 public function get_id_istanza(){
		return $this->id_istance;
	}

	/** @ignore */
   public function __toString(){
  	return $this->get_html();
  }

 

  

   /** @ignore */
    public static function AbsPath(){
 	 myField::AbsPath();
 }

  /**
	* Imposta i percorsi da far usare al package, ove non riesca a reperirli in automatico
   * 
     * @param    string $PathSito E' la path assoluta del sito es "c:\wwwwpublic\htdocs"
     * @param    string $myFormsPath la path assoluta delle myForms rispetto alla root del sito esl "/librerie/myform"
     */
   public static function setAbsPath($PathSito='',$myFormsPath='') {
  	myField::setAbsPath($PathSito,$myFormsPath);
  }
  
  
  /**
   *
   * @param boolean $status se true si aggiungono attrributi din vincoli di non null ecc dell'HTML5
   */
  static function usaHTML5($status=true){
      myField::usaHTML5($status);
  }

/**
 * Aggiunge un prefisso al nome dei CAMPI.
 * (IN PRATICA INFLUISCE SULL'ATTRIBUTO NAME DELL'HTML)
 * @param string $prefisso
 * @param boolean $applica se true si applica altrimenti si rimmuove
 */
    public function set_prefisso_nomi_campi($prefisso,$applica=true){
   	 if($applica) {
   	   $this->prefisso=$prefisso.$this->prefisso;
   	   foreach ($this->get_campi() as $campo)
   	   						$campo->set_name($prefisso.$campo->get_name(),false);
   	   }
   	 else {
   	   	$this->prefisso=preg_replace("/^$prefisso/S",'',$this->prefisso);
   	   	foreach ($this->get_campi() as $campo) {
   	   		$nome=$campo->get_name();
   	   		$campo->set_name(preg_replace("/^$prefisso/S",'',$nome),false);
   	   		}
   	   }
   	  return $this;
   }

  	/**
     * Porta a minuscolo cosa e le prime lettere maiuscole.
     * @param    string $cosa
     */
    public function Minuscolo ($cosa) {
   	 $v=explode("_",trim((string) $cosa));
   	 $x='';
     for ($i=0; $i<count($v);$i++)
    	{
     	$v[$i]=ucfirst(strtolower(trim((string) $v[$i])));
     	if (strlen($v[$i])<=3 && $x!='') $v[$i]=strtolower($v[$i]);
     	if ($x!='') $x.=' ';
     	$x.=$v[$i];
     }
     $cosa=trim((string) $x);
     return $cosa;
    }

    /**
     * REstituisce la radice di tutte le myForm e multiform nidificate.
     */
    public function get_myFormRootContainer(){
    	$fatti=array();
    	$cur=$this;
    	do {
    	  $fatti[]=spl_object_hash($cur);
    	  if(!is_object($next=$this->get_myFormContainer()) ||
    	  	 in_array(spl_object_hash($next),$fatti)) return $cur;
    	   $cur=$next;
    	} while (true);
    }



    /**
     * Restituisce il puntatore all'eventuale multiform nel quale  e'  contenuta la myForm
     *
     * @return myMultiform_DB
     */
    public function get_myFormContainer() {
     	return $this->myMultiForm;
    }

    /**
     * @ignore
     */
    public function get_id_in_myFormContainer() {
    	foreach ($this->myMultiForm->get_forms() as $id=>$form) if ($form==$this) return $id; ;
    }

	/**
     * Setta le classi css da usare nel form. 
     * DA USARE DOPO AVER ISTANZIATO I CAMPI
     * @param    string $classe_label Classe da usare per la Label del campo
     * @param    string $classe_valori Classe da usare nella casella in cui va il campo
     * @param    string $classe_campi  Classe da usare per i campi
     * @param    string $classe_righe  Classe da usare per le righe
     */
	 public function set_classi ($classe_label='',$classe_valori='',$classe_campi='',$classe_righe='') {
	 if ($classe_campi)  if ($this->campi) foreach (array_keys($this->campi) AS $id)	$this->campi[$id]->set_attributo('class',$classe_campi);;
	 if ($classe_label)  $this->classe_label=$classe_label;
	 if ($classe_valori) $this->classe_valori=$classe_valori;
	 if ($classe_righe)  $this->classe_righe=$classe_righe;
	 return $this;
	}



	/**
     * Inserisce eventuale html aggiuntivo per la colonna delle labels e dei valori es: align='right'
     * si tratta di soli parametri
     *
     * @param    string $extra_html_label  Attributi da usare la colonna delle Label
     * @param    string $extra_html_valori Attributi da usare  per la colonna del campo
     *
     */
	 public function set_extra_html_colonne ($extra_html_label='',$extra_html_valori='') {
	 $this->extra_html_label=$extra_html_label;
	 $this->extra_html_valori=$extra_html_valori;
	 return $this;
	}


	/**
     * Restituisce IL RIFERIMENTO ad un campo del form va quindi usato preceduto da &
     * @param    string|int $quale_campo Restituisce l'oggetto corrispondente ad un campo (Maiuscolo) se  e'  un numero da il campo corrispettivo al numero d'ordine,
     *                                  1=>il primo 2=>il secondo ... 0=>l'ultimo
     * @return   myField
     */
	function &get_campo($quale_campo) {
	 if(is_int($quale_campo))
	 		{
	 		 $k=$this->ordine;
	 		 if($quale_campo===0) $quale_campo=$k[count($k)-1];
	 		 				else  $quale_campo=$k[$quale_campo-1];
	 		}
	 return $this->campo($quale_campo);
	}



	/**
     * Alias di get_campo Restituisce IL RIFERIMENTO ad un campo del form va quindi usato preceduto da &
     * @param    string $nome_campo Restituisce l'oggetto corrispondente ad un campo (Maiuscolo)
     * @return   myField
     */
	function &campo($nome_campo) {
	      if (isset($this->campi[$nome_campo]) && is_object($this->campi[$nome_campo])) return $this->campi[$nome_campo];
	         elseif (isset($this->campi[$nome_campo=strtoupper($nome_campo)]) && is_object($this->campi[$nome_campo])) return $this->campi[strtoupper($nome_campo)];
	 												elseif($this->debug) echo "<hr />Errore myForms: in metodo campo('$nome_campo')/get_campo('$nome_campo') non è un campo della form<hr />";
	       $null=null;
	       return $null;
	   }


	/**
     * Restituisce IL RIFERIMENTO all'array con tutti i campi del form istanziati va quindi usato preceduto da &
     * @param    boolean $caseOriginale case delle chiavi se false tutto maiuscolo altrimenti risponde a quello usato nell'add_campo/analizza_tabella
     * @return   array
     */
	function &get_campi($caseOriginale=false) {
		if(!$caseOriginale) return $this->campi;
		$new=array();
		foreach (array_keys($this->campi) as $k) $new[$this->colonneOriginali[$k]]=&$this->campi[$k];
	    return $new;
	}


	/**
	* Aggiunge un campo al form
     * @param    myField $campo Campo da aggiungere
     * @param    string $label Label da associare al campo
     * @param    boolean $indirizzo Se true il passaggio della variabile $campo avviene per indirizzo ed il campo aggiunto rimane collegato a quello esterno
     * @param    string $dopo Nome del campo dopo il quale campo posizionare il campo aggiunto se omesso si aggiunge in coda se null si mette in testa
     */
	 public function add_campo(&$campo,$label='',$indirizzo=false,$dopo='') {
		self::MyForm();
	 	if (!$this->campi) $this->campi=array();
	 	if ($this->debug && (!is_object($campo) ))
	 						 {
	 						  echo "<hr />Errore myForms: è stato usato un add_campo('$campo','$label') ma il campo aggiunto non è un myfield";
	 						 }

	 	if (!$indirizzo)
	 			{   $old_campo=&$campo;
	 				if(method_exists($campo,'clonami')) $campo=$campo->clonami();
	 											   else $campo=clone $campo;
	 											   
	 				if($old_campo->get_myJQueries()) foreach ($old_campo->get_myJQueries() as $mJQ) $campo->add_myJQuery($mJQ);
	 			}

	 	$nome_campo=$campo->get_name();
		$this->colonneOriginali[strtoupper($nome_campo)]=$nome_campo;

	 	$nome_campo=strtoupper($nome_campo);

	 	if ($dopo==='' || !$this->campi)
	 			 {$this->campi[$nome_campo]=$campo;
	 			  $this->ordine[]=$nome_campo;
	 			 }
		 elseif ($dopo===null)
	 			 {$this->campi=array_merge(array($nome_campo=>&$campo),(array) $this->campi);
	 			  $this->ordine=array_keys($this->campi);
	 			 }
	 		else {
	 			 $nuovo=array();
	 			 if (!$this->ordine) $this->ordine=array_keys($this->campi);

	 			 foreach ($this->ordine as $id)
	 			 		{
	 			 		 $nuovo[$id]=&$this->campi[$id];
	 			 		 if (strtoupper($id)==strtoupper($dopo)) $nuovo[$nome_campo]=$campo;
	 			 		}
	 			 $this->campi=& $nuovo;
	 			 $this->ordine=array_keys($this->campi);
	 			 }

	 	if ($label) $this->labels[strtoupper($nome_campo)]=$label;
	    	   else $this->labels[strtoupper($nome_campo)]=$this->Minuscolo($nome_campo);

	    //if($nome_campo=='OSPITE') print_r($this->campi[$nome_campo]);
	    if(method_exists($this->campi[$nome_campo],'set_added'))    $this->campi[$nome_campo]->set_added($nome_campo,$this);
	    if(method_exists($this->campi[$nome_campo],'__onAdded'))    $this->campi[$nome_campo]->__onAdded($nome_campo,$this);
	    if(method_exists($this->campi[$nome_campo],'onAddedField')) $this->campi[$nome_campo]->onAddedField($nome_campo,$this);
	    
	    if($this->secure && method_exists($this->campi[$nome_campo],'set_security'))
	               { $this->campi[$nome_campo]->set_security($this->secure);
	                 if($indirizzo) $campo->set_security($this->secure);
	               }
	    return $this->campi[$nome_campo];
	}


	/**
	* Elimina un campo al form
     * @param    string $nome_campo nome del campo (Maiuscolo)
      */
	 public function unset_campo($nome_campo) {
	 unset($this->campi[strtoupper($nome_campo)]);
	 unset($this->labels[strtoupper($nome_campo)]);
	
	 if(!$this->ordine) $this->ordine=array_keys($this->campi);
	              else {
	                    $ordine=array_flip($this->ordine);
	                    unset($ordine[strtoupper($nome_campo)]);
	                    $this->ordine=array_keys($ordine);
	                   }
	 return $this;
	}


	/**
	* Associa label a campo/i
	 * 
     * @param    string|array $nome_campo nome del campo (Maiuscolo), se  e'  un array deve essere un array associativo campi=>Labels
     * @param    string $label Label da associare al campo
     */
	 public function set_label($nome_campo,$label='') {
	  if (is_array($nome_campo))
	  			foreach ($nome_campo as $id=>$val)
	  				$this->labels[strtoupper($id)]=$val;

	      else $this->labels[strtoupper($nome_campo)]=$label;
	return $this;
	}


	/**
	* Imposta l'autotab nei campi ove possibile
     */
	 public function set_autotab() {
	  $this->autotab=true;
	  return $this;
	}



	/**
	* Restituisca la/e label/s del/i campo/i
	 * 
     * @param    string|array $nome_campo nome del campo, se e' un array  e'  l'elenco delle labels
     * @return   string|array $label Label associate al/ai campo/i
     */
	 public function get_label($nome_campo) {
	  $labels=array_change_key_case($this->labels,CASE_UPPER);
	  if (is_array($nome_campo)) {$lbl=array();
	                              foreach ($nome_campo as $id) $lbl[strtoupper($id)]=$this->trasl($labels[strtoupper($id)],null,self::$encodingOn['label']);
	  							  return $lbl;
	  							  }
	    					 else return $this->trasl($labels[strtoupper($nome_campo)],null,self::$encodingOn['label']);
	}



	/**
	* Restituisce l'ordine di visualizzazione dei campi
     * @return    array
     */
	 public function get_ordine() {
		if (!$this->ordine) $this->ordine=array_keys($this->campi);
		return $this->ordine;
	}


	/**
	* Setta l'ordine di visualizzazione dei campi
     * @param    array $ordine Rappresenta l'ordine di visualizzazione dei campi, $ordine non  e'  associativo e se mancano dei campi, non vengono visualizzati ma restano nel form e se ne tiene conto altrove
     */
       public function set_ordine($ordine) {
         $this->ordine=$this->array_maiuscolo($ordine);
         return $this;
      }



	/**
	* Setta i valori ai campi
     * @param    array $valori Array associativo es $_POST o $_GET
     * @param  int $intersezione 0 setta solo i campi della form aventi chiave in $valori, 1 ricopre tutti i campi con i valori della form con il relativo valore di $valori, se 2 come 1 ma se assente da $valori lo setta a null
     */

	 public function set_values($valori,$intersezione=false) {
	 if (!$this->campi) return $this;
	 if (!$intersezione)
	 		 {foreach (array_keys($this->campi) AS $id)
	            	if (is_object($this->campi[$id]) && 
	            		isset($valori[$this->campi[$id]->get_name()])
	 			 		)
			            	{ //setta il valore e basta
			            		//ECHO $this->campi[$id]->get_name(),'=>',$valori[$this->campi[$id]->get_name()],'<BR>';
			            		/*if($this->prefisso && isset($valori[preg_replace("/^{$this->prefisso}/",'',$this->campi[$id]->get_name())]))
			            						 $this->campi[$id]->set_value($valori[preg_replace("/^{$this->prefisso}/",'',$this->campi[$id]->get_name())]);
			            					else */
			            			$this->campi[$id]->set_value($valori[$this->campi[$id]->get_name()]);
			 		 		}
	 		 }
      	else { if($intersezione==2) foreach ($this->campi as $campo) $campo->set_value('');
      	       foreach ($valori as $id=>&$v) {
      			if (is_object($this->get_campo($id))) //setta solo i valori passati in valori
      		    		$this->get_campo($id)->set_value($v);
      			}
      		 }
      return $this;
	}


	/**
	 * Imposta una dipendenza tra campi
	 *
	 * @param string $campo nome del campo
	 * @param string $relazione espressione di confronto per la verifica della dipendenza
	 * @param mixed $js se false verificva solo in check_errore la dipendenza, se true genera uno js
	 * @param 'disabled'|'hidden' $modo indica se i campi incongruenti sono da disabilitare o nascondere
	 */
	 public function set_dipendenza_campo($campo,$relazione,$js=true,$modo='disabled') {
		self::myForm();
		$sost=false;
		$relazione=str_replace(array("\r","\n","\t"),array('',' ',''),$relazione);
		do {$relazione=str_replace(array("-> "," ->"),array("->","->"),$relazione,$sost);} while ($sost);
		//$relazioni=preg_replace('/([a-z_0-9]+(->[a-z_0-9]+)?)/i',' $1 ',trim((string) $relazioni));
		//do {$relazioni=str_replace("  "," ",$relazioni,$sost);} while ($sost);
		$relazione=trim((string) $relazione);
		$relazioni=array();
		foreach (explode('§',$relazione) as $i=>$token) {
			$token=trim((string) $token);
			if(!$token) continue;
            $m=array();
			if(($i==0 && $relazione[0]!='§')
			    ||!preg_match('/^([a-z_0-9]+(->[a-z_0-9]+)?)/iSs',$token,$m)) $relazioni[]=$token;
				   else{$resto=substr($token,strlen($m[1]));
				   		$token=$m[1];

						$tok=explode('->',$token);

						if (count($tok)>=2 && is_object($this->get_myFormRootContainer()))
														{
															$form="\$this->get_myFormRootContainer()";
															for($i=0;$i<count($tok)-1;$i++) $form.="->get_form('{$tok[$i]}')";
															$tok[0]=$tok[count($tok)-1];
														}

				/*		if (count($tok)==2 && is_object($this->myMultiForm))
														{$form="\$this->myMultiForm->get_form('$tok[0]')";
														 $tok[0]=$tok[1];
														}*/
													else{
														  $form="\$this";
														  if(isset($tok[1])) $tok[0]=$tok[1];
														}
						$cls=new \stdClass();
						$cls->token=$token;
						$cls->form=$form;
						$cls->campo=$tok[0];
						$relazioni[]=$cls;
						$relazioni[]=$resto;
						}
			}
		static $id;
		++$id;
		
		self::$dipendenza_campi['relazioni'][$this->id_istance][strtoupper($campo)][$id]=$relazioni;
		self::$dipendenza_campi['info'][$this->id_istance][strtoupper($campo)][$id]=array('js'=>$js,'modo'=>$modo);
		return $this;
	}


	/**
	 * @ignore
	 */
	protected function check_dipendenze_campo($campo) {
		if (!isset(self::$dipendenza_campi['relazioni']) ||
		    !is_array(self::$dipendenza_campi['relazioni']) ||
		    !isset(self::$dipendenza_campi['relazioni'][$this->get_id_istanza()]) ||
			!is_array(self::$dipendenza_campi['relazioni'][$this->get_id_istanza()])
			) return;
		foreach (self::$dipendenza_campi['relazioni'][$this->get_id_istanza()] as $dip)
			if(isset($dip[strtoupper($campo)]) &&
			   is_array($dip[strtoupper($campo)]))
				foreach ($dip[strtoupper($campo)] as $relazione)
					{$labels=array();
					 if(!$this->campo($campo)->get_notnull() &&
					 	!$this->campo($campo)->get_value()) continue;
					 //echo $campo,'=>',$this->campo($campo)->get_value();
					 $form=$obj=null;
					 foreach ($relazione as $j=>&$cond)
					 				if(is_object($cond)) {eval("\$form={$cond->form};");
					 						 if (is_object($form)) eval("\$obj=\$form->campo('{$cond->campo}');");
					 						 if(!is_object($obj)) $cond=$cond->token;
					 						 				else {
					 						 					  eval("\$labels['strtoupper({$cond->campo})']=trim(\"'\".{$cond->form}->get_label('{$cond->campo}').\"'\");");
					 						 					  eval("\$cond_[$j]=\$this->campo_valorizzato(\$obj,\$scheletroExpr);");
					 						 					  $cond="\$cond_[$j]";
					 						 					  //echo $cond,'=>',$$cond,'<br>';
					 						 					  }
					 						 $form=$obj=null;
					 						}
					 $cerr=error_reporting(0);
					 $espressione='';
					// echo $cond_0,'<br>';
						 eval("\$espressione=(".implode("",$relazione).");");
					// echo $expr,'<br>';
					 error_reporting($cerr);
					 if(!$espressione) return $this->trasl(" § incongruente rispetto a: ".str_replace(',,',',',implode(',',$labels)),null,self::$encodingOn['messages']);
					}
	}



/**
 * @ignore
 * @param int $id_ist id_istanza del campo dipendente
 * @return array
 */	protected function &get_dipendenze_campi($id_ist) {
		$dipendenze=array();

		$istanze=array_keys((array) self::$dipendenza_campi['relazioni']);

		foreach ($istanze as  $id_istanza)
		  if($id_ist==$id_istanza)
			foreach (self::$dipendenza_campi['relazioni'][$id_istanza] as $campo=>&$relazioni)
				 foreach ($relazioni as $idr=>&$relazione) {
			 		 $newrel=array();
					 foreach ($relazione as &$cond)
					 				if(!is_object($cond)) $newrel[]=$cond;
					 					else{
					 					    $form=null;
					 					    eval("\$form={$cond->form};");
					 						$obj='';
					 					    if (is_object($form)) $obj=$form->campo($cond->campo);
					 					    if(!is_object($obj))  $newrel[]=$cond->token;
					 					                   else   $newrel[]=$obj;
					 					    	
					 						}
			 		$dipendenze[strtoupper($campo)][$idr]=$newrel;
				 }
		return $dipendenze;
		/*
		$dipendenza=array();
		if(self::$dipendenza_campi)
		  foreach (self::$dipendenza_campi as $id_istanza=>$Relazioni)
			foreach ($relazioni['relazioni'] as $campo=>$relazioni) {
				 foreach ($relazioni as $relazione) {
			 		 $newrel=array();
					 foreach ($relazione as $cond)
					 				if(!is_object($cond)) $newrel[]=$cond;
					 					else{eval("\$form={$cond->form};");
					 						 if (is_object($form)) eval("\$obj=\$form->campo('{$cond->campo}');");
					 						 if(!is_object($obj))  $newrel[]=$cond->token;
					 						 				else   $newrel[]=$obj;

					 						}
			 		}
					$dipendenze[strtoupper($campo)][]=$newrel;
			}
		return $dipendenze;		*/
	}



/**
 * @ignore
 */
	 public function campo_valorizzato($campo,$expr) {
		$expr=trim((string) $expr);
		if (!$campo) return false;
		if(!$expr)
			    {
			    if (method_exists($campo,'get_checked')) return ($campo->get_checked()?true:false);
			    $val=$campo->get_value();
			    if(is_array($val) && count($val)>0) $val=true;
			    return ($val || !$campo->get_notnull()?true:false);
			    }
		  else{
		    	if (method_exists($campo,'get_checked')) return ($campo->get_checked()?true:false);
				if ($campo->is_numeric()) return  floatval($campo->get_value());
				if ($campo instanceof myDate) return $campo->get_formatted();
				return $campo->get_value();
			 }
	}


	/**
	* Setta il valore di un  campo
     * @param    string $campo
     * @param    mixed $valore
     */

	 public function set_value($campo,$valore) {
	  if (isset($this->campi[$campo]) ) $this->campi[$campo]->set_value($valore);
	  return $this;
	}


	/**
	* Restituisce un array associativo con i valori dei campi
	 * @param    array $campi array con i nomi dei campi da estrapolare, se omesso si estraggono tutti
	 * @param 	 string $label se true la chiave dell'array non  e'  il nome ma la label associata
	 * @param 	 string $name se true la chiave dell'array non e' il nome ma l'attributo name (se anche $label non  e'  presa in considerazione)
	 * @return   array $valori
     */
	 public function get_values($campi='',$label=false,$name=false) {
	  if (!$campi)	$campi=array_keys($this->campi);
	  if (!$campi) return;
	  $valori=array();
	  $campi=array_flip($campi);
	  if (is_array($this->campi))
	  	foreach ($this->campi as $id=>$obj)
	  		if(isset($campi[$id]) && is_object($obj))
	  				{
	  				if($label) $valori[$this->get_label($id)]=$obj->get_value();
	  				 elseif ($name) $valori[str_replace('[]','',$obj->get_name())]=$obj->get_value();
	  				 	else  $valori[$id]=$obj->get_value();
	  				}
	  return $valori;
	}






	/**
	* Restituisce il valore del $nome_campo
     * @param    string $nome_campo
     * @return   mixed $valori
     */
	 public function get_value($nome_campo='') {
		//if(!$nome_campo) return $this->get_values();
		if ($this->campi[$nome_campo]) return $this->campi[$nome_campo]->get_value();
	}

		/**
	* Restituisce il valore del $nome_campo NEL FORMATO DB
     * @param    string $nome_campo
     * @return   mixed $valori
     */
	 public function get_value_db($nome_campo) {
		 return $this->campi[$nome_campo]->get_value_db();
	}


	/**
	* Imposta i tag che devono precedere e seguire ogni campo
     * @param    String $tag e' la sequenza di tag da usare; di default  e'  <tr>.
     *							Mettendo la stringa vuota tutti i campi del get_html saranno su un unica riga
     */

	 public function set_pref_html_field($tag='tr') {
		$this->pref_html=$tag;
		return $this;
	}

	 /** @ignore */
	 public function array_maiuscolo($array){
	    $nuovo=array();
	    if ($array) foreach ($array as $val) $nuovo[]=strtoupper($val);
	 	return $nuovo;
	}




	 /** @ignore */
    public function creaTd($valore='',$classe='',$extra='',$td='td') {
   	   if($this->pref_html=='div') $td='div';
   	   return "<$td  $extra ".($classe?" class=\"$classe\">":'>')."$valore</$td>";
	}


	 /** @ignore */
    public function creaTable($valore='',$classe='',$extra='') {
   	   if(stripos(trim((string) $valore), '<tr')!==0) return $valore;
   	   return "<table  $extra ".($classe?" class=\"$classe\">":'>')."$valore</table>";
	}





/** @ignore */
	 public function _qualicampi($qualicampi='',$Esclusi=true) {
		if ($qualicampi) $qualicampi=array_flip($this->array_maiuscolo($qualicampi));
	 	if (!$this->ordine) $this->ordine=array_keys($this->campi);
	 	$nomicampi=array();
	 	foreach ($this->ordine as $campo) {
	 		if (!$qualicampi) $nomicampi[]=$campo;
	 			elseif(!$Esclusi) {if (isset($qualicampi[$campo])) $nomicampi[]=$campo;}
	 				elseif($Esclusi) {if (!isset($qualicampi[$campo])) $nomicampi[]=$campo;}
	 	}
	 	return $nomicampi;
	}



	/**
	* Imposta lo showonly per tutti i campi ove possibile
	 * @param    string|array $qualicampi campi a cui applicare/non applicare (se omesso si applica a tutti indipendentemente da $Esclusi)
	 * @param 	 boolean $Esclusi se true o omesso $qualicampi sono esclusi dalla funzione se false si applica esclusivamente quelli
	 * @param  array      $attributi array associativo con eventuali attributi class,id,style per lo span con il campo visualizzato 
   * 
     */
	 public function set_showonly($qualicampi='',$Esclusi=true,$attributi=array()) {
	 if(!is_array($qualicampi)) $qualicampi=array($qualicampi);
	 foreach ($this->_qualicampi($qualicampi,$Esclusi) as $campo)
	 	if (is_object($this->campi[$campo]) && method_exists($this->campi[$campo],"set_showonly"))
	 										 $this->campi[$campo]->set_showonly(true,$attributi);
	  $this->isShowonly=true;
      return $this;
	}


	/**
	 * Il campo viene trasformato in myHidden anche dopo l'istanziazione
	 * es.
	 * <code>
	 * $f=new myForm();
	 * $f->add_campo(new myText('testo1','testo 1'));
	 * $f->add_campo(new myText('testo2','testo 2'));
	 *
	 * echo $f->get_html_completo();
	 * echo '<hr />';
	 * $f->set_hidden('testo1');
	 * echo $f->get_html_completo();
	 * </code>
	 *
	 * @param    string|array $qualicampi campi a cui applicare/non applicare (se omesso si applica a tutti indipendentemente da $Esclusi)
	 * @param 	 boolean $Esclusi se true o omesso $qualicampi sono esclusi dalla funzione se false si applica esclusivamente quelli
     * @param    array $campi_esclusi campi da non controllare
     */
	 public function set_hidden($qualicampi='',$Esclusi=true) {
	 if(!is_array($qualicampi)) $qualicampi=array($qualicampi);
	 foreach ($this->_qualicampi($qualicampi,$Esclusi) as $campo)
	 	if (is_object($this->campi[$campo])){
	 										$v=array_flip(get_class_methods(get_class($this->campi[$campo])));
	 										if (isset($v["set_hidden"]))  $this->campi[$campo]->set_hidden();
	 										}
	return $this;
	}


	 public function unset_hidden($qualicampi='',$Esclusi=true) {
	    if(!is_array($qualicampi)) $qualicampi=array($qualicampi);
	    foreach ($this->_qualicampi($qualicampi,$Esclusi) as $campo)
	        if (is_object($this->campi[$campo])){
	                                           $v=array_flip(get_class_methods(get_class($this->campi[$campo])));
	                                           if (isset($v["set_hidden"])) $this->campi[$campo]->unset_hidden();
	                                           }
	    return $this;
	}

	/**
	* Disattiva lo showonly per tutti i campi ove possibile
	 * @param    array $qualicampi campi a cui applicare/non applicare (se omesso si applica a tutti indipendentemente da $Esclusi)
     * @param 	 boolean $Esclusi se true o omesso $qualicampi sono esclusi dalla funzione se false si applica esclusivamente quelli
     */
	 public function unset_showonly($qualicampi='',$Esclusi=true) {
	  if(!is_array($qualicampi)) $qualicampi=array($qualicampi);
	  foreach ($this->_qualicampi($qualicampi,$Esclusi) as $campo)
	 	if (is_object($this->campi[$campo]) && method_exists($this->campi[$campo],"set_showonly"))
	 										 $this->campi[$campo]->unset_showonly();
	  $this->isShowonly=false;
	  return $this;
	}
	
	 public function get_showonly(){
	    return $this->isShowonly;
	}

	/**
	* Effettua analisi di validità  dei campi
     * @param    array $qualicampi campi da controllare/non controllare (se omesso si applica a tutti indipendentemente da $Esclusi)
     * @param 	 boolean $Esclusi  se true o omesso $qualicampi sono esclusi dalla verifica se false si verificano esclusivamente quelli
     * @return    string Messaggio di errore
     */

	 public function check_errore($qualicampi='',$Esclusi=true,$testoLinkCampoErrato='') {
	   $errore=$this->check_errore_diviso($qualicampi,$Esclusi);
	   
	   if ($errore) {
				   	if (is_array($errore['label'])) $lbl=implode(',',$errore['label']);
	   											elseif($errore['campo']) {
	   												 /* $js="<script type='text/javascript'>
	   												  		function SelezionaCampoErrore(){
	   												  		 try
						  										{
													  	  	    elem=document.getElementById('{$errore['campo']->get_id('id')}');
													  	  	    //if (elem.type=='text' || elem.type=='textarea'  || elem.type=='file') elem.select();
	   												  			if (elem.type=='select-one') elem.options[elem.selectedIndex].selected=true;
	   												  		    elem.focus();
						  										}catch (err) {}
	   												  		}
															 if(window.addEventListener) window.addEventListener('load',SelezionaCampoErrore,false);
															 	else if(window.attachEvent) window.attachEvent('onload',SelezionaCampoErrore);
														  </script>";*/
	   												  $lbl=$errore['label'];
	   												 }

					 $out=strtr($errore['errore'],array('%label%'=>$lbl,
					 									'%errore%'=>$errore['errore_puro'])
					 			);
					 if($testoLinkCampoErrato && $lbl && !$this->JQDialog && isset($errore['campo']) && method_exists($errore['campo'],'get_id'))
					                   $out.=" <a href='#id_".$errore['campo']->get_id('id')."' title=\"{$this->trasl('Vai al campo')}\">$testoLinkCampoErrato</a>";
					               
					 $this->onUserError($out); 
					 return $this->buildHTMLErrore($out);
	   				}
	}
	
	
	protected function buildHTMLErrore($errore){
		if($errore && $this->JQDialog) {
		    $m=$js=array();
			preg_match_all('@<script[^>]+.*</script>@US', $errore,$js);
			$this->JQDialog->set_html($errore=str_replace($m[0],'', $errore));
			return $errore.implode("\n",$js[0]).$this->JQDialog;
			}
		return $errore;
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
  	
  	 $f=new myField();
  	 $f->set_lingua($lingua);
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
  								        
  								        foreach ($this->campi as $campo) if(method_exists($campo,'add_dizionario')) $campo->set_lingua($dizionario->get_al())->add_dizionario($dizionario,$lingua);
  								        self::$dizionari=array_merge(self::$dizionari,$this->dizionario);
  								       
  								        $f=new myField();
  								        $f->set_lingua(self::$lingua);
  					                    $f->add_dizionario($dizionario,$lingua);
                    
  				              		   }
  				              		   
  					else throw new \Exception("add_dizionario solo dopo set_lingua");
                }
 
	return $this;
  }
  
  
   public function get_dizionario() {
      $d=$this->get_dizionari();
      return array_shift($d);
  }

/**
 *
 * Restituisce l'elenco di tutti i dizionari attivi.
 * @return array
 */
  public function get_dizionari() {
     if(!$this->dizionario) $this->dizionario=self::$dizionari;
  	 if (!$this->dizionario)  return array();
  				elseif (!is_array($this->dizionario)) return array($this->dizionario);
     							  else return $this->dizionario;
   }

   
   
 /** @ignore */
   public function trasl($messaggio,$parole='',$encode=false) {
     if($encode) $messaggio=myField::charset_encode($messaggio);
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
  

   public static function set_charset($charset,$encode_labels=true,$encode_messages=true,$encode_db_data=false) {
      myField::set_charset($charset);
      if($encode_labels)    self::$encodingOn['label']=$encode_labels;
      if($encode_messages)  self::$encodingOn['message']=$encode_messages;
      if($encode_db_data)   self::$encodingOn['db']=$encode_db_data;
  }
  
   public static function set_internalcache_dir($dir){
  	self::$internalCache=$dir;
  }

  /** @ignore */
  function &MyCacheDati($secondi,$nomeVariabile,$dati='',$prefisso='') {
    if(!self::$internalCache) self::$internalCache=__MYFORM_DATACACHE__;
  	$file=str_replace('\\','/',self::$internalCache.'/'.$prefisso.str_replace(array(':','\\','/','?','*','<','>','|'),array('/','/','/','_','_','_','_'),$nomeVariabile).".inc.php");
  	$sost=false;
  	do{$file=str_replace('//','/',$file,$sost);} while($sost>0);
  	if(strpos('//',self::$internalCache)===0) $file="/$file";
  	$hfile=preg_replace('@[^A-Z^a-z^0-9^_]@','_',$nomeVariabile);
  	$null=null;
  	if(!is_dir(dirname($file))) @mkdir(dirname($file),0770,true);
  	if($secondi==-1)
  					{static $cache;
  					 if(isset($cache[$nomeVariabile])) return $cache[$nomeVariabile];
  					 if(is_file($file))  {  
  					                        include_once($file);
  					                        $classe="__{$hfile}";
  					                        if(!class_exists($classe,false)) return $null;
  					                        $cache[$nomeVariabile]=$classe::getData();
  					                        return $cache[$nomeVariabile];
                          				 }
                   }
  	
  	if($secondi>0) {$this->refreshCaches=true; 
  	             	@file_put_contents($file,$body='<'."?php abstract c"."lass __{$hfile} { static function getData(){ if(time()-".time()."<=$secondi) {return unserialize(base64_decode('".base64_encode(serialize($dati))."'));} } } ?".'>');
  				 	@clearstatcache(true,$file);
        			if (is_file($file) && md5($body)!=@md5_file($file)) @unlink($file);
        		   }
    return $null;    		   
  }

/**
 * Applica la cifratura a tutti i campi nascosti (beta)
 * @param mySecurizer $secure
 * @return myForm
 */
  	 public function set_security(mySecurizer $secure){
  		$this->secure=$secure;
  		foreach ($this->campi as &$campo)
  				if(method_exists($campo,'set_security')) $campo->set_security($this->secure);
  		return $this;
  	}

  	/**
  	 * Forza il check_errore a produrre l'errore indicato a prescindere dai parametri
  	 * @param string $errore Messaggio di errore non tradotto
  	 * @param 'user'|'internal' $tipo tipo di errore
  	 * @param string $campo nome del campo (il nome che si userebbe in una get_campo)
  	 */
  	 public static function forza_errore($errore,$tipo='internal',$campo=''){
  		self::$erroreForzato=array('errore_puro'=>$errore,
  								    'errore_tipo'=>$tipo,
	                        	   'campo'=>$campo);
  	}

	/**
	* Effettua analisi di validità  dei campi
     * @param    array $qualicampi campi da controllare/non controllare (se omesso si applica a tutti indipendentemente da $Esclusi)
     * @param 	 boolean $Esclusi  se true o omesso $qualicampi sono esclusi dalla verifica se falo si verificano esclusivamente quelli
     * @return   array Array associativo con label=>'Label del campo errato' errore=>'messaggio di errore' campo=>'nome del campo errato'
     */
	 public function check_errore_diviso($qualicampi='',$Esclusi=true) {
	  if(self::$erroreForzato)
 			 {$campo=self::$erroreForzato['campo'];
 			  $errore=array('errore_forzato'=>1,
 			  				'errore_puro'=>self::$erroreForzato['errore_puro'],
 			  				'errore_tipo'=>self::$erroreForzato['errore_tipo'],
 			  				'errore'=>self::$erroreForzato['errore_puro']
 			 			 	);

 			  if($this->campi[$campo])
 			            {$prefisso='';
 			             if(self::$Gruppi[spl_object_hash($this->campi[$campo])]) $prefisso=$this->trasl("Nella sezione '%1%'",array('%1%'=>self::$Gruppi[spl_object_hash($this->campi[$campo])]['titolo'])).' ';
 			  			 $errore=array('label'=>$this->get_label($campo),
                   				 	   'campo'=>$this->get_campo($campo),
 			 			 			   'errore'=>ucfirst($prefisso.$this->trasl("il campo '%label%' %errore%"))
 			 			 			   );
 			  			 if($prefisso) $errore['sezione']=self::$Gruppi[spl_object_hash($this->campi[$campo])];
 		 				}
 		 	$this->ultimoErrore=$errore;
 		 	$this->onInternalError($errore);
 		 	return $errore;
 	   		}

  	   		 

	 foreach ($this->_qualicampi($qualicampi,$Esclusi) as $campo)
	 	IF (is_object($this->campi[$campo]) && $this->campi[$campo]->get_formula()==='')
	 		{if (isset($this->dizionario[0])) $this->campi[$campo]->set_lingua($this->dizionario[0]->get_al());
	 		 for ($d=1;$d<count($this->dizionario);$d++)  $this->campi[$campo]->add_dizionario($this->dizionario[$d],$d);
	 		 $errore=$this->campi[$campo]->Errore();
	 		 if($errore && isset(self::$encodingOn['message'])) $errore=myField::charset_encode($errore);
	 		 if (!$errore) $errore=$this->check_dipendenze_campo($campo);
	 		 IF ($errore) { $prefisso='';
	 		                if(isset(self::$Gruppi[spl_object_hash($this->campi[$campo])])) $prefisso=$this->trasl("Nella sezione '%1%'",array('%1%'=>self::$Gruppi[spl_object_hash($this->campi[$campo])]['titolo'])).' ';
	 		 				$errore=array('label'=>$this->get_label($campo),
	                        			 'errore'=>ucfirst($prefisso.$this->trasl("il campo '%label%' %errore%")),
	                        			 'errore_puro'=>$errore,
	 		 							 'errore_forzato'=>0,
	 		 							 'errore_tipo'=>'user',
	                        			 'campo'=>$this->get_campo($campo));
	 		 				if($prefisso) $errore['sezione']=self::$Gruppi[spl_object_hash($this->campi[$campo])];
	 		 				$this->ultimoErrore=$errore;
	 		 				$this->onUserError($errore);
	 		 				return $errore;
	 		 			   }
	 		}
	}


	/**
	* Imposta se visualizzare le labels
     * @param    boolean $mostra_label
     */
	 public function set_mostra_labels($mostra_label=true) {
	  $this->mostra_label=$mostra_label;
	  return $this;
	}


	/**
	 * Definisce il tipo di classe nel metodo @see store
	 *
	 * @throws \Exception generica se non ha entrambi i parametri validi
	 * @param mySessions $container E' un'istanza di mySessions( o estensione)
 	 * @param string $chiave 		Chiave da usare per i dati durante la memorizzazione
	 */
	 public function set_store_params($container,$chiave){
		if(!is_object($container) ||
		   !$chiave ||
		   !method_exists($container,'set') ||
		   !method_exists($container,'get') ||
		   !method_exists($container,'del')) throw new \Exception("Parametri non validi in set_store_params()");
		$this->stored=$container;
		$this->stored_key=$chiave;

		return $this;
	}


/**
 * Memorizza i dati in una sessione
 * @see myForm::set_store_param()
 *
 */
	 public function store_values() {
		if(!$this->stored) throw new \Exception("prima di usare lo storing usare set_store_params");
		$this->stored->set($this->stored_key,$this->get_values(),true);
		return $this;
	}

/**
 * Annulla il contenuto memorizzato in sessione
 * @see myForm::stored_values()
 *
 */
	 public function clean_stored_values(){
		if(!$this->stored) throw new \Exception("prima di usare lo storing usare set_store_params");
		$this->stored->del($this->stored_key);
		return $this;
	}


/**
 * Restituisce il contenuto memorizzato con @see stored_values
 *
 */
	 public function get_stored_values(){
		if(!$this->stored) throw new \Exception("prima di usare lo storing usare set_store_params");
		return $this->stored->get($this->stored_key);
	}


/**
 * Ripristina nella form il contenuto memorizzato con @see stored_values
 *
 */
	 public function restore_values() {
		if(!$this->stored) throw new \Exception("prima di usare lo storing usare set_store_params");
		$stored=$this->stored->get($this->stored_key);
		if($stored)$this->set_values($stored);
		return $this;
	}

	/**
	 * Imposta l'oggetto myJQMyFormTabs da usare per la visualizzazione a tabs di JQuery
	 * @param myJQMyFormTabs $JQ
	 */
	 public function set_myJQTab(myJQMyFormSezioni $JQ) {
		$this->JQTab=$JQ;
		return $this->JQTab;
	}

	/**
	 * Imposta l'oggetto myJJQDialogQMyFormDialof da usare per la visualizzazione a tabs di JQuery
	 * @param myJQMyFormTabs $JQ
	 */
	 public function set_myJQDialogErrore(myJQMyFormDialog $JQ) {
		$this->JQDialog=$JQ;
		$JQ->set_form($this);
		return $this->JQDialog;
	}


    /**
	* Restituisce l'html di una form completa di tage ed eventuali pulsanti
	 *  Es.
	 * <code>
	 * $form=new myForm();    //istanzio form
	 * $form->add_campo(..);  //aggiungo campi
	 *  .......
	 * $form->add_campo(..);
	 *
	 *
	 * $pulsanti[0] = new myPulsante('Salva',$_POST['Salva']);       //creo un array con un pulsante per salvare
	 * $pulsanti[1] = new myPulsante('Elimina',$_POST['Elimina']);   //aggiungo all'array un pulsante per eliminare
	 *
	 * echo $form->Get_html_Completo('','','method="post"','border="1"',$pulsanti);
	 *
	 * </code>
     * @param    array $campo_inizio Nome del campo da cui iniziare nella costruzione, se omesso si comincia dall'inizio
     * @param    string $campo_fine Nome del campo da cui finire nella costruzione, se omesso si usa solo campo inizio, SE OMESSI ENTRAMBI SI USANO TUTTI I CAMPI
     * @param    string $AttributiForm sono eventuali attributi del tag <form> (se omesso o false il tag non verr§ inserito ne chiuso)
     * @param    bool|string $AttributiTable sono eventuali attributi del tag table che contiene i campi (se omesso si inserisce ugualmente se flse non si mette il tag table)
     * @param    array  $Pulsanti array di myPulsante
     * @return   string
     */
  	 function &get_html_completo($campo_inizio='',$campo_fine='',$AttributiForm='',$AttributiTable='',$Pulsanti=array(),$clean=true) {
  	    $pulsanti=array();
  	    $ordine=$this->ordine;
  	    if(!$ordine) $ordine=array_keys($this->campi);
  	    
  	 	if (is_array($Pulsanti)) {foreach ($Pulsanti as $i=>$p) 
  	 	                                       if (is_object($p)) {   if($this->get_dizionario()) $p->set_lingua($this->get_dizionario()->get_al());
  	 	                                                              $pulsanti[]=$p;
  	 	                                                          }
  	 	                          }

  	 	if ($AttributiForm) {	$AttributiForm=new myTag($AttributiForm);
   	 							if (!$AttributiForm->get_attributo('method')) $AttributiForm->set_attributo('method','get');
   	 							$AttributiForm->set_attributo('accept-charset',myField::get_charset());
   	 							foreach ($this->get_campi() as $campo) {
   	 											 	if ($campo->Estende('myUploadText',true))
   	 											 			{$AttributiForm->set_attributo('method','post');
   	 											 			 $AttributiForm->set_attributo('enctype','multipart/form-data');
   	 											 			}
   	 											    }
   	 											 
   	 							if (!$AttributiForm->get_attributo('action')) $AttributiForm->set_attributo('action',$_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING']?'?'.myTag::htmlentities($_SERVER['QUERY_STRING']):''));
  	 																	else {$action=$AttributiForm->get_attributo('action');
  	 																		 if (stripos($action,'&')!==false &&
  	 							    											 stripos($action,'&amp;')===false) $AttributiForm->set_attributo('action',str_replace('&','&amp;',$action));
  	 																	  	}
								$AttributiForm=$AttributiForm->get_html();
  	 						}

		if ($this->muovi_lables<2) {
							 $x=$this->Get_html($campo_inizio,$campo_fine);
						   	 if(stripos($x,'<table')===0) $x=($this->pref_html=='tr'?"<colgroup>
						     									<col {$this->extra_html_label}></col><col {$this->extra_html_valori}></col>
						     	 			 				</colgroup>":'').$x;
						   	 }
						  else{
						  
						  
						  	if($campo_inizio) foreach ($ordine as $i=>$nome_campo) if(strtoupper($nome_campo)!=strtoupper($campo_inizio)) unset($ordine[$i]);
						  	                                                                                             else break;
                           				  	                                                                                             
						  	if($campo_fine)   foreach (array_reverse($ordine,true) as $i=>$nome_campo) if(strtoupper($nome_campo)!=strtoupper($campo_fine)) unset($ordine[$i]);
						  	                                                                                                   else break;
		                    foreach ($ordine as $nome_campo) {
		                             $campo=$this->campo($nome_campo);
									 if($campo && $campo->get_MyType()!='MyHidden')
										 {
										  $si_label=$this->get_label($nome_campo);
									      if ($si_label)
									      			{$x.=$this->creaTd($si_label,($this->classe_label?array('class'=>$this->classe_label):''),$this->extra_html_label.' scope="col"  id="th_'.$nome_campo.'"  ','th');
									      			$colgroup.="<col id='col_$nome_campo' ></col>";
									      			}
					 					 }
		                      }
						 	if($this->pref_html=='tr' && $AttributiTable!==false) $x="<colgroup>$colgroup</colgropup>$x";
					 		$x=(new myTag($this->pref_html,($this->classe_label?array('class'=>$this->classe_label):''),$x)).$this->Get_html($campo_inizio,$campo_fine);
					 		}


	     if ($pulsanti) {   $pulsantiera='';
		 					foreach ($pulsanti as $pls) $pulsantiera.="<div  style='width:".intval(100/count($pulsanti))."%;text-align:center;float:left'>{$pls->get_html()}</div>";
		 					$pulsantiera.='<div style="clear:both"></div>';
		 					$x.=(new myTag($this->pref_html,'',
					 					$this->creaTd(
					 							$pulsantiera,
					 							'',
					 					          ($this->pref_html=='tr'?'colspan="2"':'')." class='myform_pulsantiera' "
					 							)
					 			  )
					 		  );
					 		
					 	 }

		if($AttributiTable!==false) $x=$this->creaTable($x,'',$AttributiTable);
		if($this->JQTab) $x=$this->get_elaborazione_myJQ($x);

		
		if ($AttributiForm) $x="<form $AttributiForm>".$x."</form>";
		
		if(!$this->get_myFormContainer() && isset($this->myForms['DipendenzeCalls'])) $x.="<script type='text/javascript'>{$this->myForms['DipendenzeCalls']}</script>";
		if(!$clean) return $x;
		return myForm::clean_ridondanze_html($x);
	 }

	 /**
	  * Se si sono aggiunti elementi myJQuery attraverso il metodo @see set_myJQtabs()
	  * e non si usa get_html_completo, passare l'html costruito usando  @see get_html()
	  * a questo metodo prima di visualizzarlo; se non si  e'  usato almento get_html() non si avranno effetti JQ automatici
	  * @param string $html
	  */
	  public function get_elaborazione_myJQ($html) {
	 if($this->JQTab) {
	 				$titoli=array();
					foreach (self::$Gruppi as $sez) if($sez['titolo']) $titoli[$sez['titolo']]=1;
					$titoli=array_flip(array_keys($titoli));
					$this->JQTab->add_items($titoli);
					$this->JQTab->set_content($html);

					if($this->ultimoErrore['sezione'])
							$this->JQTab->set_selected($titoli[$this->ultimoErrore['sezione']['titolo']]);
					$html=$this->JQTab;
					}
	   return $html;
	 }


	/**
	 * Imposta la facoltativit§ del tag, se facoltativo @see get_xml non restituir§ nulla
	 * altrimenti si visualizza il tag vuoto <tag />
	 *
	 * @param boolean facoltativo se true l'xml si vede se false non viene mostrato
	 */
    public function set_xml_facoltativo($facoltativo=false){
  	  $this->facoltativoXML=$facoltativo;
  	  return $this;
   }


	 /**
	  * Salva l'xml della form
	  *
	  * @param string $nomeFile percorso del file in cui salvare
	  * @param string $tag  e'  il tag che deve racchiudere la form se omesso viene messo il nome della classe
	  * @param string $case se omesso si rispetta il case originale dei campi (o del DB) , U= maiuscolo L=minuscolo
	  * @param string $header header dell'XML, se false non si mette se nullo si inserisce xml version="1.0" encoding="UTF-8"
	  * @param    array|string $campo_inizio Nome del campo da cui iniziare nella costruzione, se omesso si comincia dall'inizio oppure  e'  l'array con i nomi dei campi da usare (in questo caso il secondo parametro non viene usato)
      * @param    string $campo_fine Nome del campo da cui finire nella costruzione, se omesso si usa solo campo inizio, SE OMESSI ENTRAMBI SI USANO TUTTI I CAMPI
      * @return string
	  */

	 public function salva_xml($nomeFile,$tag='',$case='',$header='',$campo_inizio='',$campo_fine='') {
		if(!$tag) $tag=get_class($this);
		$xml=$this->Get_xml($tag,$case,$header,$campo_inizio,$campo_fine);
		$scritto=@file_put_contents($nomeFile,$xml,LOCK_EX);
		@clearstatcache(true,$nomeFile); 
		if ($scritto!=strlen($xml)) @unlink($nomeFile);
		return $scritto==strlen($xml);
	}


	/**
	 * Carica dati da xml, se si passa un nome di file lo carica da li , se si passa l'xml usa quello i due parametri si escludono, se valorizzati entrambi si usa solo il nomefile
	 *
	 * @param string $nomeFile nome del file contenente xml
	 * @param string $xml xml passato
	 * @return void
	 */
	 public function load_xml($nomeFile='',$xml='') {
		if ($nomeFile) $xml=@simplexml_load_file(realpath($nomeFile),NULL, LIBXML_NOBLANKS|LIBXML_NOCDATA);
				  else $xml=@simplexml_load_string($xml,NULL, LIBXML_NOBLANKS|LIBXML_NOCDATA);
		if(!$xml) return false;
		foreach ($xml as $elemento=>$v)
						{if(!is_object($this->campo($elemento))) continue;
						 $valori=array();
						 foreach ($v as $valore) { $valore.='';
						                            myField::utf8_decode_recursive($valore);
						                            $valori[]=$valore;
						                         }
						     if(!count($valori))  {$v.='';
						                            myField::utf8_decode_recursive($v);
						                            $valori[]=$v;
						                         }


						 if ($this->campo($elemento)->estende('mymulticheck',true))
						  			 $this->campo($elemento)->set_value($valori);
								else $this->campo($elemento)->set_value($valori[0]);
						}
		return true;
	}



	 /**
	  * Imposta i valori di default per i parametrii @see get_xml
	  *
	  * @param string $tag  e'  il tag che deve racchiudere la form se omesso non ci sarà 
	  * @param string $case se omesso si rispetta il case originale dei campi (o del DB) , U= maiuscolo L=minuscolo
	  * @param string $header header dell'XML, se false non si mette se nullo si inserisce xml version="1.0" encoding="UTF-8"
	  */
	 public function set_parametri_xml($tag=null,$case='',$header=''){
		if ($tag!==null) $this->xml['tag']=$tag;
		$this->xml['case']=$case;
		if ($header!=='') $this->xml['header']=$header;
		return $this;
	}


	 /**
	  * Restituisce l'xml della form
	  *
	  * @param string $tag  e'  il tag che deve racchiudere la form se omesso non ci sarà 
	  * @param string $case se omesso si rispetta il case originale dei campi (o del DB) , U= maiuscolo L=minuscolo
	  * @param string $header header dell'XML, se false non si mette se nullo si inserisce xml version="1.0" encoding="UTF-8"
	  * @param    array|string $campo_inizio Nome del campo da cui iniziare nella costruzione, se omesso si comincia dall'inizio oppure  e'  l'array con i nomi dei campi da usare (in questo caso il secondo parametro non viene usato)
      * @param    string $campo_fine Nome del campo da cui finire nella costruzione, se omesso si usa solo campo inizio, SE OMESSI ENTRAMBI SI USANO TUTTI I CAMPI
      * @return string
	  */
	function &get_xml($tag=null,$case='',$header='',$campo_inizio='',$campo_fine='') {
	  if ($tag===null) {$tag=$this->xml['tag'];
	  					if ($tag===null) $tag=get_class($this);
	  					}

	  if (!$case) $case=$this->xml['case'];
	  if ($header==='' && !isset($this->xml['header'])) $header=$this->xml['header'];

	  if (!$this->ordine) $ordine=array_keys($this->campi);
	  				 else $ordine=$this->ordine;
	  if (is_array($campo_inizio)) {$ordine=$campo_inizio;
	  								$campo_inizio=$campo_fine='';
	  								}
	  $campo_inizio=strtoupper($campo_inizio);
	  $campo_fine=strtoupper($campo_fine);
	  $non_saltare=@func_get_arg(5);

	  if ($campo_inizio && !$campo_fine)
	  		{
	  		if(is_object($obj=$this->get_campo($campo_inizio))
	  					 && ($non_saltare || !in_array(spl_object_hash($obj),self::$salta_get_xml)))
	  						{ if(!$case) { $tag_obj=$obj->get_xml_tag(true);
	  									   if(!$tag_obj['ridefinito']) $obj->set_parametri_xml($this->colonneOriginali[$campo_inizio]);
	  									 }
	  						  if($case=='U')   $obj->set_parametri_xml($campo_inizio);
	  						  if($case=='L')   $obj->set_parametri_xml($campo_inizio);
	  						  $xml=$obj->set_xml_facoltativo($this->facoltativoXML)->get_xml();
	  						}
	  	    }

	  else {$iniziato=false;
	  		$ordine=array_flip(array_change_key_case(array_flip($ordine),CASE_UPPER));
	  		foreach ($ordine as $campo)
	  		 	if ($campo==$campo_inizio || $iniziato || !$campo_inizio)
	      		   {
	      		   	$iniziato=true;
	      		   	if(!is_object($this->campo($campo)) ||
	      		   	  (!$non_saltare && in_array(spl_object_hash($obj),self::$salta_get_xml)))  continue;
	      		    if ($campo==$campo_fine) break;
	      		    }
	  		$iniziato=false;

	      	foreach ($ordine as $campo)
	  			if ($campo==$campo_inizio || $iniziato || !$campo_inizio)
	      		   {$obj=$this->get_campo($campo);
	      		   	if(!is_object($obj)  || (!$non_saltare && in_array(spl_object_hash($obj),self::$salta_get_xml)))  continue;

	      		    if(!$case) { $tag_obj=$obj->get_xml_tag(true);
	      		    			 if(!$tag_obj['ridefinito']) $obj->set_parametri_xml($this->colonneOriginali[$campo]);
	  							 }
	  				if($case=='U')   $obj->set_parametri_xml(strtoupper($campo));
	  				if($case=='L')   $obj->set_parametri_xml(strtolower($campo));
	  				if($this->facoltativoXML && (method_exists($obj,'set_xml_facoltativo')))  $obj->set_xml_facoltativo($this->facoltativoXML);
	      		    $xml.=$obj->get_xml();
	      		    $iniziato=true;
	  	   			if ($campo==$campo_fine) break;
	      		    }
	       }
	$xml=trim((string) $xml);
	if($tag)
			{
			 if($xml) $xml="<$tag>$xml</$tag>";
			 	elseif($this->facoltativoXML) return '';
			 		else  $xml="<$tag />";
			}
	if($header===false) return $xml;
	if($header) return $header.$xml;
	return '<?xml version="1.0" encoding="UTF-8" ?>'.$xml;
	}

	/** @ignore */
    public function htmlLabelCampo($campo,$Label=true,$prepost=false) {
   			if (!$this->campo($campo)) return;
   			if(!isset($this->pre_post_label[strtolower($campo)])) $prepost=false;
   			if($prepost) $txtlabel=$this->pre_post_label[strtolower($campo)]['pre'].$this->get_label($campo).$this->pre_post_label[strtolower($campo)]['post'];
   					else $txtlabel=$this->get_label($campo);
   			if ($Label)
   			 	 return '<label for="'.$this->campo($campo)->get_id().'" class="myLabel" id="lbl_'.$this->campo($campo)->get_id().'">'.$txtlabel.'</label>';
   	  		else return '<span class="myLabel" id="lbl_'.$this->campo($campo)->get_id().'">'.$txtlabel.'</span>';
	}


	/**
	 * I campi passati come parametri non verranno usati in @see get_html e @see get_html_completo
	 *
	 *  @param    string $campo ....
     * es
	 * <code>
	 *   $f->salta_get_html('nascondi1','nascondi2' ... );
	 * </code>
	 *
	 */
	 public function salta_get_html() {
		foreach (func_get_args() as $campo)
			if (is_object($this->campo($campo)) ) self::$salta_get_html[]=spl_object_hash($this->campo($campo));
	}


	/**
	 * I campi passati come parametri non verranno usati in @see get_xml
	 *
	 *  @param    string $campo ....
     * es
	 * <code>
	 *   $f->salta_get_xml('nascondi1','nascondi2' ... );
	 * </code>
	 *
	 */
	 public function salta_get_xml() {
		foreach (func_get_args() as $campo) if (is_object($this->campo($campo)))   self::$salta_get_xml[]=spl_object_hash($this->campo($campo));
	}

	/**
	 * Imposta un un gruppo di campi @see myGroupFields
	 *
	 * @param myGroupFields $myGroupFields
	 * @param string|boolean $dopo funzione in modo analogo ad @see add_campo
	 */
	 public function set_myGroupFields(myGroupFields $myGroupFields,$dopo='') {
		$myGroupFields->in_form($this);
		$this->add_campo($myGroupFields,$myGroupFields->get_label(),false,$dopo);
		return $myGroupFields;
	}


	/**
	* Restituisce l'html dei campi sotto forma di <table> senza tag di inizio e fine
	 *
     * @param    array|string $campo_inizio Nome del campo da cui iniziare nella costruzione, se omesso si comincia dall'inizio oppure  e'  l'array con i nomi dei campi da usare (in questo caso il secondo parametro non viene usato)
     * @param    string $campo_fine Nome del campo da cui finire nella costruzione, se omesso si usa solo campo inizio, SE OMESSI ENTRAMBI SI USANO TUTTI I CAMPI
     * @return   string
     */


	protected static function get_id_sezione($titolo){
		return "mt_".str_replace('=','_',base64_encode($titolo));
	}

	/**
	 * @ignore
	 */
	 public function get_pref_html(){
		return $this->pref_html;
	}
	
/**
 * @ignore
 * 
 */
	static function &clean_ridondanze_html($Html){
	    $Html.=''; //per sicurezza forza cast a stringa;
	    $out='';
		if(!$Html) return $out;
		$max_mem=ini_get('memory_limit');
		ini_set('memory_limit', '-1');
		$JS=array();
		$tags=explode('<',$Html);
		if($tags) 
		  for($i=0;$i<count($tags);$i++) 
		      if(stripos($tags[$i],'script ')===0 && 
		         stripos($tags[$i],' src ')!==false &&
		         stripos($tags[$i+1],'/script>')===0) 
		                                      {
		                                      $JS[0][]='<'.$tags[$i].'<'.strstr($tags[$i+1], '>',true).'>';
		                                      $i++;
		                                      }

		static $js;
		if(!$js)  {$x=new myField('');
		           $js=$x->get_js_common();
		          }
		
		if(isset($JS[0]))
		            {$eliminati=array_unique($JS[0]);
		             $sostituiti=false;
		             $primo=array_shift($eliminati);
    		         $Html=str_replace($eliminati,'', $Html);
    		         $Html=str_replace($primo, $js.$primo."\n".implode("\n",$eliminati), $Html,$sostituiti);
    		         if(!$sostituiti)    $Html=$js.$primo."\n".implode("\n",$eliminati). $Html;
    		         }
		$parti=explode($js,$Html,2);
		if(count($parti)==1) {ini_set('memory_limit', $max_mem);
		                      return $parti[0];
		                     }
		$parti[1]=str_replace($js, '', $parti[1]);
		$out=$parti[0].$js.$parti[1];
		ini_set('memory_limit', $max_mem);
		return $out;
	}
	
	
	
	/**
	 * Cambia la posizione della label rispetto al campo
	 * @param boolean $stato
	 */
	 public function set_PosizioneLabel($destra=true){
	    $this->posLabel=$destra;return $this;
	}
	
	
	function &get_html($campo_inizio='',$campo_fine='',$clean=true) {

	  if (!$this->ordine) $ordine=$this->colonne;
	  				 else $ordine=$this->ordine;
	  if(!$ordine) $ordine=array_keys($this->campi);
	  if (is_array($campo_inizio)) {$ordine=$campo_inizio;
	  								$campo_inizio=$campo_fine='';
	  								}
	  $campo_inizio=strtoupper($campo_inizio);
	  $campo_fine=strtoupper($campo_fine);
	  $Nascosti='';
      $x='';  
	  //html di un solo campo
	  if ($campo_inizio && !$campo_fine)
	  		{
	  		 if (in_array(spl_object_hash($this->campi[$campo_inizio]),self::$salta_get_html)) return $x;
	  		 if ($this->campi[$campo_inizio]->is_hidden()) return $this->Get_html_campo($campo_inizio);
	 		   else {
	  	 			 $X='';
      		         $si_label=$this->Get_html_label($campo_inizio);
      		         if ($si_label && !$this->muovi_lables && !$this->posLabel)
      		         			{
      		         			 $X.=$this->creaTd($si_label,$this->classe_label,$this->extra_html_label);
      		         			}
      		         $X.=$this->creaTd($this->Get_html_campo($campo_inizio),$this->classe_valori,$this->extra_html_valori.(!$si_label?' colspan="2" ':''));
      		         if ($si_label && !$this->muovi_lables && $this->posLabel)
      		                    {
      		                     $X.=$this->creaTd($si_label,$this->classe_label,$this->extra_html_label);
      		                    }
      		    	 if (!$this->orizzontale) 
      		    	           {    if($this->pref_html=='div') $X.='<br style="clear:both" class="myform_interline" />';
      		    	             $X=new myTag($this->pref_html,array('id'=>"tr_{$this->campi[$campo_inizio]->get_id()}"),$X);
      		    	             if($this->classe_righe) $X->set_attributo('class',$this->classe_righe); 
      		    	             $X.="\n";
      		    	           }
      		    	 $X.='';
      		    	 if(!$clean) return $X;
      		    	 return myForm::clean_ridondanze_html($X);
	  	 		    }
	  	    }

	  else {$ordine=array_flip(array_change_key_case(array_flip($ordine),CASE_UPPER));
	  		//crea parti di html dei campi nascosti
	  		$iniziato=false;
	  		
	      	foreach ($ordine as $campo)
	  			if ($campo==$campo_inizio || $iniziato || !$campo_inizio)
	      		   {
	      		   	if(!is_object($this->campi[$campo])
	      		   	    ||
	      		   	   in_array(spl_object_hash($this->campi[$campo]),self::$salta_get_html)) continue;

	      		    if (!($this->campi[$campo] instanceOf myGroupFields) && $this->campi[$campo]->is_hidden() ) $Nascosti.=$this->Get_html_campo($campo);
	      		    else{$X='';
	      		         $si_label=$this->Get_html_label($campo);

	      		         if ($si_label && !$this->muovi_lables && $this->mostra_label && !$this->posLabel)
	      		         			{
	      		         			 $X.=$this->creaTd($si_label,$this->classe_label,$this->extra_html_label);
	      		         			}
						 
	      		         $X.=$this->creaTd($this->Get_html_campo($campo),$this->classe_valori,$this->extra_html_valori.(!$si_label?' colspan="2" ':''));

	      		         if ($si_label && !$this->muovi_lables && $this->mostra_label && $this->posLabel)
	      		                 {
	      		                         $X.=$this->creaTd($si_label,$this->classe_label,$this->extra_html_label);
	      		                 }

	      		    	 if (!$this->orizzontale)  
	      		    	                  {if($this->pref_html=='div') $X.='<br style="clear:both"  class="myform_interline" />';
	      		    	                   $X=new myTag($this->pref_html,array('id'=>"tr_{$this->campi[$campo]->get_id()}"),$X);
	      		    	                   if($this->classe_righe) $X->set_attributo('class',$this->classe_righe);
	      		    	                   $X.="\n";
	      		    	                  }
                         if(isset(self::$Gruppi[spl_object_hash($this->campi[$campo])]['tipo']))
           		    	  switch (self::$Gruppi[spl_object_hash($this->campi[$campo])]['tipo']) {
	      		    	 	case 'I':
	      		    	 	 self::$Gruppi['content'][self::$Gruppi[spl_object_hash($this->campi[$campo])]['titolo']]=
	      		    	 	                          (!$this->JQTab?"<div><fieldset><legend>".self::$Gruppi[spl_object_hash($this->campi[$campo])]['titolo']."</legend>":
	      		  	 				 	  			    " <noscript><hr />".self::$Gruppi[spl_object_hash($this->campi[$campo])]['titolo']."</noscript>")
	      		  	 				 	   .($this->pref_html!='tr'?
	      		  	 				 				"<{$this->pref_html}>":
	      		  	 				 				"<table>
	      		  	 				 					<colgroup>
						     								<col {$this->extra_html_label}></col><col {$this->extra_html_valori}></col>
						     	 			 			</colgroup>").$X;
	      		  	 		 $X='';
	      		    	 	break;

	      		    	 	case 'X':
	      		    	 	 self::$Gruppi['content'][self::$Gruppi[spl_object_hash($this->campi[$campo])]['titolo']].=$X;
	      		    	 	 $X='';
	      		    	 	break;

	      		    	 	case 'F':
	      		    	 	 $X=self::$Gruppi['content'][self::$Gruppi[spl_object_hash($this->campi[$campo])]['titolo']].$X.
	      		  	 						"</".($this->pref_html=='tr'?'table':$this->pref_html).">"
	      		  	 				 	 .(!$this->JQTab?"</fieldset>":'');
	      		  	 		 if($this->JQTab) $X=$this->JQTab->get_panel(self::$Gruppi[spl_object_hash($this->campi[$campo])]['titolo'], $X);
	      		    	 	break;
	      		    	 }

	      		    	 $x.=$X;
	      		     	}
	      		    $iniziato=true;
	  	   			if ($campo==$campo_fine) break;
	      		    }



			if($Nascosti)
					{
					    if (!$this->orizzontale) $x.="<{$this->pref_html} style=\"display:none;height:0px;margin:0px;border:0px;padding:0px\">\n";
					    $x.=$this->creaTd($Nascosti.'','',(!$this->orizzontale?' colspan="2" style="display:none;height:0px;margin:0px;border:0px;padding:0px" ':' style="display:none;height:0px;margin:0px;border:0px;padding:0px" '));
					    if (!$this->orizzontale)  $x.="</{$this->pref_html}>\n";
					} 
			if ($x && $this->orizzontale) $x=new myTag($this->pref_html,array('class'=>$this->classe_valori),$x);
			if(!$clean) return  $x;
	  		return myForm::clean_ridondanze_html($x);
	       }
	}


	/**
	 * Imposta modalit§ di visualizzazione campi, vedere anche @see set_showLables
	 *
	 * @param boolean $orizzontale se true e' orizzontale se false  e'  verticale
	 */
	 public function set_orientamento($orizzontale=true) {
		$this->orizzontale=$orizzontale;
	 	return $this;
	}


	/**
	 * Cambia l'impostazione di visualizzazioen delle labels vedere anche @see set_orientamento
	 *
	 * @param '0'|'1'|'2' $modalita 0=standard (davanti al campo), 1=nessuna,2= con get_html_completo diventa intestazione
	 */
	 public function set_showLables($modalita=0) {
	 	$this->muovi_lables=$modalita;
		return $this;
	}

	/**
	 * @ignore
	 */
	 public static function getcast_js($cond) {
		if($cond instanceof myDate) $cast='date';
				 elseif($cond instanceof myTime ||
				 		$cond instanceof myOra) $cast='hour';
				 elseif($cond->Estende('myFloat',true)) $cast="float,'$cond->get_separatore()'";
			     elseif($cond->Estende('myInt',true)) $cast='int';
				 elseif($cond->Estende('myRadio',true)) $cast='radio';
				 elseif($cond->Estende('mySelect',true)) $cast='select';
				 elseif($cond->Estende('myMulticheck',true)) $cast='multicheck';
				 elseif($cond->Estende('myCheck',true)) $cast='check';
				 else $cast='';
		return $cast;
	}

	/**
	 * @ignore
	 */
	protected function estrai_id_da_tag($x) {
	    $ids=$tag_implicati=array();
	    foreach (explode('>',$x) as $tag)   if( preg_match('@<.+\s+id=("[^"]+")@sU',$tag,$ids))  $tag_implicati[]=$ids[1];
	    return $tag_implicati;
	}
	

	/**
	 * @ignore
	 */
	protected function calcola_dipendenze() {
	    static $cache;
	    if($cache!='' && $cache==sha1(serialize(self::$dipendenza_campi))) return;
		$allFunc=&self::$dipendenza_campi['js_dipendenze_campo']['allFunc'];
		$funzioni=&self::$dipendenza_campi['js_dipendenze_campo']['funzioni'];
		$dipendenza=&self::$dipendenza_campi['js_dipendenze_campo']['dipendenza'];

		$field=new myField();
		//echo '<br>',$this->get_id_in_myFormContainer(),'=0>';
		//estrae le dipendenze di questo
		$extra='';
		$dipendenze=(array) $this->get_dipendenze_campi($this->id_istance);
		$tag_implicati=array();
		foreach ($dipendenze as $campo_dipendente=>&$relazioni)
			if($this->campo($campo_dipendente) &&
			   !$this->campo($campo_dipendente)->is_hidden())
				  {
				  $static=$field->myFields;
				  myJQuery::disabilita_get_src();
				  $field->myFields['CONTESTO']='myForm::calcolo_dipendenze';
				  $id_dipendente=$this->campo($campo_dipendente)->get_id();
				        $x=$this->campo($campo_dipendente)->clonami()->get_html();
				     	if ($x) {$pre=$post='';
	  						     if(isset($this->pre_post_campo[strtolower($campo_dipendente)]['pre']))  $pre="<span id=\"pre_{$id_dipendente}\">{$this->pre_post_campo[strtolower($campo_dipendente)]['pre']}</span>";
	  		    				 if(isset($this->pre_post_campo[strtolower($campo_dipendente)]['post'])) $post="<span id=\"post_{$id_dipendente}\">{$this->pre_post_campo[strtolower($campo_dipendente)]['post']}</span>";
	  		    				 $x=$pre.$x.$post;
	  							}

    				#preg_match_all('@<.+\s+id=("[^"]+").*>@sSU',$x,$tag_implicati);
				  $tag_implicati[1]=$this->estrai_id_da_tag($x);
	  					
	  					
				  myJQuery::abilita_get_src();
	  			  unset( $field->myFields['CONTESTO']);
	  			  $field->myFields=$static;

	  			  foreach ($relazioni as $idr=>&$relazione)
					{
				    if (isset(self::$dipendenza_campi['info'][$this->id_istance][strtoupper($campo_dipendente)][$idr]['js']) &&
  					         !self::$dipendenza_campi['info'][$this->id_istance][strtoupper($campo_dipendente)][$idr]['js']) continue;
					    
					$exp='';
					$tags=$tag_implicati[1];
					$modo=self::$dipendenza_campi['info'][$this->id_istance][strtoupper($campo_dipendente)][$idr]['modo'];

					if ($modo=='hidden')
								{$tag_labels=array();
								 #preg_match_all('@<.+\s+id=("[^"]+").*>@sSU',$this->Get_html_label($campo_dipendente),$tag_labels);
								 $tag_labels[1]=$this->estrai_id_da_tag($this->Get_html_label($campo_dipendente));
								 if($tag_labels[1]) $tags=array_merge($tags,$tag_labels[1]);
								}
					$trovata=false;
					foreach ($relazione as &$cond) {
					 				if(!is_object($cond))
					 							{$exp.=$cond;
					 							 $trovata=true;
					 							}
					 						else{
					 						    if($cond->is_hidden()) continue;
					 						    $cast=self::getcast_js($cond);
					 						  	$id_cond=$cond->get_id();
					 						  	if($cond->get_showonly()==1) $cast='show';
					 						  	$exp.=" myGetValueCampo(\"{$id_cond}\",'$cast',function(){ {$cond->get_js_chk()} } ) ";
					 						    if(strpos($id_cond, 'ID_STRUTTURA')!=false && strpos($id_cond, '__')==false) throw  new \Exception();
					 							//meglio non fare niente in alcuni casi il campo potrebbe risultare obbligatorio durante costr JS ma poi non esserlo più e poi rimane il vincolo
					 						//	$exp.=" myGetValueCampo(\"{$id_cond}\",'$cast',null ) ";
					 							$dipendenza[$id_cond][$campo_dipendente]=$id_dipendente;
					 						//	echo '<hr>',$id_dipendente,' dipende da ',$id_cond;

					 							$trovata=true;
					 						 	}
					 						}

					 if (!$trovata || (isset($funzioni[$id_dipendente]['chiamata'][$idr]) && $funzioni[$id_dipendente]['chiamata'][$idr])) continue;
					 
					 $exp=preg_replace('/\barray\(/Usi',' new Array(',$exp);
					 
					 $allFunc[$this->id_istance][]=$funzioni[$id_dipendente]['chiamata'][$idr]="my_dipendenza_campo_{$id_dipendente}_$idr();";
                     $funzioni[$id_dipendente]['testo'][$idr]=
					 		 "function my_dipendenza_campo_{$id_dipendente}_$idr(){
					 			  var campi=new Array(".implode(',',$tags).");
					 			  try {
					 			  		if($exp) myFieldStatoCampi(campi,false,'$modo','{$id_dipendente}');
					 			     	   else  myFieldStatoCampi(campi,true,'$modo','{$id_dipendente}');
					 			  }

					 			  catch (Exception)
					 			  		{
					 			  		    myFieldStatoCampi(campi,true,'$modo','{$id_dipendente}');
										}
					 			  $extra;
					 		 }";
					}
				}
		
	$cache=sha1(serialize(self::$dipendenza_campi));
    //self::$dipendenza_campi['daClonare']='';print_r(self::$dipendenza_campi);
	}


/**
 * @ignore
 * @param string $campo
 * @return string restituisce lo js per la costruzione della dipendenza di un campo
 */
	protected function get_js_dipendenze_campo($campo) {
	    $funz=$js='';
		$allFunc=&self::$dipendenza_campi['js_dipendenze_campo']['allFunc'];
		if(!$campo) {foreach ($allFunc as $jsarray)
								foreach (array_unique((array) $jsarray) as $js)
											$funz.="try{ $js }catch (err) {};";
					 self::$dipendenza_campi['js_dipendenze_campo']['allFunc']=array();
					 return  $funz;
					 }

		$funzioni=&self::$dipendenza_campi['js_dipendenze_campo']['funzioni'];
		$dipendenza=&self::$dipendenza_campi['js_dipendenze_campo']['dipendenza'];
		$id_campo_dipendente=$this->campo($campo)->get_id();
		$eventi=$fatti=$ids=array();
		$tipo=null;
		if(isset($dipendenza[$id_campo_dipendente]) && $dipendenza[$id_campo_dipendente] &&	!$this->campo($campo)->is_hidden())
					{
					 IF($this->campo($campo)->estende('mySelect',true)) $tipo='select';
					 	elseif($this->campo($campo)->estende('myMulticheck',true)) $tipo='multicheck';
					 		elseif($this->campo($campo)->estende('myCheck',true)) $tipo='check';
					 			elseif($this->campo($campo)->estende('myDate',true)) $tipo='data';
					//echo $campo,'=>',$this->campo($campo)->get_mytype(),'=>',$tipo,"<br>\n";
					 switch ($tipo) {
					 	case 'select':
					 		$eventi['onchange']=explode(';',(string) $this->campo($campo)->get_attributo('onchange'));
					 		$eventi['onkeyup']=explode(';',(string) $this->campo($campo)->get_attributo('onkeyup'));;
					 		$eventi['input']=explode(';',(string) $this->campo($campo)->get_attributo('input'));;
					 	break;
					 	case 'multicheck':
					 	case 'check':
					 		$eventi['onclick']=explode(';',(string) $this->campo($campo)->get_attributo('onclick'));
					 		$eventi['onkeyup']=explode(';',(string) $this->campo($campo)->get_attributo('onkeyup'));
					 		$eventi['onchange']=explode(';',(string) $this->campo($campo)->get_attributo('onchange'));
					 		$eventi['input']=explode(';',(string) $this->campo($campo)->get_attributo('input'));;
					 		break;
					 	default:
					 		$eventi['onfocus']=explode(';',(string) $this->campo($campo)->get_attributo('onfocus'));
					 		$eventi['onkeyup']=explode(';',(string) $this->campo($campo)->get_attributo('onkeyup'));
					 		$eventi['input']=explode(';',(string) $this->campo($campo)->get_attributo('input'));;
					 	break;
					 }

						/*echo '<hr>';echo $id_campo_dipendente;
						print_r($dipendenza[$id_campo_dipendente]);
						print_r($funzioni);*/
					do {
					   if(isset($dipendenza[$id_campo_dipendente]))
					     foreach ($dipendenza[$id_campo_dipendente] as $campo_condizione)
							   {
							    $fatti[$id_campo_dipendente]=$id_campo_dipendente;
							    if(!isset($fatti[$campo_condizione])) $ids[]=$campo_condizione;
							    if(isset($funzioni[$campo_condizione]['testo'])) 
							         foreach ($funzioni[$campo_condizione]['testo'] as $testo)
							    						{$js.=$testo;
							    						 $testo='';
							    						}

							    if(isset($funzioni[$campo_condizione]['chiamata'])) 
							       foreach ($funzioni[$campo_condizione]['chiamata'] as $chiamata)
							    	    foreach ($eventi as $evento=>&$chiamate) {
							    		   $chiamata=preg_replace(array('/^;/','/;$/'),array('',''), trim((string) $chiamata));
							    		     if(!in_array($chiamata,$chiamate))  $chiamate[]=$chiamata;
							    		     $chiamate=array_unique($chiamate);
							    	        }
							    
							    foreach ($eventi as $evento=>&$chiamate)
							    	switch ($tipo) {
							 			case 'multicheck':
							 					  $opzioni=$this->campo($campo)->get_opzioni();
							 					  $this->campo($campo)->setjs(array_values($opzioni),implode(';',array_unique($chiamate)),$evento);
							 			break;
							    		default:
							    			 $this->campo($campo)->set_attributo($evento,implode(';',array_unique($chiamate)),false);
							    		break;
							    		}
							    }

					    $ids=array_diff($ids,array_values($fatti));
					    $id_campo_dipendente=array_pop($ids);

					 } while ($id_campo_dipendente!='');
					 if ($js) $js="<script type='text/javascript'>$js</script>";

					 return $js;
					}

		}


	/** */
	 public function flush_dipendenze_campo() {
			 $this->calcola_dipendenze();
	   	     //if($this->myMultiForm) $this->myMultiForm->flush_dipendenze_campo();
	}



	/**Restituisce l'html della label di un certo campo senza formattazione tabellare
	 *
     * @param    string $campo Nome del campo da estrapolare
     * @return   string
     */
	function &get_html_campo($campo) {
	  $this->calcola_dipendenze();
	  $js_dipendenze=$jsCommon='';
	 
	  $campObj=$this->campo($campo);
	  if ($campObj) {
	    if (isset($this->dizionario[0]))
	    				{ //echo "$campo applico dizionario {$this->dizionario[0]->get_al()}<br>";
	    				  $campObj->set_lingua($this->dizionario[0]->get_al());
	    				}
	  	for ($d=1;$d<count($this->dizionario);$d++)
	  					{
	  						$campObj->add_dizionario($this->dizionario[$d],$d);
	  					}
	  	if ($this->get_label($campo) && !$campObj->get_tooltip() && !$campObj->get_attributo('title') && !$campObj->get_attributo('alt'))
	  									{
	  									  //if($campObj->get_tooltip()!==null)
	  									  	 $campObj->set_tooltip(strip_tags($this->get_label($campo)));
	  									}
	  	if ($this->autotab) $campObj->set_autotab();
		if($this->secure && method_exists($campObj,'set_security')) $campObj->set_security($this->secure);
		if($this->pref_html=='div' && method_exists($campObj,'set_usaDIV')) $campObj->set_usaDIV();
	  	if($campObj->get_MyType()!='MyHidden')
	  				{
	  				 $js_dipendenze=$this->get_js_dipendenze_campo($campo);

	  				 if($js_dipendenze)
	  				 		{ /*$f=new myField('');
	  				 		  if(!$f->myFields['static']['common'])
	  				 		  		  { $jsCommon=$f->get_js_common();
  				 		  			    $f->myFields['static']['common']=1;
  				 		  			  }*/
  				 		  	$funzioni_dipendenze=$this->get_js_dipendenze_campo('');
  				 		  	if($funzioni_dipendenze) {if(!isset($this->myForms['DipendenzeCalls']))  $this->myForms['DipendenzeCalls']=$funzioni_dipendenze.';';
  				 		  	                                                                  else   $this->myForms['DipendenzeCalls'].=$funzioni_dipendenze.';';
  				 		  							  $jsCommon.="	    var myHiddenElement='';
	  				 		  			   							    if(isNaN(defaultDipendenze)) var defaultDipendenze=0;
	  				 		  			   							     if(window.addEventListener) window.addEventListener('load',function(){ 	defaultDipendenze++; {$funzioni_dipendenze};defaultDipendenze--; },false);
  				 		  			   											else if(window.attachEvent) window.attachEvent('onload',function(){	defaultDipendenze++; {$funzioni_dipendenze} ;	defaultDipendenze--;});
  				 		  			   								";
  				 		  							 
  				 		  								}
					 		}
					}

	  	$x= ($campObj->get_MyType()!='MyHidden'?"<a name='id_".$campObj->get_id()."'></a>":'').$campObj->get_html().$js_dipendenze;

	  	$campo=strtolower($campo);
	  	if ($x) {$pre=$post='';
	  		    if(isset($this->pre_post_campo[$campo]['pre']))   $pre="<span id=\"pre_{$campObj->get_id()}\">{$this->pre_post_campo[$campo]['pre']}</span>";
	  		    if(isset($this->pre_post_campo[$campo]['post']))  $post="<span id=\"post_{$campObj->get_id()}\">{$this->pre_post_campo[$campo]['post']}</span>";
	  		    $x=$pre.$x.$post;
	  			}
		if(trim(preg_replace(array('@<a[^>]+></a>@Ui','@<scrip.+</script>@Ui'), '', $x))=='') $x='';
                		elseif($jsCommon!='') $x.="<script type='text/javascript'>
                		                                  //<!--
                		                                  $jsCommon
                		                                  //-->
                		                         </script>";
	  	return $x;
	  }
   }


	/**
	 * Setta dell'html da inserire prima e/o dopo un campo
	 *
	 * @param string $campo		nome del campo
	 * @param string $html_pre  htlm da mettere prima
	 * @param string $html_post html da mettere dopo
	 * @param boolean $accoda_pre se true il parametro si accoda ad altri precedentemente passati altrimenti sostituisce
    * @param  boolean $accoda_post se true il parametro si accoda ad altri precedentemente passati altrimenti sostituisce
    * @return myForm
    */
    public function set_pre_post_campo($campo,$html_pre='',$html_post='',$accoda_pre=true,$accoda_post=true){
   	  $campo=strtolower($campo);
   	  if(!isset($this->pre_post_campo[$campo]['pre'])) $this->pre_post_campo[$campo]['pre']='';
   	  $this->pre_post_campo[$campo]['pre']=($accoda_pre?$this->pre_post_campo[$campo]['pre'].$html_pre:$html_pre);
   	  if(!isset($this->pre_post_campo[$campo]['post'])) $this->pre_post_campo[$campo]['post']='';
   	  $this->pre_post_campo[$campo]['post']=($accoda_post?$this->pre_post_campo[$campo]['post'].$html_post:$html_post);
   	  return $this;
   }

   /**
    * Uguale a set_pre_post_campo() ma cumulativa passando degli array
    *<code>
    * //vogliamo inserire un asterisco dopo il campo PIPPO ed un meno prima del campo PLUTO
    * $form->set_pre_post_campi( array('PLUTO'=>'-') , array('PIPPO'=>'*') );
    * </code>
    * @param array $html_pre	array associativo con l'html da inserire prima del campo  associato
    * @param array $html_post   array associativo con l'html da inserire dopo il campo  associato
    */
    public function set_pre_post_campi($html_pre=array(),$html_post=array()){
   	   if ($html_pre)  foreach ($html_pre as $campo=>$html)  $this->set_pre_post_campo($campo,$html,'');
   	   if ($html_post) foreach ($html_post as $campo=>$html) $this->set_pre_post_campo($campo,'',$html);
   	    return $this;
   }


   /**
	 * Setta dell'html da inserire prima e/o dopo una label
	 *
	 * @param string $campo		nome del campo
	 * @param string $html_pre  htlm da mettere prima la label associata
	 * @param string $html_post html da mettere dopo   dopo la label associata
	 */
    public function set_pre_post_label($campo,$html_pre='',$html_post=''){
   	  $campo=strtolower($campo);
   	  if(!isset( $this->pre_post_label[$campo]['pre']))  $this->pre_post_label[$campo]['pre']='';
   	  if(!isset( $this->pre_post_label[$campo]['post']))  $this->pre_post_label[$campo]['post']='';
   	  $this->pre_post_label[$campo]['pre'].=$html_pre;
   	  $this->pre_post_label[$campo]['post'].=$html_post;
   	  return $this;
   }

    /**
    * Uguale a set_pre_post_label() ma cumulativa passando degli array
    *<code>
    * //vogliamo inserire un asterisco dopo il campo PIPPO ed un meno prima del campo PLUTO
    * $form->set_pre_post_campi( array('PLUTO'=>'-') , array('PIPPO'=>'*') );
    * </code>
    * @param array $html_pre	array associativo con l'html da inserire prima della label associata
    * @param array $html_post   array associativo con l'html da inserire dopo la label associata
    */
   public function set_pre_post_labels($html_pre=array(),$html_post=array()){
  	   if ($html_pre)  foreach ($html_pre as $campo=>$html)  $this->set_pre_post_label($campo,$html,'');
   	   if ($html_post) foreach ($html_post as $campo=>$html) $this->set_pre_post_label($campo,'',$html);
   	    return $this;
   }



	/**
	* Restituisce l'html del singolo campo senza formattazione tabellare
	 *
     * @param    string $campo Nome del campo da estrapolare
     * @return   string
     */
	function &get_html_label($campo) {
	    if ($this->get_label($campo) && $this->get_campo($campo)->Prevede_label)
	  	 		    	 {
	  	 		    	  $x=$this->htmlLabelCampo($campo,$this->get_campo($campo)->Richiede_tag_label);
	  	 		    	  if ($x && isset($this->pre_post_label[strtolower($campo)]['pre'])) $x=$this->pre_post_label[strtolower($campo)]['pre'].$x;
	  	 		    	  if ($x && isset($this->pre_post_label[strtolower($campo)]['post'])) $x=$x.$this->pre_post_label[strtolower($campo)]['post'];
	 					 }
	    return $x;
	}





	/**
	 * Aggiunge un segno dopo le label impostati non nulli
	 *
	 * @param string $segno da mettere
	 * @param int spazi tra label e segno
	 * @param boolean prima o dopo la label
	 */
	 public function set_segno_notnull($segno='*',$spazi=1,$dopo=true){
	    $blank='';
		for ($i=0;$i<$spazi;$i++) $blank.='&nbsp;';
		foreach ($this->get_campi() as $nome=>$campo)
					if ($campo->get_notnull()) {
					     if ($dopo)  $this->set_pre_post_label($nome,'',$blank.$segno);
					     		else $this->set_pre_post_label($nome,$segno.$blank,'');
					}
		 return $this;
	}


	/**
	 * Aggiunge un segno § dopo tutti i capmi myEuro
	 *
	 * @param string $segno da mettere
	 * @param int spazi tra campo e segno
	 * @param boolean prima o dopo il campo
	 */
	 public function set_segno_euro($segno='&euro;',$spazi=1,$dopo=true){
		for ($i=0;$i<$spazi;$i++) $blank.='&nbsp;';
		foreach ($this->get_campi() as $nome=>$campo)
					if (strtolower(get_class($campo))=='myeuro') {
					     if ($dopo) $this->set_pre_post_campo($nome,'',$blank.$segno);
					     	   else $this->set_pre_post_campo($nome,$segno.$blank,'');
						}
		 return $this;
	}




     /**
	  * Metodo statico che estrae dei sottoarray a partire da una sequenza di array,
	  * il sottoarray viene estratto solo se un degli $array per le chiavi di $nomi_campi possiede TUTTI i valori!=''
	  * <code>
	  *  $array1=array('A'=>5,'b'=>'7','c'=>4);
	  *  $array2=array('a'=>5,'b'=>'6','c'=>'','d'=>9);
	  *
	  *  //questo non restituisce nulla perch e'  il primo non ha 'd' il secondo non ha 'c'
	  *  $out= myForm::recupera_valori(array('c'=>'primo','d'=>'secondo',array($array1,$array2));
	  *
	  *  //questo restituisce  array('primo'=>5,'secondo'=>7) perch e'  trovati nel primo si ferma
	  *  $out= myForm::recupera_valori(array('a'=>'primo','b'=>'secondo',array($array1,$array2));
 	  *
	  *  //questo restituisce  array('primo'=>5,'secondo'=>6) perch e'  introdotto vincolo CASE
	  *  $out= myForm::recupera_valori(array('a'=>'primo','b'=>'secondo',array($array1,$array2),true);
	  *
      * </code>
      *
	  *
	  * @param array $nomi_campi  		array associativo in cui le chiavi vengono cercate ed i valori sono le chiavi usate nell'array restituito
	  * @param array $arrays      		array di array associativi in cui cercare
	  * @param boolean $casesensitive   confronto tra i nomi delle chiavi sensitive o meno
	  * @param boolean $tutti           se true restituisce un array se strova tuutte le chiavi se falso restituisce solo le chiavi trovate
	  * @return array
	  */
	 public static function recupera_valori($nomi_campi, $arrays,$casesensitive=false,$tutti=true) {
		if (!$casesensitive) $nomi_campi=array_change_key_case($nomi_campi);
		$n=count(array_flip($nomi_campi));
		foreach ($arrays as &$array)
		 	if($array)
		 	{
			  $trovati=array();
		  	  if (!$casesensitive) $valori=array_change_key_case($array);
		  					  else $valori=&$array;

		  	  foreach ($nomi_campi as $chiave=>$nome)
		  	 	{
		  	  	if ( (!isset($trovati[$nome]) || !$trovati[$nome]) 
		  	  	                            &&
		  	  	                   isset($valori[$chiave])
		  	  	                            &&
		  	  	    (is_array($valori[$chiave]) || trim((string) $valori[$chiave])!=='')) $trovati[$nome]=$valori[$chiave];
		  	  	if ($tutti && count($trovati)==$n) return $trovati;
		  	 	}
		  	 	if (count($trovati) && !$tutti) return $trovati;
		  	}
		 return array();
	}


		/** @ignore */
    public function clonami() {
     	self::$dipendenza_campi['daClonare']=$this;
   		if (PHP_VERSION >= 5) return clone($this);
    					else  return $this;
    }


}