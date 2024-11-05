<?php
/**
 * Contains Gimi\myFormsTools\PckmySessions\mySessions.
 */

namespace Gimi\myFormsTools\PckmySessions;




/**
 * Questa classe permette una gestione delle variabili di sessione facilitata e con ridotta probabilità di conflitto
 * <b>LE OPERAZIONI DI SET E GET PREVEDONO SERIALIZE ED UNSERIALIZE DEL VALORE (ATTENZIONE AGLI OGGETTI QUINDI) AD ECCEZIONE DELLE GENERAL
 * I DATI VENGONO SALVATI AUTOMATICAMENTE AL TERMINE DELLO SCRIPT OPPURE CON IL METODO flush()</b>
 * <code>
 * include('myForms/mySessions.php');
 *
 * $s1=new mySessions('PROCEDURA A'); //istanzio una mySession per la Procedura A
 * echo "<br>Valore pippo in PROCEDURA A:".$s1->get('pippo');  //visualizzo l'eventuale valore associato alla chiave 'pippo' della PROCEDURA A
 * $s1->set('pippo',$s1->get('pippo')+1,'',10); //incremento il precedente valore e lo salvo per 10 secondi
 *
 * echo "<br>Valore pippo in GENERAL:".$s1->get_general('pippo'); //recupero dalla sessione un valore Generale di 'pippo' (comune a tutte le PROCEDURE e distinto da quello della PROCEDURA A)
 * $s1->set_general('pippo',$s1->get_general('pippo')+1,'',10); //incremento il precedente valore e lo salvo per 10 secondi
 *
 * $s2=new mySessions('PROCEDURA B'); //istanzio una mySession per la Procedura B
 * echo "<br>Valore pippo in PROCEDURA B:".$s2->get('pippo'); //visualizzo l'eventuale valore associato alla chiave 'pippo' della PROCEDURA B
 * $s2->set('pippo',$s2->get('pippo')+1,'',10);  //incremento il precedente valore e lo salvo per 10 secondi
 *
 * echo "<br>Valore pippo in GENERAL:".$s2->get_general('pippo'); //recupero dalla sessione un valore Generale di 'pippo' (comune a tutte le PROCEDURE e distinto da quello della PROCEDURA A)
 * $s2->set_general('pippo',$s2->get_general('pippo')+1,'',10);  //incremento il precedente valore e lo salvo per 10 secondi
 *
 * Rieseguire questo script ad intervalli di meno di 10 secondi per valutare come si incrementano le variabili,
 * poi attendere 10 secondi e rilanciarlo per osservare come le variabili si azzerino.
 *
 * Notare che il valore valore Generale di 'pippo' è comune a tutte le istanze mySessions, ed e' inoltre facilmente accessibile da altri script che accedono
 * alla $_SESSION direttamente, consiglio quindi di usare i metodi set_general e get_general con estrema cautela e comunque
 * dando alle variabili nomi particolari.
 *
 * </code>
 * 
 */
	
class mySessions  {
/**@ignore */
protected $procedura,$dati, $prefisso='mySessions>',$locks=array();
/**@ignore */
protected static $nomi=null;


	/**
    * 
    * @param    string $procedura  E' il nome della procedura in uso,
    * tutti gli script che usano MySession con la stessa $procedura vedranno
    * le medesime variabili di sessione, $procedura non puo' essere una stringa nulla
    */
	 public function __construct($procedura) {
		if (!isset($_SESSION)) @session_start();
		if (!$procedura) {return null;}
		$this->procedura=$procedura;
		$this->dati=&$_SESSION;
		if(!isset(self::$nomi[$this->prefisso.$this->procedura])) self::$nomi[$this->prefisso.$this->procedura]=array();
		if(isset($this->dati[$this->prefisso.$this->procedura])) 
		          foreach (array_keys($this->dati[$this->prefisso.$this->procedura]) as $nome) 
		                                                  self::$nomi[$this->prefisso.$this->procedura][$nome]=true;
	}


	  /**
	* Restituisce il valore associato a $nome nella sessione generale e' analogo ad eseguire $_SESSION[$nome]
     *  (proprio il comando che si vuole evitare) ma con l'aggiunta dell'eventuale timeout
     * @param    string $nome
     * @return mixed
     */
     public function get_general($nome) {
    	 if (isset($_SESSION['mySessions_General>'][$nome]['Timeout']) &&
		 	       $_SESSION['mySessions_General>'][$nome]['Timeout']<time()) $this->del_general($nome);
		return isset($_SESSION[$nome])?$_SESSION[$nome]:null;
    }

    /**
     * Restituisce il true o false se ci sono le condizioni per usare l'istanza
     * @return boolean
     */
     public function is_available() {
    	return isset(self::$nomi[$this->prefisso.$this->procedura]);
    }

     public function get_procedura() {
    	return $this->procedura;
    }


   /**
     * Inserisce una variabile nella sessione generale <br>
     * e' analogo ad eseguire $_SESSION[$nome]=$valore
     *  (proprio il comando che si vuole evitare) ma con l'aggiunta dell'eventuale timeout
     * <b>TUTTI GLI SCRIPT (ANCHE QUELLI DI ALTRE PROCEDURE) POTRANNO UTILIZZARLA;
     * PERTANTO USARLA CON MOLTA CAUTELA E SOPRATTUTTO USARE NOMI PARTICOLARI
     * I DATI NON VENGONO SERIALIZZATI QUANDO SI USA QUESTO METODO, QUINDI E' IMPORTANTE CHE TUTTI
     * GLI SCRIPT DA INCLUDERE LO SIANO STATI PRIMA DI AVVIARE LA SESSIONE O RISULTERANNO INCOMPLETI
     * </b>
     * @param    string $nome Nome assegnato alla variabile (da usare per richiamarla)
	 * @param    mixed $valore Valore da associare al $nome
	 * @param    boolean $forzaNulli Se vero $nome viene inserito nella sessione anche quando $valore è nullo  di default e' false
	 * @param    int $timeOut imposta la durata in secondi della variabile nella sessione, se omesso o 0 la variabile non ha scadenza
	 * @return 	 boolean esito dell'operazione (se $nome e' nullo non fa nulla e restituisce false)
	 */
     public function set_general($nome,$valore='',$forzaNulli=false,$timeOut=0) {
        if ($nome && ($forzaNulli || ($valore!==null && $valore!=='' && $valore!==0)) )
		 		{
		 		 $this->dati[$nome]=$valore;
		 		 if($timeOut) $_SESSION['mySessions_General>'][$nome]['Timeout']=time()+$timeOut;
		 		 return true;
		 		}
		 return false;
    }

    /**
     * elimina una variabile dalla sessione generale
     *
     * @param string $nome della variabile
     */
     public function del_general($nome) {
    	unset($_SESSION[$nome]);
		unset($_SESSION['mySessions_General>'][$nome]);
		return $this;
    }



	/**
     * Inserisce una variabile nella sessione, durante l'inserimento
     * la variabile e' serializzata.
     * <b>OGNI VOLTA CHE SI ESEGUE QUESTA OPERAZIONE I DATI VENGONO SERIALIZZATI, (QUINDI ATTENZIONE AGLI OGGETTI)
     * IN QUESTO MODO E' POSSIBILE INCLUDERE GLI SCRIPT DI DEFINIZIONE DELLE CLASSI DI OGGETTI SALVATI ANCHE DOPO AVER AVVIATO LA SESSIONE,
     * MA COMUNQUE PRIMA DI RECUPERARLI.
     *
     * @param    string $nome Nome assegnato alla variabile (da usare per richiamarla)
	 * @param    mixed $valore Valore da associare al $nome
	 * @param    boolean $forzaNulli Se vero $nome viene inserito nella sessione anche quando $valore è nullo  di default e' false
	 * @param    int $timeOut imposta la durata in secondi della variabile nella sessione, se omesso o 0 la variabile non ha scadenza
	 * @return 	 boolean esito dell'operazione (se $nome e' nullo non fa nulla e restituisce false)
	 */
     public function set($nome,$valore='',$forzaNulli=false,$timeOut=0) {
       if ($nome && ($forzaNulli || ($valore!==null && $valore!=='' && $valore!==0)) && ($dato=@serialize($valore))!=='')
		 		{
		 		self::$nomi[$this->prefisso.$this->procedura][$nome]=true;
		 		$this->dati[$this->prefisso.$this->procedura][$nome]['Dati']=$dato;
		 		if ($timeOut) $this->dati[$this->prefisso.$this->procedura][$nome]['Timeout']=time()+$timeOut;
		 		return true;
		 		}
		 return false;
    }


    /**
     * Ottiene un lock basato su file tra script diversi se true lock ottenuto, false non ottenuto
     *
     * @param string $nome_lock codice mnemonico lock
     * @param int $attesa_max    attesa massima in microsecondi (su windows e con PHP<5.3 il lock è sempre bloccante quindi l'attesa non e' applicabile) , 0 non ha attesa affatto
     * @param int $timeout_naturale inapplicato, il lock si rilascia con mySession::release_lock() o quando lo script termina
     * @return boolean
     */
     public function get_lock($nome_lock,$attesa_max=0,$timeout_naturale=0) {
        $directory=ini_get('session.save_path');
		if (!is_dir($directory) || !is_writable($directory)) $directory=$_SERVER["TEMP"];
		if (!is_dir($directory) || !is_writable($directory)) $directory=dirname(__FILE__);
		$fine=microtime(1)+$attesa_max/1000000;
		$pref=str_replace(array('*','?',':','>','<','|'),'_',$this->prefisso);
		$this->locks[$nome_lock]=array('file'=>"$directory/{$pref}_{$nome_lock}.lck");
		do {
		   if(!is_resource($this->locks[$nome_lock]['res'])) $this->locks[$nome_lock]['res'] =@fopen($this->locks[$nome_lock]['file'],'c');
		   if(is_resource($this->locks[$nome_lock]['res'])) $this->locks[$nome_lock]['lock']=@flock($this->locks[$nome_lock]['res'],  LOCK_NB|LOCK_EX);

		} while (!$this->locks[$nome_lock]['lock'] && microtime(1)<=$fine);
		if(!$this->locks[$nome_lock]['lock']) unset($this->locks[$nome_lock]);
		return $this->locks[$nome_lock]['lock'];
    }


     public function release_lock($nome_lock) {
    	@fclose($this->locks[$nome_lock]['res']);
    	@unlink($this->locks[$nome_lock]['file']);
    	unset($this->locks[$nome_lock]);
    }

    /**
     * @ignore
     */
     public function __destruct(){
        if($this->locks) foreach (array_keys($this->locks) as $lock) $this->release_lock($lock);
    }

	/**
     * Inserisce piu' variabili nella sessione in un'unica volta
     * @param    array $valori Array associativo con tutti i 'nomi'=>'valori' da inserire
	 * @param    boolean $forzaNulli Se vero vengono inseriti nella sessione anche 'nomi' con 'valori' nulli; di default e' false
 	 * @param    int $timeOut imposta la durata in secondi della variabile nella sessione, se omesso o 0 la variabile non ha scadenza
	 * @return 	 boolean esito dell'operazione (se $valori e' nullo non fa nulla e restituisce false)
     */
     public function sets($valori,$forzaNulli=false,$timeOut=0)  {
      if ($valori && count($valori)>0)
      			 {
      			  foreach ($valori as $nome=>$valore) if ($nome) $this->set($nome,$valore,$forzaNulli,$timeOut);
      			  return true;
      			 }
      return false;
    }






    /**
	* Restituisce il valore associato a $nome
     * @param    string $nome
     * @return mixed
     */
    function &get($nome) {
         $valore=null;
		 if (isset($this->dati[$this->prefisso.$this->procedura][$nome]['Timeout']) &&
		 	 	   $this->dati[$this->prefisso.$this->procedura][$nome]['Timeout']<time()) $this->del($nome);

		 if (isset($this->dati[$this->prefisso.$this->procedura][$nome]))
		 	       $valore=@unserialize($this->dati[$this->prefisso.$this->procedura][$nome]['Dati']);
		if ($valore===false) return $this->dati[$this->prefisso.$this->procedura][$nome]['Dati'];
		 				else return $valore;
    }


    /**
	* Restituisce un array associativo con piu' valori della sessione
     * @param    array $nomi array non associativo con l'elenco dei nomi da usare nell'estrazione, se omesso si estrarranno tutte i valori della sessione
     * @return mixed
     */
     public function gets($nomi=array()) {
    	 $v=array();
     	 if(self::$nomi[$this->prefisso.$this->procedura]) 
     	     foreach (array_keys(self::$nomi[$this->prefisso.$this->procedura]) as $nome) 
     	              if (!$nomi || in_array($nome,$nomi))
     	 										{$v[$nome]=$this->get($nome);
     	 										 if ($v[$nome]===null) unset($v[$nome]);
     	 										}
     	 return $v;
    }


    /**
	* Elimina $nome dalla sessione
     * @param    string $nome
     */
     public function del($nome) {
        unset(self::$nomi[$this->prefisso.$this->procedura][$nome]);
    	unset($this->dati[$this->prefisso.$this->procedura][$nome]);
    	return $this;
    }



   /**
	* Elimina tutte le voci in $nomi dalla sessione.
     * @param    array $nomi non associativo con l'elenco dei nomi da usare nell'estrazione, se omesso SI DISTRUGGE TUTTO IL CONTENUTO DELL'INTERA SESSIONE
     */
     public function dels($nomi=array()) {
     	 if(self::$nomi[$this->prefisso.$this->procedura]) 
     	     foreach (array_keys(self::$nomi[$this->prefisso.$this->procedura]) as $nome) 
     	                                           if (!$nomi || in_array($nome,$nomi)) $this->del($nome);
       return $this;       							
    }


    /**
     * Crea una variabile GLOBALE di nome $nome estraendola (se esiste) dalla sessione
     * Es. Se in uno script scriviamo:
 	 * <code>
     * include_once('../MyForms/mySessions.php');
     * $s=new MySessions('procedura_x');	  //istanzio la classe
     * $s->set('CF',$_GET['CODICE_FISCALE']);  //senza terzo parametro ha effetto solo quando  $_GET e' valorizzato
     * ...
     * </code>
     *
     * Ed in un altro script scriviamo:
     * <code>
     * include_once('../MyForms/mySessions.php');
     * $s=new MySessions('procedura_x');
     * $s->globalize('CF');
     * //da questo momento esiste una variabile $CF con il valore precedentemente memorizzato nella sessione
     * ...
     * </code>
     *
     * @param  string $nome
     * @return  void
     */
     public function globalize($nome) {
        global $$nome;
     	$$nome=$this->get($nome);
     	return $this;
    }




    /**
     * Forza il salvataggio dei dati della sessione
     * @link  http://it2.php.net/manual/it/function.session-write-close.php
     *
     */
     public function flush() {
        session_write_close();
        return $this;
    }


     /**
      * Crea tante variabili GLOBALI con i nomi nell'array $nomi estraendone i valori (se esistono) dalla sessione
     * Es. Se in uno script scriviamo:
     * <code>
     * include_once('../MyForms/mySessions.php');
     * $s=new MySessions('procedura_x');//istanzio la classe
     * $s->set('CF',$_GET['CODICE_FISCALE']);  //senza terzo parametro ha effetto solo quando  $_GET e' valorizzato
     * $s->set('ALTRO','PIPPO',true);  //forza l'inserimento di ALTRO nella sessione con valore PIPPO
 	 * ...
     * </code>
     *
     * Ed in un altro script scriviamo:
     * <code>
     * include_once('../MyForms/mySessions.php');
     * $s=new MySessions('procedura_x');
     * $s->globalizes( array('CF','ALTRO') );
     * //da questo momento esistono sia $CF che $ALTRO con i valori precedentemente memorizzati nella sessione
     * ...
     * </code>
     *
     * @param    array $nomi
     * @return   void
     */

      public function globalizes($nomi=array()) {
      if (count($nomi))
     		foreach ($nomi as $nome) $this->globalize($nome);
      return $this;
    }

    /**
	* Vedi globalize ma per la sessione generale
     * @param    string $nome
     **/
     public function globalize_general($nome) {
     	global $$nome;
    	$$nome=$this->get_general($nome);
    	return $this;
    }
}