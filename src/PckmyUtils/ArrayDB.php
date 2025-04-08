<?php
namespace Gimi\myFormsTools\PckmyUtils;
use PHPSQLParser\PHPSQLCreator;
use PHPSQLParser\PHPSQLParser;
use ArrayAccess;
use Countable;
use Exception;
use Iterator;

class ArrayDB  implements ArrayAccess, Iterator,Countable
{
	private $db;
	private $tableName;
	private $primaryKey;
	private $data = [];
	private $position = 0;
	private $pars=array();
	private $limitedQuery='';
	private $allQuery='';
	private $segnaposto;
	private $cache=-1;
	private $offset = 0; // Offset iniziale per il caricamento dei dati
	private $chiavi=null;
	
	
	public function __construct($db, $tableName, $primaryKey,$segnaposto_parametri='?',$cache=-1)
	{   require_once __DIR__ . '/../thrd/PHP-SQL-Parser/vendor/autoload.php';
		$this->db = $db;
		$this->cache=$cache;
		if(!is_array($tableName)) $tableName=array($tableName,array());
		list($this->tableName,$this->pars) =$tableName;
		if(!$this->pars) $this->pars=array();
		$this->primaryKey = $primaryKey;
		$parsedQuery=(new PHPSQLParser())->parse($this->tableName);
		if(!isset($parsedQuery['SELECT'])) 	$this->limitedQuery="SELECT * FROM {$this->tableName} WHERE {$this->primaryKey} = ".($segnaposto_parametri=='?'?'?':':'.count($this->pars));
  							 else	 {
			  							if(!isset($parsedQuery['WHERE'])) 
			  									   {
			  										$parsedQuery['WHERE'] =
			  											[
					  											[
					  											'expr_type' => 'colref',
					  											'base_expr' => " {$primaryKey}= ".($segnaposto_parametri=='?'?'?':':'.count($this->pars)),
					  											'sub_tree' => false,
					  											]
			  											];
			  										}
			  								else $parsedQuery['WHERE'] = 
														[
															[
															'expr_type' => 'bracket_expression',
															'base_expr' => '',
																	'sub_tree' => $parsedQuery['WHERE'],
															],
															[
															'expr_type' => 'operator',
															'base_expr' => 'AND',
															'sub_tree' => false,
															],
															[
															'expr_type' => 'colref',
																	'base_expr' => " {$primaryKey}= ".($segnaposto_parametri=='?'?'?':':'.count($this->pars)),
															'sub_tree' => false,
															]
														];
									if(isset($parsedQuery['ORDER'])) unset($parsedQuery['ORDER']);
									$this->limitedQuery=(new PHPSQLCreator())->create($parsedQuery);
									}
	$k="X_".time();								
	if(!isset($parsedQuery['SELECT'])) $this->allQuery="SELECT {$primaryKey} {$k}, {$this->tableName}.* FROM {$this->tableName} ";
								else   $this->allQuery="SELECT {$primaryKey} {$k}, tutto{$k}.* FROM ({$this->tableName} ) tutto{$k} ";
	}
	
	// Metodo per caricare un singolo record dal database in base alla chiave primaria
	private function &loadRecord($primaryKeyValue)
	{  
		$pars=$this->pars;
	    $pars[count($this->pars)]=$primaryKeyValue;
	 	$this->data[$primaryKeyValue]=$this->db->getrow($this->limitedQuery, $pars);
	 	return $this->data[$primaryKeyValue]; // Record non trovato
	}
	
	// Metodo per caricare un blocco di record
	private function loadBatch()
	{   if(isset($this->chiavi)) return;
		if($this->cache>=0) $this->data=$this->db->cachegetassoc($this->cache,$this->allQuery,$this->pars);
					   else $this->data=$this->db->getassoc($this->allQuery,$this->pars);
		$this->chiavi=array_keys($this->data);
	}
	
	// Implementazione di ArrayAccess
	public function offsetExists(mixed $offset): bool
	{
		if (isset($this->data[$offset])) return true;
		// Carica il record se esiste nel database
		return $this->loadRecord($offset) !== null;
	}
	
	public function offsetGet(mixed $offset): mixed
	{
		if (!isset($this->data[$offset])) $this->loadRecord($offset);
		return $this->data[$offset];
	}
	
	public function offsetSet(mixed $offset, mixed $value): void
	{
		throw new Exception("Modificare i record direttamente non è supportato.");
	}
	
	public function offsetUnset(mixed $offset): void
	{
		throw new Exception("Rimuovere i record direttamente non è supportato.");
	}
	
	
	public function count():int {
		$this->loadBatch();
		return count($this->chiavi);
	}
	
	// Implementazione di Iterator
	public function current():mixed
	{
		$this->loadBatch();
		return 	$this->data[$this->chiavi[$this->position]];
	}
	
	public function key():mixed
	{
		$this->loadBatch();
		return $this->chiavi[$this->position];
	}
	
	public function next():void
	{
		++$this->position;
	}
	
	public function rewind():void
	{
		$this->position = 0;
	}
	
	public function valid():bool
	{
		$this->loadBatch();
		return $this->position < count($this->chiavi);
	}
	
	
}

