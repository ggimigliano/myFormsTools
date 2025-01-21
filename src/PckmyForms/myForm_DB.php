<?php
/**
 * Contains Gimi\myFormsTools\PckmyForms\myForm_DB.
 */

namespace Gimi\myFormsTools\PckmyForms;


use Gimi\myFormsTools\PckmyDBAdapters\myAdoDBAdapter;
use Gimi\myFormsTools\PckmyFields\myDate;
use Gimi\myFormsTools\PckmyFields\myField;
use Gimi\myFormsTools\PckmyFields\myMultiCheck;
use Gimi\myFormsTools\PckmyFields\myHidden;
use Gimi\myFormsTools\PckmyPlugins\mySecurizer;
use Gimi\myFormsTools\PckmySessions\mySessions;




/**
 *  Questa classe permette la costruzione e gestione facilitata di Form basate su una tabella di DB generico
 *  Supponiamo di voler costruire una form da una tabella Prodotti costituita id(Pk),Nome,Prezzo
 *  e che, se la variabile $_GET['id']  e'  valorizzata deve presentare i campi valorizzati con quelli riferiti a $_GET['id']
 * altrimenti si predispone all'inserimento
 *  <code>
 * ....  precedemente mi connetto al DB ed includo myForms
 * if (!$_GET['id']) $_GET['id']=$_POST['id']; //se non $_GET['id'] lo valorizza con un eventuale precedente valore postato
 * $f=new myForm_DB($AdoConn,'','Prodotti',array('id'=>$_GET['id']));
 * $f->Analizza_Tabella(); //prima di ogni cosa
 * $f->CambiaTipoCampo('PREZZO','myEuro'); //il tipo di default assegnato da myForm non mi piace e lo cambio
 * $f->IstanziaCampi(); //Ora non si possono pi u'  cambiare i tipi;
 * if ($_POST['id']) {//se E' stato premuto un pulsante
 * 					$f->set_values($_POST); //cambio i valori dei campi con quelli postati
 * 					$mess=$f->Check_Errore(); //verifico la presenza di errori
 * 					if (!$mess) {
 * 								if ($_POST['Salva']) $f->Salva();   //se premuto Salva
 * 								if ($_POST['Elimina']) {$f->Elimina(); //se premuto Elimina
 * 														$f->set_defaults();//reimposta i valori di default del form
 * 														}
 *  								$mess='Operazione completata';
 * 								}
 *
 *
 * if ($mess) echo $mess."<br>"; // visualizzo messaggio di risposta;
 * echo "<form method=post><table>"; //un po' di html serve sempre, ma poco
 * echo $f->Get_html(); //visualizzo Form
 * echo "</table>";
 *
 * $s=new myPulsante('Salva','Salva'); //creo pulsante Salva
 * echo $s->get_Html(); // lo visualizzo
 *
 * $e=new myPulsante('Elimina','Elimina'); //creo pulsante Elimina
 * $e->set_Domanda('Sicuro di voler cancellare'); //setta domanda di conferma per il pulsante
 * echo $e->get_Html(); // lo visualizzo
 * echo "</form>";
 * </code>
 * 
 */
	
class myForm_DB extends myForm  {
/** @ignore */
protected  $con, $tabella, $schema, $condizioni, $colonne, $lastqry, $ParametriAutomatici=array(),$metaDatiTabella=array(), $chiavi=array(), $indici=array(),
 	 $AutoIncrementante='',$recordset_attivo='', $istanziati=false, $separatore_decimali='.',
	 $ripetizione=false, $id,$FK,$SalvaInfo=0,$COMMENTI_TAB=array();
/** @ignore */
protected static $cacheQuotati=array(),$check_f5=true;	 


 /**
     * Costruttore di classe
     * 
     *
	 * @param    mixed $con Connessione al db da usare
     * @param    array $colonne elenco delle colonne della tabella da utilizzare, se omesso si usano tutte
     * @param    string $tabella Nome della tabella da utilizzare
     * @param    array $condizioni Array associativo Colonna=>Valore per la condizione di recupero valori dalla tabella
     */
	 public function __construct(&$con,$colonne=array(),$tabella='',$condizioni='')
	   {parent::__construct();
	    $this->con=myAdoDBAdapter::getAdapter($con);
		$tabellaParti=explode('.',$tabella,2);
		if(count($tabellaParti)>1) $this->schema=$tabellaParti[0];
		   					 else  $this->schema = $this->con->database();
								    
		$this->id=sha1($_SERVER['PHP_SELF']."=> {$this->schema}.{$tabella}.{$this->id_istance}.=>".var_export($condizioni,1).var_export($colonne,1));

		if (!$colonne) $colonne=array();
		$this->colonne=$colonne;
		$this->colonne=array_unique($this->colonne);
		$this->tabella=$tabella;
		if (is_array($condizioni))
				foreach ($condizioni as $i=>&$v)
								   $this->condizioni[strtoupper($i)]=stripslashes("$v");
		$this->ripetizione=self::is_f5();
		if ($this->ripetizione && ($recovered=$this->recover_chiavi())) $this->condizioni=$recovered;
	   }
	   
	   

	   
	/**
	 * @ignore
	 */
	   protected function recover_chiavi($chiavi=''){
	   		$s=new mySessions('myFormsStatus');
	   		$last_chiavi=$s->get($this->id); //estraggo elenco chiavi
	   		if($chiavi) {$s->set('last_status',mySecurizer::checksum_user_sent(),true);
	   					 $last_chiavi=array('key'=>$chiavi,
	   										'chk'=>mySecurizer::checksum_user_sent());
	   					 $s->set($this->id,$chiavi,true,60*5);
	   					}
	   		if(isset($last_chiavi[$this->id]['chk']) && $last_chiavi[$this->id]['chk']==mySecurizer::checksum_user_sent())
	   						return $last_chiavi[$this->id]['key'];
	   				   else $s->del($this->id);
	   }



	   /**
	    * @ignore
	    */
	   protected static function is_f5(){
	   		 static $is_f5;
	   		 if(!self::$check_f5) return false;
	   		 if($is_f5===null && isset($_SESSION)) {
	   		 				$s=new mySessions('myFormsStatus');
	   		 				$is_f5=($s->get('last_status')==mySecurizer::getInstance('myFormsStatus',$s)->checksum_user_sent());
	   		 				if(!$is_f5) $s->dels();
	   		 				}
	   		 			//	print_r($_SESSION['mySessions>myFormsStatus']);
	   		 return $is_f5;
	   }

	/**Restituisce l'html della label di un certo campo senza formattazione tabellare
	 *
     * @param    string $campo Nome del campo da estrapolare
     * @return   string
     */
	function &get_html_campo($campo) {
	 $html='';
	 $campo=strtoupper($campo);
	 $field=$this->get_campo($campo);
	  if ($field)
	  	 {
	  	   $html=parent::Get_html_campo($campo);
	  	    if ($this->prefisso_nascoste && in_array($campo,$this->chiavi))
	  			{
	  			$valori=$this->get_valori_recorddiLavoro(false,'U');
	  			$hidden=new myHidden($this->prefisso_nascoste.$field->get_name(),$valori[$campo]);
	  			$html.=$hidden->get_html();
	  			}
	  	 }
	  return $html;
   }


   /**
    * Imposta il timeout per il salvataggio delle metainformazioni delle tabelle
    * @param int $timeout secondi di cache, se 0 ogni volta ricarica da DB metadati
    */
    public function set_cache_metadata($timeout){
   	$this->SalvaInfo=$timeout;
   	return $this;
   }

   /**
    * Aggiunge campi nascosti con i valori delle chiavi ricavabili tramite get_valori_recorddiLavoro()
    * precedute da un certo prefisso. Utile quando la chiave primaria  e'  editabile dall'utente e lui pu o'  modificarla
    * Es. supponiamo che una tabella abbia come chiave primaria il codice fiscale,
    *  possiamo avere diversi casi
    * 1) il codice fiscale  e'  passato in GET per preparare la modifica, in questo caso si deve usare quello nel costruttore
    * 2) il codice fiscale  e'  regolarmente "postato", in quel caso potrebbe essere stato modificato dall'operatore quindi server ricavare quello originale
    * quindi conviene
    * <code>
    *  //cerca in $_POST prima @numeropolizza e poi numeropolizza
    *  //se non trova nulla le cerca in $_GET
	*  $condizioni=myForm::recupera_valori(array('@numeropolizza'=>'numeropolizza',
	* 											  'numeropolizza'=>'numeropolizza',
	* 											  ),
	* 											 array($_POST,$_GET)
	* 										);
	*   //alla fine $condizioni o e' null o  e'  della forma ('numeropolizza'=>valore)
	*  $form=new myForm_mysql($conn,'','persone',$condizioni);
    *  $form->add_chiavi_nascoste();
    * </code>
    *
    * @param string $prefisso  //prefisso che verrà posto davanti ai nomi delle chiavi
    */
	 public function add_chiavi_nascoste($prefisso='@') {
		if (!$this->chiavi) die('add_chiavi_nascoste() va usato dopo set_chiavi()');
		//foreach ($this->chiavi as $chiave) $this->add_campo(new myHidden("$prefisso$chiave"));
		$this->prefisso_nascoste=$prefisso;
		return $this;
	}


	 public function add_campo(&$campo,$label='',$indirizzo=false,$dopo='') {
		$obj=parent::add_campo($campo,$label,$indirizzo,$dopo);
		$this->colonne=array_keys($this->get_campi());
		return $obj;
	}

	 public function unset_campo($nome) {
		parent::unset_campo($nome);
		$this->colonne=array_keys($this->get_campi());
		return $this;
	}


	protected function tableNameForQry(){
	    return $this->schema?"{$this->schema}.{$this->get_tabella()}":$this->get_tabella();
	}
	

	/**
	 * Restituisce un array associativo che rappresenta tutte le foreign keys della tabella a cui  e'  associata la form
	 *
	 * @return array
	 */
 	 public function MetaForeignKeys()
	 {  if ($this->FK) return $this->FK;
	  
	    return $this->con->MetaForeignKeys($this->tableNameForQry(),false,true,false);
	 }

	
	function  MetaColumns(){
	    return $this->con->metacolumns($this->tableNameForQry());
	}
	 

	/** @ignore */
	 public function MetaIndexes ($tabella, $primary = FALSE, $owner=false)
   	   {
   	       return  $this->con->MetaIndexes ($this->tableNameForQry(), $primary, $owner);
		}



	 /** @ignore */
	 protected function get_meta_type($fld){
	    $classe=strtolower($this->con->rsPrefix.$this->con->databaseType());
	    if (!class_exists($classe)) $classe='ADORecordSet'; 
	    static $cache;
	    if(!isset($cache[$classe])) {
	        $cache[$classe]=new $classe(null);
	        $cache[$classe]->connection=$this->con;
	    }
	    $type=array();
	    preg_match('@^[a-z]+@',$fld->type,$type);
		return  $cache[$classe]->MetaType($type[0], $fld->max_length,$fld);
	 }

	 /**
	  * Imposta uni indice univoco tra i campi della form,
	  * la verifica viene effettuata solo quando tutte le colonne dlla form sono valorizzate
	  *
	  * @param string $nome     nome dell'indice
	  * @param array $colonne nomi delle colonne
	  */

	  public function set_univocita($nome,$colonne=array()){
	 	$this->indici[strtoupper($nome)]['unique']=1;
	 	$this->indici[strtoupper($nome)]['columns']=$colonne;
	 	return $this;
	 }

	/**
	 * Restituisce il nome della tabella di lavoro
	 *
	 * @return string
	 */
	  public function get_tabella(){
	 	$tab=array_reverse(explode('.',$this->tabella));
	 	return $tab[0];
	 }

	/**
	 * @ignore
	 */
	 protected function quota_colonne($array,$tutte=false) {
	   $where=array();
	   if ($array)
	   foreach ($array as $col=>&$val) {
	   	  $val=trim((string) $val);
	   	  $col=strtoupper($col);
	   	 if ($tutte && $val==='') return null;
    		 if (($val=trim((string) $val))!=='' && ($this->metaDatiTabella[$col]->primary_key || ($this->chiavi && array_search($col,$this->chiavi)!==false)))
    						 {$type=$this->get_meta_type($this->metaDatiTabella[$col]);
    						  if (in_array($type,array('D','T'))) {
    						  										$val=explode(' ',$val,2);
    						  										$d=new myDate('',$val[0]);
    						  										$val[0]=$d->get_formatted();
    						  										$where[]="$col=".$this->con->quote(implode(' ',$val));
    						  									 }
    						   elseif (in_array($type,array('N','I','R')) && preg_match('|^[-+]{0,1}[0-9]*[\.]{0,1}[0-9]*$|',$val))
    						  										   $where[]="$col=$val";
    						   	  						          else $where[]="$col=".$this->con->quote($val);
    						 }
	   }
	   return $where;
	 }


 /**
  * @ignore
  */
  public function get_chiaveMetadati(){
     $chiaveMetadati=explode(':',$this->_get_key_conn().':'.$this->get_tabella());
     unset($chiaveMetadati[0]);
     return implode(':',$chiaveMetadati);
 }	 
 
 /**
  * @ignore
  */
 protected function get_meta_comments(){}
	 
 function &get_metaDati(){
    $chiaveMetadati=$this->get_chiaveMetadati();
   
	if($this->SalvaInfo>0)  $INFO=$this->MyCacheDati(-1,$chiaveMetadati,null,'/metadati/');
    			
    IF (!isset($INFO['InfTable']) || !$INFO['InfTable']) 
    					{//echo "RICALCOLO $chiaveMetadati";
    					 $INFO['InfTable']=$this->MetaColumns();
    					 $INFO['_FK']=$this->MetaForeignKeys();
    					 $INFO['Indici']=$this->MetaIndexes($this->tabella,true);
    					 if(isset($INFO['Indici']['PRIMARY'])) $INFO['PK']=$INFO['Indici']['PRIMARY']['columns'];
    					 if (is_array($INFO['_FK'])) 
    					     foreach ($INFO['_FK'] as $tab=>$campi)
    					         if (is_array($campi))
    					             foreach ($campi as $cols) {
    					                 $cols=explode('=',$cols);
    					                 $INFO['FK'][strtoupper($cols[0])][]=array(strtoupper($tab)=>strtoupper($cols[1]));
    					                 $INFO['REALTAB_NAMES'][strtoupper($tab)]=$tab;
    					             }
    					 $INFO['COMMENTI_TAB']=$this->get_meta_comments(); 
						 $this->MyCacheDati( $this->SalvaInfo,$chiaveMetadati,$INFO,'/metadati/');
    					}
    return $INFO;					
 	}	 
 	
 	/**
 	 * @ignore
 	 */
 	protected function rebuild_where($where,$pars){
 	      return array('pars'=>array(),'where'=>$where);    
 	}
	/**
     * Effettua l'analisi della tabella ma ancora non istanzia i campi per
     * permettere l'uso di CambiaTipoCampo
	 */
  public function Analizza_Tabella() {
 		$this->MyForm();
    	if ($this->ParametriAutomatici) RETURN $this;
    	
    	global $ADODB_COUNTRECS;$tempcount=$ADODB_COUNTRECS;  $ADODB_COUNTRECS = true;
    	
    	$precFetch =$this->con->setfetchmode(ADODB_FETCH_ASSOC);
    	
    	$INFO=$this->get_metaDati();
    	
    	
    	$InfTable=    &$INFO['InfTable'];
    	$this->indici=&$INFO['Indici'];
    	
    	$this->FK=$FK= &$INFO['FK'];
    	$this->COMMENTI_TAB=$FK= &$INFO['COMMENTI_TAB'];
    	$REALTAB_NAMES=&$INFO['REALTAB_NAMES'];
    	
		
	    $this->metaDatiTabella=@array_change_key_case($InfTable,CASE_UPPER);

		//echo "<pre>";print_r($this->metaDatiTabella);

     	foreach ($this->colonne as $i=>$colonna){
     								  	 	     $v=explode(' ',trim((string) $colonna));
     								  	 	     $this->colonne[$i]=strtoupper($v[count($v)-1]);
     								  	 	    }
     	
     	$isRSAttivo=false;
     	$extraInfo=array();
    	IF ($this->condizioni)
    				{
    				$where=$this->quota_colonne($this->condizioni);
    				$isRSAttivo=count($where)==count($this->condizioni);
    				
    				if(count($this->colonne)>0) $colonne=implode(',',array_unique(array_merge($this->colonne,array_keys(array_change_key_case($this->condizioni,CASE_UPPER)))));
    									   else $colonne='*';
    					 
    				if(!$where ||(!$isRSAttivo && $this->SalvaInfo>0) )  $this->recordset_attivo=$Table=$this->con->selectlimit("select $colonne from {$this->tabella} where 1=0 ",1,0);
			                                                     	else { $where=$this->rebuild_where(implode(' and ',$where),$this->condizioni);
    				                                                       $this->recordset_attivo=$Table=$this->con->selectlimit("select $colonne from {$this->tabella} where ".$where['where'],1,0,$where['pars']);
    				                                                     }
    				
		  	     	for ($i=0;$i<$Table->_numOfFields;$i++)
												 {$fld=$Table->FetchField($i);
												  $extraInfo[strtoupper($fld->name)]['unsigned']=$fld->unsigned;
		 							  			  $extraInfo[strtoupper($fld->name)]['maxValue']=$fld->max_length;
		 							  			  if ($isRSAttivo && $Table->RowCount()==1) $extraInfo[strtoupper($fld->name)]['Value']=$Table->fields[$fld->name];
		                						 }
					}
		//echo "<pre>";	print_r($this->colonne );	print_r($extraInfo);

/*
    	echo "<pre>";print_r($FK);print_r($InfTable);

			 MAXSIZE = Dimensione massima del campo
				 NONNULLO = Campo con IMpossibilità di essere a NULL
				 POSITIVO= Campo con segno positivo
				 INCREMENTALE = Campo incrementale
				 VALORE = Valore del campo nel recordset recuperato
				 VALOREDEFAULT = Valore di defalut in definizione tabella
				 MYTYPE =  Nostro tipo
				 TIPO = Tipo dichiaratio dal db
				 ADODB = Nome usato dalla classe Adodb per identificare il campo
				 FK = (tabella=>campo)

*/
    	$chiavi_valorizzato=count($this->chiavi);
    	$tipo='';
		if ($InfTable)
			foreach ($InfTable as $key=>&$fld)
			  // if (count($this->colonne)==0 || isset($this->colonne[$key]))
			        {
			        $this->ParametriAutomatici[$key]=array('NONNULLO'=>false,'VALORE'=>'','VALOREDEFAULT'=>'','TIPO'=>'','MAXSIZE'=>false,'MAXVALUE'=>false,'INCREMENTALE'=>false,'ADODB'=>false,'FK'=>false,'DECIMALI'=>0,'REALTAB_NAMES'=>'','POSITIVO'=>false,'MYTYPE'=>'');
			        $this->colonneOriginali[$key]=$fld->name;
			        if ($fld->not_null) $this->ParametriAutomatici[$key]['NONNULLO']=$fld->not_null;
					if (isset($extraInfo[$key]['unsigned']) && $extraInfo[$key]['unsigned']>0) $this->ParametriAutomatici[$key]['POSITIVO']=$extraInfo[$key]['unsigned'];
					if ($fld->auto_increment) {$this->ParametriAutomatici[$key]['INCREMENTALE']=$fld->auto_increment;
											   $this->AutoIncrementante=$key;
											   $fld->primary_key=true;
											  }

					if(isset($fld->default_value)) $this->ParametriAutomatici[$key]['VALOREDEFAULT']=$fld->default_value;
					if ($isRSAttivo && $Table->_numOfRows==1) $this->ParametriAutomatici[$key]['VALORE']=$extraInfo[$key]['Value'];
 		 										//    else $this->ParametriAutomatici[$key]['VALORE']=$fld->default_value;

 		 			if ($fld->primary_key && !$chiavi_valorizzato) $this->chiavi[]=strtoupper($key);

					$this->ParametriAutomatici[$key]['TIPO']=$fld->type;
					//echo "<pre>"; 	print_r($fld);
					if (isset($fld->enums)) $this->ParametriAutomatici[$key]['TIPO'].="(".implode(',',$fld->enums).")";

					$len=$fld->max_length;
					if ($len>0) $this->ParametriAutomatici[$key]['MAXSIZE']=$len;
					  	    elseif ($this->ParametriAutomatici[$key]['MAXVALUE']) $len=$extraInfo[$key]['maxValue'];;

					$type=$this->get_meta_type($fld);
					$this->ParametriAutomatici[$key]['ADODB']=$type;

					if ($this->ParametriAutomatici[$key]['INCREMENTALE']) $this->AutoIncrementante=$key;
					//echo "<pre>";print_r($FK);
					if (isset($FK[$key][0])&& is_array($FK[$key][0]) && count($FK[$key])==1 )
											{
											  //echo "*$tab=>$cols[$key]<br>";
										     list($campo, $tab) = each($FK[$key][0]);
											 $this->ParametriAutomatici[$key]['FK']=array($tab=>$campo);
											 $this->ParametriAutomatici[$key]['REALTAB_NAMES']=&$REALTAB_NAMES;
											 }

					//echo "/$key=>$type";
					switch ($type)
					{
						case 'C': {
						 			if ($len>90) $tipo="myTextArea";
										elseif ($len==1) $tipo="myBoolean";
										   else $tipo="myText";
									}
						break;

						case 'X':  if ($len>90) $tipo="myTextArea";
										elseif ($len==1) $tipo="myBoolean";
										   else $tipo="myText";
						break;

						case 'B': if ($len>90) $tipo="myTextArea";
										elseif ($len==1)	$tipo="myBoolean";
										   else $tipo="myText";
						break;

						case 'D': $tipo="myDate";
						break;

						case 'T':{
								if ($len==8) 	$tipo="myTime";
										   else $tipo="myDateTime";
								}
						break;

						case 'L':		$tipo="myBoolean";
						break;

						case 'I':{
								IF (!$this->ParametriAutomatici[$key]['INCREMENTALE']) $tipo="myInt";
																     			  else $tipo="myHidden";
								 }
						break;

						case 'N': {
						   		  if ($fld->scale>0) {$tipo="myFloat";
						   		  					  $this->ParametriAutomatici[$key]['DECIMALI']=$fld->scale;
						   		  					  $this->ParametriAutomatici[$key]['MAXSIZE']=max(6,$len);
						   		  					  }
						   		              else   {$tipo="myInt";
						   		              		  $this->ParametriAutomatici[$key]['MAXSIZE']=max(4,$len);
						   		              		  }
								  }

						break;
						case 'R': $tipo="myHidden";
						break;
						}
					if ($this->ParametriAutomatici[$key]['POSITIVO'] && 'myInt'==$tipo) $tipo='myIntPos';
					$this->ParametriAutomatici[$key]['MYTYPE']="Gimi\\myFormsTools\\PckmyFields\\$tipo";

		            }
		
    	$this->SpecificheTecnologiche();
    	if ( ('myDate'==$tipo  || 'myDateTime'==$tipo) && ''==str_replace(array(':','-','0',' '),'',$this->ParametriAutomatici[$key]['VALORE'])  ) $this->ParametriAutomatici[$key]['VALORE']='';
    	
		
	//
		if ($this->chiavi) $this->chiavi=array_flip(array_flip($this->chiavi));
		if (isset($INFO['PK']) &&!count($this->chiavi)) $this->chiavi=$INFO['PK'];
		$this->set_chiavi($this->chiavi);
	 	//echo "<pre>"; print_r($this->ParametriAutomatici);


		//	}
	   	if (!$this->chiavi) Throw new \Exception( "Impossibile individuare chiave primaria, usare Set_chiavi() prima di Analizza_tabella({$this->tabella})");
		$k=array_flip($this->chiavi);
		if ($this->ParametriAutomatici) foreach ($this->ParametriAutomatici as $key=>$vals)   if (!isset($k[$key]) && $vals['FK']) $this->ParametriAutomatici[$key]['MYTYPE']='mySelect';


		$ADODB_COUNTRECS= $tempcount;
		$this->con->setfetchmode($precFetch);
		if($this->colonne) $this->set_ordine($this->colonne);

		return $this;
	    }


 	/** @ignore */
     public function SpecificheTecnologiche() {}

 	/** @ignore */
  	 public function _get_key_conn(){
      		return $this->con->get_key_conn();
    }


    /** @ignore */
  	 public function _get_conn(){
  		return $this->con;
    }


     /** @ignore */
     public function __get($x){  if(!is_object($this->campo($x))) 
                                             {
                                                 if(isset($this->magic_attr[$x])) return $this->magic_attr[$x];
                                             }
  	                                     else return $this->campo($x);	
  	                  }
  	
  	/** @ignore */
  	 public function __set($x,$v){  if(!is_object($this->campo($x))) $this->magic_attr[$x]=$v;
  	                                       else $this->campo($x)->set_value($v);
  	                         return $v;
  	                      }
     


	/**
     * Cambia il tipo di un campo - Si pu o'  usare prima di aver effettuato IstanziaCampi()
     * @param    string $nome_campo Nome del campo
     * @param    string $myTipo Nome della classe di myFields da usare
    */
	 public function CambiaTipoCampo($nome_campo,$myTipo) {
		 $nome_campo=strtoupper($nome_campo);
	  	 if ($this->istanziati) {echo "Impossibile cambiare tipo di $nome_campo in $myTipo perchè i campi sono giè stati istanziati o tabella DB non è stata caricata";exit;}
	  	 if(strpos($myTipo,'\\')===false) $myTipo="\\Gimi\\myFormsTools\\PckmyFields\\{$myTipo}";
	     $this->ParametriAutomatici[$nome_campo]['MYTYPE']=$myTipo;
	     if (isset($this->ParametriAutomatici[$nome_campo]['TIPO']) &&  !preg_match('/enum|set/i',$this->ParametriAutomatici[$nome_campo]['TIPO'])) unset($this->ParametriAutomatici[$nome_campo]['TIPO']);
	     if (preg_match('/multi|radio|select|check|date/i',$this->ParametriAutomatici[$nome_campo]['MYTYPE']))
			   		 {unset($this->ParametriAutomatici[$nome_campo]['MAXSIZE']);
	        	 	  unset($this->ParametriAutomatici[$nome_campo]['DECIMALI']);
	        	 	  unset($this->ParametriAutomatici[$nome_campo]['SIZE']);
			  		 }
		return $this;
		}


	/**
	* Restituisce il valore in formato ANSI
     * @param    string $nome_campo
     * @return   mixed $valori
     */
	function &get_value_db($obj) {
	    $value='';
		if (is_string($obj)) $obj=$this->campo($obj);
		if (!is_object($obj)) return '';
		//if ($obj instanceof myDate) return $obj->get_formatted('amg','-');
		//if ($obj instanceof myAnno) return $obj->get_value();
		if ($obj->estende('myFloat',true))
										{$obj->is_numeric(true);
									     $value= str_replace($obj->get_separatore(),$this->separatore_decimali,$obj->get_value());
										}
	/*	if ($obj instanceof myTime ||
			$obj instanceof myOra) {
		 							 $v=explode(':',$obj->get_value());
	 								 if (count($v)==2) $v[]='00';
	 								 if (method_exists($obj,'get_secs_mode') && !$obj->get_secs_mode()) unset($v[2]);
	 								 return implode(':',$v);
  									}
		if ($obj instanceof myOrario) return $obj->get_minuti();*/
									 
		elseif ($obj instanceof myMultiCheck)  {$obj->is_numeric(false);
			  							    if (is_array($obj->get_value())) $value=implode(',',$obj->get_value());
											 						    else $value= $obj->get_value();
	 									   }

	    elseif (is_array($obj->get_value_db()))   $value=implode(',',$obj->get_value_db());
									         else $value= $obj->get_value_db();
									    
		if($value!=='' && isset(self::$encodingOn['db']) && self::$encodingOn['db']) $value=myField::charset_encode('',$value);
		return $value;
	}


	 /** @ignore */
	function &quota_campo ($obj,$usa_cache=true) {
	    if (is_string($obj)) $obj=$this->campo($obj);
		if (!is_object($obj))  return '';
		$obj_id=spl_object_hash($obj).'_'.$obj->get_id_istanza();
        $x=$this->get_value_db($obj);
        if($usa_cache) $sha1=sha1($x);
        if($usa_cache && key_exists($obj_id,self::$cacheQuotati) && self::$cacheQuotati[$obj_id]['sha1']===$sha1) return self::$cacheQuotati[$obj_id]['val'];
                    
                                                    
 		$strlenX=strlen($x);
 	
 		
 		if ($obj->is_numeric()!==true && $obj->get_formula()==='')
 						 {  
 						  if ($strlenX==0 || ($strlenX==4 && strtolower($x)=='null')) $x='null';
 						                                                         else $x=$this->con->Quote($x);
						 }
 	 	  	  elseif ($obj->get_formula()!=='') {$x=$obj->get_formula();
 	 	  	                                     if(!$obj->is_numeric() && is_numeric($x)) $x="'$x'";
 	 	  	                                     }
 	 	  	     elseif ($obj->is_numeric()===true)
 	 	  	      	  	 {
 	 	  	      	  	 	if ($strlenX==0) $x='null';
 						 }
 				    elseif ($obj->get_value()==0) $x='null';
 		//file_put_contents('a.txt', $obj->get_name().'=>'.$x.'=>'.$obj->get_formula()."\n",FILE_APPEND);
 	    if($usa_cache) self::$cacheQuotati[$obj_id]=array('val'=>&$x,'sha1'=>$sha1);
 		return $x;
 	}







    /**
     * Setta le chiavi primarie della tabella se non avviene automaticamente
     * @param    array $elenco_chiavi Elenco delle chiavi
     * @param    boolean $autoincrementante se elenco ha un solo elemento questo parametro pu o'  indicare il fatto che sia autoincrementante
     */
 	 public function set_chiavi($elenco_chiavi,$autoincrementante=false) {
 	 if (!is_array($elenco_chiavi)) echo "Il parametro di set_chiavi deve essere un array";
 	   else  {$this->chiavi=$this->array_maiuscolo($elenco_chiavi)	;
 	          if (count($this->chiavi)==1 && $autoincrementante) $this->AutoIncrementante=$this->chiavi[0];
 	   		 //ormai inutile foreach($this->chiavi as $k) $this->chiavi_valore[$k]=$this->campi[$k]->get_value();
 	   		 }
 	   return $this;
 	}


 	/**
     * Ritorna un array associativo con le chiavi ed i relativi valori
     * @return    array
     */
 	 public function get_chiavi() {
 	  $nuovo=array();
 	  if (is_array($this->chiavi)) foreach ($this->chiavi as $k) $nuovo[$k]=$this->get_value($k);
 	  return $nuovo;
 	}

 	
 	/**
 	 * Imposta i tooltip dei campi prendendoli dai commenti alle colonne della tabella
 	 */
 	 public function set_tooltip_da_commenti() {
 	    if($this->COMMENTI_TAB)
 	        foreach ($this->COMMENTI_TAB as $campo=>$infos)
 	            if ($infos && $this->campo($campo)) $this->campo($campo)->set_tooltip($infos);
 	            return $this;
 	}

	function  get_last_query() {
		return $this->lastqry;
	}


	 /**
     * Imposta il valore come formula
     * @param    string $campo
	 * @param    string $formula Utile se si vuole che il valore venga interpretato come una formula in fase di salvataggio su db
	 *
	 * Es.
	 * <code>
	 *   .....
	 *   // Se $F  e'  un myForm_DB o estensione ..
	 *  $F->set_formula('DATA_INSERIMENTO','sysdate()');  //in questo modo il campo prenderà il valore della funzione del DB sysdate()
	 * </code>
	 */
     public function set_formula($campo,$formula) {
       	  if($this->campo($campo)) $this->campo($campo)->set_formula($formula);
       	  return $this;
    }


    /**
     * Ritorna un array associativo con i campi ed i relativi valori del record attivo
     * attenzione, non corrispone necessariamente ai valori dei campi della form, che potrebbero
     * essere stati modificati a seguito di post di dati che non hanno avuto effetto sul DB
     * questi sono proprio i valori che sono attualmente (si fa per dire) sul DB
     *
     * @param     boolean $ricarica se true forza il ricaricamento dei dati dal DB
     * @param 	  'U'|'L'|'N'   $caseChiavi dell'array Upper,Lower,Normal (di default Uppercase... come escono da DB)
     * @return    array
     */
	function &get_valori_recorddiLavoro($ricarica=false,$caseChiavi='U',$quali_campi=array()) {
	   $estratti=array();
	   if ($ricarica) {
	        	   $where=array();
    			   foreach ($this->chiavi as $col)
    			       {$val='';
    			        if(isset($this->condizioni[strtoupper($col)])) $val=trim((string) $this->condizioni[strtoupper($col)]);
    		 			if ($val==='') {$where=array();
    		 							break;
    		 						   }
    				     $where[$col]=$val;
    			        }
    			 if (!$where) $this->recordset_attivo=null;
    			      else{ if($quali_campi)  $estratti=$this->con->getrow("select /*1*/ ".implode(',',$quali_campi)." from {$this->tabella} where ".implode(' and ',$this->quota_colonne($where)));
    			                        else  $this->set_valori_recorddiLavoro($this->con->getrow("select * from {$this->tabella} where ".implode(' and ',$this->quota_colonne($where))));
    			           }
    			                         
    			}
        if(!$estratti && $this->recordset_attivo && $this->recordset_attivo->fields) $estratti=&$this->recordset_attivo->fields;
		if ($estratti) {
			 $keys=implode(',',array_keys($estratti));   
			 switch ($caseChiavi) {
			 	case 'U': if(strtoupper($keys)!=$keys) $estratti=array_change_key_case($estratti,CASE_UPPER);
			 	          break; 
			 	case 'N': break;
			 	case 'L': if(strtolower($keys)!=$keys) $estratti=array_change_key_case($estratti,CASE_LOWER);
			 	          break;
			 }
	    } 
		return $estratti;
	}

	  public function set_valori_recorddiLavoro($values) {
		if (!$this->recordset_attivo) $this->recordset_attivo=new \stdClass();
		$this->recordset_attivo->fields=array();
		foreach ($this->metaDatiTabella as $id=>&$colonna)
		                     {$k=null;
		                      if(isset($values[$id]))            $k=$id;             
		                      if(isset($values[$colonna->name])) $k=$colonna->name;
		                      if($k!==null) { $this->recordset_attivo->fields[$colonna->name]=$values[$k];
                		                      unset($values[$colonna->name]);
                		                      unset($values[$id]);
                		                     }
		                      }
		foreach ($values as $id=>&$val) if(!isset($this->recordset_attivo->fields[$id])) $this->recordset_attivo->fields[$id]=$val;
		return $this;
	}


	/** @ignore */
    protected function GeneraWhere($valori,$where=" where ") {
    	$valori=@array_change_key_case($valori,CASE_UPPER);
    	$conds=array();
    	if($valori)
    		foreach ($valori as $nome=>$valore)
    		  if (is_object($this->campo($nome)))
    	   	    	{
    	   	    	$campo=clone $this->campo($nome);
    	   	    	if(!is_callable($valore)) { $campo->unset_formula();
                                			    $campo->set_value($valore);
                                			  }
    			    $conds[]="$nome=".$this->Quota_Campo($campo,false);
    				}
    	if ($conds) return $where.implode(' and ',$conds);
    		   else return $where.' 1=0 ';
     }



    /** @ignore */
    protected function GeneraWhereActiveRecord($valori,$where=" where ") {
    	//print_r($this->get_chiavi());
    	$valori=@array_change_key_case($valori,CASE_UPPER);
    	$chiavi=@array_change_key_case($this->get_chiavi(),CASE_UPPER);
    	$condizione=(array)  @array_intersect_key((array) $valori,(array) $chiavi);
    	return $this->GeneraWhere($condizione,$where);
    }

    /** @ignore */
    protected function GeneraInsert(&$valori){
    	 return "insert into {$this->tabella} (".implode(',',@array_keys($valori)).") values (".implode(',',($valori)).")";
    }


    /** @ignore */
    protected function GeneraDelete() {
    	return "delete from {$this->tabella} ".$this->GeneraWhereActiveRecord($this->get_valori_recorddiLavoro());
    }

    /** @ignore */
    protected function GeneraUpdate(&$valori){
        $updates=array();
    	$where=$this->GeneraWhereActiveRecord($this->get_valori_recorddiLavoro());
    	foreach ($this->get_valori_recorddiLavoro(false,'U') as $nome=>$valore)
    	    if (is_object($this->campo($nome)) && isset($this->metaDatiTabella[$nome]))
    	   	 {
    		  $campo=clone $this->campo($nome);
    		  $campo->set_value($valore,1); //1 per i myupload
    		//  echo $nome,']=>',sha1($valore),'=>',sha1($this->Quota_Campo($campo)),'<=>',sha1($valori[$nome]),'<br>';
    		  if($this->campo($nome)->get_formula()!=='' || $this->Quota_Campo($campo)!==$valori[$nome]) 
    		       	//in caso di datetime nullo su DB $this->Quota_Campo($campo) darebbe ora corrente
            		  { $updates[]="$nome=".$this->Quota_Campo($nome);
            		//    echo $nome,'<=<br>';
            		  }
    	     }
    
    	if (!$updates) return '';
    	return "update {$this->tabella} set ".implode(',',$updates).' '.$where;
    }


    /**
     * Elimina dal DB la tupla relativa ai dati nel Form,se ok ritorna null
     */
     public function Elimina() {
        self::$cacheQuotati=array();
    	//echo $this->tabella.$this->con->_connectionID." ".$this->con->_commit."<br>";
    	//echo $this->tabella.$this->con->_connectionID." ".$this->con->_commit."<br>";
    	if ($this->ripetizione) return ;
		$this->lastqry='';
		if ($this->AutoIncrementante) $this->campo($this->AutoIncrementante)->is_numeric(true);

		if ($this->get_valori_recorddiLavoro())
    			{
    			 $qry=$this->generaDelete();
				 $esito=$this->con->execute($qry);
    		  	 if ($esito===false) {$errore=$this->con->ErrorMsg();
				 					  $this->onInternalError($errore);
				 					  return $errore;
				 					  }
    			 	                 else  $this->lastqry=$qry;

    			}
        }

     static function disabilita_verifica_f5(){
         self::$check_f5=false;
        }

     public function disabilita_verifica_ripetizione() {
    	$this->ripetizione=false;
    	return $this;
    }



 	/**
     * Salva i dati presenti nel Form su DB,se ok ritorna null
     * @param bool $auto_ricarica_dati se true dopo l'inserimento di una riga con formule o autoincrementanti i dati vengono ricaricati per refreshare i campi con quanto prodotto dal DB, se falso non si fa ricaricamento
     */
     public function Salva($auto_ricarica_dati=false) {
        
        self::$cacheQuotati=array();
        $inserimento=false;
		if ($this->ripetizione) return ;
		
		$Presente_formula=array();
		$this->lastqry='';
		if ($this->AutoIncrementante && is_object($this->campo($this->AutoIncrementante)) ) $this->campo($this->AutoIncrementante)->is_numeric(true);
		
		if ($this->get_chiavi() && !$this->get_valori_recorddiLavoro()) $this->get_valori_recorddiLavoro(true);
		$out=array();
		foreach ($this->campi as $id=>&$obj)  $out[strtoupper($id)]=$this->Quota_Campo($obj);
		  
		if($this->AutoIncrementante && !$this->campo($this->AutoIncrementante)->get_value())
								   {$this->campo($this->AutoIncrementante)->set_value('');
  									unset($out[strtoupper($this->AutoIncrementante)]);
  									$inserimento=true;
									}

		foreach (array_keys($out) as $nome) {
				if(!isset($this->metaDatiTabella[$nome])) unset($out[$nome]);
								elseif ($this->campo($nome)->get_formula()!=='') $Presente_formula[]=strtoupper($nome);
			   }
	
			
		
	    $qry='';
	    $last_id=false;
		if (!$inserimento && $this->get_valori_recorddiLavoro())
    			{
    			//echo "<pre>";print_r($_POST);print_r($out);
				 $qry=$this->GeneraUpdate($out);
				 if (!$qry || $this->con->execute($qry)===false)
				 						{$errore=$this->con->ErrorMsg();
				 						 $this->onInternalError($errore);
				 						 return $errore;
				 						}
				}

    	  else {
    	        $qry=$this->GeneraInsert($out);
    	       
    	        if (!$qry || $this->con->execute($qry)===false)
    	  							    {$errore=$this->con->ErrorMsg();
    	  							  	 $this->onInternalError($errore);
				 						 return $errore;
				 						}
    			if ($this->AutoIncrementante && $last_id=$this->con->Insert_ID($this->tabella,$this->AutoIncrementante))
    					{
    					 $this->campo($this->AutoIncrementante)->set_value($last_id); //avendo una id può ricaricare i valori dalla tabella
    					 if(isset($this->condizioni[strtoupper($this->AutoIncrementante)])) $this->condizioni[strtoupper($this->AutoIncrementante)]=$last_id;
    					}
    			 $inserimento=true;
    			 }
    	$this->lastqry=$qry;

    	if($this->condizioni && ($Presente_formula || $auto_ricarica_dati))
    					 		{
    					         foreach ($this->condizioni as $i => &$val) 
    					                                   $val=$this->campo($i)->get_value();
    					         $val;
								 if($auto_ricarica_dati) $Presente_formula=array(); //ricarica tutti i campi a prescindere che ci sia formula o meno
								 $added=$this->get_valori_recorddiLavoro(true,'U',$Presente_formula);
    					         if($added) $this->set_values(array_merge($this->get_values(),$added)); // valorizza i campi con è presente nel rs
    					 	   }

		$out=array();
		foreach ($this->metaDatiTabella as $nome=>&$colonna) if($this->campo($colonna->name)) $out[$colonna->name]=$this->get_value_db($this->campo($colonna->name));
		$this->set_valori_recorddiLavoro($out);

		$this->recover_chiavi($this->get_chiavi());
		//exit;
		return false;
    }


	/**
	* Effettua analisi di validità  dei campi
     * @param    array $qualicampi campi da controllare/non controllare (se omesso si applica a tutti indipendentemente da $Esclusi)
     * @param 	 boolean $Esclusi  se true o omesso $qualicampi sono esclusi dalla verifica se falo si verificano esclusivamente quelli
     * @return   array Array associativo con label=>'Label del campo errato' errore=>'messaggio di errore' campo=>'nome del campo errato'
     */
	 public function Check_Errore_Diviso($qualicampi='',$Esclusi=true) {
//echo "<pre>";print_r($this->campi);
	 $errore=parent::Check_Errore_Diviso($qualicampi,$Esclusi);
	 if ($errore) return  $errore;
	 $noChiave='';
	 if (is_array($this->indici) && count($this->indici)>0 && !$this->ripetizione) {
	 	$chiavi=$this->condizioni;
	 	if (isset($this->recordset_attivo->fields) && $this->recordset_attivo->fields)
    			{
    			 $qry=$this->GeneraWhereActiveRecord($this->get_valori_recorddiLavoro(),'');
    			 if ($qry) $noChiave=" and not ($qry)";
    			}
        
    	static $gia_fatto=array();		
	  	foreach ($this->indici as $indice)
	 	  	if ($indice['unique'] && !isset($gia_fatto[serialize($indice['columns'])] ))
	 	  				{
	 	  				$gia_fatto[serialize($indice['columns'])]=1;
						$colonne=array_flip(array_change_key_case(array_flip((array) $indice['columns']),CASE_UPPER));
						$campi=array();
						$condizioni='';
						foreach ($colonne as $colonna)
								if ($this->campo($colonna) &&
								    (strlen($valoref=$this->campo($colonna)->get_formula())
								                              ||
								     strlen($valoredb=$this->get_value_db($this->campo($colonna)))
								    ) )
											{
											    $campi[$colonna]=$valoref?function(){}:$valoredb;
										//	echo "$colonna =>".$this->campo($colonna)->get_value()."<br>";
											}
						//print_r($indice); 
						if ($campi && count($campi)==count($indice['columns']))
								{  if ($campi==$chiavi) continue;
								   $condizioni=$this->GeneraWhere($campi);
								}

	   					if ($condizioni)
	   						{
	   						$rs=&$this->con->selectlimit("select 1 from {$this->tabella} $condizioni $noChiave",1,0);
	   						if ($rs && $rs->fields)
							  {$labels=$fields=array();//print_r($colonne);
					 		   foreach ($colonne as $vals)
					 			 		if ($this->get_label($vals) && !$this->campo($vals)->is_hidden())
	   							 						 {$labels[]=$this->get_label($vals);
	   							 						  $fields[]=$this->campo($vals);
	   							 						 }
	   							return array('label'=>$labels,
	   										 'errore_forzato'=>1,
	   										 'errore_tipo'=>'internal',
	                        	 			 'errore'=>(count($labels)==1
	                        	 			 			 ?$this->trasl("Campo '%label%' usato in un altro record")
	                        	 			 			 :$this->trasl("Campi: '%label%' usati in un altro record")
	                        	 			 			),
	                        	 			 'campo'=>$fields);
	   						  }
	   						}
	   					}
	 	}
	}






    /**
     * Istanzia i Campi, a questo punto si pu o'  usare &get_campo() ma non  e'  pi u'  possibile  l'uso di CambiaTipoCampo()
     *
     * @param    boolean $carica_FK se vero crea automaticamente dei menu a tendina da tabelle per le quali ci sono Foreign keys
     *
     * N.B. Il caricamento automatico avviene SOLO se la tabella referenziata sia composta da DUE sole colonne di cui una e la chiave esterna
     */
	 public function IstanziaCampi($carica_FK=false) {

	  if (!$this->chiavi) {echo "Impossibile individuare la chiave primaria automaticamente utilizzare set_chiavi"; exit;}
	  if ($this->istanziati) {echo "Campi già  istanziati per ".$this->tabella." o tabella DB non caricata";exit;}
	  $colonne=array_flip($this->colonne);
	  if (is_array($this->ParametriAutomatici))
	  	foreach ($this->ParametriAutomatici as $id=>$val)
	    	if (!$colonne || isset($colonne[strtoupper($id)]))
	  		 {
	  		//	echo "<br>".memory_get_usage();
	  		//	echo "<hr><pre>new $val[MYTYPE]($id,$val[VALORE])<br>";
			 //    print_r($val);

	  			//$campo=$this->istanzia($val['MYTYPE'],$id,$val['VALORE']);
	  			//print_r($campo);
	  			//if ($J++==5) exit;
	  		 if(!isset($val['MYTYPE'])) continue;
	  		 
	  		 $classe=$val['MYTYPE'];
	  		 if(strpos($classe,'\\')===false &&
	  		 	stripos($classe,'my')===0 &&
	  		 	class_exists("Gimi\\myFormsTools\\PckmyFields\\$classe")) $classe="Gimi\\myFormsTools\\PckmyFields\\$classe";
	  		 $campo=new $classe($id,null);

	   		 $this->add_campo($campo,'',true);
			//print_r($this->campi);

	 		if (isset($val['NONNULLO']) && $val['NONNULLO'] && (!isset($val['INCREMENTALE']) || !$val['INCREMENTALE'])) $this->campi[$id]->set_notnull();

	  		if (is_object($this->campi[$id]) &&
	  		    $this->campi[$id]->estende('myText',true) )
	  				{
	  				if ($val['MAXSIZE'] && $val['MYTYPE']!='myDate')
	  						{
	  						 if( $this->campi[$id]->estende('myFloat',true))
	  						 			{$MinMax=str_repeat('9', $val['MAXSIZE']-$val['DECIMALI']-$val['DECIMALI']-1).'.'.str_repeat('9',$val['DECIMALI']);
	  						 			// if($this->campi[$id]->get_maxlength()===null) $this->campi[$id]->set_maxlength($val['MAXSIZE']-$val['DECIMALI']);
	  						 			 if($this->campi[$id]->get_min()===null) $this->campi[$id]->set_min(-$MinMax);
	  						 			 if($this->campi[$id]->get_max()===null) $this->campi[$id]->set_max($MinMax);
	  						 			}
	  						 	elseif( $this->campi[$id]->estende('myInt',true))
	  						 			{$MinMax=str_repeat('9', $val['MAXSIZE']);
	  						 			if($this->campi[$id]->get_maxlength()===null) $this->campi[$id]->set_maxlength(($val['POSITIVO']?$val['MAXSIZE']:$val['MAXSIZE']+1));
	  						 			if($this->campi[$id]->get_max()===null) $this->campi[$id]->set_max($MinMax);
	  						 			if($this->campi[$id]->get_min()===null) $this->campi[$id]->set_min(($val['POSITIVO']?0:-$MinMax));
	  						 			}
	  						 	elseif($this->campi[$id]->get_maxlength()===null)   $this->campi[$id]->set_maxlength($val['MAXSIZE']);
	  						}
	  				}

	  			if ($val['MYTYPE']=='myTextArea') {$this->campi[$id]->set_rows(4);
	  	      								       $this->campi[$id]->set_cols(70);
	  	      								      }
	  	      	if ($val['MYTYPE']=='myOrario')	 $this->campi[$id]->set_minuti($val['VALORE']);

	  	      	if ($val['MYTYPE']=='mySelect' && $carica_FK && is_array($val['FK']))
	  	      									{
	  	      									 $tempmode=$this->con->SetFetchMode(ADODB_FETCH_ASSOC);
    											 //echo "<pre>",$tab,print_r($val);
    											 foreach ($val['FK'] as $col=>$tab) {;}

    											 $tab=$val['REALTAB_NAMES'][$tab];
    											 $rs=&$this->con->selectlimit("select * from $tab",1,0);
                								 if(count($rs->fields)==2) {$k=array_keys($rs->fields);
	  	      									 							if (strtoupper($k[0])==$col) $altra=$k[1];
	  	      									 													else $altra=$k[0];
    											  							$this->campi[$id]->set_opzioniQRY($this->con,"select $altra,$col from $tab order by $altra");
    											  							if (!$this->campi[$id]->get_notnull()) $this->campi[$id]->set_domanda(' ');
	  	      									 						   }
    											 $this->con->SetFetchMode($tempmode);
	  	      									 }
	  	      									 

			   }
	  $values=array();
      if (is_array($this->ParametriAutomatici))
         foreach ($this->ParametriAutomatici as $id=>$val)
	  		if ((!$colonne || isset($colonne[$id])) && isset($this->campi[$id]) && $this->campi[$id])
	  			{
	  	      	if ($val['MYTYPE']=='myMultiCheck') $values[$id]=(!isset($val['VALORE']) || trim((string) $val['VALORE'])!==''?explode(',',$val['VALORE']):array());
	  										   ELSE $values[$id]=(isset($val['VALORE'])?$val['VALORE']:'');
	  		  	}
      
	 $this->set_defaults($values);
	 $this->istanziati=true;
	 return $this;
	}



	/**
	 * Imposta il separatore dei decimali da usare nelle queri del DB
	 * @param  .|, $separatore
	 */
	 public function set_separatore_decimali($separatore)
	{if ($separatore=='.' || $separatore==',') 	$this->separatore_decimali=$separatore;
 	 return $this;
	}


	/**
	 * Imposta il form con i valori prelevati dal DB per il record attivo se assente usa i defaults dichiarati nel DB
	 * Viene invocato nella myForm_DB::istanziacampi()
	 *
	 * @param array $valori valori prelevati dal DB per il record attivo
	 */
    public function set_defaults(&$valori=array()) {
   	if($valori) foreach ($valori as $id=>&$val)
   							if($this->campo($id))
   									$this->campo($id)->set_value($val);
      elseIF (is_array($this->ParametriAutomatici))
    		foreach ($this->ParametriAutomatici as $id=>&$val)
    						if($this->campo($id))  $this->campo($id)->set_value($val['VALOREDEFAULT']);
    return $this;
   }



    public function get_defaults() {
    $campi=array();   
    IF (is_array($this->ParametriAutomatici))  foreach ($this->ParametriAutomatici as $id=>$val) if($val['VALOREDEFAULT']) $campi[$id]=$val['VALOREDEFAULT'];
    return $campi;
   }



}