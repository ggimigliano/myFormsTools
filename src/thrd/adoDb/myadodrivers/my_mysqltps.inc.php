<?
class MySQL_rs_ext_empty extends ADORecordSet_empty  {}
class MySQL_rs_empty extends ADORecordSet_empty  {}

class MySQL_ADORecordSet_array extends ADORecordSet_array {


     public function MetaType($t,$len=-1)
		{$typo=parent:: MetaType($t,$len); //corregge errore in assegnazione tipo data
		if (is_object($t)) {
			$fieldobj = $t;
			$t = $fieldobj->type;
			$len = $fieldobj->max_length;
			}
		if (strtoupper($t)=='DATE') return 'T';
							 else return $typo;
		}


		/**
	 * Mia versione corretta per Zend optimizer plus
	 */
	function &GetAssoc($force_array = false, $first2cols = false)
		{ return mySQL_rs_mysqltps::GetAssoc($force_array , $first2cols);
		}
}






class MySQL_mysqltps extends ADODB_mysqltps {
	public $rsPrefix = 'MySQL_rs_';
	public $lastQry;
	public $arrayClass='MySQL_ADORecordSet_array';
	public $track=true;
	public $lastId;
	public $selects=array();
	public $logger=null;
	public $statements=array();
	public $clientFlags;
	public $_bindInputArray=true;
	public $_blackhole=false;



	/*************************** mie utilities  *****************************************************************/

	 public function set_logger($logger) {
		$this->logger=$logger;
	}

	 public function unset_logger() {
		$this->logger=null;
	}

	 public function get_logger(){
		return 	$this->logger;
	}



	 public function set_blackhole() {
		$this->_blackhole=true;
	}

	 public function unset_blackhole() {
		$this->_blackhole=false;
	}

	 public function get_binding() {
		return $this->_bindInputArray;
	}



	 public function set_binding() {
		$this->_bindInputArray=true;
	}

	 public function unset_binding() {
		$this->_bindInputArray=false;
	}



	function &_query($sql, $inputarr=false)
	{ if($this->_blackhole) { $this->lastQry=$sql;
							  return true;
							}
	  return parent::_query($sql, $inputarr);   //standard usage
	}


	 public function UnValore($sql) {
		return $this->getone($sql);
	}

	 public function CacheUnValore($secs2cache,$sql) {
		return $this->cachegetone($secs2cache,$sql);
  	}


	 public function Myserializzarray($array) {
		if (is_array($array)) foreach ($array as $i=>$v) $ser.="$i => ".stripslashes(serialize($v))."\n";
		return $ser;
	}


	/********************************************************************************/







	/**************         correzioni al funzionamento *******************/


	function &GetRow($sql,$inputarr=false)
	{$sql=trim((string) $sql);
	 if(stripos($sql,"select")===0 && !preg_match('/ limit\s+\d+$/isSU',$sql)) $sql.=' limit 1';
	 return parent::GetRow($sql,$inputarr);
	}


	function &CacheGetRow($secs2cache,$sql=false,$inputarr=false)
	{
	 $sql=trim((string) $sql);
	 if(stripos($sql,"select")===0 && !preg_match('/ limit\s+\d+$/isSU',$sql)) $sql.=' limit 1';
	 return parent::CacheGetRow($secs2cache,$sql,$inputarr);
	}

	

	function &CacheGetOne($secs2cache,$sql=false,$inputarr=false)
	{
		$sql=trim((string) $sql);
		if(stripos($sql,"select")===0 && !preg_match('/ limit\s+\d+$/isSU',$sql)) $sql.=' limit 1';
		return parent::CacheGetOne($secs2cache,$sql,$inputarr);
	}
	

    public function MetaForeignKeys($table, $owner=false)
	 {  global $ADODB_FETCH_MODE;
	 	$fk=parent::MetaForeignKeys($table, $owner,false,$ADODB_FETCH_MODE);

	 	if ($fk) return $fk;

	 	$prec=$ADODB_FETCH_MODE;

	 	$ADODB_FETCH_MODE=ADODB_FETCH_NUM;
		$rs=$this->execute("SHOW CREATE TABLE `$table`");
	//	print_r($rs->fields);
		$create=$rs->fields[1];
		$create=explode('FOREIGN KEY',$create);


		unset($create[0]);
		$f_k=array();
		if (is_array($create))
			foreach ($create as $str)
				{
				 $campi=preg_split('@[\\)\\(]+@U',str_replace('`',"'",str_replace('REFERENCES ','',$str)));
				// echo "<pre>";print_r($campi);
				 if (count($campi)>=5) {
				 						eval("\$k_locali=array($campi[1]);");
				 						eval("\$tab=$campi[2];");
				 						eval("\$k_ext=array($campi[3]);");
				 						$valori=array();
				 						foreach ($k_locali as $i=>$val) $f_k[$tab][]="$val=$k_ext[$i]";
				 						//echo $tab."-".$campi[1]."<br>";
				 						}
				}
		$ADODB_FETCH_MODE =$prec;
		return $f_k;
  	 }



   /**********************************************************************************/


  	 /**************         correzioni al funzionamento cache *******************/
	 public function _CreateCache($memcache='')
	{  global $ADODB_CACHE;
	   if(!empty($ADODB_CACHE)) return;
	   parent::_CreateCache();
	   if($memcache) {$ADODB_CACHE->_connected=true;
	   				  $ADODB_CACHE->_memcache=$memcache;
					  }

	}


	 public function _gencachename($sql,$createdir)
	{
	 global $ADODB_CACHE, $ADODB_CACHE_DIR;
	 if ($this->fetchMode === false) {
				global $ADODB_FETCH_MODE;
				$mode = $ADODB_FETCH_MODE;
			  }
			  else $mode = $this->fetchMode;
	$m = md5($sql.$this->databaseType.$mode);
	if (!$ADODB_CACHE->createdir) return $m;
	if (!$createdir) $dir = $ADODB_CACHE->getdirname($m);
				else $dir = $ADODB_CACHE->createdir($m, $this->debug);

	return $dir.'/adodb_'.$m.'.cache';
	}


  	 public function CacheExecute($secs2cache,$sql=false,$inputarr=false)
		{global $ADODB_CACHE;
		 if($this->debug) $start=microtime(1);
		 if (!$ADODB_CACHE) $this->_CreateCache();
		 $ESITO= parent::CacheExecute($secs2cache,$sql,$inputarr);
		 if($this->debug) echo "<B>DURATA: ".(microtime(1)-$start)."</B><BR>";
	  	 RETURN $ESITO;
		}

/***************************************************************************************************************/



		/**************         logging  *******************/

  	 public function Insert_ID(){
  	 if ($this->lastId===null) $this->lastId=$this->_insertid();
	 return $this->lastId;
	}

	 public function Affected_Rows(){
	 if($this->modificate===null) $this->modificate=$this->_affectedrows();
	 return $this->modificate;
	}


	 public function BeginTrans()
	{   $begin=parent::BeginTrans();
		if ($this->logger  && $this->track) $this->logger->BeginTrans();
		return $begin;
	}

	 public function CommitTrans()
	{   $commit=parent::CommitTrans();
		if ($commit && $this->logger  && $this->track) $this->logger->commit();
		return $commit;
	}

	 public function RollbackTrans()
	{   $rollback=parent::RollbackTrans();
		if ($this->logger  && $this->track) $this->logger->rollback();
		return $rollback;
	}

	 public function ErrorMsg(){
	    if($this->_errorMsg===null) $this->_errorMsg=parent::ErrorMsg();
	    return $this->_errorMsg;
	}
	
	 public function ErrorNo(){
	    if($this->_errorCode===null) $this->_errorCode=parent::ErrorNo();
	    return $this->_errorCode;
	}
	
	function &Execute($sql,$inputarr=false)
		{global $ADODB_FETCH_MODE;
		 $mode = $ADODB_FETCH_MODE;
		 $this->modificate=null;
		 if($this->retryExecute) $sql=$this->logger->logtag.$sql; //sta riprovando sempre la stessa execute quindi non la logga


		 if ($inputarr) $sql=trim(strtr($sql,array("\t"=>' ',"\n"=>' ',"\r"=>' ')) );
		 		   else $sql=trim((string) $sql);
		 $mysql=array($sql,$inputarr);


		 if ($this->logger  && $this->track && strpos($sql,$this->logger->logtag)===false && stripos($sql,'delete ')===0) $this->logger->log_delete($mysql);

		 $this->lastId=$this->modificate=$this->_errorCode=$this->_errorMsg=null;
		 $esito=&parent::Execute($sql,$inputarr);
		 
         if (!parent::ErrorMsg() && strpos($sql,$this->logger->logtag)===false)
		 					 {parent::ErrorNo();
		 					  if (stripos($sql,'insert' )===0)  $this->lastId=$this->_insertid();
		 					  if (stripos($sql,'select' )!==0) {$this->modificate=$this->_affectedrows();
		 					  							        if ($this->logger  && $this->track && $this->modificate>0)
		 																					{$attributi=get_object_vars ($this); //congela attributi
		 																					 if (stripos($sql,'update')===0) $this->logger->log_update($mysql);
		 					 																 if (stripos($sql,'insert')===0) $this->logger->log_insert($mysql);
		 					 																 foreach ($attributi as $n=>$v) $this->$n=$v; //ripristina attributi
		 					 																}
		 					  							       }
 							  $this->lastQry=$sql;
		 					 }
		 $ADODB_FETCH_MODE=$mode;

		 return $esito;
		}



}





class MySQL_rs_mysqltps extends ADORecordSet_mysqltps {

	 public function moveNext() {
		 if (is_callable('adodb_movenext')) adodb_movenext($this);
									   else parent::movenext();
	}

	function &GetArray($nRows = -1)
	{
		 if (is_callable('adodb_getall')) return  adodb_getall($this,$nRows);
									 else return parent::getArray($nRows);
	}



	/**
	 * Mia versione corretta per Zend optimizer plus
	 */
	function &GetAssoc($force_array = false, $first2cols = false)
	{
	    global $ADODB_EXTENSION;
		$cols = $this->_numOfFields;
		if ($cols < 2) return false;

		$numIndex = isset($this->fields[0]);
		$results = array();

		if (!$first2cols && ($cols > 2 || $force_array))
			{
		  	 if ($ADODB_EXTENSION) {
					while (!$this->EOF) {
					// Fix for array_slice re-numbering numeric associative keys
						$results[trim(array_shift($this->fields))] = $this->fields;
						adodb_movenext($this);
					}
				}
				else
					while (!$this->EOF) {
					// Fix for array_slice re-numbering numeric associative keys
						$results[trim(array_shift($this->fields))] = $this->fields;
						$this->MoveNext();
					}

			}
			else
			{
			 if ($ADODB_EXTENSION)
			 	{
				// return scalar values
				if ($numIndex)
					while (!$this->EOF) {
						// some bug in mssql PHP 4.02 -- doesn't handle references properly so we FORCE creating a new string
						$results[trim((string) $this->fields[0])] = $this->fields[1];
						adodb_movenext($this);
						}
				else
					while (!$this->EOF) {
						// some bug in mssql PHP 4.02 -- doesn't handle references properly so we FORCE creating a new string
						$results[trim(reset($this->fields))] = next($this->fields).'';
						adodb_movenext($this);
						}

			 	}
			 else{
			 	if ($numIndex)
					while (!$this->EOF) {
					// some bug in mssql PHP 4.02 -- doesn't handle references properly so we FORCE creating a new string
						$results[trim((string) $this->fields[0])] = $this->fields[1];
						$this->MoveNext();
					}
				 else
					while (!$this->EOF) {
					// some bug in mssql PHP 4.02 -- doesn't handle references properly so we FORCE creating a new string
						$results[trim(reset($this->fields))] = next($this->fields).'';
						$this->MoveNext();
					}
			    }
			}


		return $results;
	}

}


class MySQL_rs_ext_mysqlt extends MySQL_rs_mysqltps {}
class MySQL_rs_ext_mysqltps extends MySQL_rs_mysqltps {}
class MySQL_rs_mysqlt extends MySQL_rs_mysqltps {}




?>