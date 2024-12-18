<?php
/**
 * Contains Gimi\myFormsTools\PckmyAjax\RisultatoMyRicercaAjax.
 */

namespace Gimi\myFormsTools\PckmyAjax;


use Gimi\myFormsTools\PckmyFields\myTag;
use Gimi\myFormsTools\PckmyUtils\myCharset;

abstract class RisultatoMyRicercaAjax {
	/** @ignore */
protected $valore,$parametri,$campo,$utf8=false,$json=false;
	/**
	 * 
	 * @ignore	 
	 **/
	   public function __construct() {
	  	$this->parametri=$_POST;
	  	foreach ($this->parametri as $k=>$v)
	  									if(!is_array($v)) {
												$this->campo=$k;
												$this->valore=$v;
												break;
											  	}
		if (is_array($this->valore)) $this->valore=array_pop(array_values($this->valore));
		foreach ($this->parametri as &$info)
		                  if(is_array($info)){$info['desc']=stripslashes($info['desc']);
											  $info['val']=stripslashes($info['val']);
											  }
	  }
	  
	 /**
	   * 
	   * Imposta la codifica json dell'output
	   * @param boolean $sino
	   */
	   public function usaJSON($sino=true){
	  	$this->json=$sino;
	  }
	  
	  /**
	   * 
	   * Imposta la codifica utf8 dell'output
	   * @param boolean $sino
	   */
	  function usaUTF8($sino=true) {
	  	 $this->utf8=$sino;
	  }

	 abstract function &Calcolo();

	 /**
	  * 
	  * @ignore
	  */
     final function __destruct(){
     	$corpo='';
     	if ($opzioni=$this->Calcolo())
     		    {if (isset($_GET['chiudi'])) $corpo="<li id=\"".myTag::htmlentities($this->valore)."\" style='text-align:center' >$_GET[chiudi]</li>";
     		      if($this->utf8) {
     		      		$new=array();
				 		foreach ($opzioni as $testo=>&$valore) 
     		     				$new[myCharset::utf8_encode($testo)]=myCharset::utf8_encode($valore);
     		    	    $opzioni=&$new;
     		      		}		   
				 if($this->utf8) $charset='UTF-8';
				 			else $charset='ISO-8859-1';
				 if($this->json) $type="application/json";						
				 			else $type="text/html";
     			 if($this->utf8) header ("Content-Type: $type;charset=$charset");
          		 if($this->json) $corpo=json_encode($opzioni);
          		 			else { 
          		 				    foreach ($opzioni as $testo=>$valore) 
				 					$corpo.="<li id=\"".myTag::htmlentities($valore)."\">$testo</li>";
				 		 			$corpo="<ul>$corpo</ul>";
          		 				}
          		 header("Content-Length: ".strlen($corpo));
          		 echo $corpo;
          		 exit;			
     		    }
     }
}