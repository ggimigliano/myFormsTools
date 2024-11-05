<?
class MySQLi_rs_ext_empty extends ADORecordSet_empty  {}
class MySQLi_rs_empty extends ADORecordSet_empty  {}




class MySQLi_ADORecordSet_array extends ADORecordSet_array {
   

     public function MetaType($t, $len = -1, $fieldobj = false)
		{$typo=parent:: MetaType($t,$len); //corregge errore in assegnazione tipo data
		if (is_object($t)) {
			$fieldobj = $t;
			$t = $fieldobj->type;
			$len = $fieldobj->max_length;
			}
		if (strtoupper($t)=='DATE') return 'T';
							 else return $typo;
		}


	 public function GetAssoc($force_array = false, $first2cols = false)
		{
			$results=array();
			$v= $this->getArray();
		    if(!$v) {$v=array();return $v;}
			if (!$first2cols && (count($v[0]) > 2 || $force_array))
					 	 foreach ($v as &$fields) $results[trim(array_shift($fields))] = &$fields;
					else foreach ($v as &$fields) $results[trim(array_shift($fields))] = trim(array_shift($fields));

			return $results;
		}
			
}






class MySQLi_mysqlips extends ADODB_mysqlips {
	public $rsPrefix = 'MySQLi_rs_';
	public $lastQry;
	public $arrayClass='MySQLi_ADORecordSet_array';
	public $track=true;
	public $lastId;
	public $selects=array();
	public $logger=null;
	public $lastLogger;
	public $_blackhole=false;
	public $retryExecute;
    protected $predTrack;
    
     public function database(){
        return $this->database;
    }
     public function databaseType(){
        return $this->databaseType;
    }
	
	 public function pause_track(){
	    $this->predTrack= $this->track;
	    $this->track=false;
	}

	
	 public function restore_track(){
	    $this->track=$this->predTrack;
	    
	}
	

	/*************************** mie utilities  *****************************************************************/

	 public function set_logger($logger) {
		$this->logger=$logger;
	}

	 public function unset_logger() {
	    $this->lastLogger=$this->logger;
		$this->logger=null;
	}
	
	 public function restore_logger(){
	    $this->logger=$this->lastLogger;
	    $this->lastLogger=null;
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


	

	 public function Myserializzarray($array) {
		if (is_array($array)) foreach ($array as $i=>$v) $ser.="$i => ".stripslashes(serialize($v))."\n";
		return $ser;
	}


	/********************************************************************************/







	/**************         correzioni al funzionamento *******************/


    public function MetaForeignKeys($table, $owner = false, $upper = false, $associative = false)
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
				 $campi=preg_split('@\\)|\\(@',str_replace('`',"'",str_replace('REFERENCES ','',$str)));
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
  	 
  	 
  	 
   public function MetaColumns($table, $normalize=true)
	{
		$false = false;
		if (!$this->metaColumnsSQL) return $false;
	
		global $ADODB_FETCH_MODE;
		$save = $ADODB_FETCH_MODE;
		$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
		if ($this->fetchMode !== false)
			$savem = $this->SetFetchMode(false);
		$rs = $this->Execute(sprintf($this->metaColumnsSQL,$table));
		if (isset($savem)) $this->SetFetchMode($savem);
		$ADODB_FETCH_MODE = $save;
		if (!is_object($rs))
			return $false;
	
		$retarr = array();
		while (!$rs->EOF) {
			$fld = new ADOFieldObject();
			$fld->name = $rs->fields[0];
			$type = $rs->fields[1];
	
			// split type into type(length):
			$fld->scale = null;
			if (preg_match("/^(.+)\((\d+),(\d+)/", $type, $query_array)) {
				$fld->type = $query_array[1];
				$fld->max_length = is_numeric($query_array[2]) ? $query_array[2] : -1;
				$fld->scale = is_numeric($query_array[3]) ? $query_array[3] : -1;
			} elseif (preg_match("/^([a-z]+)\((\d+)/i", $type, $query_array)) { //RETTIFICATA PERCHEì RICADEVA IN enum('45(6)')
				$fld->type = $query_array[1];
				$fld->max_length = is_numeric($query_array[2]) ? $query_array[2] : -1;
			} elseif (preg_match("/^(enum)\((.*)\)$/i", $type, $query_array)) {
				$fld->type = $query_array[1];
				$arr = explode(",",$query_array[2]);
				$fld->enums = $arr;
				$zlen = max(array_map("strlen",$arr)) - 2; // PHP >= 4.0.6
				$fld->max_length = ($zlen > 0) ? $zlen : 1;
			} else {
				$fld->type = $type;
				$fld->max_length = -1;
			}
			$fld->not_null = ($rs->fields[2] != 'YES');
			$fld->primary_key = ($rs->fields[3] == 'PRI');
			$fld->auto_increment = (strpos($rs->fields[5], 'auto_increment') !== false);
			$fld->binary = (strpos($type,'blob') !== false);
			$fld->unsigned = (strpos($type,'unsigned') !== false);
			$fld->zerofill = (strpos($type,'zerofill') !== false);
	
			if (!$fld->binary) {
				$d = $rs->fields[4];
				if ($d != '' && $d != 'NULL') {
					$fld->has_default = true;
					$fld->default_value = $d;
				} else {
					$fld->has_default = false;
				}
			}
	
			if ($save == ADODB_FETCH_NUM) {
				$retarr[] = $fld;
			} else {
				$retarr[strtoupper($fld->name)] = $fld;
			}
			$rs->MoveNext();
		}
	
		$rs->Close();
		return $retarr;
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


  	function &CacheExecute($secs2cache,$sql=false,$inputarr=false)
		{global $ADODB_CACHE;
		 if($this->debug) $start=microtime(1);
		 if (!$ADODB_CACHE) $this->_CreateCache();
		 $ESITO= parent::CacheExecute($secs2cache,$sql,$inputarr);
		 if($this->debug) echo "<B>DURATA: ".(microtime(1)-$start)."</B><BR>";
	  	 RETURN $ESITO;
		}

/***************************************************************************************************************/



		/**************         logging  *******************/

  	 public function Insert_ID($table = '', $column = ''){
  	 if ($this->lastId===null) $this->lastId=$this->_insertid();
	 return $this->lastId;
	}

	 public function Affected_Rows(){
	 if($this->modificate===null) $this->modificate=$this->_affectedrows();
	 return $this->modificate;
	}


	 public function BeginTrans()
	{   if(!$this->_connectionID) return false;
	    if ($this->logger  && $this->track) $this->logger->BeginTrans();
		$begin=parent::BeginTrans();
		return $begin;
	}

	 public function CommitTrans($ok=true)
	{   $commit=parent::CommitTrans($ok);
	    if($this->logger  && $this->track) {
	           if (!$this->HasFailedTrans() && $commit && $ok)  $this->logger->commit();
	                                                        else $this->logger->rollback();
	           }
		return $commit;
	}

	 public function RollbackTrans()
	{   if(!$this->_connectionID) return false;
	    $rollback=parent::RollbackTrans();
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
		 if($this->debug) $start=microtime(1);
		 if($this->_blackhole) { $this->lastQry=$sql;
		                         $var=true;
		                         return $var;
		                          }
		 
		 if(!is_array($inputarr) && trim((string) $inputarr)==='') $inputarr=false;
		 $this->modificate=null;
		 if($this->retryExecute) $sql=$this->logger->logtag.$sql; //sta riprovando sempre la stessa execute quindi non la logga


		 if ($inputarr) $sql=trim(strtr($sql,array("\t"=>' ',"\n"=>' ',"\r"=>' ')) );
		 		   else $sql=trim((string) $sql);
		 $mysql=array($sql,$inputarr);


		 if ($this->logger  && $this->track && strpos($sql,$this->logger->logtag)===false && stripos($sql,'delete ')===0) $this->logger->log_delete($mysql);

		 $this->lastId=$this->modificate=$this->_errorCode=$this->_errorMsg=null;
		 $esito=parent::Execute($sql,$inputarr);
		 
		 if (!parent::ErrorMsg() && $this->logger && strpos($sql,$this->logger->logtag)===false)
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
		 if($this->debug) echo "<B>DURATA: ".(microtime(1)-$start)."</B><BR>";
		 return $esito;
		}




}





class MySQLi_rs_mysqlips extends ADORecordSet_mysqlips {}
class MySQLi_rs_ext_mysqli extends MySQLi_rs_mysqlips {}
class MySQLi_rs_ext_mysqlips extends MySQLi_rs_mysqlips {}





?>