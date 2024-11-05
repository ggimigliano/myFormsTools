<?
/*
MySQL code that supports transactions and prepared statements.
Code from Gianluca Gimigliano <gimigliano@tin.it>

Requires mysql client. Works on Windows and Unix.
*/

if (!defined('ADODB_DIR')) die();
include_once(ADODB_DIR."/drivers/adodb-mysql.inc.php");
include_once(ADODB_DIR."/drivers/adodb-mysqlt.inc.php");



class ADODB_mysqltps extends ADODB_mysqlt {


	function &_query($sql, $inputarr=false)
	  {
	    if (!$this->_bindInputArray || !is_array($inputarr)) return parent::_query($sql, $inputarr);   //standard usage
	  	     else  {$sql= str_replace(array("\r","\n","\t"),array('','',''), trim((string) $sql)); //cleaning sql
					$stmt_name="X".md5($sql); //Calculates the prepared statement name
					if (count($inputarr)>0)
	  	     						{//if $inputar is not empty sets the paramaters
	  	     						foreach ($inputarr as $k=>&$v) $vars["@A$k"]="@A$k:=".$this->quote($v);
	   		  						if ($vars) parent::_query("set ".implode(",",$vars),false); //
	  	     						}
	   				//try to use the statement immediatly
	   				$query_stmt="execute $stmt_name ".($vars?' using '.implode(',',array_keys($vars)):'');
  			 		$esito=&parent::_query($query_stmt,false);

			 		if ($this->errorNo()==1243)
			 						{
	   					            //if it doesn't exists a $stmt_name statement try to create         //if created it uses it
			 					    if (parent::_query("prepare $stmt_name from ".$this->quote($sql),false))  $esito=&parent::_query($query_stmt,false);
  								    				Else {//if some error occur it disables _bindInputArray and repeats the execute in normal mode
						    							  $this->_bindInputArray=false;
						    							  $this->retryExecute=true;
						    							  $esito=& parent::Execute($sql, $inputarr);
						    							  $this->retryExecute=false;
							  			 				  $this->_bindInputArray=true;
							  							  }
	   						      }

			return $esito;
	  		}
	  }


}



class ADORecordSet_mysqltps extends ADORecordSet_mysqlt {}

class ADORecordSet_ext_mysqltps extends ADORecordSet_mysqltps {}


?>