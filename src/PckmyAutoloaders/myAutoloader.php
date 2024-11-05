<?php
/**
 * Contains Gimi\myFormsTools\PckmyAutoloaders\myAutoloader.
 */

namespace Gimi\myFormsTools\PckmyAutoloaders;




/**
 * Effettua un'indicizzazione delle classi e le include in modo trasparente ogni
 * volta che serve, e se vogliamo un myOffice ed un mySelect senza includere nulla.
 *
 * <code>
 * myAutoloader('../caches')->get_Istanza('../myForms');
 * new mySelect('');
 * new myOffice();
 * </code>
 * @see myAutoloaders
 */
	
class myAutoloader{
 
/** @ignore */
    protected static 	$working=false,$mainEseguite=array(),$unloaded=null,$percorsi_aggiunti,$istanza=null,$registrata,$individua_classi='/(?:\babstract\s+class|\bfinal\s+class|\bclass|\binterface)\s+([\w°§]+\b)(?:[°§a-z0-9_ \n\r\t\,]*\{)/isS';
 
/** @ignore */
 protected 		 $dirSaves=array(),$percorsi_esclusi=array(),$indici=array(),$reloaded,$ignore=array();
 
/** @ignore */
 protected static 	$debug,$tentativi_ricerca,$RamCache=null;


/** @ignore */
 protected function __construct($dirSaves){
 $this->set_dir($dirSaves);
 self::$registrata=get_class($this).'::Loader';
 self::$tentativi_ricerca=1;
 $this->start();

 }
 
 
 protected static function file_put_contents($file,$data,$flags) {
 $out=@file_put_contents($file, $data,$flags);
 if($out=strlen($data)) @chmod($file,0660);
 return $out;
 }

 
/**
   * Imposta la directory, in cui andranno salvati i file temporanei
   * 
   * @param string $dirSaves
   */
 public function get_dir(){
 return $this->dirSaves;
 }

 
/**
   * Imposta la directory, in cui andranno salvati i file temporanei
   * 
   * @param string $dirSaves
   */
 public function set_dir($dirSaves){
 $dirSaves=realpath($dirSaves);
 if (!is_dir($dirSaves)) throw new \Exception("'$dirSaves' non è un directory valida");
 $this->dirSaves=$dirSaves;
 }
 
 public function ignore_class($class){
 $this->ignore[]=strtolower($class);
 return $this;
 }


/**
 * Attiva autoloading
 */
 public function start(){
 
#	spl_autoload_unregister('__autoload');
 	spl_autoload_register(self::$registrata);
 	self::$working=true;
 	if (is_callable('__autoload')) spl_autoload_register('__autoload');
 }

 
/**
   * Blocca autoloading
   */
 public function stop() {
 	spl_autoload_unregister('__autoload');
 	spl_autoload_unregister(self::$registrata);
 	self::$working=false;
 	if (is_callable('__autoload')) spl_autoload_register('__autoload');
 }

 
/**
    * Blocca autoloading
    */
 public static function is_enabled() {
 return self::$working;
 }
 
 public function set_debug($debug) {
 self::$debug=$debug;
 }

 
/**
   * Abilita/disabilita, l'autorefresh della cache (sconsigliato disabilitarla)
   *
   * @param boolean $abilitato
   */
 public function auto_refresh($abilitato) {
 if ($abilitato) self::$tentativi_ricerca=0;
 		 else self::$tentativi_ricerca=1;
 }


 public static function clean_server_caches(){
 		if(is_callable('accelerator_reset')) accelerator_reset();
 		if(is_callable('opcache_reset')) opcache_reset();
 		if(is_callable('xcache_clear_cache'))xcache_clear_cache();
 }


	
/*** @ignore **/
 protected static function file_write($nome,$content) {
 if(!is_dir(dirname($nome))) @mkdir(dirname($nome),0770,true);
 $f=@fopen($nome,'c+');
 if($f) {
	 if(!flock($f, LOCK_EX)) @fclose($f);
                	 else {
                    	 fwrite($f,$content);
                    	 ftruncate($f,strlen($content));
                    	 flock($f,LOCK_UN);
                    	 @fclose($f);
                    	 if(sha1($content)!=@sha1_file($nome)) @unlink($nome);
                    	 return true;
                    	 }
                	 }
  return false;
 }




 
/** @ignore */
 public static function leggi_file($file) {
 static $letti;
 if((is_array($letti) && key_exists($file,$letti))) return $letti[$file];
 if(is_file($file)) {
                     include($file);
                 	 $f=get_called_class().'_'.md5($file);
                 	 if(class_exists($f,false)) return $letti[$file]=$f::get_paths();
                   }
 	 
//return $letti[$file]=array('chk'=>null,'classi'=>array());
 }


 
/** @ignore */
  public static function build_dir($dir,$percorso,$save=false) {
 $percorso=pathinfo ($percorso);
 $percorso['dirname']=explode('/',str_replace('\\','/',$percorso['dirname']),2);
 $percorso=str_replace('/',DIRECTORY_SEPARATOR,$dir.DIRECTORY_SEPARATOR.$percorso['dirname'][1].DIRECTORY_SEPARATOR.$percorso['filename']);
 if($save && !is_dir($percorso) ) @mkdir($percorso,0770,true);
 return $percorso;
 }

 
/** @ignore */
  public static function scrivi_file($file,$info) {
     $f=basename(get_called_class()).'_'.md5($file);
 	 $testo='<'."? namespace Gimi\myFormsTools\PckmyAutoloaders; abstract class $f{static function get_paths(){return ".var_export($info,1)."; }}?".'>';
 	 if(!self::file_write($file,$testo)) return;
 }



 
/** Restituisce l'istanza della classe, essendo un singleton l'stanza e' sempre la stessa
  *
  * @param string $dirSaves percorso in cui salvare i file di indicizzazione
  */

  public static function get_Istanza($dirSaves) {
 	 if (self::$istanza!==null) {if (is_dir($dirSaves)) self::$istanza->set_dir($dirSaves);
 	 							 return self::$istanza;
 	 							 }
 else return self::$istanza=new myAutoloader($dirSaves);
 }


 
/**
   * Indicizza le classi presenti in un percorso, puo' essere dir o file
   *
   * @param string $percorso          percorso in cui cercare script da indicizzare
   * @param array $percorsi_esclusi   array si percorsi da non indicizzare, se omesso non si esclude niente
   * @param int $profonditaMax        numero max di sottocartelle in cui cercare, se omesso tutte
   * @param boolean $forzaReload      se true tutti gli script vengono rianalizzati
   * @return boolean/istanza          se percorso errato restituisca falso altrimente il puntatore all'istanza
   */
  public function add_percorso($percorso,$percorsi_esclusi=array(),$profonditaMax=0,$forzaReload=false) {
 $percorso=realpath($percorso);
 if ($percorso && !(is_array(self::$percorsi_aggiunti) && key_exists($percorso,self::$percorsi_aggiunti)) ) 
    {self::$percorsi_aggiunti[$percorso]=func_get_args();
 	 if (stripos($percorso,$this->dirSaves)===0) return $this;
 	 $nome_file=static::build_dir($this->dirSaves.'/_'.str_replace('\\',DIRECTORY_SEPARATOR,get_class($this)),$percorso).'.php';
 	 
     if (!(is_array($this->indici) && key_exists($percorso,$this->indici)) || !is_array($this->indici[$percorso])) $this->indici[$percorso]=self::leggi_file($nome_file);
     if (!(is_array($this->indici) && key_exists($percorso,$this->indici)) || !is_array($this->indici[$percorso]) || $forzaReload)
                         {if (is_array($percorsi_esclusi)) 
                                 foreach ($percorsi_esclusi as &$dirfile) {
                                                                         $dirfile=realpath($dirfile);
                                                                         if ($dirfile) $this->indici[$percorso]['esclusi'][$dirfile]=true;
                                                                         }
                         
                        // $this->indici[$percorso]['profondita']=$profonditaMax;
                         $this->scanClass($percorso,$this->indici[$percorso]['esclusi'],$this->indici[$percorso]['classi'],$profonditaMax,1,$forzaReload);
                         $this->reloaded[$percorso]=true;
                         }
                        
     } 
 return $this;
 }

 
 protected static function presetUnloaded(){
 	 if(is_array(self::$unloaded)) return self::$unloaded;
 	 $file=self::$istanza->get_dir().'/Unloaded_'.sha1(serialize(array_keys(self::$istanza->get_indici()))).'.txt';
 	 if(!is_file($file)) return self::$unloaded=array();
 	 else {self::$unloaded=explode("\n",@file_get_contents($file));
 	 if(!self::$unloaded) return self::$unloaded=array();
 	 }
 	 return self::$unloaded;
 }
 
 protected static function addUnloaded($classe){
 	
//die( self::$istanza->get_dir());
 	self::$unloaded[]=$classe;
 	$file=self::$istanza->get_dir().'/Unloaded_'.sha1(serialize(array_keys(self::$istanza->get_indici()))).'.txt';
 	if(!is_file($file) && isset($_SERVER['REQUEST_URI'])) self::file_put_contents($file,"{$_SERVER['REQUEST_URI']}\n",FILE_APPEND);
 	self::file_put_contents($file,"$classe\n",FILE_APPEND);
 }
 
 
/**
    * metodo statico invocato ad ogni new di classi non definite
    * @ignore
    * @param string $classe
    */
  public static function Loader($classe){
    # echo $classe,'<br>'; 
        if (is_callable('__autoload')) {@__autoload($classe);
 										if (class_exists($classe,0) || interface_exists($classe,0)) return;
 										}
 		if(stripos($classe, 'codesniffer')!==false) return false;
 										 
 		if(!is_array(self::$unloaded)) self::presetUnloaded();
 		$classe=trim(strtolower($classe));
 		if (in_array($classe,self::$unloaded)) return false;
 		
//echo "$classe?<br>";
 	 try { self::$istanza->esegui_classe($classe,self::$tentativi_ricerca);
 	 	 } catch (\Exception $e)
 	 		 {self::addUnloaded($classe);
			 if(self::$debug) self::file_put_contents(dirname(__FILE__).'/notAutoloaded.log',date("d/m/Y H:i:s")."\t$classe\t$_SERVER[REQUEST_URI]\t{$e->getMessage()} riga {$e->getLine()} script {$e->getFile()}\n", FILE_APPEND);
 	 		 }
 }


 
/**
    * Esecuzione effettiva della classe
    *
    * @param string $classe
    * @ignore
    */
 protected function esegui_classe($classe,$tentativi=0) {
 if(in_array(strtolower($classe),$this->ignore)) return Null;
 $info_classe=$this->cerca_classe($classe,$tentativi);
	 include_once($info_classe['file']);
 $variabili_definite=get_defined_vars(); 
//se per caso c'è un main eventuali variabili definite vengono "globalizzate" non è il massimo,
 
//ma il main non ci dovrebbe essere
 if ($variabili_definite) foreach ($variabili_definite as $v=>&$val)
 					if ($v!='info_classe'){ global $$v;
 											 $$v=$val;
 											}
 }

 
/**
   * Cerca la classe negli indici
   *
   * @param string $nome_classe
   * @ignore
   * @return string nome del file in cui e' definita
   */
 protected function cerca_classe($nome_classe,$tentativo=0){
 
 $out=array();
 if ($this->indici)
 foreach ($this->indici as $percorso=>&$percorsi) {
     if(isset($percorsi['classi'][$nome_classe]))
                 {
                 foreach ($percorsi['classi'][$nome_classe] as $percorsoFile=>&$chk)
                 {
                //se il chk è stato annullato o il file non c'è più elimina questa classe dell'indice... le altre verranno eliminate gradualmente
                 if (!$chk || !is_file($percorsoFile))
                                             	{unset($percorsi['classi'][$nome_classe][$percorsoFile]);
                                             	 $this->reloaded[$percorso]=true; 
                                            //se il file non esiste più lo toglie dall'array e ne segna il salvataggio
                                             	}
                                         else
                                             	{$chk_attuale=self::calcola_chk($percorsoFile); 
                                                //calcola il chk del file corrente
                                                 if ($chk!=$chk_attuale){
                                                                     	 $chk=null; 
                                                                         // setto preventivamente check di questa classe a null
                                                                 	      foreach (array_keys($this->estrai_classi($percorsoFile)) as $classe_estratta) $percorsi['classi'][$classe_estratta][$percorsoFile]=$chk_attuale;
                                                                 						 $this->reloaded[$percorso]=true; 
                                                                                // segna il salvataggio
                                                                 						 
                                                                            //dopo refresh delle classi di questo file chk potrebbe ancora essere null,
                                                                 						 
                                                                          //perchè	la classe è stata eliminata dal file, alla prox chiamata verrà definitivamente tolta dall'indicizzazione
                                                                 	      }
                                                $out[$chk_attuale]=array('file'=>$percorsoFile,'chk'=>$chk_attuale);
                                                }
                 }
     }
 }
 if (count($out)) ksort($out);
 	elseif ($tentativo==1) throw new \Exception("Classe $nome_classe non trovata");
 		else { 
 		    //se non trova la classe rianalizza tutti i percorsi forzando il reloading e riesegue questo metodo un'ultima volta
 		    foreach (self::$percorsi_aggiunti as $parametri){
     			if(!isset($parametri[0])) $parametri=array(null,null,null);
     			if(!isset($parametri[1])) $parametri[]=null;
     			if(!isset($parametri[2])) $parametri[]=null;
 		        $this->add_percorso($parametri[0],$parametri[1],$parametri[2],true);
 		    }
 		   return $this->cerca_classe($nome_classe,1);
 		 }

 return array_pop($out);
 }

 
/**
   * Calcola un check per la verifica della versione del file
   *
   * @param string $file
   * @ignore
   * @return string
   */
 protected static function &calcola_chk($file) {
 	 static $calcolati; 
//cache interna dei chk calcolati
 	 if (!(is_array($calcolati) && key_exists($file,$calcolati))) $calcolati[$file]=filemtime($file);
//.'|'.filesize($file);
 	 return $calcolati[$file];
 }

 
/** @ignore */
 static function &minimizza_codice($src) {
 	static $prec;
 	if ((is_array($prec) && key_exists($src,$prec)) && $prec[$src]) return $prec[$src];
 
//if ($codicePHP=@php_strip_whitespace($src)) return $codicePHP;
 $codicePHP=@file_get_contents($src);
 	$codicePHP=@preg_replace('#([^/]{1})/\*.+\*/#sUS','\1',$codicePHP);
 	self::file_put_contents(dirname(__FILE__).'/minimizzaAutoloaded.log',date("d/m/Y H:i:s")."\t$src\r\n", FILE_APPEND);

 	if (!$codicePHP) return '';
	$i=0;
 	do {
	$posApice=$i;
	
//$Z=substr($codicePHP,$i);
	$primoApice=false;
	while (($primoApice=strpos($codicePHP,"'",$posApice))!==false) {
		 $retroverifica=$primoApice-1;
		 $nslash=0;
		 while ($codicePHP[$retroverifica--]=='\\') $nslash++;
					 if ($nslash%2) $posApice=$primoApice+1;
					 	 else break;
					}


	$posVirgolette=$i;
	$primoVirgolette=false;
	while (($primoVirgolette=strpos($codicePHP,'"',$posVirgolette))!==false) {
					 $retroverifica=$primoVirgolette-1;
		 $nslash=0;
					 while ($codicePHP[$retroverifica--]=='\\') $nslash++;
					 if ($nslash%2) $posVirgolette=$primoVirgolette+1;
					 		 else break;
					}

	$inizioStringaSbagliata=false;
	$inizioStringa=false;
	$fineStringa=false;
	if ($primoVirgolette===false && $primoApice===false) $inizioStringa=strlen($codicePHP);
				else {
					 if ($primoVirgolette===false || ($primoApice!==false && $primoApice<$primoVirgolette))
									 {
									 $inizioStringa=$primoApice;
									 while (($primoApice=strpos($codicePHP,"'",$primoApice+1))!==false)
					 						{
					 						 $retroverifica=$primoApice-1;
		 						 $nslash=0;
					 						 while ($codicePHP[$retroverifica--]=='\\') $nslash++;
					 						 if ($nslash%2==0) break;
									 	}
					 				 if ($primoApice!==false) $fineStringa=$primoApice+1;
									 }
								else {
									 $inizioStringa=$primoVirgolette;
									 while (($primoVirgolette=strpos($codicePHP,'"',$primoVirgolette+1))!==false)
									 	{
					 						 $retroverifica=$primoVirgolette-1;
		 						 $nslash=0;
					 						 while ($codicePHP[$retroverifica--]=='\\') $nslash++;
					 						 if ($nslash%2==0) break;
									 	}
					 				 if ($primoVirgolette!==false) $fineStringa=$primoVirgolette+1;
									 }
					 }


	$posCommentoLinea=$i;
	
//echo "\nAnalisi commenti di: ".substr($codicePHP,$posCommentoLinea,$inizioStringa-$posCommentoLinea+1);
	do {if (($c1=strpos($codicePHP,'//',$posCommentoLinea))===false) $c1=$inizioStringa+1;
	 	if (($c2=strpos($codicePHP,'#', $posCommentoLinea))===false) $c2=$inizioStringa+1;
		$primoCommentoLinea=min($c1,$c2);
		if ($primoCommentoLinea>=$inizioStringa) break;
					else{
						if (($primoNL=strpos($codicePHP,"\n",$primoCommentoLinea+1))===false)
									 $primoNL=strlen($codicePHP);
				 			 elseif($primoNL>$inizioStringa) $inizioStringaSbagliata=true;

						if (($primoCR=strpos($codicePHP,"\r",$primoCommentoLinea+1))===false)
									 $primoCR=strlen($codicePHP);
							 elseif($primoCR>$inizioStringa) $inizioStringaSbagliata=true;


						$da_togliere=min($primoCR,$primoNL)-$primoCommentoLinea+1;
						
//echo "\nELIMINATO:" .substr($codicePHP,$primoCommentoLinea,$da_togliere);

						$codicePHP=substr_replace($codicePHP,'',$primoCommentoLinea,$da_togliere);
						$inizioStringa-=$da_togliere;
						$fineStringa-=$da_togliere;
						$posCommentoLinea=$primoCommentoLinea;
						}
	 } while (true);
	 $tolti=null;
	 if (!$inizioStringaSbagliata)
	 	{
	 	$porzione= substr($codicePHP,$i,$inizioStringa-$i);
	 do{
	 		 $porzione=str_replace(array('  ',"\t","\r","\n"),array(' ',' ',' ',' '),$porzione,$tolti);
	 	} while ($tolti);
	 	$codicePHP=substr_replace($codicePHP,$porzione,$i,$inizioStringa-$i);
	 	if ($fineStringa===false) break;
	 	$i=$fineStringa-($inizioStringa-$i-strlen($porzione));
	 	}

	 if ($i===false) break;
	} while ($i<strlen($codicePHP) && $inizioStringa<strlen($codicePHP));

	$prec=array($src=>trim((string) $codicePHP));
	return $prec[$src];
 }
 
 
/**
  * @ignore
  */
 protected function &tokenizer($file) {
    $tokens=token_get_all(file_get_contents($file));
    foreach ($tokens as $k=>&$v) {
                         if(!is_array($v)) $v=array(-1,$v);
                         if($v[0]==T_COMMENT || $v[0]==T_DOC_COMMENT) {unset($tokens[$k]);continue;}
                         if ($v[0]==T_WHITESPACE) $v[1]=' ';
                         unset($v[2]);
                         } 
    return $tokens; 
 }
 
 
 
/**
   * @ignore
   */
 protected function tokenizer_to_classi(&$tokens){
     
    //  ini_set('memory_limit',-1);
     $Class=0; 
     $bdy=''; 
     $out=array('classi'=>array(),'resto'=>array());
     if(!defined('T_TRAIT')) define('T_TRAIT',null);
     foreach ($tokens as $k=>&$v) {
                                 if($Class==0 && is_array($v) && in_array($v[0], array(T_ABSTRACT,T_CLASS,T_TRAIT,T_INTERFACE,T_FUNCTION,T_FINAL))) 
                                                         {$Class=1;
                                                          $bdy='';
                                                          $parentesi=null;
                                                         }
                                 if($Class==0) $out['resto'][]=$v;
                                            else { 
                                                 $bdy.=$v[1];
                                                 if($Class==1 && $v[0]==T_STRING) {$classe=$v[1]; $Class=2;}
                                                 elseif($Class==2)
                                                         { if(trim((string) $v[1]=='{')) $parentesi+=1; 
                                                                 elseif($v[0]==-1 && trim((string) $v[1]=='}')) $parentesi-=1;
                                                           if($parentesi===0)
                                                                     {$out['classi'][strtolower($classe)]=$bdy;
                                                                     $Class=0;
                                                                     $bdy='';
                                                                     }
                                                         } 
                                                 }
                                 unset($tokens[$k]);
                                 }
     return $out;
 }




 
/**
   * Estrae un array con le classi individuate nel file e relativo chk
   *
   * @param string $file
   * @param string $chk se valorizzato non viene calcolato ma si prende per buono
   * @ignore
   * @return array
   */
 protected function &estrai_classi($file,$forzaReload=false) {
    if(!is_callable('token_get_all') ) return $this->estrai_classi_notokenizer($file,$forzaReload); 
    static $cache;
    if(!$forzaReload && (is_array($cache) && key_exists($file,$cache))) return $cache[$file];
    $tokens=$this->tokenizer($file);
    $classi=$this->tokenizer_to_classi($tokens);
    $cache[$file]=&$classi['classi'];
    return $cache[$file];
 } 

 
 
 
 
/**
   * 
   * @ignore
   */
 protected function &estrai_classi_notokenizer($file,$forzaReload=false) {
 static $cache;
 if(!$forzaReload && (is_array($cache) && key_exists($file,$cache))) return $cache[$file];
 	 $classi=$matches=array();
 	 $sorgenteScript= &self::minimizza_codice($file);
 	 if (preg_match_all(self::$individua_classi,$sorgenteScript,$matches))
			foreach ($matches[1] as &$classe) $classi[strtolower($classe)]=$classe;
 	 $cache[$file]=$classi;
 return $cache[$file];
 }


 
/**
   * Cerca classi nei vari file
   * @ignore
   * @param string $percorso   		 percorso base in cui si ricerca
   * @param array  $percorsi_esclusi array con i percorsi da non indicizzare
   * @param array  $classi     		 array in cui vengono inserite le classi trovate man mano
   * @param int $profonditamax       max profondità nella ricerca delle sottodirectory
   */
 protected function scanClass($percorso,&$percorsi_esclusi,&$classi,$profonditamax,$profondita=1,$forzaReload=false) {
   if (is_dir($percorso)) $files=@scandir($percorso);
 	 				   else {$files=array(basename($percorso));
 	 						 $percorso=dirname($percorso);
 	 						 $profonditamax=1;
 	 						 }
    foreach ($files as &$file)
         {$percorsoCorrente=$percorso.DIRECTORY_SEPARATOR.$file;
         if($file!='.' && $file!='..' && !(is_array($percorsi_esclusi) && key_exists($percorsoCorrente,$percorsi_esclusi)))
                     {
                     if (stripos($percorsoCorrente,'.php',strlen($percorsoCorrente)-4)!==false ||
                    		 stripos($percorsoCorrente,'.inc',strlen($percorsoCorrente)-4)!==false)
                    		 	 {$chk=self::calcola_chk($percorsoCorrente);
                    		 	 $this->reloaded[$percorsoCorrente]=true;
                    		 	 foreach (array_keys($this->estrai_classi($percorsoCorrente,$forzaReload)) as $classe) $classi[$classe][$percorsoCorrente]=&$chk;
                                }
                     elseif ( ($profonditamax==0 || $profondita<$profonditamax) &&  is_dir($percorsoCorrente)) $this->scanClass($percorsoCorrente,$percorsi_esclusi,$classi,$profonditamax,$profondita+1,$forzaReload);
                     }
         }
 }

 
/** @ignore */
  public function __destruct() {
     if (is_array(@ob_get_status())) @ob_end_flush();
    //invocato sempre alla fine dell script quindi prima di salvare pulisce eventuali buffer
     if ($this->reloaded) 
         {	 $done=array(); 
             foreach ($this->indici as $percorso=>&$classi)
                foreach (array_keys($this->reloaded) as $percorso_file)
 			         if(!isset($done[$percorso]) && strpos($percorso_file, $percorso)===0)
 			                   {$done[$percorso]=true;
 			                    $to_write=static::build_dir($this->dirSaves.'/_'.get_class($this),$percorso).'.php';
 			                   // if(!is_file($to_write) || time()-filemtime($to_write)>60*30)
                                self::scrivi_file($to_write,$classi);
                                 //else @unlink(static::build_dir($this->dirSaves.'/_'.get_class($this),$percorso).'.php');
                                }
                
 			self::clean_server_caches();
 		 }
 }

  public function get_indici($indice=''){
     if($indice){if(key_exists($indice,$this->indici)) return $this->indici[$indice];
                 return array();
                 } 
     return $this->indici;
     }
}