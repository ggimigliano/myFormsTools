<?php
/**
 * Contains Gimi\myFormsTools\PckmySessions\myAPCSessions.
 */

namespace Gimi\myFormsTools\PckmySessions;





/**
 * I dati vengono salvati sul file ma in RAM, quindi ancora piu' performante.
 * Unico vincolo (oltre alla dimensione della memoria) e' che sia attiva e funzionante l'estensione APC
 * Il salvataggio è contestuale al set/unset quindi il metodo flush() e' inutile.
 *
 * @link  http:/http://www.php.net/manual/en/book.apc.php Manuale APC
 * 
 */
	
class myAPCSessions extends myStorageSessions {
/**@ignore */
protected $prefisso='MAPCS';

	  /**@ignore */
	  protected function my_delete($k){return @apc_delete($k);	  }
	  /**@ignore */
	  protected function my_get($k){	return @apc_fetch($k);    }
	  /**@ignore */
	  protected function my_store($k,&$v,$ttl=0){	return @apc_store($k,$v,$ttl);  }



 	/**
     * Ottiene un lock se true lock ottenuto, false non ottenuto
     *
     * @param string $nome_lock codice mnemonico lock
     * @param int $attesa_max    attesa massima in microsecondi , 0 non ha attesa
     * @param int $timeout_naturale numero di secondi dopo di che il lock si rilascia ugualmente, se 0 non scade mai, attenzione!
     * @return boolean
     */
     public function get_lock($nome_lock,$attesa_max=0,$timeout_naturale=0) {
    	$fine=microtime(1)+$attesa_max/1000000;
		do {
		    $locked=@apc_add("{$this->prefisso}_$nome_lock",1,($timeout_naturale?$timeout_naturale:2592000));
		} while (!$locked && microtime(1)<=$fine);
		return $locked;
    }


     public function is_available(){
    	return is_callable('apc_cache_info');
    }


     public function release_lock($nome_lock) {
    	@apc_delete("{$this->prefisso}_$nome_lock");
    	return $this;
    }

	 public function get_status() {
		return apc_sma_info(true);
	}

	  public function flush() {return $this; }
	 
	 

}