<?php
/*
@version   v5.20.9  21-Dec-2016
@copyright (c) 2000-2013 John Lim (jlim#natsoft.com). All rights reserved.
@copyright (c) 2014      Damien Regad, Mark Newnham and the ADOdb community
  Released under both BSD license and Lesser GPL library license.
  Whenever there is any discrepancy between the two licenses,
  the BSD license will take precedence.
Set tabs to 4 for best viewing.

  Latest version is available at http://adodb.sourceforge.net

  Oracle support via ODBC. Requires ODBC. Works on Windows.
*/
// security - hide paths
if (!defined('ADODB_DIR')) die();

if (!defined('_ADODB_ODBC_LAYER')) {
	include(ADODB_DIR."/drivers/adodb-odbc.inc.php");
}


class  ADODB_odbc_oracle extends ADODB_odbc {
	public $databaseType = 'odbc_oracle';
 	public $replaceQuote = "''"; // string to use to replace quotes
	public $concat_operator='||';
	public $fmtDate = "'Y-m-d 00:00:00'";
	public $fmtTimeStamp = "'Y-m-d h:i:sA'";
	public $metaTablesSQL = 'select table_name from cat';
	public $metaColumnsSQL = "select cname,coltype,width from col where tname='%s' order by colno";
	public $sysDate = "TRUNC(SYSDATE)";
	public $sysTimeStamp = 'SYSDATE';

	//var $_bindInputArray = false;

	 public function MetaTables($ttype = false, $showSchema = false, $mask = false)
	{
		$false = false;
		$rs = $this->Execute($this->metaTablesSQL);
		if ($rs === false) return $false;
		$arr = $rs->GetArray();
		$arr2 = array();
		for ($i=0; $i < sizeof($arr); $i++) {
			$arr2[] = $arr[$i][0];
		}
		$rs->Close();
		return $arr2;
	}

	 public function MetaColumns($table, $normalize=true)
	{
	global $ADODB_FETCH_MODE;

		$rs = $this->Execute(sprintf($this->metaColumnsSQL,strtoupper($table)));
		if ($rs === false) {
			$false = false;
			return $false;
		}
		$retarr = array();
		while (!$rs->EOF) { //print_r($rs->fields);
			$fld = new ADOFieldObject();
			$fld->name = $rs->fields[0];
			$fld->type = $rs->fields[1];
			$fld->max_length = $rs->fields[2];


			if ($ADODB_FETCH_MODE == ADODB_FETCH_NUM) $retarr[] = $fld;
			else $retarr[strtoupper($fld->name)] = $fld;

			$rs->MoveNext();
		}
		$rs->Close();
		return $retarr;
	}

	// returns true or false
	 public function _connect($argDSN, $argUsername, $argPassword, $argDatabasename)
	{
	global $php_errormsg;

		$php_errormsg = '';
		$this->_connectionID = odbc_connect($argDSN,$argUsername,$argPassword,SQL_CUR_USE_ODBC );
		$this->_errorMsg = $php_errormsg;

		$this->Execute("ALTER SESSION SET NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
		//if ($this->_connectionID) odbc_autocommit($this->_connectionID,true);
		return $this->_connectionID != false;
	}
	// returns true or false
	 public function _pconnect($argDSN, $argUsername, $argPassword, $argDatabasename)
	{
	global $php_errormsg;
		$php_errormsg = '';
		$this->_connectionID = odbc_pconnect($argDSN,$argUsername,$argPassword,SQL_CUR_USE_ODBC );
		$this->_errorMsg = $php_errormsg;

		$this->Execute("ALTER SESSION SET NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
		//if ($this->_connectionID) odbc_autocommit($this->_connectionID,true);
		return $this->_connectionID != false;
	}
}

class  ADORecordSet_odbc_oracle extends ADORecordSet_odbc {

	public $databaseType = 'odbc_oracle';

	 public function __construct($id,$mode=false)
	{
		return parent::__construct($id,$mode);
	}
}
