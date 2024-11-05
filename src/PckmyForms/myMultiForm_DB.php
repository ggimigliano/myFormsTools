<?php
/**
 * Contains Gimi\myFormsTools\PckmyForms\myMultiForm_DB.
 */

namespace Gimi\myFormsTools\PckmyForms;


use Gimi\myFormsTools\PckmyAutoloaders\myAutoloaders;
use Gimi\myFormsTools\PckmyFields\myUpload;
use Gimi\myFormsTools\PckmyFields\myField;
use Gimi\myFormsTools\PckmyFields\myTag;
use Gimi\myFormsTools\PckmyPlugins\myEventManager;
use Gimi\myFormsTools\PckmyPlugins\mySecurizer;
use Gimi\myFormsTools\PckmySessions\mySessions;



/**
*  Container di myForm
 **/
	
class myMultiForm_DB extends myEventManager implements \ArrayAccess {
 /** @ignore */
 protected static $docAll=0;      
/** @ignore */
protected $docOn=0,$myForms,$id_istance,$security,$myMultiform=null,$xml=array('tag'=>null,'case'=>'','header'=>''),$cloni=array(),$eventManager=array();

    public function set_myMultiform($myMultiForm) {
     	if (!$myMultiForm) $this->myMultiForm=null;
    				  else $this->myMultiForm=$myMultiForm;
    }

    /**
     * Invocato quando questa form viene aggiunta ad una myMultiForm_DB
     * eventualmente da ridefinire
     * 
     * @param string $idForm
     * @param myMultiForm_DB $form
     */
     public function onAddedForm($idForm,$form){}

     public function onStart(){}
     public function onClose(){}
     public function init(){}

    /** @ignore */
 	public function offsetSet($offset, $value) {
 		return $this->add_Form($offset,$value);
    }
    /** @ignore */
    public function offsetExists($offset) {
        return is_object($this->get_form($offset));
    }
    /** @ignore */
    public function offsetUnset($offset) {
    	$this->unset_Form($offset);
    }
    /** @ignore */
    public function offsetGet($offset) {
        return $this->get_form($offset);
    }


	/** @ignore */
  	 public function __toString(){
	  	return $this->get_html();
  	}
  	/** @ignore */
    protected   $Forms=array(), $Dipendenze=array(),$lastqry, $separati=true, $AbilitaTransazioni=array(true,true);
    /** @ignore */
	 public function __call($m,$v){ 
	    $m=strtolower($m);
	    $classInfo=new \ReflectionClass(get_class($this));
	    while($classInfo) {
	        $class=$classInfo->getName();
	        if(strtolower($class)==$m)  { $pars=array();
                                	        for ($i=0;$i<count($v);$i++) $pars[]="\$v[".$i."]";
                                	        eval("return $class::__construct(".implode(',',$pars).");");
                                	        }
	        $classInfo=$classInfo->getParentClass();
	    }
	    
	  if($this->Forms)
	 	  foreach ($this->Forms as $form)
	 		  if (method_exists($form,$m)) return call_user_func_array(array($form, $m),$v);
	   return parent::__call($m,$v);	
	}

/*
	function __get($x){  if($this->form($x)!=null)  return $this->form($x);}
	 
	function __set($x,$v){  if($this->form($x)!=null) $this->form($x)->set_values($v);
	                         return $v;
	                      }
	*/ 	
	/**
	 * poichè le myForm sono dotate di metodi magici queste operazioni
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
	                       myForm::set_auto_doc_tutti_attributi($crea_back);
	                     }
	}


	/** @ignore */
	 public function __destruct(){
	    if(($this->docOn || self:: $docAll) &&
	            (!class_exists('myAutoloaders',false) || myAutoloaders::get_debugging())) myForm::build_documentation_properties($this, $this->get_forms(),$this->docOn|self::$docAll);
	/*   foreach (array_keys($this->Forms) as $idForm) $this->unset_Form($idForm); NO perchè se passati per indirizzo potrebbe cancellarli da altre classi
    */ }



   public function onUserError(&$error) {
  	foreach ($this->eventManager as &$mng) $mng->onUserError($error,$this);
  }
   public function onInternalError(&$error) {
  	foreach ($this->eventManager as &$mng) $mng->onInternalError($error,$this);
  }


/**
 * Distrugge l'istanza e tutte le form in essa contenute indip. dal fatto che siano aggiunte per indirizzo
 *
 */
	 public function destruct(){
	  	foreach ($this->Forms as $id=>&$form)
	 					{
	 					if(method_exists($form,'destruct')) $form->destruct();
	 					if(method_exists($form,'__destruct')) $form->__destruct();
	 					$this->unset_Form($id);
						}
	}


/**
 * se  e'  un istanza di servizio (non si visualizza il suo html)  potrebbe creare problemi
 * con lo javascript di altre form, questo metodo
 */
	 public function set_no_html(){
		foreach ($this->Forms as &$form)
	 					{
	 					if(method_exists($form,'set_no_html')) $form->set_no_html();
	 					}
	}

	/**
	 * REstituisce la radice di tutte le myForm e multiform nidificate
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
     * Costruttore di classe
     * 
     * @param  boolean $separati se false l'html viene costuito in modo che campi di form diversi ma con stessa "name" vengano fusi dopo il POST, il default e TRUE
     *
	 */
	 public function __construct($separati=true)
	   { parent::__construct();
	     $this->separati=$separati;
	     if(!$this->id_istance) {

	     	$f=new myField('');
	     	$this->myForms=&$f->myFields['myForms'];
	     	$this->id_istance=$f->get_id_istanza();

	     	if (!isset($this->myForms['usafile'])) $this->myForms['usafile']=true;
	     	$this->mostra_label=true;
	     }
	   }
	   
	   
	   


     /** @ignore */
     protected function ordineIns() {
     	if(!$this->Forms) return array();
     	$idForms=array_combine(array_keys($this->Forms),array_keys(array_change_key_case($this->Forms)));
    	if ($this->Dipendenze) $dips=array_change_key_case($this->Dipendenze);
    	$Ordine=array();
    	while (count($idForms)>0) {
    	  $precord= $dips; 
    	  foreach ($idForms as $id_orig=>$id)
    	    {
    	    $dipende=false;
    	  	if (is_array($dips))
    	  	            foreach ($dips as $figli) 
                        	  				{
                        	  				$figli=array_change_key_case($figli);
                        	  				if (isset($figli[$id]))
                        	  						{   $figli=array_change_key_case($figli[$id]);
                        	  							$dipende=true;
                        	  							break;
                        	  						}
                        	  				}

    	    if (!$dipende || count($dips)==0)
    	   	   {$Ordine[]=$id_orig;
    	     	unset($idForms[$id_orig]);
    	        unset($dips[$id]);
    	  		}
    	   }
    	   if($dips==$precord) break;
    	}  
    	while (count($idForms)>0) $Ordine[]=array_shift($idForms);
    	return $Ordine;
    }

    /**
     *
     * Imposta un eventManager EventManager
     * @throws \Exception se  $mng non ha metodi pubblici onUserError e onInternalError
     * @param myEventManager $mng
     * @param boolean $propaga se true $mng si applica alle form aggiunte con addForm
     */
     public function add_EventManager(myEventManager $mng,$propaga=true){
    	if(!method_exists($mng, 'onUserError') &&
    	   !method_exists($mng, 'onInternalError')
    	   ) throw new \Exception("set_EventManager deve passare un'istanza di EventManager o sottoclasse che abbia i metodi pubblici onUserError e onInternalError");
    	$this->eventManager[]=array($mng,$propaga);
    }


     public function set_security(mySecurizer $secure) {
     $this->security=$secure;
     foreach ($this->Forms as &$form) $form->set_security($this->security);
     return $this;
     }

	/**
     * Aggiunge Form al multiForm
     * @param   string|int $idForm identificativo univoco della Form aggiunta vedi Salva_form();
     * @param   myForm $Form Form da aggiungere
     * @param   boolean $indirizzo se false (default) viene aggiunto un clone del form, altrimenti si inserisce il riferimento all'oggetto passato
     */

     public function add_Form($idForm,&$Form,$indirizzo=false) {
    	 if ($indirizzo) {	if (PHP_VERSION>=5) $this->Forms[$idForm]=$Form;
    									   else $this->Forms[$idForm]=&$Form;
    					}
    			else    {
    					//$this->Forms[$idForm]=unserialize(serialize($Form));
    					//$this->Forms[$idForm]->con=$Form->con;
    					//$this->Forms[$idForm]->recordset_attivo=$Form->recordset_attivo;
    					$this->cloni[$idForm]=$idForm;
    					if (PHP_VERSION>=5)  $this->Forms[$idForm]=clone ($Form);
    									else $this->Forms[$idForm]=$Form;
    					}
    	if(!$this->Forms[$idForm]->get_id_istanza()) $this->Forms[$idForm]->myForm();//se estensione senza estensione del costruttore forza il costruttore originale
		$this->Forms[$idForm]->set_myMultiform($this);

		$this->Forms[$idForm]->onAddedForm($idForm,$this);
		foreach ($this->eventManager as &$mng) if($mng[1]) $this->Forms[$idForm]->add_EventManager($mng[0]);
		if($this->security) {$this->Forms[$idForm]->set_security($this->security);
		                     if($indirizzo) $Form->set_security($this->security);
		                    }
		$my=new myUpload();
		if ($this->separati  && $_FILES)
		 			foreach ($_FILES as $i=>$file)
   	  	 					{$i=explode('__',$i,2);
   	  					     if ($i[0]==$idForm) $my->MyFields['FILES'][$i[1]]=$file;
   	  	 				  	}

		 //foreach ($Form->get_campi() as $i=>$v) if ($_FILES[$i]) $_FILES[$idForm.'___'.$i]=$_FILES[$i];
//echo "<pre>$idForm";    					print_r(	$this->Forms[$idForm]);
    	$idForms=array_keys((array) $this->Forms);
    	foreach ($idForms as $i=>$id) {
    		//echo "<pre>$idForm";   print_r($this->Forms[$id]->MetaForeignKeys());
    		$INFO=$this->Forms[$id]->get_metadati();
    		if ($INFO['_FK']) {
    			$_FK=array();
    			foreach ($INFO['_FK'] as $tab=>$campi)
		 					if (is_array($campi))
		 								foreach ($campi as $cols) 
		 								        if(!is_array($cols)) {
		 										$cols=explode('=',$cols);
		 									  	$_FK[strtoupper($cols[0])][]=array(strtoupper($tab)=>strtoupper($cols[1]));
		 										}
		 							


    			foreach ($_FK as $campoFiglio=>&$padri)
    		     	foreach ($padri as &$fk)
    			     		foreach ($fk as $tabella=>&$campoPadre)
    			     				foreach ($this->Forms as $idf=>&$form)
    					     				  if (
    					     				      strtoupper($form->get_tabella())==strtoupper($tabella) &&  $idf!=$id &&
    					     				  	  (strtoupper($form->get_tabella())!=strtoupper($this->Forms[$id]->get_tabella()) 
		 									  										||
    					     				  	  				strtoupper($campoFiglio)!=strtoupper($campoPadre)
		 									  	   )
		 									  	  ){
    					     				  		$this->Set_dipendenze($id,$campoFiglio,$idf,$campoPadre);
    					     				    	}
    		}
    	}

    	return $this->Forms[$idForm];
      }


      public function unset_form($idForm) {
     	if($this->Forms[$idForm] && method_exists($this->Forms[$idForm],'set_myMultiform')) $this->Forms[$idForm]->set_myMultiform(null);
    	unset($this->Forms[$idForm]);
    	unset($this->Dipendenze[$idForm]);
    	foreach ($this->Dipendenze as &$dipendenza) unset($dipendenza[$idForm]);

    	return $this;
    }




     /**
     * Elimina dai DB la tuple relative ai Forms aggiunti,se ok ritorna null
     * @param    array $idForms Array con gli id dei form da eliminare (fa fede ordine), se omesso si eliminano tutti in ordine di inserimento
     */
     public function Elimina($idForms='') {
    	$this->lastqry='';
    	if (!$idForms) $idForms=array_keys($this->Forms);
    	$fatta=array();
    	if ($this->AbilitaTransazioni[0])
    		foreach ($idForms as $id) {
    			$keyconn=$this->Forms[$id]->_get_key_conn();
    			if (!$fatta[$keyconn])
    					{$fatta[$keyconn]=1;
    					 $this->Forms[$id]->_get_conn()->begintrans();
    					}
    		}

    	$idForms=array_flip($idForms);
    	$ordine=array_reverse($this->ordineIns());

        foreach ($ordine as $id)
    	  if (isset($idForms[$id]))
    	   {$errore=$this->Forms[$id]->Elimina();
    	   	if ($errore) break;
    		$this->lastqry.=$this->Forms[$id]->get_last_query();
    	    if ($errore) break;
    	   }

    	$fatta=array();
    	if ($this->AbilitaTransazioni[1])
    		foreach (array_keys($idForms) as $id) {
    			$keyconn=$this->Forms[$id]->_get_key_conn();
    			if (!$fatta[$keyconn])
    					{
    					 if (!$errore) $this->Forms[$id]->_get_conn()->committrans();
    						 	  else $this->Forms[$id]->_get_conn()->rollbacktrans();
    					 $fatta[$keyconn]=1;
    					}
    		}
    	$this->onInternalError($errore);
    	return $errore;
     }



   /**
     * Salva la tuple relative ai Forms aggiunti sui DB, se ok ritorna null
     * @param    array $idForms Array con gli id dei form da salvare (fa fede ordine), se omesso si salvano tutti in ordine di inserimento
     */

     public function Salva($idForms='') {
    	//$FILES=$_FILES;
		$fatta=array();
    	$this->lastqry='';
    	if (!$idForms) $idForms=array_keys($this->Forms);
    	
    	if ($this->AbilitaTransazioni[0])
    		foreach ($idForms as $id) {
    			$keyconn=$this->Forms[$id]->_get_key_conn();
    			if (!$fatta[$keyconn])
    					{$fatta[$keyconn]=1;
    					 if( $this->Forms[$id]->_get_conn()) $this->Forms[$id]->_get_conn()->BeginTrans();
    					}
    		}

    	$idForms=array_flip($idForms);
    	$ordine=$this->ordineIns();
    	$dips=$this->Dipendenze;
		foreach ($ordine as $id)
    	  if (isset($idForms[$id]))
    	   {
			 /*$_FILES=$FILES;
    	     if ($this->separati && $_FILES)
    	    			{
   	  	 				foreach ($_FILES as $i=>&$file)
   	  	 					{$i=explode('__',$i,2);
   	  	 	 				 if ($i[0]==$id) $_FILES[$i[1]]=$file;
   	  	 				  	}
   	  					}*/
    	    $errore=$this->Forms[$id]->Salva();
    	   	if ($errore)   break;

    		$this->lastqry.=$this->Forms[$id]->get_last_query();
    	    if (isset($dips[$id]))
    	     				   {
    	     					 foreach ($dips[$id] as $FormFiglio=>$campi)
    	     					 		foreach ($campi as $campoPadre=>$campoFiglio)
    	     					 			if (is_object($this->Form($FormFiglio)) &&
    	     					 				is_object($this->Form($FormFiglio)->campo($campoFiglio)) &&
    	     					 				is_object($this->Form($id)) &&
    	     					 				is_object($this->Form($id)->campo($campoPadre))
    	     					 				)
    	     					 			{
    	     					 		//	echo "<br>=======================>$id=>$FormFiglio $campoPadre {$this->Forms[$id]->get_value($campoPadre)}=>$campoFiglio=".$this->Forms[$id]->get_value($campoPadre)."<br>";
    	     					 			 //print_r(array_merge($this->Forms[$FormFiglio]->get_values(),array($campoFiglio=>$this->Forms[$id]->get_value($campoPadre))));
    	     					 			 $this->Form($FormFiglio)->campo($campoFiglio)->set_value($this->Form($id)->campo($campoPadre)->get_value());
    	     					 			}
    	     					 		//	else 	echo "<br>NON ASSEGNATO=======================>$id=>$FormFiglio $campoPadre {$this->Forms[$id]->get_value($campoPadre)}=>$campoFiglio=".$this->Forms[$id]->get_value($campoPadre)."<br>";

    	     					 unset($dips[$id]);
    	     			  	  }
			//	echo "<hr>";
    	   if ($errore) break;
    	   }

    	$fatta=array();
    	if ($this->AbilitaTransazioni[1])
    		foreach (array_keys($idForms) as $id){
    			$keyconn=$this->Forms[$id]->_get_key_conn();
    			if ($keyconn && !$fatta[$keyconn])
    					{
    					 if (!$errore) $this->Forms[$id]->_get_conn()->committrans();
    					 		  else $this->Forms[$id]->_get_conn()->rollbacktrans();
    					 $fatta[$keyconn]=1;
    					}
    		}
    	//$_FILES=$FILES;
    	$this->onInternalError($errore);
    	
    	return $errore;
     }




    /**
     * Setta dipendenza relazionale (di Foreign Key) tra i campi delle form che compongono la MultiForm
     * @param   string|int   $idFormFiglio     E' l'idForm assegnato con  il metodo addForm
     * @param  	string 		 $nomeCampoFiglio  E' il campo che deve assumere il valore
     * @param   string|int   $idFormPadre	   E' l'idForm assegnato con  il metodo addForm
     * @param  	string 		 $nomeCampoPadre   E' il campo che distribuisce il valore
     */

     public function Set_dipendenze($idFormFiglio,$nomeCampoFiglio,$idFormPadre,$nomeCampoPadre) {
    	$this->Dipendenze[$idFormPadre][$idFormFiglio][strtoupper($nomeCampoPadre)]=strtoupper($nomeCampoFiglio);
    	return $this;
     }



 	/**
     * Annulla tutte le dipendenze relazionali (di Foreign Key) tra i campi delle form che compongono la MultiForm
  	 * @param   string|int   $idFormFiglio     E' l'idForm assegnato con  il metodo addForm
     * @param   string|int   $idFormPadre	   E' l'idForm assegnato con  il metodo addForm
     */

     public function unset_dipendenze($idFormFiglio,$idFormPadre) {
    	unset($this->Dipendenze[$idFormPadre][$idFormFiglio]);
    	return $this;
     }


     /**
      * Restituisce il parametro passato nel costruttore
      *
      * @return boolean
      */
      public function get_separati(){
     	return $this->separati;
     }

    private function __check_errore($idForms=array(),$Esclusi=array(),$testoLinkCampoErrato=array(),$function) {
	    $IDForms=@array_keys((array) $this->Forms);
    	if (!count($IDForms)) return;
    	if(!$idForms) {$idForms=array();
    				   foreach($IDForms as $idf){
    							$idForms[$idf]=array();
    							$Esclusi[$idf]=false;
    							}
    				  }

    	if(array_values($idForms)===$idForms) $idForms=array_flip((array)$idForms);//se non è associativo lo flippo

    	foreach($IDForms as $idf)
    			{if(isset($idForms[$idf]))  $idForms[$idf]=(is_array($idForms[$idf])?$idForms[$idf]:array());
    			 if(!isset($Esclusi[$idf]))  $Esclusi[$idf]=false;
    			}

    	$FILES=$_FILES;
    	$f=new myUpload('');

    	foreach ($idForms as $idForm=>$esclusi)
    	    {if(!is_object($this->Forms[$idForm])) continue;
    	     $_FILES=$FILES;
    	     if (!is_array( $esclusi)) $esclusi='';

    	     if ($this->separati && $_FILES)
    	    			{
   	  	 				foreach ($_FILES as $i=>&$file)
   	  	 					{$i=explode('__',$i,2);
   	  	 	 				 if ($i[0]==$idForm) $_FILES[$i[1]]=$file;
   	  	 				  	}
   	  					}
			 $f->myFields['FILES']=$_FILES;
			 $errore=$this->Forms[$idForm]->$function($esclusi,$Esclusi[$idForm],$testoLinkCampoErrato[$idForm]);
			 if ($errore) return array('errore'=>$errore,'id'=>$idForm,'errore_tipo'=>'user');
			}

    	$_FILES=$FILES;
    }



    /**
     * Aggiunge un porefisso al nome dei CAMPI (IN PRATICA INFLUISCE SULL'ATTRIBUTO NAME DELL'HTML)
     * @param string $prefisso
     * @param boolean $applica se true si applica altrimenti si rimmuove
     */
  /*  function set_prefisso_nomi_campi($prefisso,$applica=true){
    	foreach ($this->Forms as &$form) if(method_exists($form,'set_prefisso_nomi_campi')) $form->set_prefisso_nomi_campi($prefisso,$applica);
    	return $this;
    }
*/

    /**
     * Analogo a myForm
     * @param    array $idForms Array associativo che associa ad ogni idForms i nomi dei campi da escludere/includere, se omesso si verificano tutti, se non  e'  associativo si considerano tutti i campi
     * @param    array $Esclusi Array associativo che associa ad ogni idForms un booleano se true o assente si escludono i campi elencati nel parametro precedente, se falso si applica solo a quelli nel parametro precedente
     */
     public function check_errore($idForms='',$Esclusi=array(),$testoLinkCampoErrato=array()) {
        $errore=$this->__check_errore($idForms,$Esclusi,$testoLinkCampoErrato,'check_errore');
        if($errore) {
                     if($errore['errore_tipo']=='user') $this->onUserError($errore);
                	   						      else $this->onInternalError($errore);
                    }
    	return $errore['errore'];
    }



    /**
     * Analogo a myForm
     * @param    array $idForms Array associativo che associa ad ogni idForms i nomi dei campi da escludere/includere, se omesso si verificano tutti
     * @param    array $Esclusi Array associativo che associa ad ogni idForms un boleano se true o assente si escludono i campi elencati nel parametro precedente, se falso si applica solo a quelli nel parametro precedente
     */
     public function check_errore_diviso($idForms='',$Esclusi=array(),$testoLinkCampoErrato=array()) {
    	$errore= $this->__check_errore($idForms,$Esclusi,$testoLinkCampoErrato,'check_errore_diviso');
    	if($errore['errore_tipo']=='user') $this->onUserError($errore);
    							 else $this->onInternalError($errore);
    	return $errore;
    }


  	 /**
     * Analogo a myForm
     * @param     string $idForm  e'  si l'id del form in cui settare il valore
     * @param     string $campo
     * @param     mixed $valore
     */
	 public function set_value($idForm,$campo,$valore) {
		if ($this->Forms[$idForm]) {
				 if ($this->separati && stripos("{$idForm}__",$campo)===0)
				 						{$campo=explode('__',$campo,2);
				 						 $campo=$campo[0];
				 						}

				$this->Forms[$idForm]->set_value($campo,$valore);
		}
		return $this;
  	}


    /**
	* Setta i valori ai campi
     * @param    array $valori Array associativo es $_POST o $_GET
     */
    public function set_values($valori) {
     foreach (array_keys($this->Forms) as $id) {
     	  if (!$this->separati)  $this->Forms[$id]->set_values($valori);
   	  					else  {$vals=array();
/*   	  							echo "<hr>";
   	  							print_r($valori);*/
	      						foreach ($valori as $i=>&$v) {
	      						//	echo "$i,<br>";
	      							$i=explode('__',$i,2);
	      							if ($i[0]==$id) $vals[$i[1]]=&$v;
	      							}
	      						//print_r($vals);	
	      						$this->Forms[$id]->set_values($vals);
   	  						  }
     }			  
	  return $this;
 //echo "<pre>"; print_r($_FILES);
	}


	 /**
     * Analogo a myForm
     * @param     string $idForm  e'  si l'id del form da cui estrarre il valore
     * @param    string|array $nome_campo nome del campo, se e' un array  e'  l'elenco delle labels
     * @return   string|array $label Label associate al/ai campo/i
     */

	 public function set_label($idForm,$nome_campo,$label) {
		 $this->Forms[$idForm]->set_label($nome_campo,$label);
		 return $this->Forms[$idForm];
  	}


  	function &get_forms() {
		return $this->Forms;
	}



	function &get_form($idForm) {
	  $null=null; 
	  if($this->Forms)
	  	 foreach (array_keys($this->Forms) as $id)	if(strtolower($idForm)==strtolower($id) ) 	 return $this->Forms[$id];
	  return $null;  	
	}


	function &form($idForm) {
		return $this->get_form($idForm);
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
	   if($this->Forms)
	  		 foreach ($this->Forms as $id=>$form)
	  		 			{
	  		 			 $form->set_store_params($container,"{$id}_$chiave");
	  		 			}
		return $this;
	}


	 public function store_values() {
	  if($this->Forms) foreach ($this->Forms as &$form) $form->store_values();
	 return $this;
	}


	 public function get_stored_values(){
	    $out=array();
		if($this->Forms) foreach ($this->Forms as $form) $out=array_merge((array) $out, $form->get_stored_values());
	  	return $out;
	}


	 public function restore_values() {
		if($this->Forms) foreach ($this->Forms as &$form)  $form->restore_values();
	  	return $this;
	}



     /**
     * Analogo a myForm
     * @param     string $idForm  e'  si l'id del form da cui estrarre il valore
     * @param     boolean $caseOriginale se false tutto maiuscolo se true quello originale
     * @return    mixed
     */
	function &get_campi($idForm='',$caseOriginale=false) {
		if(!$idForm) {$v=array();
		              foreach ($this->Forms as $form)  $v=array_merge($v,$form->get_campi($caseOriginale));
					  return $v;
					 }
			elseif ($this->Forms[$idForm]) return $this->Forms[$idForm]->get_campi($caseOriginale);

  	}





  	 /**
     * Analogo a myForm
     * @param     string $idForm  e'  si l'id del form da cui estrarre il valore
     * @param     string $campo Nome del campo di cui si vuole conoscere il valore
     * @return    mixed
     */
	 public function get_value($idForm,$campo) {
		if ($this->Forms[$idForm]) return $this->Forms[$idForm]->get_value($campo);

  	}


  	 /**
     * Restituisce array associativo i valori del form
     * @param     string $idForm  e'  si l'id del form da cui estrarre i valori, se omesso estrae i valori di tutti i form
     * @return    array
     */
  	
	function &get_values($idForm=array(),$label=false,$name=false) {
		//echo "<pre>";print_r($this->Forms[$idForm]);
		$v=array();
		if ($idForm) return $this->Forms[$idForm]->get_values('',$label,$name);
				elseif(is_array($idForm))
					 {foreach ($this->Forms as $i=>$f) $v[$i]=$f->get_values('',$label,$name);
    				  return $v;
					 }
					else {$v=array();
						  foreach ($this->Forms as $i=>$f) $v=array_merge($v,$f->get_values('',$label,$name));
    				  	  return $v;
					      }

  	}


  	 /**  Analogo a myForm
  	 * @param  array $idForms array con le id dei form da visualizzare, se omesso si visualizzano tutti
	 * @param    string $AttributiForm tag della form
     * @param    array  $pulsanti array di myPulsante
     * @param    string $AttributiTable tag della table
     * @return   string
     */
  	 function &get_html_completo($idForms='',$AttributiForm='',$Pulsanti=array(),$AttributiTable=" style='border:0' ",$clean=false) {
  	 	$pulsanti=array();
  	 	if (is_array($Pulsanti)) {foreach ($Pulsanti as $p) if (is_object($p)) $pulsanti[]=$p; 	}
  	 						else $pulsanti=array();
		$HTML=$this->Get_html($idForms,array(),array(),false);

   	 	if ($AttributiForm) {	$AttributiForm=new myTag($AttributiForm);
   	 							if (!$AttributiForm->get_attributo('method')) $AttributiForm->set_attributo('method','get');
   	 							$AttributiForm->set_attributo('accept-charset',myField::get_charset());
   	 								foreach ($this->get_campi() as $campo) {
   	 											 	if (is_object($campo) && $campo->Estende('myUploadText',true))
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
  	 	 $x=trim((string) $HTML);
  		 if($x!==''){
			if ($pulsanti) 
						{
						if (!$idForms) $idForms=array_pop(array_keys($this->Forms));
    							elseif (is_array($idForms)) $idForms=array_pop($idForms);
						 $f=$this->get_form($idForms);
						 if(method_exists($f,'creaTable')) $ff=$f;
						 foreach ($pulsanti as $pls) $pulsantiera.="<div  style='width:".(intval(100/count($pulsanti))-1)."%;text-align:center;float:left'>{$pls->get_html()}</div>";
						 $pulsantiera.='<div style="clear:both"></div>';
						 if(!preg_match('@</tr>$@i',$x)) $x.=$pulsantiera;
    			 								    else  $x.=(new myTag($f->get_pref_html(),'', //tr
    																   $f->creaTd( //td
    																		$pulsantiera,
    																		'','colspan="2"')
    																	)
    														);
						 						    
						}
		
		
			if($AttributiTable!==false)
						{
						 if($ff) $x=$ff->creaTable($x,'',$AttributiTable);
							elseif(preg_match('@</tr>$@i',trim((string) $x))) $x="<table {$AttributiTable}>$x</table>";
																			else $x="<div {$AttributiTable}>$x</div>";
						}													
		 	if($f) $f->get_elaborazione_myJQ($x);
  		 }
		/*
		$table=new myTag($AttributiTable);
		if(trim((string) $HTML)) {
				$x="<table $table>".$HTML;
				if ($pulsanti) {
								foreach ($pulsanti as $pls) if(method_exists($pls,'get_html')) $pulsantiera.="<td style='width:".(100/count($pulsanti))."%;text-align:center'>".$pls->get_html()."</td>";
								$x.='<tr>
										<td colspan="2">
											<table style="width:100%;border:0">
											   <tr>'.$pulsantiera.'</tr>
											</table>
										</td>
									</tr>		';
							   }
				$x.="</table>";
				}*/
		if ($AttributiForm) $x="<form $AttributiForm>$x</form>";
	    if(!$clean) $x=myForm::clean_ridondanze_html($x);
	    if(isset($this->myForms['DipendenzeCalls']))  $x.="<script type='text/javascript'>{$this->myForms['DipendenzeCalls']};</script>";
   		return $x;		
	 }



    /**
     * Analogo a myForm
     * @param  array $idForms array con le id dei form da visualizzare, se omesso si visualizzano tutti
     * @param  array $inizio  array associativo con chiave id_form e valore il campo iniziale (eventuale)
     * @param  array $FINE array associativo con chiave id_form e valore il campo FINALE (eventuale)
	 * @return string
     */

    function &get_html($idForms='',$inizio=array(),$fine=array(),$clean=true) {
        $out='';
    	if (!$idForms) $idForms=array_keys($this->Forms);
    			elseif (!is_array($idForms)) $idForms=array($idForms);
    	foreach ($idForms as $idf)
    	    if(isset($this->Forms[$idf]) && $this->Forms[$idf]) {
    	           if(!isset($inizio[$idf])) $inizio[$idf]='';
    	           if(!isset($fine[$idf])) $fine[$idf]='';
    	           $out.=$this->Get_html_Form($idf,$inizio[$idf],$fine[$idf],$clean);
    	           }
        if(!$clean) return $out;    	    
    	return myForm::clean_ridondanze_html($out);
  	}





  	 /**
	  * Salva l'xml della form
	  *
	  * @param string $nomeFile percorso del file in cui salvare
	  * @param  array $idForms array assaociativo che associa alle id delle form da visualizzare il relativo tag, se il valore è null si usa l'id,se il valore e' FALSE non si mette tag nella form, se l'array  e'  null si visualizzano tutte le form con l'id come tag
      * @param  array $inizio  array associativo con chiave id_form e valore il campo iniziale (eventuale)
      * @param  array $FINE array associativo con chiave id_form e valore il campo FINALE (eventuale)
	  * @return string
      */

     public function Salva_xml($nomeFile,$tag='',$case='',$header='',$idForms='',$inizio=array(),$fine=array()) {
    	if(!$tag) $tag=get_class($this);
    	$xml=$this->get_xml($tag,$case,$header,$idForms,$inizio,$fine);
    	$scritti=@file_put_contents($nomeFile,$xml,LOCK_EX);
    	@clearstatcache(true,$nomeFile);
    	if(strlen($xml)==$scritti) return true;
    						 else {@unlink($nomeFile);
    						 	   $this->onInternalError($this->trasl('Impossibile salvare'));
    						 	   return false;
    							   }
    }


    /**
     * Analogo a myForm
     * @param  array $idForms array assaociativo che associa alle id delle form da visualizzare il relativo tag, se il valore è null si usa l'id,se il valore e' FALSE non si mette tag nella form, se l'array  e'  null si visualizzano tutte le form con l'id come tag
     * @param  array $inizio  array associativo con chiave id_form e valore il campo iniziale (eventuale)
     * @param  array $FINE array associativo con chiave id_form e valore il campo FINALE (eventuale)
	 * @return string
     */

    function &get_xml($tag=null,$case='',$header='',$idForms='',$inizio=array(),$fine=array()) {
    	if ($tag===null) {$tag=$this->xml['tag'];
	  					if ($tag===null) $tag=get_class($this);
	  					}
	    if (!$case) $case=$this->xml['case'];
	    if ($header==='' && !isset($this->xml['header'])) $header=$this->xml['header'];

    	if (!$idForms) $idForms=array_combine(array_keys($this->Forms),array_keys($this->Forms));
    			elseif (!is_array($idForms)) $idForms=array($idForms);

    	foreach (array_keys($idForms) as $idf)
    	    {
    	    $out.=$this->Forms[$idf]->get_xml(null,$case,false, $inizio[$idf],$fine[$idf]);
    	 	}

		$xml=trim((string) $out);
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
						{
						 $form=$this->form((string) $elemento);
						 if($form)  {$dom = new \DOMDocument('1.0', 'UTF-8');
						             $dom->appendChild($dom->importNode(dom_import_simplexml($v),true));
						 			 $form->load_xml(null,$dom->saveXML());
									}
						}
	  return true;
	}


  /**
     * Analogo a myForm
     * @param  string $idForm  e'  si l'id del form da visualizzare
     * @param  array $campo_inizio Nome del campo da cui iniziare nella costruzione, se omesso si comincia dall'inizio
     * @param  string $campo_fine Nome del campo da cui finire nella costruzione, se omesso si usa solo campo inizio, SE OMESSI ENTRAMBI SI USANO TUTTI I CAMPI
     * @return string
     */

	function &get_html_form($idForm,$campo_inizio='',$campo_fine='',$clean=true) {
	    $html='';
		if(!isset($this->Forms[$idForm]) || !$this->Forms[$idForm]) return $html;
		if (!$this->separati) $html=$this->Forms[$idForm]->get_html($campo_inizio,$campo_fine,$clean);
					ELSE   {
						   	$this->Forms[$idForm]->set_prefisso_nomi_campi($idForm."__",true);
						    			$html=$this->Forms[$idForm]->get_html($campo_inizio,$campo_fine,$clean);
    						$this->Forms[$idForm]->set_prefisso_nomi_campi($idForm."__",false);
						    }
		return $html;
   }



  	 public function abilitazione_transazioni($start,$chiusura) {
  		$this->AbilitaTransazioni=array($start,$chiusura);
  		return $this;
  	}


}