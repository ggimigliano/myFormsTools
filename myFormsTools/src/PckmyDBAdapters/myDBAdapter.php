<?
namespace Gimi\myFormsTools\PckmyDBAdapters;


abstract class myDBAdapter {
    protected $conn,$type;private $fetchmode;
   
     public static function getAdapter($conn){
        static $cache=array();
        IF($conn instanceof myDBAdapter) return $conn;
        if(is_resource($conn)) $k=(string) $conn;
                          else $k=spl_object_hash($conn);
                        
        if(!key_exists($k,$cache))
                                {                          
                                if($conn  instanceof \ADOConnection) $cache[$k]= new myAdoDBAdapter($conn,$conn->databaseType);
                                                               else $cache[$k]= self::buildAdoDBConn($conn);
                               }
        return $cache[$k];                               
    }

    function &_rs2rs($rs){
        return $this->conn->_rs2rs($rs);
    }
    
     public function __get($x){
        return $this->conn->$x;
    }
    
     public function __call($metodo, $param_arr){
       return  call_user_func_array(array($this->conn,$metodo), $param_arr);
    }
    
     public function __set($x,$v){
        $this->conn->$x=$v;
    }
    
    private static function buildAdoDBConn($conn){
        include_once __DIR__.'/../thrd/adoDb/adodb.inc.php';
        if(eval('$conn instanceof Doctrine\DBAL\Connection')) 
                    { 
                        if($conn->getWrappedConnection() instanceof \PDO) $conn=$conn->getWrappedConnection();
                                                                    else $conn=$conn->getWrappedConnection()->getWrappedResourceHandle();
                    }
                    
        global $ADODB_COUNTRECS;$ADODB_COUNTRECS=true;            
        global $ADODB_LANG;$ADODB_LANG='it';
        global $ADODB_FETCH_MODE; $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        
        $ADODB_COUNTRECS;$ADODB_LANG;$ADODB_FETCH_MODE;
        $tipoDB=null;
        switch (true) {
            case($conn instanceof \mysqli): 
                        {$tipoDB='mysql';
                         $classe='MySQLi_mysqlips';
                         if(!class_exists('ADODB_mysqli',false))   include_once(__DIR__."/adoDb/drivers/adodb-mysqli.inc.php");
                         if(!class_exists('ADODB_mysqlips',false)) include_once(__DIR__."/adoDb/myadodrivers/adodb-mysqlips.inc.php");
                         if(!class_exists($classe,false))          include_once(__DIR__."/adoDb/myadodrivers/my_mysqlips.inc.php");
                         break;
                        }
                        
             case($conn instanceof \PDO):
                        {$tipoDB=strtolower($conn->getAttribute(\PDO::ATTR_DRIVER_NAME));
                         switch ($tipoDB) {
                             case 'ibm' : $tipoDB='db2'; break;
                             case 'odbc':
                                         if($conn->exec("show variables like 'sql_mode'"))                 $tipoDB='mysql';
                                            elseif($conn->exec("SELECT SYSDATE FROM DUAL"))                   $tipoDB='oci';
                                                 elseif($conn->exec("select current date from sysibm.sysdummy1")) $tipoDB='db2';
                                                        elseif($conn->exec("SELECT GETDATE() AS CurrentDateTime"))   $tipoDB='mssql';
                                        break;
                             }
                         
                         $classe="ADODB_pdo";
                         if(!class_exists($classe,false)) include_once(__DIR__."/adoDb/drivers/adodb-pdo.inc.php");
                         
                         if(!class_exists("ADODB_pdo_{$tipoDB}",false) && 
                            is_file(__DIR__."/adoDb/drivers/adodb-pdo_$tipoDB.inc.php"))
                                    { $classe="ADODB_pdo_{$tipoDB}";
                                      include_once(__DIR__."/adoDb/drivers/adodb-pdo_$tipoDB.inc.php");
                                    }
                         break;
                        }
                             
            case( is_resource($conn) && is_callable('oci_server_version') && @oci_server_version($conn) ):
                        {$tipoDB='oci';
                         $classe='ADODB_oci8';
                         if(!class_exists($classe,false)) include_once(__DIR__."/adoDb/drivers/adodb-oci8.inc.php");
                         break;
                        }
                        
            case( is_resource($conn) && is_callable('sqlsrv_server_info') && @sqlsrv_server_info($conn) ):
                       {$tipoDB='mssql';
                        $classe='ADODB_mssqlnative';
                        if(!class_exists($classe,false)) include_once(__DIR__."/adoDb/drivers/adodb-mssqlnative.inc.php");
                        break;
                        }
                        
            case( is_resource($conn) && is_callable('mssql_query') && @mssql_query('SELECT @@VERSION',$conn) ):
                       {$tipoDB='sqrlsrv';
                        $classe='ADODB_mssql';
                        if(!class_exists($classe,false)) include_once(__DIR__."/adoDb/drivers/adodb-mssql.inc.php");
                        break;
                        }
                               
            case( is_resource($conn) && is_callable('pg_version') && ($vrs=@pg_version($conn)) ):
                       {$tipoDB='pgsql';
                        $vrs=intval($vrs['client']);
                        if($vrs<4) $drv='postgres';
                            elseif($vrs<7) $drv='postgres64';
                                else $drv='postgres'.min($vrs,9);  
                        $classe="ADODB_$drv";                                
                        if(!class_exists($classe,false)) include_once(__DIR__."/adoDb/drivers/adodb-$drv.inc.php");
                        break;
                        }
                        
                        
            case( is_resource($conn) && is_callable('db2_server_info') && @db2_server_info($conn) ):
                       {$tipoDB='db2';
                        $classe='ADODB_db2';
                        if(!class_exists($classe,false)) include_once(__DIR__."/adoDb/drivers/adodb-db2.inc.php");
                        break;
                        }
                            
            case($conn instanceof \SQLite3):
                        {$tipoDB='sqlite';
                         $classe="ADODB_sqlite3";
                         if(!class_exists($classe,false)) include_once(__DIR__."/adoDb/drivers/adodb-sqlite3.inc.php");
                         break;
                        }   
                        
            case( eval('$conn instanceof SQLiteDatabase') || 
                  (is_resource($conn) && is_callable('sqlite_query') && @sqlite_query($conn,"SELECT date('now');")) 
                ):
                        {$tipoDB='sqlite';
                         $classe="ADODB_sqlite";
                         if(!class_exists($classe,false)) include_once(__DIR__."/adoDb/drivers/adodb-sqlite.inc.php");
                         break;
                        }
                        
            case( is_resource($conn) && is_callable('mysql_info') && @mysql_info($conn) ): 
                        {
                         $tipoDB='mysql';
                         $classe='MySQL_mysqltps';
                         if(!class_exists('ADODB_mysql',false))    include_once(__DIR__."/adoDb/drivers/adodb-mysql.inc.php");
                         if(!class_exists('ADODB_mysqlt',false))   include_once(__DIR__."/adoDb/drivers/adodb-mysqlt.inc.php");
                         if(!class_exists('ADODB_mysqlips',false)) include_once(__DIR__."/adoDb/myadodrivers/adodb-mysqltps.inc.php");
                         if(!class_exists($classe,false))          include_once(__DIR__."/adoDb/myadodrivers/my_mysqltps.inc.php");
                         break;
                        }
                        
            case( is_resource($conn) && is_callable('odbc_data_source') && ($vrs=@odbc_data_source($conn,SQL_FETCH_FIRST)) ):
                        {
                          $tipoDB='odbc';
                          $classe='ADODB_odbc';
                          if(!class_exists("ADODB_odbc",false)) include_once(__DIR__."/adoDb/drivers/adodb-odbc.inc.php");
                          $tmp=new \ADODB_odbc();
                          $tmp->_connectionID=$conn;
                          if($tmp->getone("SELECT SYSDATE FROM DUAL"))                      $drv='oracle';
                                elseif($tmp->getone("select current date from sysibm.sysdummy1")) $drv='db2';
                                    elseif($tmp->getone("SELECT GETDATE() AS CurrentDateTime"))   $drv='mssql';
                          unset($tmp);
                          if($drv) {$classe="ADODB_odbc_$drv";
                                    if(!class_exists($classe,false)) include_once(__DIR__."/adoDb/drivers/adodb-odbc_$drv.inc.php");
                                    }
                          break;
                        }                        
          }
        if(!$classe) return null;
        
        $con=new $classe();
        $con->_connectionID=$conn;
        
        
        switch ($tipoDB) {
            case 'sqlsrv':
            case 'mssql':                
                        $con->database=$con->getone("SELECT SCHEMA_NAME()");
            break;
            
            case 'mysql':
                        $con->database=$con->getone("SELECT DATABASE()");
            break;
            
            case 'oci':
                        $con->database=$con->getone("select sys_context('USERENV','CURRENT_SCHEMA') from dual");
            break;
            
            case 'pgsql':
                        $con->database=$con->getone("SELECT current_database()");
            break;
            }
        
        return new myAdoDBAdapter($con,$tipoDB);           
    } 
    
    
     public function __construct($conn,$tipoDB){
        $this->conn=$conn;
        $this->type=$tipoDB;
    }

    
     public function database(){
	    return $this->conn->database;
	}
	
     public function databaseType(){
        return $this->type;
    }
    
     public function get_key_conn(){
        return (is_object($this->conn->_connectionID)?spl_object_hash($this->conn->_connectionID):print_r($this->conn->_connectionID,1)).':'.$this->databaseType().":".$this->conn->host.":".$this->conn->user.":".$this->database();
    }

  

	
	function &GetAll($sql,$inputarr=false){
	    return $this->GetArray($sql,$inputarr);
	}

	function &GetAssoc($sql, $inputarr=false,$force_array = false, $first2cols = false)
		{	$results=array();
			$v=$this->getArray($sql, $inputarr);
			if(!$v)  return $results;
			if (!$first2cols && (count($v[0]) > 2 || $force_array))
					foreach ($v as &$fields) $results[trim(array_shift($fields))] = &$fields;
			   else foreach ($v as &$fields) $results[trim(array_shift($fields))] = trim(array_shift($fields));
			return $results;
		}
	
	/*
	abstract  function GetRow($sql,$inputarr=false);
    abstract  function CacheGetRow($secs2cache,$sql=false,$inputarr=false);
    abstract function SetFetchMode($mode) ;
    
    abstract function MetaForeignKeys($table, $owner=false, $upper=false);
    **
     * List columns in a database as an array of ADOFieldObjects.
     * See top of file for definition of object.
     *
     * @param $table	table name to query
     * @param $normalize	makes table name case-insensitive (required by some databases)
     * @schema is optional database schema to use - not supported by all databases.
     *
     * @return  array of ADOFieldObjects for current table.
     *
    abstract function MetaColumns($table,$normalize=true);
    
    **
     * List indexes on a table as an array.
     * @param table  table name to query
     * @param primary true to only show primary keys. Not actually used for most databases
     *
     * @return array of indexes on current table. Each element represents an index, and is itself an associative array.
     *
     * Array(
     *   [name_of_index] => Array(
     *     [unique] => true or false
     *     [columns] => Array(
     *       [0] => firstname
     *       [1] => lastname
     *     )
     *   )
     * )
     *
    abstract function MetaIndexes($table, $primary = false, $owner = false) ;
    abstract function Quote($s) ;
    
    abstract function SelectLimit($sql,$nrows=-1,$offset=-1, $inputarr=false);
    abstract function Execute($sql,$inputarr=false) ;
    abstract function ErrorMsg() ;
    abstract function Insert_ID($table='',$column='') ;
    abstract function Begintrans();
    abstract function RollbackTrans();
    abstract function CommitTrans() ;
    abstract function &GetArray($sql,$inputarr=false);
	*/
}



?>