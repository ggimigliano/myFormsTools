<?php
/**
 * Contains Gimi\myFormsTools\myDizionario.
 */

namespace Gimi\myFormsTools;

use Gimi\myFormsTools\PckmyAutoloaders\myAutoloader;



/**
 * 
 * Classe utile per creare traduzioni, utilizzabile per i messaggi d'errore per le myForms.
 * Al momento e' disponibile il dizionario completo dei messaggi d'errore delle myForms in Inglese.
 * @see myDizionario::trasl()
 *
 */
	
class myDizionario  {
/** @ignore */
    protected static $encoding=false;
/** @ignore */
protected  $dal,$al,$log=false,$casesens=true,$percorso,$container=array(),$containers,$to_save=false,$obj_encoding=false;



	/**
	 * Costruttore del dizionario
	 * 
	 * @param string(2) $al    //codice della lingua in cui tradurre
	 * @param string(2) $dal   //codice della lingua da cui tradurre	
	 * @param string    $percorso //eventuale percorso pse si usa un dizionario personalizzaato
	 * @return myDizionario
	 */
	 public function __construct($al,$dal='it',$percorso='') {
		$this->dal=strtolower($dal);
		$this->al=strtolower($al);
		if (!$percorso) $percorso=dirname(__FILE__)."/langs/{$this->dal}-{$this->al}.diz.php";
		if ("$dal-$al"!='it-it' && !isset($this->container["$dal-$al"])) 
											 {$traduzioni=&$this->containers["$dal-$al"];
											  @include($percorso);
											  $this->percorso=realpath($percorso);
											  }
		$traduzioni;
        $this->container=&$this->containers["$dal-$al"];	
	}
	
/**
	 * @ignore
	 */
	 public function __call($m,$v){
	   if(strtolower($m)==strtolower(__CLASS__)) {
	        $pars=array();
	        for ($i=0;$i<count($v);$i++) $pars[]="\$v[{$i}]";
	        eval("return $m::__construct(".implode(',',$pars).");");
	        return;
	       }
	}
 
   
	/*** @ignore **/
  protected static function file_write($nome,$content) {
     $md5=md5($content);
    
     if ($md5==@md5_file($nome)) return true;
     
     $new_name=$nome.'.'.microtime(1).mt_rand(10,99999999).'.tmp';
      do{if (@file_put_contents($new_name,$content) 
     				&& 
        	$md5==@md5_file($new_name) 
        			&&
           (!is_file($nome) || @rename($nome,"$new_name.prev")) 
           			&&  
        	@rename($new_name,$nome)) 
        				{@unlink("$new_name.prev");
        				 return true;
        				}
        @unlink("$new_name.prev");				
        @unlink($new_name); 				
        usleep($wt=rand(100,10000));
        @unlink("$new_name.prev");				
        @unlink($new_name); 	
        $n+=$wt;
      } while ($n<1000000);
      return false;
    }
  
  
	/**
	 * Attiva/disattiva log errori
	 * (in sostanza nell'array del file dizionario si aggiungono tutte le parole non trovate)
	 *
	 * @param boolean $stato
	 */
	 public function log_errori($stato=true) {
	    if($stato===null) return $this->log;
	    $pred=$this->log;
		$this->log=$stato;
		return $pred;
	}
	
	/**
	 * Restituisce l'array con tutte le traduzioni
	 *
	 * @return array (e' un array associativo che ha come chiave il testo da tradurre e valore il testo tradotto)
	 */
	 public function get_trasl() {
		$v=$this->container;
		ksort($v);
		return $v;
	}
	
	
	/**
	 * Restituisce il codice della lingua da cui si traduce
	 *
	 * @return string
	 */
	 public function get_dal() {return $this->dal;}
	
	
	/**
	 * Restituisce il codice della lingua in cui si traduce
	 *
	 * @return string
	 */
	 public function get_al() {return $this->al;}
	
	
	/**
	 * Aggiunge le traduzioni passate al dizionario corrente senza salvare
	 *
	 * @param array $traduzioni (e' un array associativo che ha come chiave il testo da tradurre e valore il testo tradotto)
	 */
	 public function add_trasl($traduzioni) {
		foreach ($traduzioni as $da=>&$in) if (!$this->casesens) $this->container[strtolower($da)]=$in;
															else $this->container[$da]=$in;
	}
	
	
	/**
	 * Sostituisce le traduzioni passate al dizionario corrente senza salvare
	 *
	 * @param array $traduzioni (e' un array associativo che ha come chiave il testo da tradurre e valore il testo tradotto)
	 */
	 public function set_trasl($traduzioni) {
		$this->container=array();
		$this->add_trasl($traduzioni);
	}
	
	
	
/**
	 * Salva le traduzioni passate nel dizionario corrente
	 *
	 * @param array $traduzioni (e' un array associativo che ha come chiave il testo da tradurre e valore il testo tradotto) se omesso si risalvano i valori correnti
	 * @param boolean $quota se true effettua quotatura dei testi
	 */
	 public function Salva($traduzioni='',$quota=true){
		if (!$traduzioni) $traduzioni=$this->get_trasl();
		$testo="<?\n";
		foreach ($traduzioni as $da=>&$ad) 
								{if ($quota) {$da=str_replace("'","\\'",$da);
											  $ad=str_replace("'","\\'",$ad);
											 }
											 
								$testo.="\$traduzioni['$da']='$ad';\n";
								}			
		$testo.="\n?>"; 
		
		self::file_write($this->percorso,$testo);
		
		unset($this->container["{$this->dal}-{$this->al}"]);
		self::myDizionario($this->dal,$this->al,$this->percorso); 
	}
	
	/**
	 * Imposta il case per le traduzioni
	 *
	 * @param boolean $sensitive se true è sensitive se false e' insensitive
	 */
	 public function set_case($sensitive) {
		$this->casesens=$sensitive;
		//if (!$sensitive && $this->container) foreach ($this->container as $da=>&$in) $this->container[strtolower($da)]=$in;
	}
	
	/**
	 * Traduce un testo
	 *
	 * @param string $x testo da tradurre
	 * @param array $parole array associativo per eventuali segnaposto
	 * @param boolean $esito se si e' trovata una traduzione per il testo
	 * @return string
	 * 
	 * <code>
	 * Supponendo che il file 'miei_dizionari/mie_voci_ing_it.inc' 
	 * contenga:
	 * <?
     *  $traduzioni=array (
     * 					   'tu hai un gioco'=>'you have a game',
     * 					   'tu hai #n# giochi'=>'you have #n# games'
     * 					  );
	 * ?>
	 * 
	 * 
	 * Possiamo scrivere:
	 * <?
	 * $d=new myDizionario('en','it','miei_dizionari/mie_voci_ing_it.inc');
	 * if($n==1)     echo $d->trasl('tu hai un gioco');  //se $n vale 1 usa una frase al singolare
	 * 			else echo $d->trasl('tu hai #n# giochi',array('#n#'=>$n)); //altrimenti la usa al plurale parametrizzando il numero $n
	 * ?>
	 * </code>
	 */
	 public function trasl($x,$parole=array(),&$esito='') {
		$esito=false;
		if(ctype_digit($x) || (strtolower($this->dal)=='it' && strtolower($this->al)=='it'))  { $nuova=$x;$esito=true;}
		else{$esito=false;
				 if (!($x=trim((string) $x))) return $x;	
				 if ($this->casesens)  {
									   $esito=isset($this->container[$x]) && $this->container[$x]!=='';
									   $nuova=$this->container[$x];
									  }	
								 else{ 
								 	   $v=array_change_key_case($this->container);
								 	   $esito=isset($v[strtolower($x)]) && $v[strtolower($x)]!=='';
								 	   $nuova=$v[strtolower($x)];
								 	 }
				if (!$esito)  {
				                if($this->container) {
				                    $rev=array_flip($this->container);
				                    if ($this->casesens) 
    				                        {
    				                         if(isset($rev[$x])) $esito=-1;
    				                        }
        				              else {
        				                     $rev=array_change_key_case($rev);
        				                     if(isset($rev[strtolower($x)])) $esito=-1;
        				                    }
				                }           
				                 if($esito===false && $this->log) {
				                           $this->to_save=true;
            							   $this->container[$x]='';
            				              }
            				     if($esito==-1) $nuova=$x;
							  }
				//if (!$nuova) $nuova=$x;			  
			    }			
			
		
		$to_enc=$this->obj_encoding?$this->obj_encoding:self::$encoding;
		
		if((!isset($to_enc[0]) || $to_enc[0]!="ISO-8859-1") && isset($to_enc[0][1]) && $to_enc[0][1] && 
		                              (!isset($to_enc[0][2]) || !$to_enc[0][2]))  $nuova=iconv("ISO-8859-1", "{$to_enc[0]}//TRANSLIT", $nuova);      
		if ($nuova && $parole) $nuova=strtr($nuova,$parole);
		if((!isset($to_enc[0]) || $to_enc[0]!="ISO-8859-1") && isset($to_enc[0][1]) && $to_enc[0][1] && 
		                               isset($to_enc[0][2]) && $to_enc[0][2])  $nuova=iconv("ISO-8859-1", "{$to_enc[0]}//TRANSLIT", $nuova);
		
		return $nuova;
	}
	
	/**
	 * Imposta il charset generale
	 * 
	 * @param string $charset
	 * @param boolean $codifica_testi se true i testi vengono codificati altrimenti vengono tradotti prendendoli da dizionario 
	 * @param boolean $anche_parole se true anche le parole segnaposto vengono codificate
	 */
	 public static function set_charset($charset,$codifica_testi=true,$anche_parole=false){
	    self::$encoding=array(strtoupper($charset),$codifica_testi,$anche_parole);
	}
	
   /**
	 * Imposta il charset per l'istanza
	 * 
	 * @param string $charset
	 * @param boolean $codifica_testi se true i testi vengono codificati altrimenti vengono tradotti prendendoli da dizionario 
	 * @param boolean $anche_parole se true anche le parole segnaposto vengono codificate
	 */function set_obj_charset($charset,$codifica_testi=true,$anche_parole=false) {
	    $this->obj_encoding=array(strtoupper($charset),$codifica_testi,$anche_parole);
	    return $this;
	}
	
	 public function __destruct() {
		if ($this->to_save) 
				{$this->salva();
				 myAutoloader::clean_server_caches();
				} 	
	}
	
}