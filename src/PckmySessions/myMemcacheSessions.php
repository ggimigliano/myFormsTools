<?php
/**
 * Contains Gimi\myFormsTools\PckmySessions\myMemcacheInternalCache.
 */

namespace Gimi\myFormsTools\PckmySessions;




/**
 * Analoga alle MyFileSessions nei metodi e nel funzionamento,
 * i dati pero' non vengono salvati sul file ma in RAM, quindi ancora piu' performante.
 * Unico vincolo (oltre alla dimensione della memoria) e' che sia attiva e funzionante l'estensione memcache
 * Il salvataggio è contestuale al set/unset quindi il metodo flush() e' inutile.
 *
 * @link  http://it2.php.net/manual/it/ref.memcache.php Manuale memcache
 * 
 */
	
class myMemcacheInternalCache extends myStorageSessions {
/** @ignore */
protected $prefisso='MMS',$maxSizeItem=1048000;
/** @ignore  */
protected $memcache;protected static $memcaches=array();


	/**
	 *
	 * 
	 * @param string $procedura
	 * @param string/array $server il nome (o i nomi) nel formato ip:porta:peso (quest'ultimo nel caso siano piu' server)
	 * @param object &$memcache è l'istanza di memcache creata oppure e' l'eventuale istanza di memcache da utilizzare (va bene anche un'istanza di Memcached)
	 * @param boolean &$errore viene settato a true se c'e' un'errore durante la connessione al server
	 */
	 public function __construct($procedura,$server='127.0.0.1:11211',&$memcache=null,&$errore=false)
		{$k=sha1(serialize($server));
		if(is_object($memcache)) $this->memcache=self::$memcaches[$k]=$memcache;
		   elseif(is_object(self::$memcaches[$k]))  $this->memcache=self::$memcaches[$k];
			 	else{
					 if(!is_array($server)) $server=array($server);
					 if(class_exists('Memcache',false))
					 		{$memcache=new \Memcache;
					 		 foreach ($server as $srv) {
					   			$srv=explode(':',$srv);
					   			$memcache->addServer($srv[0],($srv[1]?$srv[1]:11211),true,1);
					 			}
					 		}
					 if(!is_object($this->memcache) && class_exists('Memcached',false))
					 		{$memcache=new \Memcached($this->prefisso);
					 		 foreach ($server as $srv) {
					   			$srv=explode(':',$srv);
					   			$memcache->addServer($srv[0],($srv[1]?$srv[1]:11211));
					 			}
					 		}
					 if (@$memcache->getVersion()) $this->memcache=self::$memcaches[$k]=$memcache;
					 }
		if(is_object($this->memcache)) parent::__construct($procedura);
	 }


	 public function is_available(){
		return is_object($this->memcache);
	}


	/** @ignore */
	protected function my_delete($k){
		if(!is_object($this->memcache)) return false;
		if(!$this->maxSizeItem) return @$this->memcache->delete($k, 0);
		$i=0;$next='';
		do {$deleted=@$this->memcache->delete("$k$next",0);
			$i++;
			if($i) $next="/next~$i";
		}while ($deleted);
		return true;
	}


	/** @ignore */
	protected function my_get($k){
		if(!is_object($this->memcache)) return false;
		if(!$this->maxSizeItem) return @$this->memcache->get($k);
		$v='';
		$keys=array();
		$i=0;$next='';
		do {$keys[]="$k$next";
			if($i%5==1) {   $vals=$this->real_get($keys);
							$v.=implode('',$vals);
							if(!isset($vals[$keys[count($keys)-1]]) || !$vals[$keys[count($keys)-1]]) break;
							$keys=array();
					 	}
			$next="/next~".(++$i);
		} while (true);
		if(!$this->compress) return @unserialize($v);
		return $v;
	}


	/**
	 * @ignore
	 */
	protected function my_store($k,&$to_store,$ttl=0){
		if(!is_object($this->memcache)) return false;
		if(!$this->compress) $to_store=serialize($to_store);
		$esito=true;$i=0;
		do {//echo "<br>storing ",($i>0?"$k/next~$i":$k);
			$esito=$esito && $this->real_store(($i>0?"$k/next~$i":$k), substr($to_store, $i*$this->maxSizeItem,$this->maxSizeItem),$ttl);
	    }while($esito && ++$i*$this->maxSizeItem<strlen($to_store));
		//$this->real_store('', $TO_STORE);
		return $esito;
	}


	/** @ignore */
	private function &real_get( &$k){
		if($this->memcache instanceof  \Memcache) return @$this->memcache->get($k);
		if($this->memcache instanceof  \Memcached)  return @$this->memcache->getMulti($k);
	}

	/** @ignore */
	private function real_store($k,&$v,$ttl=0){
		if($ttl>30*24*3600) $ttl=30*24*3600;
		if($this->memcache instanceof  \Memcached) return @$this->memcache->set($k,$v,$ttl);
		if($this->memcache instanceof  \Memcache)  return @$this->memcache->set($k,$v,($this->maxSizeItem?0:MEMCACHE_COMPRESSED),$ttl);
	}


    /**
     * Ottiene un lock se true lock ottenuto, false non ottenuto
     *
     * @param string $nome_lock codice mnemonico lock
     * @param int $attesa_max    attesa massima in microsecondi , 0 non ha attesa
     * @param int $timeout_naturale numero di secondi dopo di che il lock si rilascia ugualmente, se 0 scade dopo 30 giorni (durata max per memcache), attenzione!
     * @return boolean
     */
     public function get_lock($nome_lock,$attesa_max=0,$timeout_naturale=0) {
    	$fine=microtime(1)+$attesa_max/1000000;
		do {
		    $locked=@$this->memcache->add("{$this->prefisso}_$nome_lock",1,0,($timeout_naturale?$timeout_naturale:2592000));
		} while (!$locked && microtime(1)<=$fine);
		return $locked;
    }


     public function release_lock($nome_lock) {
    	@$this->memcache->delete("{$this->prefisso}_$nome_lock");
    }


	 public function get_status($type='') {
		if(!$type) return $this->memcache->getStats();
			  else return $this->memcache->getExtendedStats($type);
	}


	  public function flush() {return $this; }
}