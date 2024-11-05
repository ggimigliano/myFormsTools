<?php
/**
 * Contains Gimi\myFormsTools\myMenu.
 */

namespace Gimi\myFormsTools;


use Gimi\myFormsTools\PckmyFields\myField;
use Gimi\myFormsTools\PckmyFields\myTag;



/**
 * @author Gianluca GIMIGLIANO
 */
	
class myMenu {
protected $padri=array();	
protected $extrahtml,$sostituzioni=array('$sfondo_selezione_menu'=>'#0A246A',
										 '$sfondo_selezione_sottomenu'=>'#0A246A',
										 '$sfondo_menu'=>'#D4D0C8',
										 '$sfondo_sottomenu'=>'#D4D0C8',
										 
										 '$colore_selezione_menu'=>'white',
										 '$colore_selezione_sottomenu'=>'white',
										 
										 '$colore_menu'=>'#000000',
										 '$colore_sottomenu'=>'#000000',
										  
										 '$colore_bordo'=>'#808080',
										 '$h'=>'h2',
										 '$una_riga'=>''//white-space: nowrap;
										
										 );
protected $id_menu,$larghezza=array();
static private $inclusoJs=false; 
protected $cssFile,$cssAdd,$cssPred;
protected $intestazione='h3';
	
	/**
	 * @ignore
	 **/ 
	protected function Load_CSS(){
		$this->sostituzioni['$h']=$this->intestazione;
		$testo=strtr($this->cssPred.file_get_contents($this->cssFile).$this->cssAdd,array("\r"=>'',"\t"=>'',"\n"=>'','"'=>"'")+$this->sostituzioni);
		$testo=preg_replace('#/\*.+\*/#U','',$testo);
	
		//return "<style type='text/css'>$testo</style>";
		$matches=array();
		preg_match_all('/[^{]+{[^}]+}/U',$testo,$matches);
		foreach ($matches[0] as &$m) {$m=trim((string) $m);
									  $out1.="document.styleSheets[0].cssText+=\"$m\";\n";
									  $out2.="document.styleSheets[0].insertRule(\"$m\",i++);\n";
									  }
		$out1.="document.styleSheets[0].cssText+=\"#{$this->id_menu}{float:none;} body{behavior:url(/".myField::get_MyFormsPath()."css/mymenu.htc);font-size:100%; }#{$this->id_menu} ul li{float:left;width:100%;}#{$this->id_menu} {$this->intestazione}, #{$this->id_menu} a{height:1%;}\";";
		return "<script type='text/javascript'>
		if (document.styleSheets[0].cssText) { $out1 }
		 							    else {i=document.styleSheets[0].cssRules.length;
		 							    	   $out2
											 }
		</script>";
	}

/**
	 * Costruttore riceve un array bidymensionale in cui: 
	 * la prima chiave e' una chiave univoca della voce
	 * la seconda e' la chiave del menu padre (eventualmente null)
	 * il valore è il contenuto del menu, e' un testo html o un oggetto con il metodo get_html
	 * 
	 * <code>
	 * $v=array();
	 * $v[1]['']='primo';
	 * $v[2]['']='secondo';
	 * $v[3][1]='<a href="#">terzo</a>';
	 * $v[4][2]='altrosotto';
	 * $v[5][2]=new MyLink("#4",null,'quarto'); //anche un oggetto che possiede il metodo get_html
	 * $v[6][4]='<a href="#">quinto</a>';
	 * $v[7][4]='<a href="#">sesto</a>';
	 * $v[8][4]='<a href="#">settimo</a>';
	 * $menu=new mymenu($v);
	 * echo $menu->get_html();
	 * </code>
	 * 
	 * @param array $opzioni
	 */
	 public function __construct(array $opzioni=array()){
		$this->id_menu="my_menu_".md5(microtime(true));
		//$this->id_menu="menu";
		if (is_array($opzioni))
			foreach ($opzioni as $chiave=>&$tmp) 
				if (is_array($tmp)) foreach ($tmp as $padre=>&$contenuto) 
					if (is_array($contenuto)) $this->add_opzione($chiave,$padre,$contenuto[0],$contenuto[1]);
										else  $this->add_opzione($chiave,$padre,$contenuto);
		$this->cssFile=myField::get_PathSito().myField::get_MyFormsPath().'css/mymenu.css';		
		$this->sostituzioni['#menu']='#'.$this->id_menu;						
	}
	
	
	/**
	 * Aggiunge del css in forma testuale
	 *
	 * @param string $testoCss testo css da aggiungere
	 * @param boolean $fine	se true alla fine del css proprio del menu se false all'inizio
	 */
	 public function add_css_text($testoCss,$fine=true) {
		if ($fine) 	$this->cssAdd.=$testoCss;
		 	else	$this->cssPred=$testoCss.$this->cssPred;
	}
	
	
	/**
	 * Aggiunge un'opzione al menu
	 *
	 * @param string 		 $chiave    della voce di menu
	 * @param string 		 $padre	    chiave del padre (eventualmente null)
	 * @param string/oggetto $contenuto Contenuto del menu, e' un testo html o un oggetto con il metodo get_html
	 */
	 public function add_opzione($chiave,$padre,$contenuto,myTag $tag=null) {
		$this->padri[$padre][$chiave]=$contenuto;
		if ($tag!==null) $this->extrahtml[$chiave]=$tag->get_attributi();
	}
	
	
	/**
	 * Indica un eventuale css personalizzato da usare
	 *
	 * @param string $file percorso del css da caricare
	 */
	 public function set_css($file) {
		$this->cssFile=$file;
	}
	
	/**
	 * Imposta una larghezza minima in em, funziona bene da +ie7
	 *
	 * @param float $em
	 * @param string $id e' l'id del nodo a cui applicarla, se omesso si applica a tutti
	 */
	 public function set_larghezza_voce($em,$id='') {
		$this->larghezza[$id]=$em;	
	}
	
	/**
	 * Imposta i colori degli sfondi
	 *
	 * @param string $sfondomenu
	 * @param string $sfondosottomenu
	 * @param string $sfondoselezione
	 */
	 public function set_colori_sfondo($sfondomenu='',$sfondosottomenu='',$sfondoselezione='') {
		if ($sfondomenu) $this->sostituzioni['$sfondo_menu']=$sfondomenu;
		if ($sfondoselezione) $this->sostituzioni['$sfondo_selezione']=$sfondoselezione;
		if ($sfondosottomenu) $this->sostituzioni['$sfondo_sottomenu']=$sfondosottomenu;
	}
	
	
	
	/**
	 * Imposta i colori dei testi
	 *
	 * @param string $menu
	 * @param string $sottomenu
	 * @param string $selezione
	 * @param string $bordo
	 */
	 public function set_colori_testo($menu='',$sottomenu='',$selezione='',$bordo='') {
		if ($menu) $this->sostituzioni['$colore_menu']=$menu;
		if ($selezione) $this->sostituzioni['$colore_selezione']=$selezione;
		if ($sottomenu) $this->sostituzioni['$colore_sottomenu']=$sottomenu;
		if ($bordo) $this->sostituzioni['$colore_bordo']=$bordo;
	}
	
	
	/**
	 * Se usato si puo attivare/disattivare la visualizzazion delle voci su una riga 
	 * di default attiva
	 *
	 * @param boolean $modalita
	 */
	 public function set_suunariga($modalita=true) {
		if ($modalita) $this->sostituzioni['$una_riga']='white-space: nowrap;';
			      else $this->sostituzioni['$una_riga']='';
	}
	
	/**
	 * @ignore
	 */
	protected function maxlen($padre,$max=0){
	    $ms=$is=0;
	    if (isset($this->padri[$padre]) && is_array($this->padri[$padre]))
			foreach ($this->padri[$padre] as $valore) {
					if (is_object($valore) && method_exists ($valore, 'get_html')) $valore=$valore->get_html();
					$valore=strip_tags($valore);
					for ($i=0;$i<strlen($valore);++$i)
									switch ($valore[$i]) {
										case 'i':
										case 't':
										case 'f':
 										case 'j':											
 										case 'l':
 										case '1': ++$is;break;
 										case 'm': $ms++;
 										   		   break;  	
										default: {
												  if (strtotime($valore[$i])==$valore[$i]) $ms++;	
												 }
									}   		   
									
					$resto=strlen($valore)-$ms-$is;
					$l=$resto/1.25+$ms+$is/3;
					$max=round(max($max,$l,$this->larghezza[$padre]),1);
					}
		 		
		 return $max;			
	}
	
	/**
	 * Restutuisce l'html del menu
	 *
	 * @return string
	 */
	 public function get_html($padre='',$max=0) {
	   $max=$this->maxlen($padre,$max);
	   $stile="style='width:{$max}em'";
	   if (is_array($this->padri[$padre]))
			foreach ($this->padri[$padre] as $chiave=>$valore) {
					if (is_object($valore) && method_exists ($valore, 'get_html')) $valore=$valore->get_html();
					$aggiungere_div=strip_tags($valore)==$valore;
							
					if (!$padre) $valore= "<{$this->intestazione}>$valore</{$this->intestazione}>"; 
					if($aggiungere_div) $valore="<div>$valore</div>";		
					
				    $htmlTemp.= "<li {$this->extrahtml[$chiave]} $stile>$valore\n".$this->get_html($chiave,$max)."</li>\n";
				    
					if (!$padre) {$html.="<ul style='margin:0px!important;border:0!important;padding:0!important;'>$htmlTemp</ul>";$htmlTemp='';}
							else {$html=$htmlTemp;}
					}
		if($html) {	
		 if (!$padre)  return $this->Load_CSS()."<div id='{$this->id_menu}'>$html</div><div style='clear:both'> </div>";		
		 	      else return "<ul style='margin:0px!important;border:0!important;padding:0!important;'>$html</ul>";
		}
	}
	
	/**
	 * @ignore
	 */
	 public function __toString(){
		return $this->get_html();
	}
}