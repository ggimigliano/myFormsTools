<?php
/**
 * Contains Gimi\myFormsTools\PckmyAutoloaders\myAutoloaders.
 */

namespace Gimi\myFormsTools\PckmyAutoloaders;




/**
 * E' una classe di utilità che sceglie il tipo di autoloader migliore a seconda della configurazione del server
 * In automatico include le myForms
 * <code>
 * myAutoloaders::get_Istanza() //solo questo carica l'indicizzazione delle myForms nella modalità migliore
 *					->add_percorso('./include',2) //include tutte le classi sotto include limitandosi a 2 sottodirectori
 *					->add_percorso('./include/classi.php'); //include le classi in classi.php
 *
 * </code>
 */
	
abstract class myAutoloaders{

/** @ignore */
protected static $modalita,$istanza=false;

	
/**
	 * Forza l'uso della myAutoloader che prevede inclusione secca dei file
	 * per farne meglio il debug
	 *
     * 
	 * @param '1'|'2' $modalita se 1 si usa un myAutoloader semplice (in pratica si sfrutta solo l'indicizzazione delle classi)
	 * 						   2 si usa la l'Autoloader scelto ma in modalità di debug
	 */
	public static function set_debugging($modalita=1) {
	 self::disable_e_strict_warning();
		self::$modalita=$modalita;
	}

	public static function get_debugging() {
	 self::disable_e_strict_warning();
	 return self::$modalita;
	}
	
	
	public static function disable_e_strict_warning(){
	 static $done;
	 if(!$done) set_error_handler('Gimi\\myFormsTools\\PckmyAutoloaders\\myAutoloaders::error_handler',E_WARNING);
	 $done=TRUE;
	}
	
	
   public static function error_handler($errno , $errstr, $errfile , $errline ){
  	 if((error_reporting() & $errno)===0) return;
	 switch ($errno) {
					 case E_WARNING:
					 if(preg_match('@Declaration of .+ should be compatible with@',$errstr)) return;
					 $err="WARNING";
					 break;
					 case E_NOTICE: $err="NOTICE";break;
					 case E_DEPRECATED: $err="DEPRECATED";break;
					 case E_ERROR: $err="ERROR";break;
					 case E_STRICT: $err="STRICT";break;
					 default:return;
					 }
	 $err=ucfirst(strtolower($err));
	 if (intval(ini_get("display_errors"))===1 ||  strtolower(ini_get("display_errors"))=='on') echo "<br /><b>$err:</b> $errstr in <b>$errfile</b> on line <b>$errline</b><br />\n";
 	 if (ini_get('log_errors')) error_log("$err: $errstr in $errfile on line $errline\n" );
 
	}
	
	


	
/**
	 * Restituisce l'istanza tra myAutoloader  
	 * Tutt le myForms sono automaticamente comprese.
	 *
	 * @param  string $percorso
	 * @return myAutoloader
	 */
	public static function get_Istanza($percorso=''){
		static $percorso_prec;
		
        if (!$percorso) $percorso=$percorso_prec;
        if (!$percorso) $percorso="{$_SERVER['DOCUMENT_ROOT']}/myAutoloader";
		if (!is_dir($percorso)) mkdir($percorso,0770,true);
		$percorso_prec=$percorso;
	    $istanza=myAutoloader::get_Istanza($percorso);
		$istanza->set_debug(self::$modalita);
		$istanza->auto_refresh((self::$modalita?0:1));
		
	    if (!defined('__MYFORM_DATACACHE__')) 
	                                       {define('__MYFORM_DATACACHE__',dirname(__FILE__).'/../datacache');
                                            if (!is_dir(__MYFORM_DATACACHE__)) mkdir(__MYFORM_DATACACHE__,0770,true);
                                           }
		return $istanza;
	}
}