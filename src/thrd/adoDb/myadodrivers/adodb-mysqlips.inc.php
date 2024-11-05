<?
/*
 MySQLi code that supports prepared statements with binding of sql parameters and output
Code from Gianluca Gimigliano <gimigliano@tin.it>

Requires mysql client. Works on Windows and Unix.
*/


if (!defined('ADODB_DIR')) die();
include_once(ADODB_DIR."/drivers/adodb-mysqli.inc.php");


class adodb_mysqlips extends adodb_mysqli {
	public $databaseType = 'mysqlips';
	
	public $dontFetchFirst=false;
	public $fields=null;
	public $metaColumnsSQL = "SHOW COLUMNS FROM %s";
	public         $preserve_zerofill=false;
	protected  static  $riformattazioni=array();
	protected $use_mysqli_fetch_all=true; //per ora se un campo è zerofill lo restituisce senza zeri meglio disattivarla
	public     $_bindInputArray=true;
	protected  $_bindInputArrayCur=array(),$_bindOutputArrayCur=array();
	protected  $statements=array(),$_bindInputArrayOrig=array(),$_bindOutputArrayOrig=array();
	
	 public static function get_formattazione($shortSql){
	    return self::$riformattazioni[$shortSql];
	}
	
	 public function set_binding(&$sql,$io='I',$status=true,$coprire=true) {
	    $sha1=sha1($sql);
	    
	    if($io=='I') {
	           if(!isset($this->_bindInputArrayCur[$sha1]) || $coprire) $this->_bindInputArrayCur[$sha1]=$status;
	           }
	    else { if(!isset($this->_bindOutputArrayCur[$sha1]) || $coprire) $this->_bindOutputArrayCur[$sha1]=$status;
	        }
	    return $this;
	}
	
	 public function restore_binding(&$sql,$io='') {
	    //echo "Rest '$sql'<br>";
	    $sha1=sha1($sql);
	    if($io=='I' || !$io) {
	        if(isset($this->_bindInputArrayCur[$sha1]))  unset($this->_bindInputArrayCur[$sha1]);
	        }
	    if($io=='O' || !$io) {
	        if(isset($this->_bindOutputArrayCur[$sha1])) unset($this->_bindOutputArrayCur[$sha1]);
	                              
	       }
	    return $this;
	} 
	
	
	 public static function explodeExternal($x,$str,$boundary=true,$ignoraParentesi=false) {
        if(!is_array($x)) $y=$x;
                    else {$x=$x[0];
                          $y=$x[1];
                          }
        if($boundary) $occorrenze=preg_split('~\b'.trim((string) $x).'\b~iSU',$str);
                 else $occorrenze=preg_split("~$x~iSU",$str);
        if(count($occorrenze)<=1) return $occorrenze;
        $I=0;
        $salta=0; $intesto=false;
    
    
        for($i=0;$i<count($occorrenze)-1;$i++) {
            $occorrenze[$i]=trim((string) $occorrenze[$i]);
            for($j=0;$j<strlen($occorrenze[$i]);$j++)
            {
                if($occorrenze[$i]{$j}=="'" &&
                (
                    ($j==0 && ($i==0 || $occorrenze[$i-1]{strlen($occorrenze[$i-1])-1}!='\\'))
                    ||
                    ($j>0 &&  $occorrenze[$i]{$j-1}!='\\')
                    )
                )  $intesto=!$intesto;
                if(!$intesto && !$ignoraParentesi && $occorrenze[$i]{$j}=='(')  $salta++;
                if(!$intesto && !$ignoraParentesi && $occorrenze[$i]{$j}==')')  $salta--;
    
            }
    
            /*
             if($salta==0) for($j=0;$j<strlen($occorrenze[$i+1])-1;$j++)
             {if($occorrenze[$i]{$j}=="'" &&
             $occorrenze[$i]{$j-1}!='\\') {$intesto=!$intesto;}
             if(!$intesto && $occorrenze[$i+1]{$j}==')') {$salta--;break;}
             if(!$intesto && $occorrenze[$i+1]{$j}=='(') {$salta++;break;}
             }
             */
    
            $trovati[$I][]=$occorrenze[$i];
            if($salta==0 && !$intesto) $I++;
            //	 echo "I======>$I perche salta=$salta \n";
        }
        $trovati[$I][]=$occorrenze[$i];
        //echo '<pre>';echo "<b>$x</B>";;print_r($trovati);;
        $x=preg_replace('/\\\([^\\\]{1})/','\1',$y);
        foreach ($trovati as &$trovato) $trovato=implode(($boundary?" $y ":$y),$trovato);
        //echo '<pre>TROVATO';print_r($trovati);
        return $trovati;
    }
 	

	 public function ErrorMsg()
	  {
	  	if (is_array($this->_queryID))   return $this->_errorMsg=$this->_queryID['stmt']->error;
	    return parent::ErrorMsg();
	  }


	/*	Returns: the last error number from previous database operation	*/
	 public function ErrorNo()
	  {
	  	if (is_array($this->_queryID)) return $this->_queryID['stmt']->errno;
	    return parent::ErrorNo();
	  }

/**

	function &query_named_stmt($sql, $inputarr=false)
	  {
	  		$sql= str_replace(array("\r","\n","\t"),array('','',''), trim((string) $sql)); //cleaning sql
	  		$stmt_name="X".md5($sql); //Calculates the prepared statement name
			if (count($inputarr)>0)
				  	{//if $inputar is not empty sets the paramaters
				  		foreach ($inputarr as $k=>&$v) $vars["@A$k"]="@A$k:=".$this->quote($v);
				  		if ($vars) $this->query("set ".implode(",",$vars),false); //
				  	}
		  	//try to use the statement immediatly

		  	$query_stmt="execute $stmt_name ".($vars?' using '.implode(',',array_keys($vars)):'');
		  	$esito=$this->_query($query_stmt,false);

		  	if ($this->errorNo()==1243)
		  						{
							  	 //if it doesn't exists a $stmt_name statement try to create         //if created it uses it
							  	 if ($this->_query("prepare $stmt_name from ".$this->quote($sql),false))
							  	 		$esito=$this->_query($query_stmt,false);
							  	   Else {//if some error occur it disables _bindInputArrayCur and repeats the execute in normal mode
							  			$this->retryExecute=true;
							  			$esito=$this->_query($sql, $inputarr);
							  			$this->retryExecute=false;

							  		}
							  	}

		  	return $esito;
	  }

*/
	
	function &Execute($sql,$inputarr=false){
	            
	           if($sql[0]==' ' || $sql[0]=="\r" || $sql[0]=="\n"|| $sql[0]=="\t") $sql=trim((string) $sql);
	           if(stripos($sql,'select')===0) $this->set_binding($sql,'O',false,false);
	           try{
	               $out=parent::Execute($sql,$inputarr);
	   		      } catch (Exception $e)  {
                    	                   $parti=self::explodeExternal('\?',$sql,false);
                                           foreach ($parti as $i=>&$parte) {
                                                $parte=str_replace('\?','?',$parte);
                                                if($i<count($parti)-1) $parte.=$this->quote($inputarr[$i]);
                                                }
                                           $out=ADOConnection::Execute(implode('',$parti),false);
                    	                  }
	           $this->restore_binding($sql);
	           return $out;
	}
							  	 

    function &_query($sql, $inputarr='')
	{
     global $ADODB_COUNTRECS;
     static $max_allowed_packet;
     $out=false;
     if(!$this->_connectionID) return $out;
	 $sha1=sha1($sql);
	 $is_select=stripos($sql,'select')===0 ;
	 $is_show=stripos($sql,'show')===0 ;
     if ($this->_queryID!=false) 
     			{//cleans partial results
         		 if (is_array($this->_queryID))   $this->_queryID['stmt']->store_result();
	 										 else $this->_connectionID->store_result();
				 }
   //  echo $this->_bindOutputArrayCur[$sha1]."<=Bind Out<br>";
   // di fatto mai $this->_bindOutputArrayCur[$sha1] perchè non garantisce prestazioni migliori anzi, in alcuni casi peggiora
     if(!$inputarr && !$this->_bindOutputArrayCur[$sha1]) return parent::_query($sql,$inputarr);
          {if(!$max_allowed_packet) {$max_allowed_packet=$this->_connectionID->query("show variables like 'max_allowed_packet'")->fetch_object();
                                     $max_allowed_packet=$max_allowed_packet->Value-8; //Per sicurezza tolgo 8 bytes (nota stackoverflow)
                                    }
           $a='';
	       if(!$inputarr) $inputarr=array();
	       $inputarr=array_values($inputarr);
	       foreach($inputarr as $k => &$v)
									 {if (strlen($v)>$max_allowed_packet) $a .= 'b';
	 	                                         elseif (is_integer($v)) $a .= 'i';
									 				  else $a .= 's';
									 }
		   $shortSql=(is_object($this->_connectionID)?$this->_connectionID->thread_id:$this->_connectionID).sha1($sql).$this->fetchMode.$a.$this->_bindOutputArrayCur[$sha1];
	       if (!isset($this->statements[$shortSql]) || !$this->statements[$shortSql]['stmt']->reset() )
						{//echo "Preparing..$sql<br>";
	           		     $this->statements[$shortSql]['stmt']=$this->_connectionID->stmt_init();
	           		     $this->statements[$shortSql]['KEY']=$shortSql;
						 $prepared=$this->statements[$shortSql]['stmt']->prepare($sql);
						 if(!$prepared) {unset($this->statements[$shortSql]);
						                 throw new Exception('NoPrepare:'.$sql.$this->statements[$shortSql]['stmt']->error."\n\n");
						                 }
						 if(count($inputarr)>0) 
						                 {
                						 $this->statements[$shortSql]['param']=array_fill(0,count($inputarr),null);
                						 $bindOk=false;
                						 $binding="\$bindOk=\$this->statements[\$shortSql]['stmt']->bind_param(\$a";
                						 foreach ($this->statements[$shortSql]['param'] as $i=>$null) $binding.=",\$this->statements[\$shortSql]['param'][$i]";
                						 $binding.=');';
                						 eval($binding);
                						 if(!$bindOk || $this->statements[$shortSql]->error) 
                						                         {unset($this->statements[$shortSql]);
                						                          throw new Exception('NoBinding:'.$this->statements[$shortSql]->error);
                						                         }
                						 }
  						 if (!$is_show && !$is_select) $is_select=$this->statements[$shortSql]['stmt']->field_count>0;
						 if ($is_select && ($this->preserve_zerofill || $this->_bindOutputArrayCur[$sha1])) $this->MetaForBinding($shortSql,$sql);
					    }
		   
		    if(isset($this->statements[$shortSql]['param'])) 
		                      {foreach ($this->statements[$shortSql]['param'] as &$k) $k=null;
			                   for ($i=0;$i<count($inputarr);$i++) if($a[$i]!='b')  $this->statements[$shortSql]['param'][$i]=$inputarr[$i] ;
			                                             else    foreach(str_split($inputarr[$i],$max_allowed_packet) as $part) $this->statements[$shortSql]['stmt']->send_long_data($i,$part);
		                      }                           
			                                                     
			if (!@$this->statements[$shortSql]['stmt']->execute())
													     {if($this->statements[$shortSql]['stmt']->errno==1243){
                        									 				      unset($this->statements[$shortSql]);
                        									 				      return $this->_query($sql, $inputarr);
                        									 				      }
									 				      
    									 				  $this->_errorMsg=$this->statements[$shortSql]['stmt']->error;
    									 				  $this->_errorNum=$this->statements[$shortSql]['stmt']->errno;
    									 				  if ($this->debug) ADOConnection::outp("Query: " . $sql . " failed. " . $this->_errorMsg);
    									 				  return $out;
    													 }
    	    if(isset($this->statements[$shortSql]['param'])) foreach ($this->statements[$shortSql]['param'] as &$k) $k=null;
    	    if (!$is_show && !$is_select) $is_select=$this->statements[$shortSql]['stmt']->field_count>0;
    	    if ($is_select &&  ($this->preserve_zerofill || $this->_bindOutputArrayCur[$sha1]) ) 
    	                                                 {$binding="\$this->statements[\$shortSql]['stmt']->bind_result(";
			                                              foreach ($this->statements[$shortSql]['data'] as $i=>&$f) $binding.="\$this->statements[\$shortSql]['data'][{$this->quote($i)}],";
			                                              $binding[strlen($binding)-1]=')';
			                                              eval($binding.';');
			                                              $this->statements[$shortSql]['stmt']->store_result();
			                                              return  $this->statements[$shortSql];
														}
						  				elseif ($is_select || $is_show){
						  				                
						  						   		 if($ADODB_COUNTRECS) $this->_connectionID->store_result();
						  						   		                 else $this->_connectionID->use_result();
						  						   		 $out= $this->statements[$shortSql]['stmt']->get_result();
						  						   		 return $out;
						  						  		}
												else    return  $this->statements[$shortSql];
												        
												      
		}
		return $out;
	}

/*
	function &Execute($sql,$inputarr=false)
	{   if(is_array($inputarr) && count($inputarr)==0)
		return parent::Execute($sql,$inputarr);
	}
*/

	 function &MetaForBinding (&$shortSql,&$sql) {
	    $stmt=&$this->statements[$shortSql];
	 	$mode=$this->fetchMode;
	 	if(!$mode) {global $ADODB_FETCH_MODE;$mode=$ADODB_FETCH_MODE;}
	 	if(!$mode) $mode=ADODB_FETCH_BOTH;
	 	
        $metadatastmt = @$stmt['stmt']->result_metadata();
        if(!$metadatastmt) {$this->set_binding($sql,'O',false);return;}
        
        $fields = array();
        $out = array();

        $fields[0] = &$stmt['stmt'];
        $count = 1;
		$FieldsInfo=array();
		$riformatta=array();
		$j=0;
		
        while($field = @$metadatastmt->fetch_field()) {
        	 $FieldsInfo[]=$field;

			 $Reformat=null;
        	 if($field->flags & MYSQLI_ZEROFILL_FLAG) {//$this->set_binding($sql,'O',true);
        	 										   //$this->dontFetchFirst=false;
        	 										   $Reformat=function($v)use($field){return ($v!==null?sprintf("%0{$field->length}s",$v):$v);};
        	 										   $toReformat=true;
        	 										  }

        	 if ($mode==ADODB_FETCH_BOTH || $mode==ADODB_FETCH_ASSOC) $riformatta[$field->name]=$Reformat;
        	 if ($mode==ADODB_FETCH_BOTH || $mode==ADODB_FETCH_NUM)   $riformatta[$j]=$Reformat;
        	 if ($mode==ADODB_FETCH_BOTH || $mode==ADODB_FETCH_ASSOC) $fields[$count++] =  &$out[$field->name];
         	 if ($mode==ADODB_FETCH_BOTH )  $out[$j++]        =  &$out[$field->name];
         	 if ($mode==ADODB_FETCH_NUM)    $fields[$count++] =  &$out[$j++];
            }
        @$metadatastmt->free_result();
        $stmt['fields']=$fields;
        $stmt['fieldsinfo']=&$FieldsInfo;
        $stmt['data']=&$out;
        if($toReformat) self::$riformattazioni[$stmt['KEY']]=&$riformatta;
    }


	 public function _affectedrows()
	{
		 if (is_array($this->_queryID))   return  $this->_queryID['stmt']->affected_rows;
							 	     else return  $this->_connectionID->affected_rows;

	}


	 public function _insertid()
	{
	  if (is_array($this->_queryID))   return  $this->_queryID['stmt']->insert_id;
							      else return  $this->_connectionID->insert_id;
	}


	 public function __destruct() {
		if ($this->statements)
		      foreach ($this->statements as &$v) 
					if (is_object($v['stmt']))    @$v['stmt']->close();
		parent::Close();			
	}


	/**
	 *
	 * @param sql			SQL statement
	 * @param [inputarr]		input bind array
	 */
	function &GetArray($sql,$inputarr=false)
		{ global $ADODB_COUNTRECS;
		  $adodb_countrecs=$ADODB_COUNTRECS;
		  if(is_array($inputarr) && count($inputarr)>0) $this->set_binding($sql,'I');
		  if (!$this->preserve_zerofill && $this->use_mysqli_fetch_all && is_callable('mysqli_fetch_all') )
		  									   {//dontFetchFirst=true incompatibile se produce arrai ossia se _bindOutputArrayCur=true
		  										$this->set_binding($sql,'O',false);  //non fare binding
		  										$this->dontFetchFirst=true;   //salta il primo record ossia usa fetch_all
		  										}
		  									else{
		  										$this->set_binding($sql,'O',true); //fare binding
		  										$this->dontFetchFirst=false; //non usare fetch_all
		  										}

    //	global $tt;$t=microtime(1);
		  $ADODB_COUNTRECS=1;
		  $rs = $this->Execute($sql,$inputarr);
   	      if (!$rs){ $ADODB_COUNTRECS=$adodb_countrecs;
   	      			  $this->dontFetchFirst=false;
   	      			  $this->restore_binding($sql);
   	      			  $false=false;
   	      			  if (defined('ADODB_PEAR')) return ADODB_PEAR_Error();
		  										 return $false;
		  			}
		 $arr = $rs->GetArray();
	//	 echo $t= microtime(1)-$t,'<br>';$tt+=$t; echo'<br>';
	     $ADODB_COUNTRECS=$adodb_countrecs;
		 $this->dontFetchFirst=false;
		 $this->restore_binding($sql);
		return $arr;
	}

	
	function &GetAll($sql,$inputarr=false){
	    return $this->GetArray($sql,$inputarr);
	}

	 public function GetAssoc($sql, $inputarr=false,$force_array = false, $first2cols = false)
		{	$results=array();
			$v=$this->getArray($sql, $inputarr);
			if(!$v)  return $results;
			if (!$first2cols && (count($v[0]) > 2 || $force_array))
					foreach ($v as &$fields) $results[trim(array_shift($fields))] = &$fields;
			   else foreach ($v as &$fields) $results[trim(array_shift($fields))] = trim(array_shift($fields));
			return $results;
		}
	
	
	 public function GetRow($sql,$inputarr=false)
    	{
	     if($sql[0]==' ' || $sql[0]=="\r" || $sql[0]=="\n"|| $sql[0]=="\t") $sql=trim((string) $sql);
    	 if(stripos($sql,"select")===0 && !preg_match('/ limit\s+\d+$/isSU',$sql)) $sql.=' limit 1';
    	 return parent::getrow($sql,$inputarr);
    	}


	 public function CacheGetRow($secs2cache,$sql=false,$inputarr=false)
    	{
    	 if($sql[0]==' ' || $sql[0]=="\r" || $sql[0]=="\n"|| $sql[0]=="\t") $sql=trim((string) $sql);
    	 if(stripos($sql,"select")===0 && !preg_match('/ limit\s+\d+$/isSU',$sql)) $sql.=' limit 1';
    	 return parent::CacheGetRow($secs2cache,$sql,$inputarr);
    	}

	 public function GetOne($sql=false,$inputarr=false)
	   {
		if($sql[0]==' ' || $sql[0]=="\r" || $sql[0]=="\n"|| $sql[0]=="\t") $sql=trim((string) $sql);
		if(stripos($sql,"select")===0 && !preg_match('/ limit\s+\d+$/isSU',$sql)) $sql.=' limit 1';
		return parent::getOne($sql,$inputarr);
	   }
	    

	 public function CacheGetOne($secs2cache,$sql=false,$inputarr=false)
	   {
		if($sql[0]==' ' || $sql[0]=="\r" || $sql[0]=="\n"|| $sql[0]=="\t") $sql=trim((string) $sql);
		if(stripos($sql,"select")===0 && !preg_match('/ limit\s+\d+$/isSU',$sql)) $sql.=' limit 1';
		return  parent::CacheGetOne($secs2cache,$sql,$inputarr);
	   }
	   	

}



class ADORecordSet_mysqlips extends ADORecordSet_mysqli {
var $databaseType='mysqlips';
protected $changecase;
    

	 public function _initrs()
	{global $ADODB_COUNTRECS;
	  
	 if(defined('ADODB_ASSOC_CASE') && ADODB_ASSOC_CASE==ADODB_ASSOC_CASE_LOWER) $this->changecase=function&(&$a){$a=strtolower($a);};
	 if(defined('ADODB_ASSOC_CASE') && ADODB_ASSOC_CASE==ADODB_ASSOC_CASE_UPPER) $this->changecase=function&(&$a){$a=strtoupper($a);};
	 
	 if (!is_array($this->_queryID)) parent::_initrs();
                        	   else {
                            	   	$this->Statement=new stdClass();
                            	    $this->Statement->stmt=$this->_queryID['stmt'];
                            	    $this->Statement->stmt_return_data=&$this->_queryID['data'];
                            
                            	   	$this->FieldsInfo=&$this->_queryID['fieldsinfo'];
                            	   	$this->_numOfRows  = isset($this->_queryID['stmt']->num_rows)? $this->_queryID['stmt']->num_rows : -1;
                            	  	$this->_numOfFields=$this->_queryID['stmt']->field_count;
                            	  	$this->macrofunction='';
                            	   }
	}


	 public function GetArray($nRows =-1){
	   	if(!$this->connection->dontFetchFirst) return parent::GetArray($nRows);
		if($nRows==-1)   $out=$this->_queryID->fetch_all($this->fetchMode );
					else $out=array_slice($this->_queryID->fetch_all($this->fetchMode ),0,$nRows);
					
	   if(defined('ADODB_ASSOC_CASE') && ADODB_ASSOC_CASE!==ADODB_ASSOC_CASE_NATIVE && $this->fetchMode!=MYSQLI_NUM)
		      foreach ($out as &$row) $row=array_change_key_case($row,ADODB_ASSOC_CASE==ADODB_ASSOC_CASE_LOWER? CASE_LOWER: CASE_UPPER);
		return $out;      
	}



	 public function GetArrayLimit($nRows =-1,$offset=-1){
	    if ($offset==-1) return $this->GetArray($nRows);
		          elseif(!$this->connection->dontFetchFirst ) return parent::GetArrayLimit($nRows,$offset);
	    if($nRows==-1) $out= array_slice($this->_queryID->fetch_all($this->fetchMode ),$offset);
			  	 else  $out= array_slice($this->_queryID->fetch_all( $this->fetchMode ),$offset,$nRows);
			  	
	   if(defined('ADODB_ASSOC_CASE') && ADODB_ASSOC_CASE!==ADODB_ASSOC_CASE_NATIVE  && $this->fetchMode!=MYSQLI_NUM)
		      foreach ($out as &$row)   $row=array_change_key_case($row,ADODB_ASSOC_CASE==ADODB_ASSOC_CASE_LOWER? CASE_LOWER: CASE_UPPER);
		return $out;
	}
	

	 public function GetAssoc($force_array = false, $first2cols = false)
		{
			$results=array();
			$v= $this->getArray();
			if(!$v) return array();
			if (!$first2cols && (count($v[0]) > 2 || $force_array))
					 	 foreach ($v as &$fields) $results[trim(array_shift($fields))] = &$fields;
					else foreach ($v as &$fields) $results[trim(array_shift($fields))] = trim(array_shift($fields));

			return $results;
		}


     public function FetchField($fieldOffset = -1)
	{
		$fieldnr = $fieldOffset;
		if ($fieldOffset != -1) {
		  if (is_array($this->_queryID)) $o= $this->FieldsInfo[$fieldnr];
		  					  elseif(!$this->_queryID) return null;
		  						  else  {$fieldOffset = @$this->_queryID->field_seek($fieldnr);
		  							     $o = @$this->_queryID->fetch_field();
		  							     if(!$o) return null;
		  								}
		}

		/* Properties of an ADOFieldObject as set by MetaColumns */
		$o->primary_key = $o->flags & MYSQLI_PRI_KEY_FLAG;
		$o->not_null = $o->flags & MYSQLI_NOT_NULL_FLAG;
		$o->auto_increment = $o->flags & MYSQLI_AUTO_INCREMENT_FLAG;
		$o->binary = $o->flags & MYSQLI_BINARY_FLAG;
		// $o->blob = $o->flags & MYSQLI_BLOB_FLAG; /* not returned by MetaColumns */
		$o->unsigned = $o->flags & MYSQLI_UNSIGNED_FLAG;
		return $o;
	}


	 public function _insertid()
		{if (!is_array($this->_queryID)) return parent::_insertid();
									else return $this->_queryID['stmt']->insert_id;
		}


	 public function _affectedrows()
		{if (!is_array($this->_queryID)) return parent::_affectedrows();
									else return $this->_queryID['stmt']->affected_rows;
		}


	 public function _seek($row)
		{
		if ($this->_numOfRows == 0) return false;
		if ($row < 0)	return false;
		if (!is_array($this->_queryID)) $this->_queryID->data_seek($row);
								else 	$this->_queryID['stmt']->data_seek($row);
		$this->EOF = false;
		return true;
		}


	 public function MoveNext()
		{
		if ($this->EOF) return false;
		$this->_currentRow++;
		if ($this->_fetch()) return true;
		$this->EOF = true;
		return false;
	}


    public static function _fetchval_formatted(&$a,$format){
			if($format!='') $a=$format($a);
			return $a;
		}

     public static function _fetchval($a){
        	return $a;
		}

	 public function _fetch()
	 {	
	 	$this->fields=null;
	 	if($this->connection->dontFetchFirst)  return true;
	 	if (!is_array($this->_queryID)) $this->fields= $this->_queryID->fetch_array($this->fetchMode);
		                           else{
										if (!@$this->_queryID['stmt']->fetch()) return false;
										if (is_array(($this->_queryID['data'])))
													{   
													 if(!adodb_mysqlips::get_formattazione($this->_queryID['KEY'])) $this->fields=array_map('ADORecordSet_mysqlips::_fetchval',$this->_queryID['data']);
																  			       else $this->fields=array_combine(array_keys($this->_queryID['data']),
                        														    								array_map('ADORecordSet_mysqlips::_fetchval_formatted',$this->_queryID['data'],adodb_mysqlips::get_formattazione($this->_queryID['KEY']))
                        														    								);
													}
									   }
								   
        if(defined('ADODB_ASSOC_CASE') && is_array($this->fields) && ADODB_ASSOC_CASE!==ADODB_ASSOC_CASE_NATIVE  && $this->fetchMode!=MYSQLI_NUM)
                        $this->fields=array_change_key_case($this->fields,ADODB_ASSOC_CASE==ADODB_ASSOC_CASE_LOWER? CASE_LOWER: CASE_UPPER);
	 	return is_array($this->fields);
	 }



	 public function _close(){
	    if ($this->_queryID!==false) {
			 if (is_array($this->_queryID))  
			                         {
			                          if ($this->_queryID['stmt'] instanceof mysqli_stmt) @$this->_queryID['stmt']->free_result();
									 }
								 else{
								       if($this->_queryID instanceof  mysqli_result)      @$this->_queryID->free_result();
								     }
		}
	  	$this->_queryID = false;
	  	if(is_object($this->connection) && 
	  	   is_object($this->connection->_queryID)) $this->connection->_queryID = false;
	}


	 public function __destruct() {$this->_close();}

}


class ADORecordSet_ext_mysqlips extends ADORecordSet_mysqlips {}



?>