<?php
/**
 * Contains Gimi\myFormsTools\PckmySessions\myStorageSessions.
 */

namespace Gimi\myFormsTools\PckmySessions;



/**
 * Classe astratta per la costruzione di mySession con funzionamento di storage
 * I DATI SONO CONDIVISI TRA TUTTI GLI SCRIPT E NON LEGATI ALLA SESSSIONE UTENTE COME PER LA CLASSE MADRE
 *
 * @abstract
 */
	
abstract class myStorageSessions extends mySessions{
/** @ignore */
	protected $compress=false;
/** @ignore */
	 public function get_lock($nome_lock,$attesa_max=0,$timeout_naturale=0){throw new \Exception ('Metodo da reimmplementare');}
/** @ignore */
	 public function release_lock($nome_lock){throw new \Exception ('Metodo da reimmplementare');}
/** @ignore */
	 public function is_available(){throw new \Exception ('Metodo da reimmplementare');}
/** @ignore */
	 public function flush(){throw new \Exception ('Metodo da reimmplementare');}

	/** @ignore */
	protected function my_compress(&$v) {
		$v=serialize($v);
		if(strlen($v)>512) $v=gzcompress($v,5);
	}

	/** @ignore */
	protected function my_uncompress(&$v) {
		if($v[0]=='x') {$x=gzuncompress($v);
						if($x!==null) $v=$x;
						}
		$v=unserialize($v);
	}

	/** @ignore */
	abstract protected  function my_delete($k);
	/** @ignore */
	abstract protected  function my_get($k);
	/** @ignore */
	abstract protected  function my_store($k,&$v,$ttl=0);

	/** @ignore */
	 public function __destruct() {
		$this->compress=false;
		$this->my_store("{$this->prefisso}_{$this->prefisso}_nomi",self::$nomi[$this->prefisso.$this->procedura]);
	}


	 public function __construct($procedura) {
			if (!$procedura) {return;}
			$this->procedura=$procedura;
			if((!isset(self::$nomi[$this->prefisso.$this->procedura]) || !is_array(self::$nomi[$this->prefisso.$this->procedura])) && 
			    $this->is_available())  
			           self::$nomi[$this->prefisso.$this->procedura]=$this->my_get("{$this->prefisso}_{$this->prefisso}_nomi");
			           
			if(!isset(self::$nomi[$this->prefisso.$this->procedura]) || 
			   !is_array(self::$nomi[$this->prefisso.$this->procedura])) self::$nomi[$this->prefisso.$this->procedura]=array();
	}

	 public function set_general($nome,$valore='',$forzaNulli=false,$timeOut=0) {
		if ($nome && ($forzaNulli || ($valore!==null && $valore!=='' && $valore!==0)))
		  {if($this->compress)  $this->my_compress($valore);
		   $esito=$this->my_store("{$this->prefisso}_$nome",$valore,(int) $timeOut);
		   return $esito;
		  }
		return false;
	}

	 public function get_general($nome) {
		$valore=$this->my_get("{$this->prefisso}_$nome");
		if($this->compress) $this->my_uncompress($valore);
		return $valore;
	}


	 public function del_general($nome) {
		return $this->my_delete("{$this->prefisso}_$nome");
	}

	 public function set($nome,$valore='',$forzaNulli=false,$timeOut=0) {
		if ($nome && ($forzaNulli || ($valore!==null && $valore!=='' && $valore!==0)))
			{if($this->compress)  $this->my_compress($valore);
			 $esito=$this->my_store("{$this->prefisso}_{$this->procedura}::$nome",$valore,(int) $timeOut);
			 if($esito) self::$nomi[$this->prefisso.$this->procedura][$nome]=true; //illegal offset
			 return $esito;
			}
		return false;
	}

	function &get($nome) {
		$valore=$this->my_get("{$this->prefisso}_{$this->procedura}::$nome");
		if($this->compress) $this->my_uncompress($valore);

		return $valore;
	}

	 public function del($nome) {
		unset(self::$nomi[$this->prefisso.$this->procedura][$nome]);
		return $this->MY_delete("{$this->prefisso}_{$this->procedura}::$nome");
	}


	/**
	 * Restituite lo stato compressione non ha effetto su myFileSessions
	 * @return boolean
	 */
	 public function get_compress_status($status) {
		return $this->compress;
	}

	/**
	 * Imposta l'uso della compressione dei dati <b> non ha effetto su myFileSessions</b>
	 * @param 0..9 $status se -1 i dati non vengono compressi , altrimenti indica il fattore i compressione gzcompress
	 */
	 public function set_compress_status($status) {
		if(is_callable('gzcompress')) $this->compress=$status;
		return $this;
	}
}