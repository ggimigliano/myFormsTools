<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\myJQueryUI.
 */

namespace Gimi\myFormsTools\PckmyJQuery;

use Gimi\myFormsTools\PckmyFields\myField;
use Gimi\myFormsTools\PckmyFields\myUUID;
 


/**
 * Classe di utilità per produrre codice delle classi {@link http://jqueryui.com/}
 */
	
abstract class myJQueryUI extends myJQuery{
	/** @ignore */
    protected $myJQVarName,$costruito,$myObject,$opzioni=array(),$eventi=array(),$widget,
	          $css_font_class=array('.ui-dialog-content','.ui-widget-content','.ui-dialog'),$css_font_default='{font-size:12px !important}';
	/** @ignore */
	protected static $css_fonts=array(),$tema;

	/** @ignore */
	final public function __construct($id='') {
		if(!$id) $id="#X".str_replace('-','',myUUID::v4());
		parent::__construct($id);
		$this->set_myJQVarName($this->get_id_istanza());
		if(!self::$tema) self::set_tema('base');
	    static::init($this->widget);
	    self::add_src(self::$percorsoMyForm."jquery/ui/jquery-ui.min.js");
	}
	
	
	/** @ignore */
	final static function get_tema(){
	    if(!self::$tema) self::set_tema('base');
	    return self::$tema;
	}

	/** @ignore */
	final function get_istance_defaults($nome=''){
		$out=NEW \stdClass();
		if($nome) {
		    if(!isset($this->opzioni[$nome])) $out->opzioni=array();
			                             else $out->opzioni=(array) $this->opzioni[$nome];
			 if(!isset($this->eventi[$nome]))$out->eventi=array();
			                             else $out->eventi=(array) $this->eventi[$nome];
		}
		else {
			$out->opzioni=(array) $this->opzioni;
			$out->eventi=(array) $this->eventi;
		}
		return $out;
	}
	

	/** @ignore */
	 public function __set($attr,$val){
		$this->set_option($attr,$val,spl_object_hash($this));
	}
	
	/** @ignore */
	function &__get($attr){
	    return $this->opzioni[$attr][spl_object_hash($this)];
	}
	
	/**
	 * @return @ignore
	 */
    public function prepara_codice(){
	    $this->set_common_defaults($this->get_common_defaults());
	    if(!isset(self::$css_fonts[$this->widget]) && $this->css_font_default) $this->set_css_font($this->css_font_default);
	    if(!$this->costruito)
                	      {  
                	        $this->set_istance_defaults();
                	        $this->construct();
                	        $this->costruito=true;
                	      }
	   return $this;    
	}
	
	
	/**
	 * Restituisce il codice senza attendere il caricamento della pagina per il lancio
	 */
	 public function get_codice_immediato(){
	    $this->prepara_codice();
	    return self::get_src()."<script type='text/javascript'>
	                               //<!--
	                           ".(trim((string) $this->get_common_code())."\n".trim((string) $this->get_code()))."
	                               //--></script>";
	}

	
   
	/** @ignore */
	final public function get_widget(){
		return $this->widget;
	}


	/**
	 * Imposta un'opzione dell'istanza
	 * @param string $option nome opzione
	 * @param string $args   parametri opzione
	 * @param string $k      eventuale chiave
	 */
	final function set_option($option,$args,$k='') {
		if(!$this->costruito) {
			if($k) 	 $this->opzioni[$option][$k]=$args;
				else $this->opzioni[$option][]=$args;
			return $this;
		}
		$this->add_code("{$this->JQid()}.{$this->widget}(\"option\",\"$option\",".(self::quote($args)).")");
		return $this;
	}

	/**
	 * Imposta font css per l'elemento
	 * @param string $css css relativi ai font
	 */
	 public static function set_css_font($css){
		$css=trim((string) $css);
		if($css[0]!='{') $css="{ $css }";
		self::$css_fonts[get_called_class()]=$css;
	}

	/**
	 * Nome del tama standard da usare
	 * @param string $css
	 */
	 public static function set_tema($css='css'){
		self::$tema=$css;
		if(strpos($css,'/')===false) $css='/'.myField::get_MyFormsPath()."jquery/ui/themes/$css/jquery-ui.min.css";
		self::add_css($css,'tema-UI');
	}
	
	

	
	/**
	 * Impostra codice da eseguire per un certo evento
	 * @param string $evento
	 * @param string $val  	 codice da eseguire
	 * @param string $k    	 chiave
	 * @param string $segnatura dell'evento (es. function(ui,event)) se omesso usa {@link myJQueryUI::get_event_sign()}
	 * @return myJQueryUI
	 */
	 public function set_event($evento,$val,$k='',$segnatura=''){
		$class=get_class($this);
		if(!$segnatura) $segnatura=trim((string) $class::get_event_sign($segnatura));
	
		$event=new \stdClass();
		$event->segnature=$segnatura;
	
		$event->code=$val;
		if(!isset($this->eventi[$evento]['__START__'])) $this->eventi[$evento]['__START__']=array();
		if(!isset($this->eventi[$evento]['__END__']))   $this->eventi[$evento]['__END__']=array();
		if($k) 	 $this->eventi[$evento][$k]=$event;
			else $this->eventi[$evento][]=  $event;
		return $this;
	}

	/**
	 * @ignore
	 * @return string
	 */
	protected  function build_options(){
	    $opt=$this->get_istance_defaults();
		$opts=array();
		foreach ($opt->opzioni as $k=>&$v) {
			if(is_array($v))
				{
				foreach ($v as &$w) $w=self::quote($w);
				$v=implode(";\n",$v);
				}
			if(trim((string) $v)==='') $v="''";
			$opts[]="$k:$v";
		}
	   
		foreach ($opt->eventi as $k=>$v) {
			$segnature='';
			if(is_array($v))
				{$temp=$v['__END__'];
			     unset($v['__END__']);
			     if($temp) $v[]=$temp;
			   
			     $codes=array();
				 foreach ($v as &$w)
					{ if(!$segnature && isset($w->segnature)) $segnature=$w->segnature;
				      $codes[]=(isset($w->code)?$w->code:null);
					}
						
				$v=$this->get_event_code($segnature,implode(";\n",$codes),true);
				}
			if(!trim((string) $v)) continue;
			$opts[]="$k:$v";
		}
	    return "{".implode(',',$opts)."}";
	}
	

	/**
	 * @ignore
	 */
	protected  function construct(){
		$this->costruito=true;
		$opts=$this->build_options();
		if($opts!==false) {
		      if(!$opts)	$this->add_code("{$this->myJQVarName()}={$this->JQid()}.{$this->widget}()",null);
		               else $this->add_code("{$this->myJQVarName()}={$this->JQid()}.{$this->widget}($opts)",null);
		      }
		$css='';
		if($this->css_font_class) {
			             foreach ($this->css_font_class as $class) $css.=$class.self::$css_fonts[get_called_class()]." ";
			             $codice=myJQuery::get_add_style($css,true);
			             $pred=self::get_common_codes('commonJQUI');
			             if(strpos($pred,$codice)===false)  $pred.=";".$codice.";";
			             self::add_common_codes($pred,'commonJQUI');
		              }

		return $this;
	}

    /**
     * Restituisce il nome della variabile globale Javascript associata al widget 
     * @return string
     */
     public function myJQVarName(){
         return $this->myJQVarName; 
	}
	
	/**
	 * Restituisce il nome della variabile globale Javascript associata al widget
	 * @return string
	 */
	public function set_myJQVarName($id){
	    return $this->myJQVarName="myJQVar_{$id}";
	}
	
	
    /**
     * @ignore
     */
	 public function get_common_code($usa_auto_avvio=true){
		 return   myJQuery::get_common_codes('commonJQUI',$usa_auto_avvio).
		          myJQuery::get_common_codes($this->widget,$usa_auto_avvio);
	}
	

	/** @ignore */
      public function add_common_code($code,$k=''){
        return myJQuery::add_common_codes($code,$this->widget, $k);
	}
	

	/**
	 *
	 * @param string $metodo
	 * @param string $code
	 * @param boolean $solo_funzione
	 * @return string
	 */
	final function get_event_code($metodo,$code,$solo_funzione=false){
		$codice="$metodo {\n{$code}\n}";
		if($solo_funzione) return $codice;
		return "{$this->JQid()}.{$this->widget}({ $metodo: $codice })\n";
	}



	/**
	* Imposta le chiamate statiche es. caricamento degli script .js
	* @param string $widget e' il nome del widget jQuery implementato
	*/
	  static protected function init( &$widget){}


	/**
 	* Impostare (tramite {@link self::add_code()}) qui commandi specifici della singola istanza
 	*/
	 public abstract  function set_istance_defaults();


	/**
	* Restituisce un array con le impostazioni di default comuni a tutte  le istanze JQ di questa classe
	* L'array e' associativo con chiave il nome dell'attributo/evento
	* il valore puo' essere un valore (se attributo)
	* @return array
	*/
	 public abstract  function get_common_defaults();

	/**
	 * Restituisce il codice JQuery per l'iniziualizzazione del widget sulla base dell'arrai in input
	 * @param array $commons e' l'array prodotto da {@link myJQueryUI::get_common_defaults()}
	 */
	 public abstract  function set_common_defaults(array $commons);

	/**
	 * Restituisce la "segnatura" della funzione dell'evento
	 * @param string $nome_evento
	 */
	 public static function get_event_sign($metodo){
		return 'function(event, ui)';
	}


}