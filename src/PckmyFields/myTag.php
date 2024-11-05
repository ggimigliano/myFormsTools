<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myTag.
 */

namespace Gimi\myFormsTools\PckmyFields;



use Gimi\myFormsTools\myCSS;




/**
 *
 * Classe base per produrre un tag html,
 * es.
 * <code>
 * echo new myTag('a',array('href'=>'#'),   //tag Anchor che ha come attributo href=#
 *					new myTag('b','','link') //contenuto dell'Anchor e' un tag Bold che contiene la stringa 'link'
 * 			       );
 * echo new myTag('hr'); //mostra un filetto hr
 * </code>
 * il risultato sarà :
 * &lt;a href=&quot;#&quot; &gt;&lt;b &gt;link&lt;/b&gt;&lt;/a&gt;
 * &lt;hr  /&gt;
 *
 * <code>
 * echo new myTag('a',array('href'=>'#'),   //tag Anchor che ha come attributo href=#
 *					new myTag('b','','link') //contenuto dell'Anchor e' un tag Bold che contiene la stringa 'link'
 * 			       );
 * echo new myTag('hr'); //mostra un filetto hr
 * </code>
 *   Stesso risultato se si scrive
 * <code>
 * echo new myTag("<a href=# title='ciao'>",'',
 *					new myTag('b','','link') //contenuto dell'Anchor e' un tag Bold che contiene la stringa 'link'
 * 			       );
 * echo new myTag('hr'); //mostra un filetto hr
 * </code>

 */
	
class myTag implements \ArrayAccess {
/**
 * @ignore
 */
protected $secure,$attributi=array(),$javascript='',$tagName='',$contenuto=null,$id_istance;
/**
 * @ignore
 */protected  static $id_istanza=1,$charset='ISO-8859-1',$autorecode=false,$autorecoded=false;

/**
 * @ignore
 */public function offsetSet($offset, $value) {	$this->set_attributo($value,$offset);   }

/**
 * @ignore
 */public function offsetExists($offset) {  return $this->attributi[$offset]!='';   }

/**
 * @ignore
 */public function offsetUnset($offset) { 	$this->unset_attributo($offset);   }
    
/**
 * @ignore
 */ public function offsetGet($offset) { return $this->attributi[$offset];   }

/**
 * @ignore
 */public static function crea_id_istanza()  {return  ++self::$id_istanza;}
    
/**
 * @ignore
 */static function htmlentities($string)  {	return htmlentities((string) $string,ENT_COMPAT,self::$charset,false);    }


    
 /**
  * Costruttore
  *
  * @param string $nome puo' essere il nome, o anche semplicemente l'html del tag da creare (in questo caso vanno omessi gli altri due parametri)
  * @param array $attributi array con gli attributi del tag
  * @param string/object $contenuto eventuale contenuto del tag puo' essere una stringa o un oggetto che implementa __toString es lo stesso myTga o estensioni
  */
  public function __construct($nome='',$attributi=array(),$contenuto=null) {
    if (self::$autorecode && !empty($_POST) && !self::$autorecoded) self::recodePOST();
    $this->id_istance=self::crea_id_istanza();
    $nome=trim((string) $nome);
    $m=array();
    if(preg_match('#^[a-z]*$#iS',$nome,$m)) $this->tagName=strtolower($nome);
   									else {
   										   if(!preg_match('#^<([a-z]+)#iS',$nome,$m) && $m) $this->tagName=strtolower($m[1]);
   										   $this->importa_attributi($nome);
   										 }
  	$this->attributi+=array_change_key_case((array) $attributi);
 	$this->contenuto=$contenuto;

 }        
 
  static public function setAutoRecodePOST($status){
      self::$autorecode=$status;
  }
  
  

   public static function set_charset($charset){
      self::$charset=strtoupper($charset);
  }
  
   public static function get_charset(){
     return  self::$charset;
  }
  
  
  static function &utf8_decode($string){
       if(self::$charset=='UTF-8') return $string;
       if (!preg_match("/[\200-\237]/", $string) && !preg_match("/[\241-\377]/", $string))   return $string;
      
       $string = preg_replace_callback("/([\340-\357])([\200-\277])([\200-\277])/",
                                         function($m){$codice=(ord($m[1])-224)*4096 + (ord($m[2])-128)*64 + (ord($m[3])-128);
                                                        if($codice<=0)  return '';
                                                            elseif($codice<255)  return chr($codice);
                                                                        else return "&#{$codice};";
                                                     },
                                         $string
                                        );
      
      $string = preg_replace_callback("/([\300-\337])([\200-\277])/",
                                         function($m){ $codice=(ord($m[1])-192)*64+(ord($m[2])-128);
                                                        if($codice<=0)  return '';
                                                            elseif($codice<255)  return chr($codice);
                                                                   else return "&#{$codice};";
                                                      },
                                         $string
                                      );
       
      return ($string);
  }
  
  
 static public function utf8_decode_recursive(&$val) { 
     if(self::$charset=='UTF-8') return;
     static $sost;
     if(!isset($sost)) { 
                            $sost=array('da'=>array('„',  '”',       '“',       '’',      '`',     '‘',   '’',  '–',   '…',
                                                 '&rdquo;','&ldquo;','&rsquo;','&rsquo;', '&lsquo;',   '&hellip;' ,'&amp;','&euro;','&ndash;',
                                                 '&#8222;', '&#8221;', '&#8220;', '&#8217;', '&#8216;', '&#8217;', '&#8211;', '&#8230;', '&#8364;'
                                                 ),
                                      'ad'=>array('"',  '"',      '"',      "'",      "'",      "'",   "'",  '-',   '...',
                                                  '"',  '"',      "'",      "'",      "'",         '...' ,     '&',     '€',     '-',
                                                  '"',  '"',      '\'',     '\'',       '\'',         '-',   '...', '€'
                                      )
                                  );
                                               
                        }
     if(is_string($val))           $val= str_replace($sost['da'],$sost['ad'],static::utf8_decode($val)) ;   
         elseif(is_array($val))
            foreach ($val as &$v)
                    if(is_string($v)) $v=str_replace($sost['da'],$sost['ad'],static::utf8_decode($v));
                                elseif (is_array($v)) static::utf8_decode_recursive($v);
 } 
 
 static public function recodePOST(){
           if (!empty($_POST) && !self::$autorecoded && self::$charset=='ISO-8859-1') {
                $flag_unicoded = false;
                if ( stripos($_SERVER['HTTP_CONTENT_TYPE'], 'charset=utf-8') !== false || 
                     (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
                    ) $flag_unicoded = true;
                if ($flag_unicoded) { 
                                    myField::utf8_decode_recursive($_POST);
                                    if($_FILES) 
                                          foreach ($_FILES as &$FILE)
                                                  myField::utf8_decode_recursive($FILE['name']);
                                    self::$autorecoded=true;
                                    }
            }
       }  
	
	     


 /**
	* assegna un JS ad un evento, di default e' onclick
 * @param	 string $JS  E' lo JavaScript da lanciare
 * @param	 string $evento  Il nome dell'evento su cui attivare lo JS
 * @param    boolean $sostituisci sostituisce eventuali eventi precedenti
 */
   public function SetJS($JS,$evento='onclick',$sostituisci=true,$null=null) {
   	 if(isset($this->attributi[strtolower($evento)])) 
  	                 {
  	                  if(isset($this->attributi[strtolower($evento)])) $prec=$this->attributi[strtolower($evento)];
  	                                                              else $prec=false;
  	                  if(!$sostituisci && $prec) $JS="$prec;$JS";
  	                 }
  	 if(!$JS)    $this->unset_attributo(strtolower($evento));                
  	        else $this->set_attributo(strtolower($evento),str_replace('"','\\"',$JS));
  	 return $this;
  }

/**
 * alias di @see setjs
		*/
	  public function set_js($JS,$evento='onclick',$sostituisci=true,$null=null) {
		     return $this->setjs($JS,$evento,$sostituisci);
		  }

  /**
	* Restituisce il campo in html pronto per la visualizzazione
   *  @return string
   */
    public function get_Html () {
        if (!trim((string) $this->tagName)) return $this->stringa_attributi();
			else  return $this->jsAutotab().
					($this->contenuto===null || in_array($this->tagName,array('img','input','br','hr','meta','link')))?
					                          "<$this->tagName {$this->stringa_attributi()} />":
									   		  "<$this->tagName {$this->stringa_attributi()}>{$this->contenuto}</$this->tagName>";
  }

   public function get_id_istanza(){
     return $this->id_istance;
  }


/** @ignore*/
  public static function Nonhtmlentities( $string ){
	if (is_callable('html_entity_decode'))	return html_entity_decode($string,ENT_QUOTES,self::$charset);
	// replace numeric entities
	 $string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
	 $string = preg_replace('~&#([0-9]+);~e', 'chr(\\1)', $string);
	 // replace literal entities
	 $trans_tbl = get_html_translation_table(HTML_ENTITIES,ENT_COMPAT,self::$charset);
	 $trans_tbl = array_flip($trans_tbl);
	 return strtr($string, $trans_tbl);
  }

/** @ignore*/
    public function __call($m,$v){
        $m=strtolower($m);
        $classInfo=new \ReflectionClass(get_class($this));
        while($classInfo) {
            $class=$classInfo->getName();
            if(strtolower($class)==$m)  { $pars=array(); 
                                          for ($i=0;$i<count($v);$i++) $pars[]="\$v[{$i}]";
                                          eval("return $class::__construct(".implode(',',$pars).");");
                                        }
            $classInfo=$classInfo->getParentClass();
        }
  }


 /** @ignore*/
   public function __toString(){
  	return $this->get_html();
  }


  /**
	  * Imposta un attributo, inserendo la coppia  (nome_attributo,valore)
	  * oppure un insieme di attributi se il primo campo e' un array
	  * associativo con tutti gli attributi ed il relativo valore
	  *
	  * @param	 array|string $primo
	  * @param	 string $valore
	  */

  public function set_attributo ($primo,$valore='') {
	 //echo "$primo=$valore<br />";
	 if (is_array($primo)) foreach ($primo as $nome=>$valore)
	 									  {$nome=trim(strtolower($nome));
				  						   /* if ($nome=='style') $this->set_style($valore);
				  						   			  else */
				  						   $this->attributi[$nome]=(is_array($valore)?$valore:trim((string) $valore));
	 									  }
				  elseif ($valore!==null) { $primo=trim(strtolower($primo));
				  							/*if ($primo=='style') $this->set_style($valore);
				  											else */
				  							$this->attributi[$primo]=(!is_scalar($valore)?$valore:trim((string) $valore));
				  						   }
															  // else $this->unset_attributo('value');
	return $this;
 }


 /**
	* @param	 string $nome
	 */
  public function unset_attributo ($nome) {  unset($this->attributi[$nome]);  return $this;}


 /**
	* @param	 string $nome
	* @return	string
	 */
 function &get_attributo($nome) {if(isset($this->attributi[$nome])) return $this->attributi[$nome];
                                 $null=null;
                                 return $null;
                                 }


 /**
	* @return	string
	 */
  public function get_attributi () {return $this->attributi; }

     
     
     /**
      * Restitituisce tutti gli attribiti in cui nome o contenuto danno true alle funzioni anonime inserite
      * @param function()|null $fun_nome_attr
      * @param function()|null $fun_val_attr
      * @return array
      */
      public function get_attributi_filtrati($fun_nome_attr=null,$fun_val_attr=null){
         if(!$fun_nome_attr) $fun_nome_attr=function($x){return true;};
         if(!$fun_val_attr)  $fun_val_attr=function($x){return true;};
         $funzioni=array();
         foreach ($this->attributi as $attr=>$valore) if($fun_nome_attr($attr) && $fun_val_attr($attr)) $funzioni[$attr]=$valore;
         return $funzioni;
     }

	  public static function unid(){
	 	return session_id().'.'.microtime(1).'.'.rand(1,99999);
	 }

   /**
	  * Imposta una proprietà css e relativo valore es:
	  * <code>
	  *   $f->set_style('color','red');   //imposta colore rosso
	  *   $f->set_style('font-weight','bold'); //imposta grassetto
	  *
	  *   //si puo' sostituire con
	  *   $f->set_attributo('style','color:red;font-weight:bold'); //ma cochcio alla sintassi
	  * </code>
	  *
	  * @param	 string $proprieta
	  * @param	 string $valore
	  */
  public function set_style($proprieta,$valore){
    $css=new myCSS();
    if(!isset($this->attributi['style']) || 
       !$this->attributi['style']) $valori=array();
                              else $valori=$css->parseStyle($this->attributi['style']);
                
	if (strlen(trim((string) $valore))==0) unset($valori[$proprieta]);
							else $valori[$proprieta]=$valore;
	$css->add_classe($valori,'classe');
	$this->attributi['style']=$css->UnparseStyle('classe',true);
	return $this;
 }

/**
 * Effettua il parsing del html passato
 * es
 * $x= myTag::parse_attributi_tag('<a b primo=valore1 secondo =" val\\"ore "  terzo  = \' " \\\' " \' ');
 * allora
 * $x = array (
 * 					'primo' => 'valore1',
 * 					'secondo' => ' val"ore ',
 * 					'terzo' => ' " \' " ')
 *
 * @param string $html e' l'HTML da importare
 * @param boolean $decode_htmlentities se true le entità html vengono decodificate
 */
  public static function parse_attributi_tag($html,$decode_htmlentities=true) {
 	$parametri=array();
 	$x=trim((string) $html);
 	if ($x[0]=='<') {$x=explode(' ',$x,2);
 					 $x=$x[1];
 					 }
 	$x=explode('>',$x,2);
 	$x=$x[0];
 	do {$x=explode('=',$x,2);

 		$x[0]=explode(' ',trim((string) $x[0]));
 		$parametro=$x[0][count($x[0])-1];
 		if(!$parametro) break;
 		if(!isset($x[1])) break;
 		$x[1]=trim((string) $x[1]);
 		if (!$x[1]) break;

 		$i=1;
 		$p=-1;
 		$j=-1;
 		if ($x[1][0]=='"')	do {$p=strpos($x[1],'"',$i);
 							    if ($p!==false && $x[1][$p-1]!='\\') break;
 							    $i=$p+1;
 		    					} while ($p!==false);
 		elseif ($x[1][0]=="'")	do {$p=strpos($x[1],"'",$i);
 							    if ($p!==false && $x[1][$p-1]!='\\') break;
 							    $i=$p+1;
 		    					} while ($p!==false);
						 else  {$x[1].=' ';
						 	    $j=strpos($x[1]," ");
						 		}

		if($j!=-1) {$v=stripslashes(substr($x[1],0,$j));
					if ($decode_htmlentities) $v=myTag::nonhtmlentities($v);
					$parametri[trim((string) $parametro)]=$v;
					$x=substr($x[1],$j,strlen($x[1])-$j);
					}
 			elseif($p!=-1)
 						{
 						 $v=stripslashes(substr($x[1],1,$p-1));
 						 if ($decode_htmlentities) $v=myTag::nonhtmlentities($v);
 						 $parametri[trim((string) $parametro)]=$v;
 						 $x=substr($x[1],$p+1,strlen($x[1])-$p);
 						}
 	    		else break;

 	} while(true);
 	return $parametri;
 }



 /**
 * Importa gli attributi espressi sotto forma di stringa
 * es
 * <code>
 * $x= new myTag('a',array('target'=>'_blank'),'click qui');
 * $x->importa_attributi(" href=link title='ciao' ");
 * echo $x;
 * //otterremo:
 * <a target="_blank" href="link" title="ciao" >click qui</a>
 * </code>
 */
  public function importa_attributi($html) {
 	$this->attributi+=self::parse_attributi_tag($html);
 }




 function &stringa_attributi ($v=array(),$Esclusi=true,$novalue=false) {
     
 	 if (!$this->attributi) return '';
 	 if(!is_array($v)) $v=array();
	 $v=array_flip($v);
	 $out='';
	 foreach ($this->attributi as $nome=>&$valore)
			if (!is_array($valore) && (abs($Esclusi-isset($v[$nome]))) && $nome!='value' )
									  { $valore=(string) $valore;
									  	if ($nome=='style' && trim((string) $valore))
									  						{
									  						$css=new myCSS();
									  						$css->add_classe($css->parseStyle($valore),'classe');
									  						$valore=$css->UnparseStyle('classe',true);
									  						}
									  	if ($nome) $out.=strtolower($nome)."=\"".str_replace('"','&quot;',$valore)."\" ";
											  else $out.=$valore.' ';
									  }
 	 if (isset($this->attributi['value']))
 		 {$val=$this->attributi['value'];
 		 if(is_array($val)) $val=array_pop($val);
 		 //if(!mb_check_encoding($val,'utf-8')) die($val.'File non codificato correttamente in UTF-8');
 		 $value=htmlentities(trim((string) $val),ENT_COMPAT,self::$charset,false);
 		 if (!$Esclusi && isset($v['value']))                     $out.='value="'.$value.'"';
 		      elseif ($Esclusi && !isset($v['value']) && !$novalue) $out.='value="'.$value.'"';
     	 }
     	 
	 return $out;
 }


/** @ignore */
  public function clonami() {
		 if (PHP_VERSION >= 5) return clone($this);
						 else  return $this;
	}
/** @ignore */
   public function __clone(){
	   if(isset($this->campi))	foreach ($this->campi as $i=>$f)  if( is_object($f)) $this->campi[$i]=clone $f;
  }

}