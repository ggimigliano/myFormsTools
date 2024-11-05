<?php
/**
 * Contains Gimi\myFormsTools\PckmyOffice\myExcel.
 */

namespace Gimi\myFormsTools\PckmyOffice;


use Gimi\myFormsTools\PckmyFields\myTag;
use Gimi\myFormsTools\PckmyFields\myDate;
use Gimi\myFormsTools\PckmyTables\myMatrix;
use Gimi\myFormsTools\PckmyTables\myTable;
use Gimi\myFormsTools\myCSS;


/**
* 
* Questa classe permette di creare dei file in html che Excel sia in grado gestire
* 
* unica limitazione: si puo' mettere solo un "foglio" per file
* <code>
* $dati=array(array('colonna1','colonna2','colonna3'),
* 			  array('primo',1,'1/2/2005'),
*			  array('secondo',2,'2005-2-1'),
*			  array('terzo',3,'1-2/2006'),
* 			  array('quarto',4,'11/2/2006'),
* 			  array('quinto',5,'1/12/2006'),
* 			  array('sesto',6,'12/2/2006'),
* 			  array('settimo',7,'12/2/2006')
* 			);
* $xls=new MyExcel('Nome Foglio',$dati);
*
* $xls->set_style('text-decoration: underline; font-weight: bold','A',1,$xls->get_n_cols()); //RENDE MAIUSCOLA E SOTTOLINEA LA PRIMA RIGA
*
*
* $xls->Set_Formula('SUM(B2:A'.$xls->get_n_rows().')',
* 	 					  'B',
* 	  					  $xls->get_n_rows()+1); //inserisco la SOMMA dei valori della colonna A dopo l'ultima riga della colonna "A"
* $xls->set_valore('<b>Totale</b>','A',$xls->get_n_rows()); //inserisco la scritta "Totale" IN GRASSETTO a quella che adesso e' l'ultima riga della colonna "A"
* $xls->set_protezione('A',$xls->get_n_rows(),$xls->get_n_COLS());   //PROTEGGE L'ULTIMA RIGA
*
* $xls->unisci_celle('A',3,1,2);
*
* $xls->allinea_orizzontale('center');  //ALLINEA AL CENTRO IL CONTENUTO DI TUTTA LA TABELLA
* $xls->allinea_verticale('top','A',1,1,$xls->get_n_rows());  //ALLINEA IN ALTO IL CONTENUTO DELLA PRIMA COLONNA
*
* $xls->set_colori('red','blue','B',2,$xls->get_n_cols()-1,$xls->get_n_rows()-2);
*
* $xls->BloccaRiquadri(2);
*
*
* $xls->send('file.xls');				  //restituisco il file xls con il nome file.xls
* </code>
*
*
*/
	
class myExcel extends myTable  {
protected /** @ignore */ $built=false,$stream,$attributi,$info_cell, $proteggi='False';


	/**
	* Costruttore di classe
	 *
     * 
	 * @param   string $nome e' Nome della classe
	 * @param   array $valori array di array con i valori
     * @param    boolean $fetch_intestazioni Se true usa le chiavi della prima riga come intestazione
    **/
	 public function __construct($nome,$valori=array(),$fetch_intestazioni=true) {

		myMatrix::__construct($valori,$fetch_intestazioni);

		$this->attributi['nome']=(strlen(trim((string) $nome))==0?'Foglio1':$nome);
		$this->attributi['query']=array();
	}

	
	protected function put_stream($dati) {
	    if(!$this->stream) $this->set_output_stream();
		@fwrite($this->stream, myExcel::assolutizza($dati));
	}


 /**  */
	 public function stili(&$attuale,$attributi) {
		$align=$this->estrai_attributo($attributi,'align');
		if ($align) $attuale['style']['text-align']=$align;
		$valign=$this->estrai_attributo($attributi,'valign');
		if ($valign) $attuale['style']['vertical-align']=$align;
		$style=$this->estrai_attributo($attributi,'style');
		if($style) {$css=new MyCss();
					$attuale['style']=array_merge((array) $attuale['style'],(array) $css->parseStyle($style));
					}
		$class=$this->estrai_attributo($attributi,'class');
		if ($class) $attuale['class']=$class;
		return $attributi;
	}



	/**
	 * Es.
	 * <code>
	 * $tabella=new myTableQRY($conn,"select stipendio,matricola,cognome from utente"
	 * 	 					   true,
	 *						  'border=1 cellspacing=0 cellpadding=1');  //creo mytable
	 * $xls=new MyExcel('fog');  //creo xls vuoto
	 * $xls->Importa_MyTable($tabella,array(1,0)); //importo solo la terza e la prima colonna in quest'ordine
	 * $xls->send('file.xls');
	 * </code>
	 *
	 * @param   myTable $MyTable e' la tabella a partire della quale costr. l'excel
 	 * @param    array $colonne E' opzionale e se inserito indica i numeri delle colonne da visualizzare (viene rispettato l'ordine)
     * @param    array $righe E' opzionale e se inserito indica i numeri delle righe da visualizzare (viene rispettato l'ordine)
     * @param    boolean $intestazione Indica se deve essere inportata anche l'eventuale intestazione
     * @param    boolean $formattato Indica se deve essere rispettato le formattazuione della tabella impostata nella mytable
     */
	 public function Importa_MyTable($MyTable,$colonne='',$righe='',$intestazione=true,$formattato=true) {
	    $info_cella=array();
		if(!$colonne && !$righe && !$formattato) 
					{
					  $this->set_matrix($MyTable->get_matrix(),true);	
					  if($intestazione) $this->set_intestazione($MyTable->get_intestazioni());
					  return $this;
					}
					
	     $valori=array();
		 if ($formattato) $this->attributi['TABLE']=$MyTable->ricalcola_TAG(0,0,'TABLE');

		 
		 $usa_ricalcola_cella=$MyTable->isOverridden('ricalcola_cella','myTable');
		
		 if (!$righe)	for ($y=0;$y<$MyTable->get_N_rows();$y++)	 $righe[]=$y;
		 if (!$colonne) for ($x=0;$x<$MyTable->get_N_cols();$x++)  $colonne[]=$x;
		 $i=0;
		 $d=new myDate('');
		 if ($righe)
		     foreach ($righe as &$y) {
		 		 $j=0;
		 		 foreach ($colonne as &$x)
		 		 			{
						    if ($formattato)
						        			{
						        			 $this->info_cella[$i][$j]['TD']['ereditati']=$this->stili($this->info_cella[$i][$j]['TD'],
						        			 											   $MyTable->ricalcola_TAG($y,0,'TR'));
							  				 $this->info_cella[$i][$j]['TD']['ereditati'].=" ". $this->stili($this->info_cella[$i][$j]['TD'],
						        			 											   $MyTable->ricalcola_TAG($y,$x,'TD'));
						        			}
							if($usa_ricalcola_cella) 	$valore=$MyTable->ricalcola_cella($y,$x);
												   else $valore=$MyTable->get_cell($y,$x);
												   
							if(strlen(trim((string) $valore))) 
								{$d->set_value(strip_tags($valore));
								 if (!$d->Errore()) $valore=$d->get_formatted();
								}
							$valori[$i][$j]=$valore;
							$j++;
						    }
				$i++;
		 }


	  if ($intestazione && $MyTable->get_intestazioni())
			{$intesta=$MyTable->get_intestazioni();
			 foreach (array_keys($intesta) as $x)
			 		{
				 	if ($formattato)
				 			{
		  					$info_cella[$x]['TD']['ereditati']=     $this->stili($info_cella[$x]['TD'],$MyTable->ricalcola_TAG_intestazione($x,'TR'));
		  					$info_cella[$x]['TD']['ereditati'].=' '.$this->stili($info_cella[$x]['TD'],$MyTable->ricalcola_TAG_intestazione($x,'TD'));
							}
		  		// 	$intesta_new[$x]=$intesta[$x];

			 		}

			 $this->set_intestazioni($intesta);
			 if ($formattato) $this->info_cella[-1]=$info_cella;
			}
		$this->valori=&$valori;
		return $this;
	}



  /**
    * Viene chiamata per ogni cella quando si costruisce l'html della tabella, estendendo questa classe e
    * riscrivendo questo metodo e' possibile inserire regole di visualizzazione personalizzate in base a riga,colonna
    * es. se si vuole che il dato della colonna 6 sia un link che usa il valore della colonna 7
    * <code>Class prova extends myTable {
    * 		function &ricalcola_cella($riga,$colonna) {
	* 				if ($colonna!=6) return parent::ricalcola_cella($riga,$colonna);
	* 							else return '&lt;a href="link.php?parametro='.$this->get_cell($riga,7).'"&gt;'.$this->get_cell($riga,6).'&lt;/a&gt;';
	*  		}
	* }</code>
	* @param    int $riga
    * @param    int $colonna
    * @return   string Il valore che deve comparire nella cella della tabella
    */
	function &ricalcola_cella($riga, $colonna) {
		return $this->get_cell($riga,$colonna);
	}


	 /**  */
	 public function maxr($x) {
		if (!$x) return;
		return max(@array_keys($x));
	}

    /**  */
	 public function maxc($x) {
	    $max=-10000;
		if ($x) foreach ($x as $xx) $max=max($max,max(@array_keys((array) $xx)));
		return $max;
	}


	 /**  */
	 public function calcolacoord($x) {
		if (is_int($x)) $x-=1;
				   else $x=ord(strtoupper($x))-ord('A');
        return $x;
	}


/**  */
	 public function estrai_attributo(&$x,$attr,$coppia=false) {
		while ($x!=($X=str_replace(array(' =','= '),array('=','=') ,$x))) $x=$X;
		
		$p1=$p=stripos($x,$attr);
		if ($p===false) return null;

		$p+=strlen($attr);
		$attr=substr($x,$p1,$p1+strlen($attr));
		$x.=' ';
		if ($x[$p]=="'") {$p++;$f=stripos(($x),"'",$p+2);$sep="'";}
				elseif($x[$p]=='"') {$p++; $f=stripos(($x),'"',$p+2);$sep='"';}
					else $f=stripos($x," ",$p+2);


		if ($p>=$f) return null;
		$out=substr($x,$p,$f-$p);
		$x=str_replace(substr($x,$p1,$f-$p1+1),'',$x);
		if (!$coppia) return $out;
					else return array('val'=>$out,'sep'=>$sep,'attr'=>$attr);
	}


	/**  */
	/*function estrai_attributo(&$x,$attr) {
		$attr=trim(strtolower($attr));
		while ($x!=($X=str_replace(' =','=',$x))) $x=$X;
		while ($x!=($X=str_replace('= ','=',$x))) $x=$X;

		$p1=$p=strpos(strtolower($x),$attr);
		if ($p===false) return null;
		$p=1+strpos(strtolower($x),'=',$p+1);
		$extra=1;
		$x.=' ';
		if ($x[$p]=="'") {$p++;$f=strpos(strtolower($x),"'",$p+2);}
				elseif($x[$p]=='"') {$p++; $f=strpos(strtolower($x),'"',$p+2);}
					else $f=strpos(strtolower($x)," ",$p+2);


		if ($p>=$f) return null;
		$out=substr($x,$p,$f-$p);
		$x=str_replace(substr($x,$p1,$f-$p1+1),'',$x);
        return $out;
	}
*/


    /**  */
	 public function implodi($x) {
		if (is_array($x))
				foreach ($x as $k=>$v) {
										if (is_array($v)) {$u='';
														   foreach ($v as $kk=>&$vv)
														   			if ($k=='style')  $u.="$kk:$vv;";
														   					    else  $u.="$kk$vv";
														   $out.=" $k=\"$u\"";
														   }
													 else $out.=" ".trim((string) $v);
									   }
        return $out;
	}

	/**  */
	 public function assolutizza($html) {
		$dir=explode('/',$_SERVER['PHP_SELF']);
		unset($dir[count($dir)-1]);
		$dir=implode('/',$dir);
		$relativo=$_SERVER['HTTP_HOST']."$dir/";
		$assoluto=$_SERVER['HTTP_HOST'];

		$pre[]=" action=";
	    $pre[]=" src=";
	    $pre[]=" background=";
	    $pre[]=" href=";
	    $pre[]="url\(";
	    $pre[]=" codebase=";
	    $pre[]=" url=";
	    $pre[]=" archive=";
	    $pre[]=" action=";
	    $html=explode('<',$html);
	    foreach ($html as $i=>$tag)
	    	if (strpos($tag,'>')!==false)
	    		{
	    		$tag=explode(' ',$tag,2);
	    		if(count($tag)<2) $html[$i]=$tag[0]; 
	    			else{
	    			$corretti='';
	    			foreach ($pre as $attr)
	    		   	  if (stripos($html[$i],$attr)!==false)
	    				 {$old=$tag[1];
	    				  $tag[1]=' '.$tag[1];
	    				  $v=myExcel::estrai_attributo($tag[1],$attr,true);
	    				
	    				  $V=$v['val'];
	    				  $S=$v['sep'];
	    				  $A=$v['attr'];
	    				  if(parse_url($V)==null) $tag[1]=$old;
	    				  else {
	    				    	if ($V[0]=='/') $V="http://".str_replace('//','/',myTag::htmlentities("$assoluto$V"));
	    							elseif(stripos($V,'http://')!==0) $V="http://".str_replace('//','/',myTag::htmlentities("$relativo$V"));
	    				 		$corretti.="$A$S$V$S ";
	    				  	}
	    				}
					if (stripos($tag[0],'param')!==false && strpos($tag[1],'name="movie"')!==false)
	    				 {$old=$tag[1];
	    				  $tag[1]=' '.$tag[1];
	    				  $attr=" value=";

	    				  $v=myExcel::estrai_attributo($tag[1],$attr,true);
	    				  $V=$v['val'];
	    				  $S=$v['sep'];
	    				  $A=$v['attr'];
	    				  if(parse_url($V)==null) $tag[1]=$old;
	    				  else {
	    				    	if ($V[0]=='/') $V="http://".str_replace('//','/',myTag::htmlentities("$assoluto$V"));
	    							elseif(stripos($V,'http://')!==0) $V="http://".str_replace('//','/',myTag::htmlentities("$relativo$V"));
	    				 		$corretti.="$A$S$V$S ";
	    				  		}
	    				}
	    			$tag[1]=$corretti.$tag[1];
	    			$html[$i]=implode(' ',$tag);
	    			}
	    		}
	    $html=implode('<',$html);
	    return $html;
	}




	/*function ____assolutizza($html) {


		$delim[]='"';
	    $delim[]="'";
	    $delim[]="";
	    $pre[]="src=";
	    $pre[]="background=";
	    $pre[]="href=";
	    $pre[]="url\(";
	    $pre[]="codebase=";
	    $pre[]="url=";
	    $pre[]="archive=";
	    $pre[]="action=";
	    foreach ($pre as $pref) foreach ($delim as $del) {
	    				//$html=preg_replace("/($pref$del)"."([a-zA-Z0-9\_]{})/i",$pref.$del.'http://'.$_SERVER['HTTP_HOST']."$dir/\$3",$html);

	    				//$html=preg_replace("/($pref$del\/)/i",$pref.$del.'http://'.$_SERVER['HTTP_HOST'].'/',$html);
	    				//$html=preg_replace("/($pref$del\.)/i",$pref.$del.',$html);
	    				$html=preg_replace("/($pref$del)([a-zA-Z0-9\_\/\:\.\&\?\#]+)/e","(ereg('^http:','\\2') || ereg('^#','\\2') ? stripslashes('\\1\\2') :(ereg('^\/','\\2')?stripslashes('\\1$assoluto\\2'):stripslashes('\\1$relativo\\2')))",$html);
	    				}
	    return $html;
	}
	*/

	 public function get_tipo($x) {
		if (strlen($x)>0)
			{
			 if (preg_match('/^([\+\-]{0,1}[0-9]*\.{0,1}[0-9]?)$/',$x) || //float
			 	 preg_match('/^(([0-9]{1,2})([\.|\-|\:]{1,1})([0-9]{1,2}))(\.|\-|\:([0-9]{1,2}){0,1})?$/',$x) || //time
			 	 preg_match('/^(([0-9]{1,4})[/.-]([0-9]{1,2})[/.-]([0-9]{1,4}))$/',$x) //data aa-mm-gg gg-mm-aa
			 	 ) return " x:num ";

			}
	}


    function &get_html($colonne='',$righe='') {
   // echo "<pre>";     print_r($this->info_cella[0][0]);      exit;
      if(!$colonne) $colonne=array();
      if(!$righe) $righe=array();
      $this->put_stream('<table '. $this->attributi['TABLE'].' '.$this->implodi($this->info_cella[0][0]['TABLE']).'>');
      $i=0;
      
      
      $rigahtml='';
    
      if($this->get_intestazioni()) {
							      	foreach($this->get_intestazioni() as $j=>$intesta)
										    $rigahtml.='<td '.$this->implodi($this->info_cella[-1][$j]['TD']).'>'.$intesta.'</td>';
								  	$this->put_stream("<tr ".$this->implodi($this->info_cella[$i][$j]['TR']).">$rigahtml</tr>\n");
							      }
							   
	  $rig=$this->get_n_rows();
	  $col=$this->get_n_cols();
	  
      for($i=0;$i<$rig;$i++)
      	{
	      $rigahtml='';
	 	  for($j=0;$j<$col;$j++)
	 	 				if (!$this->info_cella[$i][$j]['nascosta'])
	 	 						{
	 	   					     $rigahtml.='<td '.$this->implodi($this->info_cella[$i][$j]['TD']).'>'.$this->ricalcola_cella($i,$j).'</td>';
	 	   						}
	 	 // echo "$i $j ".$this->implodi($this->info_cella[$i][$j]['TR'])."<br>";
		  $this->put_stream("<tr ".$this->implodi($this->info_cella[$i][$j]['TR']).">$rigahtml</tr>\n");
		 }
	  $this->put_stream("</table>");
	  return null;
	}



	/**
	* IMPOSTAZIONI DI STAMPA
	 * @param   'orizzontale'|'verticale' $verso  del foglio
	 * @param   int $dx  margine in cm
	 * @param   int $su  margine in cm
	 * @param   int $sx  margine in cm
	 * @param   int $giu margine in cm
	 * @param   int $intestazione intestazione in cm
	 * @param   int $piepagina piepagina in cm
	 * @param   int $larghezza in cm
	 * @param   int $altezza in cm
	 */
	 public function imposta_pagina($verso="",$dx="",$su="",$sx="",$giu="",$intestazione="",$piepagina="",$larghezza=21,$altezza=29.7) {
	    if ($verso=='orizzontale' || $verso=='landscape')	$verso='landscape';
	                                                    else $verso='';
	    $this->attributi[$this->attuale]['size']="size:{$larghezza}cm {$altezza}cm ";
	    $this->attributi[$this->attuale]['margini']="".
	    ($dx!=='' && $sx!=='' && $su!=='' && $giu!=='' ?"margin: ".$dx."cm ".$su."cm ".$sx."cm ".$giu."cm;\n":"").
	    ($intestazione?"mso-header-margin:".$intestazione."cm;\n":"").
	    ($piepagina?"mso-footer-margin:".$piepagina."cm;\n":"").
	    ($verso?"mso-page-orientation:$verso;\n":"").
	    "mso-horizontal-page-align:center;\n";
	    return $this;
	}



	/**
	*Setta intestazione e pie pagina, i dati vanno inseriti secondo la codifica Excel
	* Es. volendo mettere come intestazione il nome del foglio e come pie' pagina la scritta Pagina x di y
	* <code>
	*  $xls->set_intestazione("&H","Pagina &P di &M");
	* </code>
	*
	* @param   string $intestazione
	* @param   string $piepagina
	*/
	 public function set_intestazione_piepagina($intestazione="",$piepagina="") {
	$this->attributi['intestazione']='';
	if ($intestazione) $this->attributi['intestazione']="mso-header-data:\"$intestazione\";";
	if ($piepagina) $this->attributi['intestazione'].="mso-footer-data:\"$piepagina\";";
	return $this;
	}



	/**
	* Blocca i riquadri sulla riga $y
	 * <code>
	 *     $xls->BloccaRiquadri(2); //blocca la prima riga
	 * </code>
	 * @param   int $y
    */
	 public function BloccaRiquadri($y){
		$y=$this->calcolacoord($y);
	 	$this->attributi['blocca']="
		   <x:FreezePanes/>
           <x:FrozenNoSplit/>
		   <x:SplitHorizontal>1</x:SplitHorizontal>
     	   <x:TopRowBottomPane>$y</x:TopRowBottomPane>
     	   <x:ActivePane>2</x:ActivePane>";
	 	return $this;
	}


	 public function get_n_rows() {
		return  1+max($this->maxr($this->valori),$this->maxr($this->info_cella));
 	}


	 public function get_n_cols() {
        return 1+max($this->maxc($this->valori),$this->maxc($this->info_cella));
	}

	/**
	* Unisci celle
	 * @param   string|int $x  E' LA LETTERA  O NUMERO DELLA COLONNA (a partire da 1)
	 * @param   int 	 $y  RIGA A PARTIRE DA 1
	 * @param   int $ncolonne
	 * @param   int $nrighe
	 */
	 public function unisci_celle($x,$y,$ncolonne='',$nrighe='') {
		if ($ncolonne<1) $ncolonne=1;
		if ($nrighe<1) $nrighe=1;
		$x=$this->calcolacoord($x);
		$y=$this->calcolacoord($y);
	#	$rig=$this->get_n_rows()-1;
    #  	$col=$this->get_n_cols()-1;
      	if ($nrighe>1) {$this->info_cella[$y][$x]['TD']['rowspan']="rowspan='$nrighe'";}
      	if ($ncolonne>1) {$this->info_cella[$y][$x]['TD']['colspan']="colspan='$ncolonne'";}
		for ($i=0;$i<$nrighe;$i++)
			for ($j=0;$j<$ncolonne;$j++)
				if ($j!=0 || $i!=0)	$this->info_cella[$i+$y][$j+$x]['nascosta']=1;
	   return $this;
	}



	/**
	* Inserisce interruzione di pagina
	 * @param   int 	 $y       RIGA A PARTIRE DA 1
	 */
	 public function add_interruzione_pagina($y) {
		$y=$this->calcolacoord($y);
		//$this->info_cella[$y][2]['TR']['style']['page-break-before']='always';
		$this->attributi['interruzioni'].= "
		<x:RowBreaks>
      <x:RowBreak>
       <x:Row>$y</x:Row>
      </x:RowBreak>
     </x:RowBreaks>";
		return $this;
	}


	/**
	* Imposta caratteristiche grafiche delle celle con foglio di style
	 *  es, per impostare il testo delle celle A1 e A2 in grassetto e sottolineato
	 * <code>
	 *  $xls->set_style('text-decoration: underline; font-weight: bold','A',1,2);
	 * </code>
	 * @param   string|int $x E' LA LETTERA  O NUMERO DELLA COLONNA (a partire da 1)
	 * @param   int $y      RIGA A PARTIRE DA 1
	 * @param   string  $parametri
	 * @param   int $ncolonne
	 * @param   int $nrighe
	 * @param   boolean $verifica se vero effettua verifica sui nomi dei parametri passati
	 */
	 public function set_style($parametri,$x='',$y='',$ncolonne='',$nrighe='',$verifica=true) {
		if ($ncolonne<1) $ncolonne=1;
		if ($nrighe<1) $nrighe=1;
		$css=new MyCss();
		$par=$css->parseStyle($parametri,$verifica);
		if (!$par) return;
		if (!$x && !$y)
				{$this->info_cella[0][0]['TABLE']['style']=array_merge((array) $this->info_cella[0][0]['TABLE']['style'],(array) $par);
				 return;
				}
		$x=$this->calcolacoord($x);
		$y=$this->calcolacoord($y);
		for ($i=0;$i<$nrighe;$i++)
			for ($j=0;$j<$ncolonne;$j++) {
				$this->info_cella[$i+$y][$j+$x]['TD']['style']=array_merge((array) $this->info_cella[$i+$y][$j+$x]['TD']['style'],(array) $par);
			}
		return $this;
	}



	/**
	* Allineamento verticale del contenuto delle celle
	 * @param   'top'|'bottom'|middle' $allineamento
	 * @param   string|int $x       E' LA LETTERA  O NUMERO DELLA COLONNA (a partire da 1)
	 * @param   int 	 $y       RIGA A PARTIRE DA 1
	 * @param   int $ncolonne
	 * @param   int $nrighe
	 */
	 public function allinea_verticale($allineamento,$x='',$y='',$ncolonne='',$nrighe='') {
		$this->set_style("vertical-align:$allineamento",$x,$y,$ncolonne,$nrighe);
		return $this;
	}


	/**
	* Allineamento orizzontale del contenuto delle celle
	 * @param 	'center'|'left'|'right' $allineamento
	 * @param   string|int $x E' LA LETTERA  O NUMERO DELLA COLONNA (a partire da 1)
	 * @param   int $y  	RIGA A PARTIRE DA 1
	 * @param   int $ncolonne
	 * @param   int $nrighe
	 */
	 public function allinea_orizzontale($allineamento,$x='',$y='',$ncolonne='',$nrighe='') {
		$this->set_style("text-align:$allineamento",$x,$y,$ncolonne,$nrighe);
		return $this;
	}


	/**
	* Imposta font delle celle
	 * @param   string|int $x  E' LA LETTERA  O NUMERO DELLA COLONNA (a partire da 1)
	 * @param   int $y       RIGA A PARTIRE DA 1
	 * @param 	string $font nome del font
	 * @param 	float  $dim    dimensione in pt
	 * @param   int $nrighe
	 * @param   int $ncolonne
	 */
	 public function set_font($font,$dim='',$x='',$y='',$ncolonne='',$nrighe='') {
		$this->set_style("font-style:$font",$x,$y,$ncolonne,$nrighe);
		if ($dim) $this->set_style("font-size:$dim pt",$x,$y,$ncolonne,$nrighe);
		return $this;
	}



	/**
	* Imposta colori delle celle
	 * @param 	string $ctesto colore del testo es 'red' o '#ff0000'
	 * @param 	string $csfondo colore dello sfondo
	 * @param   string|int $x  E' LA LETTERA  O NUMERO DELLA COLONNA (a partire da 1)
	 * @param   int $y       RIGA A PARTIRE DA 1
	 * @param   int $ncolonne
	 * @param   int $nrighe
	 */
	 public function set_colori($ctesto='',$csfondo='',$x='',$y='',$ncolonne='',$nrighe='') {
		if ($ctesto)  $this->set_style("color:$ctesto",$x,$y,$ncolonne,$nrighe);
		if ($csfondo) $this->set_style("background:$csfondo",$x,$y,$ncolonne,$nrighe);
		return $this;
	}


	/**
	* Protegge celle scelte,
	 * SERVE SOLO AD IMPEDIRE L'INSERIMENTO ERRATO IN ZONE RISERVATE
	 * L'UTENTE PUO' AGEVOLMENTE RIMUOVERE QUESTA PROTEZIONE
	 * @param   string|int $x  E' LA LETTERA  O NUMERO DELLA COLONNA (a partire da 1)
	 * @param   int $y       RIGA A PARTIRE DA 1
	 * @param   int $ncolonne
	 * @param   int $nrighe
	 */
	 public function set_protezione($x='',$y='',$ncolonne='',$nrighe='') {
		$this->set_style("mso-protection:locked visible",$x,$y,$ncolonne,$nrighe);
		$this->proteggi='True';
		return $this;
	}


	/**
	* setta classe css
	 * @param   string $class
	 * @param   string|int $x E' LA LETTERA  O NUMERO DELLA COLONNA (a partire da 1)
	 * @param   int $y  RIGA A PARTIRE DA 1
	 * @param   int $ncolonne
	 * @param   int $nrighe
	 */
	 public function set_class($class,$x='',$y='',$ncolonne='',$nrighe='') {
		if ($ncolonne<1) $ncolonne=1;
		if ($nrighe<1) $nrighe=1;
		if (!$x && !$y)
				{
				 $this->info_cella[0][0]['TABLE']['class']= $class;
				 return;
				}
		$x=$this->calcolacoord($x);
		$y=$this->calcolacoord($y);
		for ($i=0;$i<$nrighe;$i++)
			for ($j=0;$j<$ncolonne;$j++)
					$this->info_cella[$i+$y][$j+$x]['TD']['class']=$class;
		return $this;
	}




	/**
	* Inserisce formula
	 * @param   mixed $valore
	 * @param   string|int $x   E' LA LETTERA  O NUMERO DELLA COLONNA (a partire da 1)
	 * @param   int $y        RIGA A PARTIRE DA 1
     */
	 public function set_valore($valore,$x,$y) {
		$vuota=array_fill(0,$this->calcolacoord($x)+1,'');
		for($j=0;$j<$this->calcolacoord($y);$j++) 
				if(!isset($this->valori[$j])) 
					$this->valori[$j]=$vuota;
		$this->valori[$this->calcolacoord($y)][$this->calcolacoord($x)]=$valore;
		return $this;
	}





	/**
	* Inserisce formula
	 * Es.
	 * $tabella=$conn->myTableQRY("select stipendio,matricola,cognome from utente"
	 * 	 					   false,
	 *						  'border=1 cellspacing=0 cellpadding=1');  //creo mytable con
	 * $xls=new MyExcel('fog');  //creo xls vuoto
	 * $xls->Importa_MyTable($tabella,array(1,0)); //importo solo la terza e la prima colonna in quest'ordine
	 *
	 * $xls->Set_Formula('SUM(B2:A'.$xls->get_n_rows().')',
	 * 					  'B',
	 * 					  $xls->get_n_rows()+1);  //inserisco la SOMMA dei valori della colonna A dopo l'ultima riga della colonna "A"
	 *
 	 * $xls->set_valore('<b>Totale</b>','A',$xls->get_n_rows()); //inserisco la scritta "Totale" a quella che adesso e' l'ultima riga della colonna "A"
  	 * $xls->send('file.xls');
	 *
	 *
	 * @param   string|int $x //E' LA LETTERA  O NUMERO DELLA COLONNA (a partire da 1)
	 * @param   int $y  //RIGA A PARTIRE DA 1
	 * @param   string $formula
     */
	 public function set_Formula($formula,$x,$y){
		$this->info_cella[$this->calcolacoord($y)][$this->calcolacoord($x)]['TD']['formule']="x:fmla=\"=$formula\"";
		//echo "<pre>";print_r($this->info_cella);
		return $this;
	}


	/**
	* Definisce un intervallo in cui dovranno essere caricati file esterni,
	 *  x1,y1,x2,y2 definiscono l'area dalla quale si potrà chiamare l'"aggiorna dati"
	 *  x1,y1 e' l'angolo in alto a destra nella quale si posiziona la tabella importata
	 * @param   string $nome
	 * @param   string $url (MAX 255 caratteri)
	 * @param 	boolean $AncheFormattazione se true (default e' false) carica anche la formattazione dell'HTML
	 * @param   string $x1  E' LA LETTERA dell'angolo in alto a destra
	 * @param   int  $y1  RIGA A PARTIRE DA 1  dell'angolo in alto a destra
 	 * @param   string $x1  E' LA LETTERA della colonna  dell'angolo in basso a sinistra
	 * @param   int  $y1  RIGA A PARTIRE DA 1 dell'angolo in basso a sinistra
	 *
    */
	 public function set_query($nome,$url,$AncheFormattazione=false,$x1,$y1,$x2,$y2,$NTabella=''){
	// $url=myTag::htmlentities($url);

	 if (strlen($NTabella))  	$idTabella="<x:HTMLTables><x:Text>$NTabella</x:Text></x:HTMLTables>";
	 						else $idTabella=" <x:EntirePage/>";

	 if ($AncheFormattazione) $AncheFormattazione="<x:HTMLFormat>All</x:HTMLFormat>";
	 $this->attributi['query'][$nome]="
	<x:QueryTable>
	 <x:NoPreserveFormatting/>
     <x:Name>$nome</x:Name>
     <x:AutoFormatFont/>
     <x:AutoFormatPattern/>
     <x:QuerySource>
       <x:QueryType>Web</x:QueryType>
      <x:DoNotJoinDelimiters/>
      <x:NoTextToColumns/>
      <x:EnableRedirections/>
      <x:RefreshedInXl9/>
      $AncheFormattazione
      <x:URLString HRef=\"".substr($url,0,255)."\" />
       $idTabella
	  <x:VersionLastEdit>2</x:VersionLastEdit>
      <x:VersionLastRefresh>2</x:VersionLastRefresh>
     </x:QuerySource>
    </x:QueryTable>";
	$this->attributi['esterni'][$nome]="
  <x:ExcelName>
  <x:Name>$nome</x:Name>
  <x:SheetIndex>1</x:SheetIndex>
  <x:Formula>='".$this->attributi['nome']."'!\$$x1\$$y1:\$$x2\$$y2</x:Formula>
 </x:ExcelName>";
	return $this;
	}


    /**  */
	private function get_worksheet() {
		return "
  	  <x:ExcelWorksheet>
  		 	<x:Name>".$this->attributi['nome']."</x:Name>
 	  <x:WorksheetOptions>
    	 <x:Print>
      		<x:ValidPrinterInfo/>
      		<x:PaperSizeIndex>9</x:PaperSizeIndex>
      		<x:HorizontalResolution>-3</x:HorizontalResolution>
      		<x:VerticalResolution>0</x:VerticalResolution>
     	</x:Print>

	     <x:Selected/>
    	".$this->attributi['blocca']."

     	<x:Panes>
      		<x:Pane>
       			<x:Number>3</x:Number>
      		</x:Pane>
      		<x:Pane>
       			<x:Number>2</x:Number>
       	    	<x:ActiveRow>0</x:ActiveRow>
       		    <x:ActiveCol>0</x:ActiveCol>
      		</x:Pane>
     	</x:Panes>
    ".($this->proteggi=='True'?
       "<x:ProtectContents>".$this->proteggi."</x:ProtectContents>
     	<x:ProtectObjects>False</x:ProtectObjects>
     	<x:ProtectScenarios>False</x:ProtectScenarios>":"")."
      </x:WorksheetOptions>
      <x:PageBreaks>
		".$this->attributi['interruzioni']."
      </x:PageBreaks>
    ".@implode("\n",$this->attributi['query'])."
    </x:ExcelWorksheet>";
	}



	/**  */
	protected function creaExcel($css=array(),$html='') {
		if($this->built) return;
		
		if ($css) foreach ($css as $nomecss) $CSS.=file_get_contents($nomecss);
		$this->put_stream("
		<html xmlns:o=\"urn:schemas-microsoft-com:office:office\"
		xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
		xmlns=\"http://www.w3.org/TR/REC-html40\">
		<head>
		<meta http-equiv=Content-Type content=\"text/html; charset=windows-1252\">
		<meta name=ProgId content=Excel.Sheet>
		<meta name=Generator content=\"Microsoft Excel 11\">
		<!--[if !mso]>
			<style>
				v\:* {behavior:url(#default#VML);}
				o\:* {behavior:url(#default#VML);}
				x\:* {behavior:url(#default#VML);}
				.shape {behavior:url(#default#VML);}
				tr	{mso-height-source:auto;}
				col	{mso-width-source:auto;}
				br	{mso-data-placement:same-cell;}
			</style>
		<![endif]-->
		<!--[if gte mso 9]>
		<xml>
 			<o:DocumentProperties>
 			 <o:Author>MyExcel</o:Author>
 			 <o:Created>".date('Y-m-d')."T".date('H:i:s')."Z</o:Created>
 			 <o:Version>".time()."</o:Version>
 			 <o:Title>". $this->attributi['nome']."</o:Title>
 			</o:DocumentProperties>
		</xml>
		<![endif]-->
		<style>
		<!--
		".($this->attributi['margini'].$this->attributi['intestazione']?"@page{".$this->attributi['margini'].$this->attributi['intestazione']."}":'')."


		table
			{mso-displayed-decimal-separator:\"\,\";
			 mso-displayed-thousand-separator:\"\.\";
			 mso-protection:unlocked visible;
			}
		$CSS
		-->
		</style>
		<!--[if gte mso 9]>
		<xml>
 		<x:ExcelWorkbook>
  		<x:ExcelWorksheets>
			".$this->get_worksheet()."
		</x:ExcelWorksheets>
		
  			<x:WindowTopX>10</x:WindowTopX>
  			<x:WindowTopY>10</x:WindowTopY>
  			<x:ProtectStructure>False</x:ProtectStructure>
  			<x:ProtectWindows>False</x:ProtectWindows>
  		</x:ExcelWorkbook>
		".@implode("\n",$this->attributi['esterni'])."
		</xml><![endif]-->
		</head>
		<body  lang=IT >");
		if($html) $this->put_stream($html);
		 	 else $this->get_html();
		$this->put_stream("</body></html>");
		$this->built=true;
	}

	
	
	/**
	* Invia al client il file excel (determina la chiusura dello script)
	 * @param   string $nomeFile
	 * @param   array $css elenco dei nomi dei css da importare
	 * @param   string $html e' l'html da usare per la creazione del xls in alternativa ai dati memorizzati
	 * @param   boolean $exit se e' true viene eseguito un exit al termine della send, altrimenti lo script chiamante prosegue (in questo caso non dare echo o altre visualizzazioni dopo la send)
  */

	 public function send($nomeFile,$css=array(),$html='',$exit=true,$forza_download=true) {
		//session_cache_limiter('private');
		//$tutto=

		header("Pragma:");
		header("Expires: " . gmdate("D, d M Y H:i:s",time()+30) . " GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	
		header("Cache-Control: private, must-revalidate, post-check=0, pre-check=0");
		header("Content-Description: File Transfer");
		header("Content-Transfer-Encoding: binary");
		header('Content-Type: application/vnd.ms-excel');
		header('X-Content-Type-Options: nosniff');
		if($forza_download) header("Content-Disposition: attachment; filename=\"".basename($nomeFile)."\"");
					else    header("Content-Disposition: inline; filename=\"".basename($nomeFile)."\"");
				

		$this->creaExcel($css,$html);
	
		if (@rewind($this->stream) && !headers_sent())
							{
							$size=fstat($this->stream);
							$size=$size['size'];
							header("Content-Length: ".$size);
							while(!feof($this->stream)) echo @fread($this->stream,4096);
							}
	    if ($exit) exit;
	}


	
/**
 * Alias di myExcel::Salva()
 */
	 public function save($nomeFile,$css=array(),$html='') {
			$this->salva($nomeFile,$css,$html);
	}
	
	

	 public function set_output_stream($stream=''){
		if($stream) $this->stream=$stream;
				else $this->stream=fopen('php://output', 'w');
		return $this;
	}
	
	
	/**
	* Salva il file su disco
	 * @param   string $nomeFile
	 * @param   array $css elenco dei nomi dei css da importare
	 * @param   string $html e' l'html da usare per la creazione del xls in alternativa ai dati memorizzati
	 */
	 public function salva($nomeFile,$css=array(),$html='') {
		$this->creaExcel($css,$html);
		if(!@rewind($this->stream)) return $this;
		$f=@fopen($nomeFile,'w');
		@stream_copy_to_stream($this->stream, $f);
		@fclose($f);
		return $this;
	}
	
	
	
}