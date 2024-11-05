<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myURL.
 */

namespace Gimi\myFormsTools\PckmyFields;




class myURL extends myText {
/** @ignore */
protected	$componenti_fisse=array(),$controllo=false;


 public function __construct($nome,$valore='', $classe='') {
	 parent::__construct($nome,$valore,$classe);
	 $this->set_regExp('^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?');
	 $this->set_MyType('MyURL');
	 $this->set_MaiMin('');
  }


 /**
  * Funzione inversa di get_componenti
  * @see myURL::get_componenti
  * @static
  * @param array $componenti
  *
  */
  public function ricomponi_componenti($componenti)
{
    if (!is_array($componenti)) return false;
    $uri  = isset($componenti['scheme']) ? $componenti['scheme'].':'.((strtolower($componenti['scheme']) == 'mailto') ? '' : '//') : '';
    $uri .= isset($componenti['user']) ? $componenti['user'].(isset($componenti['pass']) ? ':'.$componenti['pass'] : '').'@' : '';
    $uri .= isset($componenti['host']) ? $componenti['host'] : '';
    $uri .= isset($componenti['port']) ? ':'.$componenti['port'] : '';
    if(isset($componenti['path']) && strtolower($componenti['scheme']) != 'mailto')
        {
        $uri .= (substr($componenti['path'], 0, 1) == '/') ? $componenti['path'] : ('/'.$componenti['path']);
    	}
    if(isset($componenti['path']) && strtolower($componenti['scheme']) == 'mailto')
        {
        $uri .=  $componenti['path'] ;
    	}
    $uri .= isset($componenti['query']) ? '?'.$componenti['query'] : '';
    $uri .= isset($componenti['fragment']) ? '#'.$componenti['fragment'] : '';
    return $uri;
}



 protected function get_errore_diviso_singolo() {
    $errore=parent::get_errore_diviso_singolo();
 	if ($errore) return $errore;
 	if($this->get_value()) {
                 	$parse=$this->get_componenti();
                 	if (!$parse) return ' '.$this->trasl("formalmente errato");
                 	foreach ($this->componenti_fisse as $i=>$v) {
                 		          if (!isset($parse[$i])) return  $this->trasl("formato errato").", {$v['mex']}";
                 				  if($i!='query' && strpos("|{$v['valore']}|",'|'.$parse[$i].'|')===false)  return  $this->trasl("formato errato").", {$v['mex']}";
                 						  elseif($i=='query') {   $q=array();
                 						                          parse_str($parse[$i],$q);
                 						                          foreach ($v['valore'] as $Q) {
                 						                                       //  if(array_keys($QQ)!=array_keys($Q))   return $this->trasl("formato errato").", {$v['mex']}";
                 						                                          foreach ($Q as $k=>$val)  if($val!='' && !preg_match($val,  $q[$k])) return $this->trasl("formato errato").", {$v['mex']} {$q[$k]} in @".preg_quote($val,'@').'@';
                 						                                        }
                 						                                        
                 						                        }
                 	                      }
                     $this->set_value($this->ricomponi_componenti($parse));
                	}
 }

 /**
  * Fissa delle componenti, le componenti fissate non potranno essere cambiate dell'utente e se omesse verranno aggiunte
  * @param string array $scheme
  * @param string array $host
  * @param int array $port
  * @param string array $path
  * @param array array $qs array di parametri, i valori  associati  sono espressioni regolari complete di delimitatori
  *   *
  * <code>
  *   $u=new myURL('nome');
  *   $u->fissa_componenti(array('http','https'),array('www.italia.it','www.italia.gov.it'));
  *   $u->set_value('www.prova.it');
  *   echo $u->Errore();   //Restituisce "formato errato, deve iniziare per: http oppure https
  *   echo $u->get_value();  //restituisce http://www.prova.it , all'host errato www.prova.it
  *                          // (ma segnalato nell'errore) si aggiunge il primo
  *                          // degli schemi di default
  * </code>
  */
  public function fissa_componenti($scheme='',$host='',$port=0,$path='', $qs=array()) {
     if ($scheme) {if(!is_array($scheme)) $scheme=array($scheme);
                   $this->componenti_fisse['scheme']['mex']=$this->trasl("deve iniziare per: %1%://",array('%1%'=>implode(' '.$this->trasl('oppure').' ',$scheme)));
 	 			   $this->componenti_fisse['scheme']['valore']=implode('|',$scheme);
 	 			  }
 	 if ($host) {if(!is_array($host)) $host=array($host);
 	              $this->componenti_fisse['host']['mex']=$this->trasl("deve avere l'host: %1%",array('%1%'=>implode(' '.$this->trasl('oppure').' ',$host)));
 	 			  $this->componenti_fisse['host']['valore']=implode('|',$host);
 	 			 }
 	 if ($port>0) {if(!is_array($port)) $port=array($port);
 	              $this->componenti_fisse['port']['mex']=$this->trasl("deve usare la porta: %1%",array('%1%'=>implode(' '.$this->trasl('oppure').' ',$port)));
 	 			  $this->componenti_fisse['port']['valore']=implode('|',$port);
 	 			 }
 	 if ($path) {if(!is_array($path)) $path=array($path);
 	              $this->componenti_fisse['path']['mex']=$this->trasl("il percorso deve essere: %1%",array('%1%'=>implode(' '.$this->trasl('oppure').' ',$path)));
 	 			  $this->componenti_fisse['path']['valore']=implode('|',$path);
 	 			 }
 	 if ($qs)   {if(!isset($qs[0])) $qs=array($qs);
 	 			 $this->componenti_fisse['query']['mex']=$this->trasl("il percorso una sequenza accettabile dopo il simbolo '?' ");
 	 			 $this->componenti_fisse['query']['valore']=$qs;
 	 			 }	 
    return $this; 	 			 
 }



 /**
  * Restituisce le componenti della url
  * @link  http://it2.php.net/manual/it/function.parse-url.php
  * @static
  * @param string $url se omesso restituisce il parsing del valore corrente dell'istanza
  * @return array  con le componenti come chiavi
  *
  * <code>
  * $url = 'http://username:password@hostname/path?arg=value#anchor';
  * print_r(  myurl::get_componenti($url)  );
  *
  * Il precedente esempio visualizzerà:
  * Array
  *  (
  * [scheme] => http
  * [host] => hostname
  * [user] => username
  * [pass] => password
  * [path] => /path
  * [query] => arg=value
  * [fragment] => anchor
  * )
  * </code>

  */
  public function get_componenti($url='') {
 	 if (!$url) $url=$this->get_value();
 	 if (strpos($url,':')===false) $url='myfields://'.$url;
 	 $p=(array) @parse_url($url);
 	 foreach ($p as $i=>$v) if ($v=='myfields') $p[$i]='';
 	 return $p;
 }

 function &get_value() {
  		return myText::get_value();
  }

   public function set_value($valore) {
  		myText::set_value($valore);
  		return $this; 
  }

 /**
  * Fa comparire l'icona per verificare la correttezza del link
  *
  */

  public function set_controllo(){
 	$this->myFields['static']['js_src'][get_class($this)]="
 	<script type='text/javascript'>
 	    //<!--
	function ApriMyUrl(LINK){
		if (document.getElementById(LINK).value) {
				finestrella=window.open(document.getElementById(LINK).value, 'myURL', 'resizable=1,toolbar=0,menubar=0,location=1,scrollbars=yes,resize=1,width=800,height=400');
				finestrella.focus();
			}
	}
 	    //-->
	</script>";
 	$this->controllo="
 		<script type='text/javascript'>
 	    //<!--
 	    document.write('<a href=\"javascript:ApriMyUrl(\\'".$this->attributi['id']."\\')\" title=\"{$this->trasl("Per verificare il link click qui")}\" ><img src=\"/".self::get_MyFormsPath()."/icone/link.gif\"  /></a>');
 	        //-->
 	</script>";
 	return $this;
 }

 
 protected function html5Settings($attrs=array()){
     $attrs['type']='url';
     myField::html5Settings($attrs);
 }

  public function get_html() {
    $this->html5Settings();
    $out= (isset($this->myFields['static']['js_src'][get_class($this)])?$this->myFields['static']['js_src'][get_class($this)]:'').$this->send_html(parent::get_html()).$this->controllo;
 	$this->myFields['static']['js_src'][get_class($this)]='';
 	$jsCommon=$this->get_js_common();//if(!$this->myFields['static']['common']) {$jsCommon=$this->get_js_common();$this->myFields['static']['common']=true;}
 	return (!$this->con_js?'':$jsCommon).$out;

 }

    /**
	  * Restituisce un myLink con la url memorizzata nell'istanza
	  *
	  * @param	 string $tooltip E' l'eventuale tooltip
	  * @param	 '_blank'|'_parent'|'_self' $target Opzionale
	  * @param	 string $classe E' l'eventuale classe css da utilizzare per il link
	  * @return  myLink
	  *
      * <code>
      *   $u=new myURL('nome','http://www.italia.gov.it');
      *   $icona=new myIcon('prova.gif');
      *   $icona->set_link($u->get_myLink());
      *   echo $icona->get_html();
      * </code>
	  */
  public function get_myLink($tooltip="",$target='',$classe=''){
 	if (!$this->get_value() || $this->get_errore_diviso_singolo()) return;
 	if (!$tooltip) {$tooltip=$this->get_componenti();
 					$tooltip=$tooltip['host'];
 					}
 	return new myLink($this->get_value(),$tooltip,$target,$classe);
 }


}