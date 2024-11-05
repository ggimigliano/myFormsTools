<?php
/**
 * Contains Gimi\myFormsTools\PckmyOffice\myWord.
 */

namespace Gimi\myFormsTools\PckmyOffice;


use Gimi\myFormsTools\PckmyUtils\myMultiParter;
use Gimi\myFormsTools\PckmyUtils\myRAMStream;



/**
 * 
 * Questa classe permette di creare dei file in html che Word sia in grado gestire
 * 
 * l'uso migliore e' la produzione di documenti destinati alla stampa.
 * Se tali doc dovranno essere archiviati NON inserire immagini nel documento o si
 * correrà il rischio che non saranno sempre visualizzati.
 * <code>
 * //definisco due pagine con dei segnaposto che scelco essere della forma *parola*
 * $prima_pagina='<div align="right">
 * 						Spett. <b>*cognome*</b> *nome*<br>
 * 			   </div>
 * 			   <div align="justify">
 * 			   			Le comunico che lei e' ....
 * 			   </div>';
 *
 * $seconda_pagina='...*esito*';
 *
 * //istanzio l'oggetto inizializzandolo con la prima pagina
 * $doc=new MyWord($prima_pagina);
 *
 * //aggiungo la seconda pagina
 * $doc->add_pagina($seconda_pagina);
 *
 * //dichiaro un array di valori in cui le chiavi corrispondono ai segnaposto
 * $destinatari=array();
 * $destinatari[0]=array('*cognome*'=>'Gimigliano','*nome*'=>'Gianluca','*esito*'=>'Promosso');
 * $destinatari[1]=array('*cognome*'=>'Rossi',     '*nome*'=>'Guido',   '*esito*'=>'Bocciato');
 * $destinatari[2]=array('*cognome*'=>'Foti',      '*nome*'=>'Carlo',   '*esito*'=>'Non dico niente');
 *
 *
 * //In alternativa va bene anche:
 * //$destinatari=$conn->execute("select cognome as '*cognome*' , nome as '*nome*' from persone ");
 *
 *
 * $doc->set_stampa_unione($destinatari);
 *
 * //scelgo un'intestazione ed un pie' pagina che mi mette in basso a destra il numero di pagina
 * $doc->set_intestazione("<b>Ministero Affari Inutili</b>" , '<div align="right">Pag. <span style=\'mso-field-code:" PAGE "\'></span></div>');
 * $doc->set_protezione();
 * $doc->send('file.doc');
 * </code>
 */
	
class myWord  {
protected /** @ignore */ $stream,$attributi,$attuale=1, $proteggi='', $id, $stampaunione, $xmlHeaderFooter;


	/**
	* Costruttore di classe
  	 * 
	 * @param   string $html e' l'html che rappresenta il contenuto
	 */
	 public function __construct($html='') {
		if($html) $this->attributi[$this->attuale]['html']=$html;
		$this->id= md5(uniqid(rand(), true));
	}


		/**  */
	 public function get_sezione($pag) {
	    if(!$this->attributi[$pag]['size']) {
	               if(stripos($this->attributi[$this->attuale]['margini'],'landscape')===false)
	                                           $this->attributi[$pag]['size']="size:595.3pt 842pt;";
	                                      else $this->attributi[$pag]['size']="size:842.0pt 21.0cm;";
	     }
	        
	    
		return "@page WordSection$pag
        {
		  {$this->attributi[$pag]['size']}
		  {$this->attributi[$pag]['margini']}
		  {$this->attributi[$pag]['header']}
		  {$this->attributi[$pag]['footer']}
		  mso-paper-source:0;
		}
		div.WordSection$pag{
             page:WordSection$pag;
            }
		";
	}



	/**
	 * Scrittura effettiva dei dati sullo stream usato dall'istanza
	 */
	protected function put_stream($dati) {
		if(!$this->stream) {myramStream::register('myRamStreamDOC');
							$this->stream=fopen("myRamStreamDOC:///".spl_object_hash($this).".doc", "w+");
							}
		@fwrite($this->stream, $dati);
	}


	/**
	* Inserisce una nuova pagina
	 * @param   string $html e' l'html che rappresenta il contenuto della nuova pagina
	 */
	 public function add_pagina($html) {
	        $this->attributi[$this->attuale]['html'].= "<br clear='all' style='page-break-before:always' />";
			$this->attuale++;
			$this->attributi[$this->attuale]=$this->attributi[$this->attuale-1];
			$this->attributi[$this->attuale]['html']=$html;
			return $this;
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
	 public function imposta_pagina($verso="",$dx="",$su="",$sx="",$giu="",$intestazione="",$piepagina="",$larghezza='',$altezza='') {
	    if ($verso=='orizzontale' || $verso=='landscape')  	 {$verso='landscape';
	                                                          if(!$larghezza) $larghezza=29.7;
	                                                          if(!$altezza)   $altezza=21;
	                                                         }
	                                                    else {$verso='portrait';
	                                                          if(!$larghezza) $larghezza=29.7;
	                                                          if(!$altezza)   $altezza=21;
	                                                         }
        	                                                    
	    $this->attributi[$this->attuale]['size']="size:{$larghezza}cm {$altezza}cm ;mso-page-orientation:$verso;";
	    $this->attributi[$this->attuale]['margini']=($dx!=='' && $sx!=='' && $su!=='' && $giu!=='' ?"margin: ".$dx."cm ".$su."cm ".$sx."cm ".$giu."cm;\n":"").
                                            	    ($intestazione?"mso-header-margin:".$intestazione."cm;\n":"").
                                            	    ($piepagina?"mso-footer-margin:".$piepagina."cm;\n":"").
                                            	    "mso-horizontal-page-align:center;\n";
	    return $this;
	}

	/**
	 * @deprecated
	 */
	 public function set_intestazione($intestazione='',$piepagina="") {
		$this->set_intestazione_piepagina($intestazione,$piepagina);
	}



	/*
	function set_intestazione($intestazione='',$piepagina="") {
		$s=new MySessions('myOffice');
		$intesta=$s->get('intestazioni');
		$id=$this->id;
		if ($intestazione) {$intesta[$id][$this->attuale]['intestazione']=myExcel::assolutizza($intestazione);
						  //  $this->attributi[$this->attuale]['header']="mso-header:url('http://$_SERVER[HTTP_HOST]/".myField::get_MyFormsPath()."myOfficeFiles.php?cosa=Intestazioni&id=$id&".ini_get('session.name')."=".session_id()."') h".$this->attuale.";\n";
						    $this->attributi[$this->attuale]['header']="mso-header:url('file:///c:/$id".session_id().".xml') h".$this->attuale.";\n";
						   }
		if ($piepagina) {$intesta[$id][$this->attuale]['piepagina']=myExcel::assolutizza($piepagina);
						 $this->attributi[$this->attuale]['footer']="mso-footer:url('file:///c:/$id".session_id().".xml') f".$this->attuale.";\n";
						}

		if ($intestazione.$piepagina) $s->set('intestazioni',$intesta);

	}
		*/
	/**
	* IMPOSTAZIONI DI PAGINA
	 * @param   string $intestazione html che rappresenta intestazione
	 * @param   string $piepagina html che rappresenta piepagina
	 */
	 public function set_intestazione_piepagina($intestazione='',$piepagina="") {
		$id=$this->id;
		if ($intestazione) {//$intesta[$id][$this->attuale]['intestazione']=myExcel::assolutizza($intestazione);
						    $this->attributi[$this->attuale]['header']="mso-header:url('MyDoc_file/MyHeader.htm') h".$this->attuale.";\n";
						   }
		if ($piepagina) {//$intesta[$id][$this->attuale]['piepagina']=myExcel::assolutizza($piepagina);
						  $this->attributi[$this->attuale]['footer']="mso-footer:url('MyDoc_file/MyHeader.htm') f".$this->attuale.";\n";
						}

		if ($intestazione.$piepagina) {
				$this->xmlHeaderFooter='<html xmlns:v="urn:schemas-microsoft-com:vml"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1252">
<meta name=ProgId content=Word.Document>
<meta name=Generator content="Microsoft Word 11">
<meta name=Originator content="Microsoft Word 11">
</head>
 <link id=Main-File rel=Main-File href="../MyDoc.htm">
 <![if IE]>
	<base href="file:///C:/'.$id.'/MyDoc_file/MyHeader.htm" id="webarch_temp_base_tag">
 <![endif]>
</head>

<body lang=IT>
'.($intestazione?"<div style='mso-element:header' id=h".$this->attuale."><div class=MsoHeader>$intestazione</div> </div>":"")
 .($piepagina?   "<div style='mso-element:header' id=f".$this->attuale."><div class=MsoFooter>$piepagina</div></div>":"").
"</body></html>
";

	/*		$this->xmlHeaderFooter['filelist.xml']="<xml xmlns:o=\"urn:schemas-microsoft-com:office:office\">
											 <o:MainFile HRef=\"../doc1.htm\"/>
											 <o:File HRef=\"header.htm\"/>
											 <o:File HRef=\"filelist.xml\"/>
 										</xml>";
 										*/
		}
		return $this;
	}



	/**
	* Protegge celle scelte,
	 * SERVE SOLO AD IMPEDIRE L'INSERIMENTO ERRATO IN ZONE RISERVATE
	 * L'UTENTE PUO' AGEVOLMENTE RIMUOVERE QUESTA PROTEZIONE
	 */
	 public function set_protezione() {
		$this->proteggi="  <w:DocumentProtection>ReadOnly</w:DocumentProtection>";
		return $this;

	}


	/**
	* Imposta l'oggetto su cui si deve creare lo stampa_unione.
	 * L'oggetto puo' essere un recordset ADODB o un array di array.
	 * In entrambi i casi per ogni riga dell'oggetto l'html contenuto
	 * nella classe MyWord verrà replicata e gli eventuali segnaposto
	 * presenti nell'html verranno sostituiti con il valore della chiave corrispondente.
     * vedere esempio generale della classe per un esempio d'uso
	 */
	 public function set_stampa_unione($oggetto) {
		$this->stampaunione=$oggetto;
		return $this;
	}


	/** @ignore */
	protected function stampa_unione($html,$str='') {
	  $multip=new myMultiParter();
	  $interPag="<br clear=all style='page-break-before:always'>";
	  if (is_array($this->stampaunione))
	  				{$n=count($this->stampaunione);
	  				 foreach ($this->stampaunione as $chiavi)
	  				 					{ if(--$n==0) $interPag='';
	  				 					  $this->put_stream($multip->EncodeQP(str_replace(array_keys($chiavi),array_values($chiavi),
	  				 					  $html.$interPag)));
	  				 					}
	  				}
	  		  else while (!$this->stampaunione->EOF)
	  		  		{
	  		  		 $chiavi=$this->stampaunione->fields;
	  		  		 $this->stampaunione->movenext();
	  		  		 if($this->stampaunione->EOF) $interPag='';
	  		  		 $this->put_stream($multip->EncodeQP(str_replace(array_keys($chiavi),array_values($chiavi),$html.$interPag)));
	  		  	    }
	}



	/**  */
	function &get_html($html='') {
		if (!$html) foreach (array_keys($this->attributi) as  $pag) 
		             $html.="<div class=\"WordSection$pag\"  >".$this->attributi[$pag]['html']."</div>";
		      else  $html= "<div class=\"WordSection1\">".$html."</div>";
		return $html;
	}



	/**  */
	 public function get_header($css=array()) {
		if (count($css)>0) foreach ($css as $nomecss) $CSS.=file_get_contents($nomecss);
		if($this->xmlHeaderFooter) $CSS.='@page{mso-footnote-separator:url("MyDoc_file/MyHeader.htm") fs;
												 mso-footnote-continuation-separator:url("MyDoc_file/MyHeader.htm") fcs;
												 mso-endnote-separator:url("MyDoc_file/MyHeader.htm") es;
												 mso-endnote-continuation-separator:url("MyDoc_file/MyHeader.htm") ecs;
													mso-footnote-position:beneath-text;}
											@page WordSection
												{
												mso-even-header:url("MyDoc_file/MyHeader.htm") eh1;
												mso-header:url("MyDoc_file/MyHeader.htm") h1;
												mso-even-footer:url("MyDoc_file/MyHeader.htm") ef1;
												mso-footer:url("MyDoc_file/MyHeader.htm") f1;
												mso-first-header:url("MyDoc_file/MyHeader.htm") fh1;
												mso-first-footer:url("MyDoc_file/MyHeader.htm") ff1;
												mso-paper-source:0;}
											div.WordSection
												{page:WordSection;
												 mso-footnote-position:beneath-text;}';
									
		foreach (array_keys($this->attributi) as  $pag)   $CSS.=$this->get_sezione($pag);
		
			return '<html xmlns:v="urn:schemas-microsoft-com:vml"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1252">
<meta name=ProgId content=Word.Document>
<meta name=Generator content="Microsoft Word 11">
<meta name=Originator content="Microsoft Word 11">

<!--[if gte mso 9]>
		<xml>
 			<o:DocumentProperties>
 			 <o:Author>MyWord</o:Author>
 			 <o:Created>'.date('Y-m-d').'T'.date('H:i:s').'Z</o:Created>
 			 <o:Version>'.time().'</o:Version>
 			</o:DocumentProperties>
		</xml>
<![endif]-->
<!--[if gte mso 9]>
<xml>
 <w:WordDocument>
  '.$this->proteggi.'
  <w:View>Print</w:View>
  <w:HyphenationZone>14</w:HyphenationZone>
  <w:PunctuationKerning/>
  <w:ValidateAgainstSchemas/>
  <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
  <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
  <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
  <w:Compatibility>
   <w:BreakWrappedTables/>
   <w:SnapToGridInCell/>
   <w:WrapTextWithPunct/>
   <w:UseAsianBreakRules/>
   <w:DontGrowAutofit/>
  </w:Compatibility>
  <w:BrowserLevel>MicrosoftInternetExplorer4</w:BrowserLevel>
 </w:WordDocument>
</xml>
<![endif]--><!--[if gte mso 9]><xml>
 <w:LatentStyles DefLockedState="false" LatentStyleCount="156">
 </w:LatentStyles>
</xml><![endif]-->

<!--[if !mso]>
			<style>
				v\:* {behavior:url(#default#VML);}
				o\:* {behavior:url(#default#VML);}
				x\:* {behavior:url(#default#VML);}
				.shape {behavior:url(#default#VML);}
			</style>
<![endif]-->

		<style>
<!--'./*myExcel::assolutizza*/($CSS).'
	-->
		</style>
	<!--[if gte mso 10]>
	<![endif]--><!--[if gte mso 9]>
<xml>
 <o:shapedefaults v:ext="edit" spidmax="3074"/>
</xml><![endif]--><!--[if gte mso 9]>
<xml>
 <o:shapelayout v:ext="edit">
  <o:idmap v:ext="edit" data="1"/>
 </o:shapelayout>
</xml><![endif]-->
</head>';

	}


	/** @ignore */
	protected function creaWord($css=array(),$html=''){
		if($this->built) return;
		$tutto=$this->get_header($css).'<body lang="IT">
															<!-- myWordBody -->
																'.$this->get_html($html).'
															<!-- myWordBody -->
														
										</body>
				</html>';
		//					else  $tutto=$this->stampa_unione($this->get_html($html),$this->get_header($css).'<body lang="IT"><div class="WordWordSection1">').'</div></body></html>';
								 

		$id=$this->id;

		$tmp=new myMultiParter();

		$tutto=$tmp->IncorporaImmagini(array('link'=>'href','img'=>'src'),$tutto);
		if ($this->xmlHeaderFooter) $this->xmlHeaderFooter=$tmp->IncorporaImmagini(array('link'=>'href','img'=>'src'),$this->xmlHeaderFooter);

		$a=new myMultiParter();
		$a->AddStringImage($tutto,"","c:/$id/MyDoc.htm","quoted-printable",0,'inline_attach');
		if ($this->xmlHeaderFooter) $a->AddStringImage($this->xmlHeaderFooter,'',"c:/$id/MyDoc_file/MyHeader.htm","quoted-printable");

		$a->SetAttachments(array_merge( (array) $a->GetAttachments(), (array) $tmp->GetAttachments()));

		$a->Prepare();
		$this->built=true;
		
		$this->put_stream($a->CreateHeader());
		if(!$this->stampaunione) $this->put_stream($a->CreateBody());
							else { 
							  
								$body=explode('<!-- myWordBody -->',$a->CreateBody());
								$this->put_stream($body[0]);
								$this->stampa_unione($tutto);
								$this->put_stream($body[2]);
								
								
							}

	}






	/**
	* Invia al client il file Word (determina la chiusura dello script)
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
    	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    	header("Cache-Control: private");
    	header("Content-Description: File Transfer");

		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Content-disposition: attachment; filename=\"".basename($nomeFile)."\"");

		header("Content-Type: application/force-download");//header('Content-type: application/msword');
		header("Content-Transfer-Encoding: binary");;
		header('X-Content-Type-Options: nosniff');
		
		if($forza_download) header("Content-Disposition: attachment; filename=\"".basename($nomeFile)."\"");
					else    header("Content-Disposition: inline; filename=\"".basename($nomeFile)."\"");
		
		$this->creaWord($css,$html);
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
		$this->creaWord($css,$html);
		if(!@rewind($this->stream)) return $this;
		$f=@fopen($nomeFile,'w');
		@stream_copy_to_stream($this->stream, $f);
		@fclose($f);
		return $this;
	}
		
}