<?php
/**
 * Contains Gimi\myFormsTools\PckmyForms\myForm_Oracle.
 */

namespace Gimi\myFormsTools\PckmyForms;





/**
 *  Versione di myForm_DB ottimizzata per Oracle
  **/
	
class myForm_Oracle extends myForm_DB  {
/** @ignore */
private $sessione_corrente;
/** @ignore */
protected $separatore_decimali='.',$SalvaInfo=43000;

/**
     * Costruttore di classe
	  * 
     *
	 * @param    mixed $con Connessione al db da usare
     * @param    array $colonne elenco delle colonne della tabella da utilizzare, se omesso si usano tutte
     * @param    string $tabella Nome della tabella da utilizzare
     * @param    array $condizioni Array associativo Colonna=>Valore per la condizione di recupero valori dalla tabella
     */
	 public function __construct(&$con,$colonne=array(),$tabella='',$condizioni=array())
	   {if ($colonne) foreach ($colonne as $i=>$v) $colonne[$i]=strtoupper($v);
	    parent::__construct($con,$colonne,strtoupper($tabella),$condizioni);
	    if($this->schema==$this->con->database()) $this->schema=$this->con->getone("select sys_context('USERENV','CURRENT_SCHEMA') from dual");
		$this->SalvaInfo=43000;
	   }
	   
	   
	/**
	 * @ignore
	 */
	protected function get_meta_comments(){
	    return $this->COMMENTI_TAB;
	}


	   

	/** @ignore */
	private function presente_tipo($classe){
		foreach ($this->get_campi() as $campo) if (strtolower(get_class($campo))==strtolower($classe)) return true;
		return false;
	}


	 public function analizza_tabella(){
		$this->set_ambiente(true);
		parent::Analizza_Tabella();
		foreach (array_keys($this->ParametriAutomatici) as $nome) if (preg_match('|^SYS_.+\$$|',$nome)) unset($this->ParametriAutomatici[$nome]);
		return $this;
	}


	 public function IstanziaCampi($fk=false){
		parent::IstanziaCampi($fk);
		$this->reset_ambiente();
		return $this;
	}



	/**
	* Restituisce il valore in formato OCI
     * @param    string $nome_campo
     * @return   mixed $valori
     */
	function &get_value_db($obj) {
	    $null='';
		if (is_string($obj)) $obj=$this->campo($obj);
		if (!is_object($obj) || strlen($obj->get_value())==0) return $null;
		return parent::get_value_db($obj);
	}


	/**
	 * Setta l'ambiente migliore per il salvataggio dei datetime, viene invocato automaticamente
	 * utile invocarlo manualmente se si usano tanti salva su myForm_Oracle non inseriti in una myMultiform
	 *
	 */
	 public function set_ambiente($forza=false){
		if ($forza || (!$this->sessione_corrente && $this->presente_tipo('mydatetime')))
				{ if(!$this->con->enterprisedb)  $sessione_corrente=$this->con->getone("SELECT value FROM V\$NLS_PARAMETERS where parameter='NLS_DATE_FORMAT'");
									  	else    $sessione_corrente=$this->con->getone("show DATESTYLE");
	 			  if (strtolower($sessione_corrente)=='yyyy-mm-dd hh24:mi:ss') $this->sessione_corrente=null;
	 			  														 else {$this->sessione_corrente=$sessione_corrente;
	 			  															   $this->con->execute("alter session set nls_date_format = 'yyyy-mm-dd hh24:mi:ss'");
	 			  															  }
				}
		return $this;
	}

	/**
	 * reimposta l'ambiente allo stato normale
	 *
	 */
	 public function reset_ambiente(){
		if ($this->sessione_corrente) $this->con->execute("alter session set nls_date_format = '{$this->sessione_corrente}'");
		$this->sessione_corrente=null;
		return $this;
	}




  /**
    * Setta una sequence per la tabella
    * @param  string $nome_sequence
    * @param  string $nome_campo
   */
   public function set_sequence($nome_campo,$nome_sequence) {
  	$this->seq_oracle=array($nome_campo=>$nome_sequence);
  	return $this;
  }


   public function Salva($auto_ricarica_dati = true) {

   if ($this->ripetizione) return ;
   // se la colonna è not null non dovrebbe bloccare elaborazione*/
   $seq=array();
   if (is_array($this->seq_oracle))
   foreach ($this->seq_oracle as $nome_campo=>$nome_sequence) {
    if (!$this->chiavi)  {echo "Impossibile individuare automaticamente le chiavi primarie, impostarle con set_chiavi"; exit;}
  	 if ($nome_campo=='' xor $nome_sequence=='')  {echo "Specificare sia la sequence che il nome della colonna in cui deve andare il valore"; exit;}
 	   elseif ($nome_campo && !isset($this->campi[$nome_campo])) {echo "Sequence associata a colonna '$nome_campo' non presente nella tabella"; exit;}
 	 	 elseif ($nome_campo)
 	 	 	  {$this->campo($nome_campo)->is_numeric(true);

 	 	 	   if (!$this->get_value($nome_campo)) {$seq[$nome_campo]=$this->con->getone("SELECT $nome_sequence.nextval as SEQ FROM DUAL");
 	 	 	   										$this->campo($nome_campo)->set_value($seq[$nome_campo]);
 									  	  	     	if(isset($this->condizioni[strtoupper($nome_campo)])) $this->condizioni[strtoupper($nome_campo)]=$seq[$nome_campo];
	    											}
	    	  }
    }

    $this->set_ambiente();
 	   $esito=parent::salva($auto_ricarica_dati);
    $this->reset_ambiente();

    if ($esito!=null && $seq!=null)
    				foreach ($this->seq_oracle as $nome_campo=>&$nome_sequence)
    						{//rollaback manuale della sequenzce
    						$this->campo($nome_campo)->set_value('');
 							if(isset($this->condizioni[strtoupper($nome_campo)])) $this->condizioni[strtoupper($nome_campo)]=null;
	    					}
	return $esito;
    }


     public function SpecificheTecnologiche() {
     //	echo "***************************";
     if	($this->ParametriAutomatici)
     	foreach ($this->ParametriAutomatici as $id=>$val)
	        {
	        	if ($val['TIPO']=='DATE')  $this->ParametriAutomatici[$id]['MYTYPE']='Date';
	         }
		}




     public function Elimina() {
    	if ($this->ripetizione) return ;

    	$this->set_ambiente();
    	$esito=parent::Elimina();
    	$this->reset_ambiente();

  	   return $esito;
    }



	  public function check_errore_diviso($qualicampi='',$Esclusi=true){
	 		$this->set_ambiente();
	 			$esito=parent::Check_Errore_Diviso($qualicampi,$Esclusi);
	 		$this->reset_ambiente();
	 		return $esito;
	 }




	function &MetaColumns()
	{   $table=$this->get_tabella();
	
	    /*

	    $info=parent::MetaColumns($table,$normalize);
	    if (is_array($info) && count($info)>0) {return $info;}
	 	*/

	    if(!$this->con->enterprisedb) $schemacol=' owner ';
	    					     else $schemacol=' schemaname ';
	    $rs = $this->con->Execute("select column_name, data_type,  data_length, data_scale,data_precision,nullable, data_default from all_tab_columns where $schemacol=:db and table_name=:tabella order by column_id",array('db'=>$this->schema,'tabella'=>$table));
        if ($rs === false || $rs->EOF) $rs = $this->con->Execute("select  column_name, data_type,  data_length, data_scale,data_precision,nullable,data_default from  all_tab_columns  where table_name=:tabella order by column_id",array('tabella'=>$table));
		if ($rs === false || $rs->EOF) {return false;}

		$retarr = array();
		while (!$rs->EOF) {
				$fld = new \ADOFieldObject();
				$fields=array_values($rs->fields);
				$fld->name = $fields[0];
				$fld->type = $fields[1];
				$fld->not_null=$fields[5]=='N';
				$fld->default_value=$fields[6];
				$this->COMMETI_TAB[$fields[0]]=$fields[7];
				if ($fields[3]) {
					if ($fields[4]>0) $fld->max_length = $fields[4];
					$fld->scale = $fields[3];
					if ($fld->scale>0) $fld->max_length += 1;
				  } else $fld->max_length = $fields[2];
				
				$retarr[strtoupper($fld->name)] = $fld;
				$rs->MoveNext();
			}
		$rs->Close();
		return $retarr;
	}



	// Mark Newnham
	function &MetaIndexes ($table, $primary = FALSE, $owner=false)
	{  
		$table = strtoupper($this->get_tabella());
		if(!$this->con->enterprisedb) $schemacol=' owner ';
								  else $schemacol=' schemaname ';


		$idxs= $this->con->getarray("SELECT INDEX_NAME, UNIQUENESS FROM ALL_INDEXES WHERE TABLE_NAME=:tabella AND $schemacol=:schema",array('tabella'=>$table,'schema'=>$this->schema));
        if(!$idxs) $idxs= $this->con->getarray("SELECT INDEX_NAME, UNIQUENESS FROM ALL_INDEXES WHERE TABLE_NAME=:tabella",array('tabella'=>$table));

        $indexes=array();
        if($idxs) 
        	foreach ($idxs as &$idx) {
        		$row=array_values($idx);
			  	$indexes[$row[0]] = array(
										   'unique' => ($row[1] == 'UNIQUE'),
										   'columns' =>array_values((array) $this->con->getassoc("SELECT COLUMN_POSITION,COLUMN_NAME FROM ALL_IND_COLUMNS WHERE INDEX_NAME=:indice",array('indice'=>$row[0])))
											);
			  	ksort ($indexes[$row[0]]['columns']);
         		}
 
        return $indexes;
	}



}