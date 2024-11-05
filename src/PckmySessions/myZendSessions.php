<?php
/**
 * Contains Gimi\myFormsTools\PckmySessions\myZendSessions.
 */

namespace Gimi\myFormsTools\PckmySessions;




/**
 * I dati vengono salvati sul file ma in RAM, quindi ancora piu' performante.
 * Unico vincolo (oltre alla dimensione della memoria) e' che sia attiva e funzionante l'estensione ZendCache dello Zend Server
 * Il salvataggio è contestuale al set/unset quindi il metodo flush() e' inutile.
 *
 */
	
class myZendSessions extends myStorageSessions {
/** @ignore */
protected  $prefisso='MZS';
/** @ignore */
	protected function my_delete($k) {return @zend_shm_cache_delete($k); }
/** @ignore */
	protected function my_store($k,&$v,$ttl=0) {return @zend_shm_cache_store($k,$v,$ttl); }
/** @ignore */
	protected function my_get($k) {return @zend_shm_cache_fetch($k); }
/** @ignore */
	 public function dels($nomi=array()) 
	           {
	               if(!$nomi) @zend_shm_cache_clear("{$this->prefisso}_{$this->procedura}");
	                      else return parent::dels($nomi); 
	           }
/** @ignore */
	 public function is_available(){return is_callable('zend_shm_cache_store');}




 	/**
     * Ottiene un lock se true lock ottenuto, false non ottenuto
     * funziona solo se Zend_Cache prevede emulazione APC (come di default)
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



      public function release_lock($nome_lock) {@apc_delete("{$this->prefisso}_$nome_lock");}
	  public function delete($k) {return @zend_shm_cache_delete($k);}
	  public function flush() { }
}