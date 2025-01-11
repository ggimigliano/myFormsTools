<?php
/**
 * Contains Gimi\myFormsTools\PckmySessions\myFileSessions.
 */

namespace Gimi\myFormsTools\PckmySessions;







/**
 * Analoga alle mySessions nei metodi ma diversa nel funzionamento.
 * Tutte le variabili vengono salvate in dei file condivisi da tutti gli script.
 * Mentre i dati di una mySessions sono accessibili dagli script eseguiti nell'ambito di una finestra del browser
 * i dati di una MyFileSessions sono condivisi tra tutti gli script ini esecuzione e non si perdono quando si chide la finestra (come con le sessioni).
 * Utile quando ci sono informazioni che si vogliono condividere per migliorare le prestazioni indipendentemente dalle sessioni attive (es. numero di accessi)
 *
 *
 * <b>LE OPERAZIONI DI SET E GET PERVEDONO SEMPRE SERIALIZE ED UNSERIALIZE DEL VALORE (ATTENZIONE AGLI OGGETTI QUINDI)
 * I DATI VENGONO SALVATI AUTOMATICAMENTE AL TERMINE DELLO SCRIPT O ATTRAVERSO IL METODO flush()
 *
 *
 * PROVARE A LANCIARE QUESTO SCRIPT DA DUE SESSIONI DIVERSE E CONFRONTARLO CON IL COMPORTAMENTO DELL'ANALOGO SCRIPT DELLE mySessions.
 * </b>
 * <code>
 * include('myForms/mySessions.php');
 *
 * $s1=new MyFileSessions('PROCEDURA A');  //istanzio una mySession per la Procedura A
 * echo "<br>Valore pippo in PROCEDURA A:".$s1->get('pippo');//visualizzo l'eventuale valore associato alla chiave 'pippo' della PROCEDURA A
 * $s1->set('pippo',$s1->get('pippo')+1,'',10); //incremento il precedente valore e lo salvo per 10 secondi
 *
 * echo "<br>Valore pippo in GENERAL:".$s1->get_general('pippo'); //recupero dalla sessione un valore Generale di 'pippo' (comune a tutte le PROCEDURE e distinto da quello della PROCEDURA A)
 * $s1->set_general('pippo',$s1->get_general('pippo')+1,'',10);  //incremento il precedente valore e lo salvo per 10 secondi
 *
 * $s2=new MyFileSessions('PROCEDURA B');   //istanzio una mySession per la Procedura B
 * echo "<br>Valore pippo in PROCEDURA B:".$s2->get('pippo'); //visualizzo l'eventuale valore associato alla chiave 'pippo' della PROCEDURA B
 * $s2->set('pippo',$s2->get('pippo')+1,'',10);   //incremento il precedente valore e lo salvo per 10 secondi
 *
 * echo "<br>Valore pippo in GENERAL:".$s2->get_general('pippo'); //recupero dalla sessione un valore Generale di 'pippo' (comune a tutte le PROCEDURE e distinto da quello della PROCEDURA A)
 * $s2->set_general('pippo',$s2->get_general('pippo')+1,'',10);   //incremento il precedente valore e lo salvo per 10 secondi
 *
 * Rieseguire questo script ad intervalli di meno di 10 secondi per valutare come si incrementano le variabili,
 * poi attendere 10 secondi e rilanciarlo per osservare come le variabili si azzerino.
 *
 * Notare che il valore valore Generale di 'pippo' è comune a tutte le istanze mySessions, ed e' inoltre facilmente accessibile da altri script che accedono
 * alla $_SESSION direttamente, consiglio quindi di usare i metodi set_general e get_general con estrema cautela e comunque
 * dando alle variabili nomi particolari.
 *
 *
 * </code>
 * 
 *
 */
	
class myFileSessions extends MyStorageSessions {
/** @ignore */
protected $percorso='', $prefisso='MFS_', $stato=null, $modificate=array();
/** @ignore */
protected static $percorso_default='';

/** @ignore */
protected function my_delete($k) {return mySessions::del($k);}
/** @ignore */
protected function my_store($k,&$v,$ttl=0) {}
/** @ignore */
protected function my_get($k) {}
/** @ignore */
 public function is_available(){return @is_dir($this->percorso);}


	/**
    * 
    * @param    string $procedura  E' il nome della procedura in uso, <br>
    * tutti gli script che usano MyFileSessions con la stessa $procedura vedranno
    * le medesime variabili, $procedura non puo' essere una stringa nulla
    */
	 public function __construct($procedura,$directory='') {
		static $dati,$modificate;
		if(!self::$percorso_default) self::$percorso_default=__MYFORM_DATACACHE__.'/myFileSessions';
		if (!$procedura) {return null;}
		$this->procedura=$procedura;
		if ($directory) {if(!is_dir($directory)) @mkdir($directory,0770,true);
						 $this->percorso=$directory;
						}
					
		if (!$this->percorso || !is_dir($this->percorso)) 
									  {if(!is_dir(self::$percorso_default)) @mkdir(self::$percorso_default,0770,true);
										$this->percorso=self::$percorso_default;
									  }
		if (!is_dir($this->percorso)) $this->percorso=ini_get('session.save_path');
		if (!is_dir($this->percorso)) $this->percorso=$_SERVER["TEMP"];
		if (!is_dir($this->percorso)) $this->percorso=dirname(__FILE__);
		$this->percorso.='/MyFileSessions';
		if (!is_dir($this->percorso) && !@mkdir($this->percorso,null,0770)) return null;
		$this->modificate=&$modificate;
		$this->dati=&$dati;
		if(isset($this->dati[$this->prefisso.$this->procedura]) && 
					is_array($this->dati[$this->prefisso.$this->procedura]))
                                    foreach (array_keys($this->dati[$this->prefisso.$this->procedura]) as $nome)
                                                        self::$nomi[$this->prefisso.$this->procedura][$nome]=true;
         if (!isset($this->dati[$this->prefisso.$this->procedura]) || !$this->dati[$this->prefisso.$this->procedura]) $this->load_procedura();
	}

	/**
	 * Imposta il percorso di default in cui verranno salvati i dati delle istanze myFileSessions
	 *
	 * @param string $percorso
	 */
	 public static function set_percorso_default($percorso){
		self::$percorso_default=$percorso;
	}


	 public function set_compress_status($status) {}


	 public function get_status() {
		return $this->stato;
	}


	/** @ignore */
	 public function flock_get_contents($filename){
       $return=false;
       if($handle = @fopen($filename, 'r'))
       		{flock($handle, LOCK_SH);
       		 $return=@fread($handle,filesize($filename));
       		 flock($handle, LOCK_UN);
             @fclose($handle);
            }
      return $return;
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
    	$fine=microtime(1)+$attesa_max/1000000;
		#$pref=str_replace(array('*','?',':','>','<','|'),'_',$this->prefisso);
		$this->locks[$nome_lock]=array('file'=>"{$this->get_nome_file_procedura()}.{$nome_lock}.lck");
		do {
		   if(!is_resource($this->locks[$nome_lock]['res'])) $this->locks[$nome_lock]['res']=@fopen($this->locks[$nome_lock]['file'],'c');
		   if(is_resource($this->locks[$nome_lock]['res'])) $this->locks[$nome_lock]['lock']=@flock($this->locks[$nome_lock]['res'],  LOCK_NB|LOCK_EX);

		} while (!$this->locks[$nome_lock]['lock'] && microtime(1)<=$fine);
		if(!$this->locks[$nome_lock]['lock']) unset($this->locks[$nome_lock]);
		return $this->locks[$nome_lock]['lock'];
    }


	/** @ignore */
	 public function load_general($handle='') {
		if (!$handle) $dati=@$this->flock_get_contents($this->get_nome_file_generale());
				else  $dati=@fread($handle,@filesize($this->get_nome_file_generale()));
		if ($dati===false) return false;

		$this->dati[$this->prefisso]=@unserialize($dati);
		if (!$this->dati[$this->prefisso]) $this->dati[$this->prefisso]=array();
		if (time()-@filemtime($this->get_nome_file_generale())>3600*24) $this->check_scaduti_generale();
		return true;
	}


	function &get($nome) {
		return mySessions::get($nome);
	}


	/** @ignore */
	 public function load_procedura($handle='') {

		if (!$handle) $dati=@$this->flock_get_contents($this->get_nome_file_procedura());
				else  $dati=@fread($handle,@filesize($this->get_nome_file_procedura()));
		if ($dati===false) return false;

		$this->dati[$this->prefisso.$this->procedura]=@unserialize($dati);
		if (!$this->dati[$this->prefisso.$this->procedura]) $this->dati[$this->prefisso.$this->procedura]=array();

		if (time()-@filemtime($this->get_nome_file_procedura())>3600) $this->check_scaduti_procedura();

		//echo "<pre>";	print_r($this->get('GRAFICA'));exit;
		return true;
	}


	  public function flush_procedura() {
            if (!$this->modificate['procedura'][$this->prefisso.$this->procedura]) return true;
            $temp=$this->dati[$this->prefisso.$this->procedura];

            $fp=@fopen($this->get_nome_file_procedura(),'w+');
            if(!$fp) return false;
            @flock($fp, LOCK_EX);

            $this->load_procedura($fp);
            foreach ($this->modificate['procedura'][$this->prefisso.$this->procedura] as $chiave=>$segno)
                                          {
                                          if ($segno=='-') unset($this->dati[$this->prefisso.$this->procedura][$chiave]);
                                                    else $this->dati[$this->prefisso.$this->procedura][$chiave]=&$temp[$chiave];
                                          }


            $out=serialize($this->dati[$this->prefisso.$this->procedura]);
            $ok=rewind($fp) && (fwrite($fp,$out)===strlen($out)) && ftruncate($fp,strlen($out));
            @flock($fp, LOCK_UN);
            @fclose($fp);
            if ($ok) unset($this->modificate['procedura'][$this->prefisso.$this->procedura]);
                  else  @unlink($this->get_nome_file_procedura());
            return $ok;
            }



       public function flush_general() {
            if (!$this->modificate['general'][$this->prefisso]) return true;
            $temp=$this->dati[$this->prefisso];

            $fp=@fopen($this->get_nome_file_generale(),'w+');

            @flock($fp, LOCK_EX);
            $this->load_general($fp);
            foreach ($this->modificate['general'][$this->prefisso] as $chiave=>$segno) {
						 if ($segno=='-') unset($this->dati[$this->prefisso][$chiave]);
									else $this->dati[$this->prefisso][$chiave]=&$temp[$chiave];
            			}

            $out=serialize($this->dati[$this->prefisso]);
            $ok=@rewind($fp) && @fwrite($fp,$out)===strlen($out) && @ftruncate($fp,strlen($out));

            @flock($fp, LOCK_UN);
            @fclose($fp);

            if (!$ok) @unlink($this->get_nome_file_generale());
                   else unset($this->modificate['general'][$this->prefisso]);
            return $ok;
      }



	/** @ignore */
	 public function check_scaduti_generale() {
	    foreach ( array_keys($this->dati[$this->prefisso]) as $nome) $this->get_general($nome);
	}


	/** @ignore */
	 public function get_nome_file_procedura() {
		return "{$this->percorso}/{$this->prefisso}_".base64_encode($this->procedura).'.dat';
	}


	/** @ignore */
	 public function check_scaduti_procedura() {
	    foreach (array_keys( $this->dati[$this->prefisso.$this->procedura]) as $nome ) $this->get($nome);
	}


	/** @ignore */
	 public function get_nome_file_generale() {
		return "{$this->percorso}/{$this->prefisso}.dat";
	}


	/**
	 * Salva i dati su disco, solo da quel momento diventano disponibili per gli altri script
	 * su PHP5 i dati si salvano in automatico anche al termine dello script, su PHP4 occorre invocarlo esplicitamente
	 *
	 * @param boolean $procedura salva i dati della procedura indicata nel costruttore
	 * @param boolean $general   salva i dati generali
	 * @return boolean esito
	 */
	 public function flush($procedura=true, $general=true){
		if ($procedura) $esito_procedura=$this->flush_procedura();
		if ($general)   $esito_general=$this->flush_general();
		return $this->stato=(!$procedura || $esito_procedura) && (!$general || $esito_general);
	}



     public function set($nome,$valore='',$forzaNulli=false,$timeOut=0) {
    	$this->modificate['procedura'][$this->prefisso.$this->procedura][$nome]='+';
        $esito=mySessions::set($nome,$valore,$forzaNulli,$timeOut);
        if(!$esito) $this->del($nome)	;
        return $esito;
    }

      public function del($nome) {
    	$this->modificate['procedura'][$this->prefisso.$this->procedura][$nome]='-';
     	return mySessions::del($nome);
    }




    public function get_general($nome) {
		if (!isset($this->dati[$this->prefisso])) $this->load_general();
        if (isset($this->dati[$this->prefisso][$nome]['Timeout']) &&
		 	 	   $this->dati[$this->prefisso][$nome]['Timeout']<time()) $this->del_general($nome);
		 return @unserialize($this->dati[$this->prefisso][$nome]['Dati']);
    }


     public function set_general($nome,$valore='',$forzaNulli=false,$timeOut=0) {
    if (!isset($this->dati[$this->prefisso])) $this->load_general();
       if ($nome && ($forzaNulli || ($valore!==null && $valore!=='' && $valore!==0)) && ($dato=@serialize($valore))!=='')
		 		{$this->modificate['general'][$this->prefisso][$nome]='+';
		 		 $this->dati[$this->prefisso][$nome]['Dati']=$dato;
		 		 if ($timeOut) $this->dati[$this->prefisso][$nome]['Timeout']=time()+$timeOut;
		 		 return true;
		 		}
		 return false;
    }


    public function del_general($nome) {
   		if (!isset($this->dati[$this->prefisso])) $this->load_generale();
   	    $this->modificate['general'][$this->prefisso][$nome]='-';
   	   	unset($this->dati[$this->prefisso][$nome]);
    }

}