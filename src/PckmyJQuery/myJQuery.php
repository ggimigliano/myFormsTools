<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\myJQuery.
 */

namespace Gimi\myFormsTools\PckmyJQuery;


use Gimi\myFormsTools\myCSS;
use Gimi\myFormsTools\PckmyFields\myField;
use Gimi\myFormsTools\PckmyUtils\myCharset;



/**
 *
 *
 * Questa classe si prefigge di essere uno strumento per sviluppare "velocemente" codice JQuery o quantomeno in modo piu' PHP-friendly
 * es.
 * Questo jQuery
 * <code>
 *   <script type="text/javascript" src="jquery.js"></script>
 *   <script type="text/javascript">
 *   $(document).ready(function(){
 * 					 $("p").slideUp();
 * 					 $("p").slideDown();
 *   			 });
 * </script>
 *
 *</code>
 *
 *Si potrà produrre con questo PHP
 *
 *<code>
 *<?
 * $p=new myJQuery('p');
 * $p->add_code($p->slideUp());
 * $p->add_code($p->slideDown());
 * echo $p;
 *?>
 *</code>
 * @author Gianluca Gimigliano
 *
 */
	
class myJQuery {
    /** @ignore */
    protected $id,$costruito=false,$id_istanza;
	/** @ignore */
	private $codice=array('__START__'=>array(),'__END__'=>'');
	/** @ignore */
	protected static $srcs,$istanze,$percorsoMyForm,$identificatore='$',$isIE,$isFirefox,$isChrome;
    public static $max_z_index=0;

	/** @ignore */
	private static function &getStaticVar(){
		if(!isset(myJQuery::$srcs['static'])) myJQuery::$srcs['static']=array();
		if(!isset(myJQuery::$srcs['static']['myJqueryJS'])) myJQuery::$srcs['static']['myJqueryJS']=array();
		return myJQuery::$srcs;
	}

	/** @ignore */
	 public function HTCpath($file){
		$c=parse_url($_SERVER['REQUEST_URI']);
		return substr(preg_replace('@[^/]+/@U','../',$c['path']),1).myField::get_MyFormsPath()."js/$file";
	}
	/** @ignore */
	public function __call($metodo,$args){
	     return "{$this->JQid()}.$metodo".self::encode_array($args,'()');
		 }

		 /** @ignore */
	 public static function __callStatic($metodo,$args){
	 	return self::$identificatore.".$metodo".self::encode_array($args,'()');
	}

	
	static function  isMSIE($forza=''){
	    $m=array();
	    if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/MSIE ([0-9\.]+)/',$_SERVER['HTTP_USER_AGENT'],$m) && $forza)   self::$isIE=$forza;
		if(self::$isIE) return self::$isIE;
		if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/MSIE ([0-9\.]+)/',$_SERVER['HTTP_USER_AGENT'],$m)) return self::$isIE=$m[1];
		return false;
	}


	/**
	 * Restituisce codice JQuery per ottenere la versione del browser (se IE)
	 * @return string
	 */
	static function  isMSIEJQ(){
		$var=self::JQvar();
		return "($var.browser.msie?parseInt($var.browser.version, 10):0) ";
	}

	/**
	 * Restituisce codice JQuery per ottenere la versione del browser (se FOX)
	 * @return string
	 */
	static function  isFirefoxJQ(){
		$var=self::JQvar();
		return "($var.browser.mozilla?parseInt($var.browser.version, 10):0) ";
	}

	/**
	 * Restituisce codice JQuery per ottenere la versione del browser (se Chrome)
	 * @return string
	 */
	static function  isChromeJQ(){
		$var=self::JQvar();
		return "($var.browser.webkit?parseInt($var.browser.version, 10):0) ";
	}

	static function  isFirefox($forza=''){
	    $m=array();
		if(preg_match('/Firefox\/([0-9\.]+)/i',$_SERVER['HTTP_USER_AGENT'],$m) && $forza)  self::$isFirefox=$forza;
		if(self::$isFirefox) return self::$isFirefox;
		if(preg_match('/Firefox\/([0-9\.]+)/i',$_SERVER['HTTP_USER_AGENT'],$m)) return self::$isFirefox=$m[1];
		return false;
	}

	static function  isChrome($forza=''){
	    $m=array();
		if(preg_match('/chrome\/([0-9\.]+)/i',$_SERVER['HTTP_USER_AGENT'],$m) && $forza)  self::$isChrome=$forza;
		if(self::$isFirefox) return self::$isFirefox;
		if(preg_match('/chrome\/([0-9\.]+)/i',$_SERVER['HTTP_USER_AGENT'],$m)) return self::$isChrome=$m[1];
		return false;
	}

	/** @ignore */
	private static function init_static(){
		if(!is_array(self::$istanze)) {
			self::$istanze=array();
			self::$percorsoMyForm="/".myField::get_MyFormsPath();
			self::add_src(self::$percorsoMyForm."jquery/jquery.min.js");
		    self::add_src(self::$percorsoMyForm."jquery/myJQUtils/myJQueryUtils.js");
			
		}
	}


	/** @ignore */
	private static function parse_style($style,$quotachiavi=false){
		$out=array();
		$classi=$attr=array();
		if(preg_match_all('/([^\\{]+)\\{([^\\}]+)\\}/UsS',preg_replace('@\/\*.+\*\/@sU','',$style),$classi))
			foreach (array_keys($classi) as $i) {
			preg_match_all('/([^:]+):([^;]+);/UsS',trim((string) $classi[2][$i].';'),$attr);
			foreach (array_keys($attr) as $j)
				if(trim(trim((string) $attr[1][$j])))
				{
					if($quotachiavi)$out[trim((string) $classi[1][$i])]["'".trim((string) $attr[1][$j])."'"]=trim((string) $attr[2][$j]);
					else $out[trim((string) $classi[1][$i])][trim((string) $attr[1][$j])]=trim((string) $attr[2][$j]);
				}
		}
		return $out;
	}


	/**
	 * @param string $id e' il selettore JQuery
	 */
	public function __construct($id='') {
		$this->id=$id;
		self::init_static();
		self::$istanze[get_class($this)][]=$this;
		$myf=new MyField();
		$this->id_istanza=$myf->get_id_istanza();
		$this->add_src($myf->get_js_common(false));
	}

	
	 public function set_id_istanza($id){
	    return $this->id_istanza=$id;
	}
	
	
	
	 public function get_id_istanza(){
	    return $this->id_istanza;
	}
	
	
	/**
	 * Aggiunge alla classe del sorgente JQuery esterno da caricare (es. componenti o plugin)
	 *
	 * @param string $url   url del sorgente da caricare, se inizia per 'jquery/' usa le librerie interne a myQuery
	 * @param string $k     eventuale chiave del codice, per evitare doppia incluseione di codice uguale ma scritto diveramente (magare per problami di case o path)
	 */
	final static function add_src($url,$k=''){
		self::init_static();
		if(strpos($url,'jquery/')===0) $url=self::$percorsoMyForm.$url;
		$srcs=&self::getStaticVar();

		if(!$url) return;

		if(!$k) $k=md5($url);
		//if(!isset($srcs['static']['myJqueryJS']["sents"])) $srcs['static']['myJqueryJS']["sents"]=array();
					
		if(strtok(trim((string) $url),"\n\t;}{")!=trim((string) $url))
			 $srcs['static']['myJqueryJS']["src"][$k]="\n<script type='text/javascript'>
			                                                             //<!--
		                                                                          $url
		                                                                  //-->        
		                                                 </script>\n";
		else $srcs['static']['myJqueryJS']["src"][$k]="\n<script type='text/javascript' src='$url'></script>\n";

	}

	/**
	 * Imposta la compatibility mode della classe per evitare comnflitti nel caso si inserisca codice in pagine preesistenti che gia usano jquery
	 * @see http://api.jquery.com/jQuery.noConflict/
	 * @param string $identificatore e' l'identificatore jquery che verrà sempre usato nel codice autoprodotto
	 */
	final static function set_compatibility($identificatore='jQuery'){
	  self::$identificatore= $identificatore;
   }

   final static function abilita_get_src(){
   		$srcs=&self::getStaticVar();
   		$srcs['static']['myJqueryJS']['stop_get_src']=0;
   }
   
   final static function disabilita_get_src(){
   	     $srcs=&self::getStaticVar();
   	 	 $srcs['static']['myJqueryJS']['stop_get_src']=1;
   }
   
   /**
    *
    * Restituisce l'html per l'inclusione degli script aggiunti tramite {@link myJQuery::add_src()}
    * Attenzione una volta invocato il codice prodotto non verrà riprodotto all'invocazione successivo, in paratica ogni volta che si esegue {@link myJQuery::add_src()}
    * il codice aggiunto uscirà una sola volta da una  {@link myJQuery::get_src()}
    * @return string
    */
	final static function get_src(){
		$srcs=&self::getStaticVar();
		$js='';
	//	$js.=print_r($srcs['static']['myJqueryJS'],1)."**********";
		//if(!$srcs['static']['myJqueryJS']["src"] || ($usa_auto_avvio && $srcs['static']['myJqueryJS']["sent"])) return '';
		if(isset($srcs['static']['myJqueryJS']["src"])) 
			{
			 if(!isset($srcs['static']['myJqueryJS']["sents"])) $srcs['static']['myJqueryJS']["sents"]=array();
			 $js.=implode("\n",array_unique(array_diff($srcs['static']['myJqueryJS']["src"], $srcs['static']['myJqueryJS']["sents"])));
			 $srcs['static']['myJqueryJS']["sents"]=array_unique(array_merge($srcs['static']['myJqueryJS']["sents"],$srcs['static']['myJqueryJS']["src"]));
			}
		if(!isset($srcs['static']['myJqueryJS']["css"])) $srcs['static']['myJqueryJS']["css"]=array();	
		if(!isset($srcs['static']['myJqueryJS']["sents_css"])) $srcs['static']['myJqueryJS']["sents_css"]=array();	
		$src=array_diff($vv=array_values(array_unique((array) $srcs['static']['myJqueryJS']["css"])),(array) $srcs['static']['myJqueryJS']["sents_css"]);
		$srcs['static']['myJqueryJS']["sents_css"]=$vv;
		if($src) $js.="\n<script type=\"text/javascript\">
		                      //<!--
		                      ".implode("\n",$src)."
		                       //-->   
		                     </script>\n";

      
		if(!isset($srcs['static']['myJqueryJS']["sents_info"]) || !$srcs['static']['myJqueryJS']["sents_info"]){
				 $js.=$srcs['static']['myJqueryJS']["sents_info"]='
						<script type="text/javascript">
				     //<!--
				     ';
				 if(self::$identificatore!='$') $js.=(self::$identificatore!='jQuery'?self::$identificatore.'=':'').'jQuery.noConflict();'."\n";
				 $js.="//-->
				     </script>";
			}
			
        if(!isset($srcs['static']['myJqueryJS']['cached_src'])) $srcs['static']['myJqueryJS']['cached_src']='';
		if(isset($srcs['static']['myJqueryJS']['stop_get_src']) && $srcs['static']['myJqueryJS']['stop_get_src']) 
		           {$srcs['static']['myJqueryJS']['cached_src'].=$js;
					 return;
					}
			  else {
			  	    if(isset($srcs['static']['myJqueryJS']['cached_src'])) $js.=$srcs['static']['myJqueryJS']['cached_src'];
			  	    $srcs['static']['myJqueryJS']['cached_src']='';
			  	    return $js;
			      }
	}

	/**
	 * Aggiunge codice da eseguire specifico della classe, ma comune a tutte le istanze (utile per classi figlie)
	 * @param string $code codice jquery
	 * @param string $k è una chiave da associare al codice (se omesso e' hash del codice) per impedire o ottenere effetti di sovrascrittura,
	 * 					aggiungere due codici diversi con la stessa chiave daranno produrrà la sovrascrittura del primo codice con il primo inserito
	 */
	 public function add_common_code($code,$k=''){
		return myJQuery::add_common_codes($code,get_called_class(), $k);
	}


	/**
	 * Restituisce il codice aggiunto con {@link myJQuery::add_common_code()}
	 * @return string
	 */
	 public function get_common_code(){
		 return myJQuery::get_common_codes(get_called_class());
	}


    /**
     * Aggiunge codice da eseguire specifico della classe, ma comune a tutte le istanze (utile per classi figlie)
     * @param string $code      codice jquery
     * @param string $widget    namespace
     * @param string $k			chiave del codice
     */
	final static function add_common_codes($code,$widget='',$k='') {
		if(!$widget) $widget=get_called_class();
		$srcs=&self::getStaticVar();
		if(!$k) $k=md5($code);
/*		if($srcs['static']['myJqueryJS']["sent_$widget"])
							$srcs['static']['myJqueryJS']["sent_$widget"]=false;*/
		$srcs['static']['myJqueryJS']["src_$widget"][$k]=$code;
	}



	/**
     * Restituisce il codice comune a tutto il namespace
     * @param string $widget    namespace
     */

	final static function get_common_codes($widget,$usa_auto_avvio=true){
	
		$srcs=&self::getStaticVar();
		if(isset($srcs['static']['myJqueryJS']['stop_get_src']) && $srcs['static']['myJqueryJS']['stop_get_src']) return;
		if(!isset($srcs['static']['myJqueryJS']["src_$widget"])) $srcs['static']['myJqueryJS']["src_$widget"]=array();
		if(!isset($srcs['static']['myJqueryJS']["sent_$widget"])) $srcs['static']['myJqueryJS']["sent_$widget"]=array();
		$src=array_diff($vv=array_values(array_unique((array) $srcs['static']['myJqueryJS']["src_$widget"])),(array) $srcs['static']['myJqueryJS']["sent_$widget"]);
		$srcs['static']['myJqueryJS']["sent_$widget"]=$vv;
		$js=implode("\n",$src);


		/*if(!$srcs['static']['myJqueryJS']["src_$widget"] || ($usa_auto_avvio && $srcs['static']['myJqueryJS']["sent_$widget"])) return '';
		$js=implode(";\n",array_unique((array) $srcs['static']['myJqueryJS']["src_$widget"])).";\n";
		$srcs['static']['myJqueryJS']["sent_$widget"]=true;
*/
		return $js;
	}


	/**
	 * @ignore
	 */
	final static function set_auto_avvio(){
		$srcs=&self::getStaticVar();
		
		if($srcs['static']['myJqueryJS'])
				foreach ($srcs['static']['myJqueryJS'] as $k=>&$v) 
				        {
						if(stripos($k,'sent')===0 || stripos($k,'src')===0) 
						          if(is_array($v)) $v=array();
						                      else $v='';
				        }
	}

	/**
	 * @ignore
	 */
	protected function set_id($id){
		$this->id=$id;
	}
	
	 public function alter_id($id){
	    $this->set_id($id);
	}

	/**
	 * Restituisce l'id della classe
	 * @return string
	 */
	 public function get_id(){
		return $this->id;
	}

	/**
	 * Restituisce parte di codice myJQuery per il selettore
	 * es.
	 * <code>
	 *  $x=new myJQuery('#pippo');
	 *  echo $x->JQid(); //restituisce $('#pippo').
	 * </code>
	 * @param boolean $punto se falso non si aggiunge il punto
	 * @return string
	 */
	final function JQid($punto=false){
		return self::$identificatore."('{$this->get_id()}')".($punto?'.':'');
	}

	/**
	 * Restituisce il nome JS della variabile Jquery
	 * @return string
	 */
	final static function JQvar(){
		return self::$identificatore;
	}

	/**
	 * Restituisce il codice per aggiungere un'operazione
	 * @param string $codice
	 * <code>
	 *    echo myJQuery::get_add_action('alert(1)'); //restituisce: <script type="text/javascript">$(function(){    alert(1);   }); </script>
	 * </code>
	 */
	final static function get_add_action($src){
		if($src)  return '<script type="text/javascript">
		    //<!--
			      						'.self::$identificatore.'(function(){'."\n$src\n".'});
			      //-->
			      						    </script>'."\n";
	}

/**
 *
 * Restituisce codice Jquery per l'aggiunta di codice html alla pagina
 * @param string $dove un identificatore jquery es: #miodiv
 * @param string $html html da aggiungere
 * @param string $comando comando JQuery da usare (oppure prepend)
 * @return string
 */
	final static function get_add_html($dove,$html,$comando='append'){
		return self::$identificatore."('$dove').{$comando}(".self::quote(trim((string) $html)).")";
	}




	/**
	 *
	 * Restituisce il codice JS per modificare fogli di stile
	 * <code>
	 *    echo '<script>',myJQuery::get_add_style('#prima{color:red} #seconda{color:red}',true),'<script>';
	 * </code>
	 * @param string $style intero gruppo di classi css
	 * @param boolean $ready_to_use se true restituisce il codice altrimenti restituisce una funzione anonima
	 * @return string
	 */
	final static function get_add_style($style,$ready_to_use=false) {
		return myCSS::get_css_jscode($style,$ready_to_use);
	/*	$p=self::parse_style($style,true);
		foreach ($p as $k=>$s)
				{
				  $jq=new myJQuery($k);
				  $out.=$jq->css($s).";\n";
				}
		return $out;*/
	}

	/**
	 *
	 * Restituisce il codice JS per aggiungere un css esterno alla pagina
	 * @param string $css percorso del css se inizia per 'jquery/' lo cerca internamente alle myForms altrimenti usa il percorso inserito
	 * @return string
	 */
	final static function get_add_css_file($css) {
		if(strpos($css,'jquery/')===0) $css=self::$percorsoMyForm.$css;
		return '(function(){
		                var ss1 = document.createElement(\'style\');
            				ss1.setAttribute("type", "text/css");
            			document.getElementsByTagName("body")[0].appendChild(ss1);
            			var def="@import url(\''.$css.'\');";
            			if (!ss1.styleSheet) ss1.appendChild(document.createTextNode(def));
            						  else  {if(ss1.styleSheet.addImport)
            											 ss1.styleSheet.addImport("'.$css.'",0);
            										else ss1.styleSheet.cssText = def;
            								}
            			})();								     
			';
	}

/**
 *
 * Aggiunge classi css relative ad un namespace
 * @param string $css
 * @param string $k il namespace
 */	final static function add_css($css,$k='') {
		$srcs=&self::getStaticVar();
		if($k) $srcs['static']['myJqueryJS']["css"][$k]=myJQuery::get_add_css_file($css);
		  else $srcs['static']['myJqueryJS']["css"][]=myJQuery::get_add_css_file($css);
	}



/**
 * Effettua la quotatura traduzione di variabili php in codice jquery
 * <code>
 * 		$a=array('a'=>'b','b'=>'d');
 * 		echo myJQuery::quote($a); //restituisce {'a':'b','b':'d'}
 * </code>
 * @param mixed $val
 * @param string $parentesi forza parentesi esterne per gli array, inserire {} oppure [] nulla sceglie in automarico
 * @return string
 *
 */
	final static function quote($val,$parentesi='',$quota_chiavi=false,$encoding='ISO'){
	   if(is_array($val)) return self::encode_array($val,$parentesi,$quota_chiavi,$encoding);
			elseif (is_bool($val)) return ($val?'true':'false');
				else{$trim=trim((string) $val);
				     if(strpos($trim,self::$identificatore)===0
				       ||
				       strpos($trim,'function')===0
				       ||
				       strpos($trim,'new ')===0
				       ||
				      (isset($trim[0]) && $trim[0]==='{')
				       ||
				       (isset($trim[0]) &&$trim[0]==='[')
				       ||    
				       (isset($trim[0]) &&($trim[0]==="'" /*&& $trim[strlen($trim)-1]==="'"*/) ))
				                {
				                   return $val;
				                }
						elseif (is_string($val)) 
						              {   if($encoding==='UTF-8') $val=myCharset::utf8_encode($val);
						                  return "'".str_replace(array("\n","\t","\r"),
						                                         array('\\n','\\t','\\r'),str_replace("'","\\'",$val))."'";
						              }
							else return $val;
				    }		
	}


/**
 * restituisce true se il parametro e' un array associativo
 * @param array $a
 * @return bool
 */
	final static function  is_associativo(array $a){
	  	return array_values($a)!==$a;
	}

	/**
	 * @param ignore
	 */
	final static function quote_keys(array $array) {
		 $new=array();
		 if($array) foreach($array as $k=>$v) $new["'".str_replace("'", "\\'", $k)."'"]=$v;
		 return $new;
	}


	/**
	 * Effettua codifica di array php in codice jquery usata da {@link myJQuery::quote()}
	 * @param array $a
	 * @param string $parentesi forza parentesi esterne per gli array, inserire {} oppure [] nulla sceglie in automarico
     * @param bool $quota_chiavi
     * @@param string $encoding ISO|UTF-8
	 * @return string
	 *
	 */
	final static function &encode_array($a,$parentesi='',$quota_chiavi=false,$encoding='ISO'){
		$associativo=self::is_associativo($a);
		if($parentesi!=''){
			if($parentesi[0]=='{') $associativo=true;
			 				  else $associativo=false;
			}
		
	   if($associativo && $quota_chiavi) $a=self::quote_keys($a);	
	   foreach ($a as $k=>&$v) {
				       if($associativo){if(!preg_match('@^\'.*\'$@S',$k) && !preg_match('@^[a-z0-9A-Z_]+$@S',$k)) $k="'".str_replace("'","\'",$k)."'";
	       								 $v="$k:".self::quote($v,'',$quota_chiavi,$encoding);
				       					}
	                           else $v=      self::quote($v,'',$quota_chiavi,$encoding); 
	              }
	   
	    if($parentesi=='') {
	                         if($associativo) $parentesi='{}';
	                                     else $parentesi='[]';
	                       }
	   $out=$parentesi[0].implode(',',$a).$parentesi[1];
	   return $out;
	}

	/**
	 * @ignore
	 */
	final protected  function clean_code(){
		$this->costruito=false;
		$this->codice=array('__START__'=>array(),'__END__'=>array());
	}

	/**
	 *
	 * Aggiunge del codice per la preparazione del widget
	 * @param string $codice
	 */
	  public function add_code($codice,$ns=''){
	    if($ns===null || $ns===-1) $ns='__START__';
	    if($ns===1) $ns='__END__';
	    if(!key_exists(trim((string) $ns), $this->codice) || !$this->codice[trim((string) $ns)]) $this->codice[trim((string) $ns)]=array();	    
	    $this->codice[trim((string) $ns)][]=$codice.";";
	    return $this;         
	}

	/**
	 *
	 * Restituisce il codice preparato  per il widget
	 */
	 public function get_code($ns=''){
		$v=array();
		if($ns) $v=$this->codice[trim((string) $ns)];
			elseif($this->codice) {
			             if($this->codice['__START__']) $v=$this->codice['__START__'];
			             foreach ($this->codice as $ns=>&$w) if(!in_array($ns,array('__END__','__START__'))) $v=array_merge($v,$w);
			             if($this->codice['__END__'])  $v=array_merge($v,$this->codice['__END__']);
			              }
		return trim(implode("\n",$v));
	}
	
	 public function get_new_html(&$html){ return $html;}
	 public function prepara_codice(){$this->costruito=true;}
	 public function get_html(){
	    if(!$this->costruito) $this->prepara_codice();
	    $out=trim((string) $this->get_common_code())."\n".trim((string) $this->get_code());
	    if(!trim((string) $out)) return  self::get_src();
	    return self::get_src().
	           self::get_add_action(
	                               $this->get_new_html($out)
	                               );
	}
	

	/**
	 * @ignore
	 */
	 public function __toString(){
		return $this->get_html().'';
	}

    /**
     * @ignore
     */
	public function set_order_memory(){
		self::add_src('jquery/tablesorter/jquery.cookie.js');
		self::add_src('jquery/tablesorter/jquery.json.js');
		return $this;
	}
}