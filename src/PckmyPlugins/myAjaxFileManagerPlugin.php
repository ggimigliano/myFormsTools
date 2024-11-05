<?php
/**
 * Contains Gimi\myFormsTools\PckmyPlugins\myAjaxFileManagerPlugin.
 */

namespace Gimi\myFormsTools\PckmyPlugins;


use Gimi\myFormsTools\PckmyFields\myField;



/**
 * Applicabile a myEditor o anche myText per ottenere un percorso e/o modificare filesystem
 *
 */
	
class myAjaxFileManagerPlugin {
/** @ignore */
private $ajaxmanpar=array();
public $START_CODE,$END_CODE;

			 public function __set($k,$v){
				$this->ajaxmanpar[$k] =(is_array($v)?$v:self::cleanslashes($v));
			}


			 public function __get($k){
				return $this->ajaxmanpar[$k];
			}

			private static function cleanslashes($x){
			    $n=null;
			    $x=str_replace('\\','/',$x);
			    $inizio='';
			    if(strpos($x,'//')===0) $inizio='/';
			    do{
			        $x=str_replace('//','/',$x,$n);
			    }while ($n>0);
			    return $inizio.$x;
			}
			
    		 public function build_params(){
    		            $pars=$this->ajaxmanpar;
    		            $pars['codice']=array($this->START_CODE,$this->END_CODE);
    		         	$p=base64_encode(serialize($pars));
    		  			return "check=".sha1($p.':'.sha1($p).':'.$p)."&par=$p";
    		}
		
    		
    		 public function get_params(){
    		   $p=unserialize(base64_decode($_GET['par']));
    		   if($_GET['check']!=sha1($_GET['par'].':'.sha1($_GET['par']).':'.$_GET['par'])) return false;
    		   return $p;
    		}

		  /**
		   * restituisce la url da usare per lincare il plugin
		   */
		   public function get_url_plugin(){
		   	return "/".myField::get_MyFormsPath().'js/tinymce/plugins/filemanager/plugin.min.js';
		  }

		   public function get_url(){
		      return "/".myField::get_MyFormsPath().'js/tinymce/plugins/filemanager/dialog.php?'.$this->build_params();
		  }
		  
		  
		  /*
		  function get_code($selettore){
		      myJQuery::add_src('jquery/fancybox/jquery.fancybox.pack.js');
		      myJQuery::get_add_css_file('jquery/fancybox/jquery.fancybox.css');
		      $j=new myJQuery($selettore);
		      $j->add_code("{$j->jqid()}.fancybox({	'width': 900,'height'	: 800,	'type': 'iframe',  'autoScale' : false });");
		      return $j;
		  }*/
		  
		  public static function get_relative($percorso){
		      if(strpos($percorso,'/')!==0) $percorso="/$percorso";
		      $percorso=str_replace('\\', '/',$percorso);
		      
		      $dirPlugin=explode(realpath($_SERVER['DOCUMENT_ROOT']),realpath(str_replace('\\', '/',dirname(__FILE__)).'/../js/tinymce/plugins/filemanager'),2);
		      $dirPlugin=str_replace('\\','/',$dirPlugin[1]);
		  
		      	
		      $partiDir=substr_count($dirPlugin,'/');
		     
		      if(strpos($percorso,'//')!==0 && $partiDir) $percorso= implode('',array_fill(0,$partiDir,'/..')).$percorso;
		      if(strpos($percorso, '/..')===0) $percorso=".$percorso";
		      return $percorso;
		  }
		  
		  
		/**
		 * @param string $percorso percorso della direcrtory da gestire
		 */
		 public function __construct($percorso,$percorso_anteprime='') {
		        if(!preg_match('@/$@', $percorso)) $percorso.='/';
		        $this->upload_dir  =$percorso;
		        $this->current_path=self::get_relative($percorso).'/';
		        if(!$percorso_anteprime)  $this->current_path;
		                          elseif (!preg_match('@/$@', $percorso_anteprime)) $percorso_anteprime.='/'; 
		        $this->thumbs_base_path=$percorso_anteprime;
		    	}


}