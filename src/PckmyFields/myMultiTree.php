<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myMultiTree.
 */

namespace Gimi\myFormsTools\PckmyFields;
 


use Gimi\myFormsTools\myCSS;
use Gimi\myFormsTools\PckmyDBAdapters\myAdoDBAdapter;

class myMultiTree extends myMultiCheck	{
/** @ignore */
public   $albero,$chiavi,$pulsante,$JS,  $nodes=array(),$folders=array(),$figli=array(), $aperto=false,$opzioni,$campi,	$opzionitree=array(),$Id, $statoNodi=array();

 	/**
	 	* Questo oggetto permette di creare dei campi nascosti "ma visibili"
	 	* Si puo' usare in alternativa ad un myMulticheck quando le opzioni sono tante
	 	* e soprattutto hanno un'organizzazione gerarchica
	 	*
	 	*
	 	* <code>
		  * $albero=array('lazio'=>array('','Lazio'),
		  *											'rm'=>array('lazio','Roma'),
		  *											'fr'=>array('lazio','Frosinone'),
		  *											'ri'=>array('lazio','Rieti'),
		  *											'lt'=>array('lazio','Latina'),
		  *											'H501'=>array('rm','Roma',1),
		  *											'H244'=>array('rm','Mentana',1),
		  *											'H324'=>array('rm','Monterotondo',1),
		  *											'H421'=>array('fr','Arpino',1),
		  *											'H445'=>array('fr','Isola Liri',1),
		  *											'friuli'=>array('','Friuli Venezia Giulia'),
		  *											'ud'=>array('friuli','Udine'),
		  *											'pn'=>array('friuli','Pordenone'),
		  *											'C444'=>array('pn','Pordenone',1),
		  *											'CR34'=>array('pn','Altro comune in prov. Pordenone',1)
		  *											);
		  *
	 	* $miotree=new myMultiTree('mio',array('H501','CR34'),$albero); //istanzio l'oggetto inizializzando con il valore ricevuto in get
		  * echo $miotree->get_html();
		  * </code>
		  *
	  	* 
	  	*  @param	 string $nome E' il nome del campo
	 	* @param	 array $valori  da assegnare come default
	 	* @param	 array $albero E' un array associativo come nell'esempio
		* @param	 string $classe e' la classe css da utilizzare
	 	*/
		   public function __construct($nome,$valore='',$albero='',$classe='') {
					 parent::__construct($nome,$valore,array(),$classe);
					 $this->set_MyType('MyMultiTree');
					 $this->Id=(int) $this->get_id_istanza();
					 $this->folders['aperta']="/".self::get_MyFormsPath()."js/imgTree/folderopen.gif";
					 $this->folders['chiusa']="/".self::get_MyFormsPath()."js/imgTree/folder.gif";
					 if (is_array($albero)) $this->set_Opzioni($albero);
					 if ($valore) $this->set_value($valore);
		  }


		  
/**
 * Permette di personalizzare le icone di cartella aperta e cartella chiusa
 * sarebbe meglio che fossero immagini di dimensione 23x20
 *
 * @param string $foglia Url dell'immagine da usare, nei casi non sia stata specificata nel set_opzioni
 * @param string $aperta Url dell'immagine da usare (se '*' non viene visualizzata)
 * @param string $chiusa Url dell'immagine da usare (se '*' non viene visualizzata)
 */
		   public function set_icons($foglia,$aperta='',$chiusa='')			{
					 if ($aperta=='*') $aperta="/".self::get_MyFormsPath()."js/imgTree/piatta.gif";
					 if ($chiusa=='*') $chiusa="/".self::get_MyFormsPath()."js/imgTree/piatta.gif";
					 if ($foglia) $this->folders['foglia']=$foglia;
					 if ($aperta) $this->folders['aperta']=$aperta;
					 if ($aperta) $this->folders['chiusa']=$chiusa;
					 return $this;
		  }

		  
		   public function get_titolo($valore=''){
		  	if(!$valore) $valore=$this->get_value();
		  	$vals=$this->get_opzioni();
		  	$vals=$vals[$valore];
		 	if(is_array($vals)) {
		  						 $vals=array_values($vals);
		  						 return $vals[1];
		  						}
		  	return null;					
		  }


		  /** @ignore*/
		   public function _get_html_show($pars='', $null = null, $null2 = NULL){
		      $this->clean_value();
		      $opzioni=$opzOrig=$this->get_opzioni();
		      $val=$this->get_value();
		      if(!is_array($val)) $val=array($val);
		       
		      foreach ($opzioni as $id=>&$opzione) {
		          $k=array_keys($opzione);
		          if($opzione[$k[2]] && !in_array($id,$val)) unset($opzione[$k[2]]);
		       }
		      
		     
		      $this->set_opzioni($opzioni);
		      $this->pota_opzioni();
		      
		      $this->unset_showonly();
		      $out=$this->get_html();
		      $this->set_showonly();
		      $this->set_opzioni($opzOrig);
		      return $out;
		  }
		  
		  

		  function &get_html($noLabel = false, $attributi_td = '') {
					 $get_html=isset($this->Metodo_ridefinito['get_Html']['metodo'])?$this->Metodo_ridefinito['get_Html']['metodo']:null; if ($get_html!='') return $this->$get_html(isset($this->Metodo_ridefinito['get_Html']['parametri'])?$this->Metodo_ridefinito['get_Html']['parametri']:null);
					 $js=$riga='';
					 $this->set_attributo('style','border:0px;padding:0');
					 $MyTree=$this->Id;

					 if (!isset($this->myFields['static']['js_src'][get_class($this)]) || !$this->myFields['static']['js_src'][get_class($this)])
					 				{$js.="<script type='text/javascript' src='/".self::get_MyFormsPath()."js/tree.js'></script>";
					 				 $this->myFields['static']['js_src'][get_class($this)]=1;
					 				}

					 if ($this->albero) 
					           foreach ($this->albero as $i=>$v) {
					                       for($j=0;$j<=3;$j++) if(!isset($v[$j])) $v[$j]='';
					                       $riga.="nodes[$MyTree][$i]  =new Array(\"".myTag::htmlentities($v[0]).'","'.myTag::htmlentities($v[1]).'","'.myTag::htmlentities($v[2]).'","'.myTag::htmlentities($v[3])."\");\n";
					                   }
					 $js.="<script type='text/javascript'>
					  		if(nodes===undefined) nodes=new Array();
					  		if(icons===undefined) icons=new Array();
					  		if(openNodes===undefined) openNodes=new Array();
					  		 ".myCSS::get_css_jscode("#div{$MyTree}_ .myTreeComponent {padding:0!important;margin:0!important;border:0!important;vertical-align:bottom!important} 
					  		                          #div{$MyTree}_ .myTreeHidden{display:none}",true)."
					 	</script>";
					 	
					 
					 
					  $js.="<table style='white-space:nowrap!important;font-size:xx-small!important;vertical-align:bottom!important;padding:0px!important;'>
					           <tr>
					       <td style='text-align:left'>";
					  $js.=$this->Tree($MyTree,$this->albero);
					  ksort($this->statoNodi);

					  $this->statoNodi[]='0';
					  $js.="<script type='text/javascript'>
					  		     nodes[$MyTree]= new Array();
								openNodes[$MyTree]=new Array(".implode(',',$this->statoNodi).");
								$riga
								icons[$MyTree]=new Array(new Image(),new Image(),new Image(),new Image(),new Image(),new Image(),new Image(),new Image());
								icons[$MyTree][0].src = '/".self::get_MyFormsPath()."js/imgTree/plus.gif';
								icons[$MyTree][1].src = '/".self::get_MyFormsPath()."js/imgTree/plusbottom.gif';
								icons[$MyTree][2].src = '/".self::get_MyFormsPath()."js/imgTree/minus.gif';
								icons[$MyTree][3].src = '/".self::get_MyFormsPath()."js/imgTree/minusbottom.gif';
								icons[$MyTree][4].src = '".$this->folders['chiusa']."';
								icons[$MyTree][5].src = '".$this->folders['aperta']."';
								icons[$MyTree][6].src = '/".self::get_MyFormsPath()."js/imgTree/plus.gif';
								icons[$MyTree][7].src = '/".self::get_MyFormsPath()."js/imgTree/minus.gif';
								 
							{$this->JS}	
								</script>	          
						  ";

					  $js.="</td></tr></table>";

					  $jsCommon=$this->get_js_common();//if(!$this->myFields['static']['common']) {$jsCommon=$this->get_js_common();$this->myFields['static']['common']=true;}
					  $MyTree++;
					  $html=$this->get_hidden_aggiuntivo().(!$this->con_js?'':$jsCommon.$this->jsAutotab().$js).(isset($this->attributi['onclick'])?$this->get_html_Accessibilita($this->attributi['onclick']):'');
					  return $html;
			}


		  /**
		  * Imposta l'albero su cui costruire il tutto
		  * Es.
		  * <code>
		  *			 $albero=array('lazio'=>array('','Lazio'),
		  *										  'rm'=>array('lazio','Roma',0,'/icons/provincia.gif'),
		  *											'fr'=>array('lazio','Frosinone'),
		  *											'ri'=>array('lazio','Rieti'),
		  *											'lt'=>array('lazio','Latina'),
		  *											'H501'=>array('rm','Roma' ,1,'/icons/comune.gif'),
		  *											'H244'=>array('rm','Mentana',1),
		  *											'H324'=>array('rm','Monterotondo',1),
		  *											'H421'=>array('fr','Arpino',1),
		  *											'H445'=>array('fr','Isola Liri',1,'/icons/comune.gif'),
		  *											'friuli'=>array('','Friuli Venezia Giulia'),
		  *											'ud'=>array('friuli','Udine'),
		  *											'pn'=>array('friuli','Pordenone',0,'/icons/provincia.gif'),
		  *											'C444'=>array('pn','Pordenone',1,'/icons/comune.gif'),
		  *										  'CR34'=>array('pn','Altro comune in prov. Pordenone',1,'/icons/comune.gif')
		  *											);
		  * $miotree->set_opzioni($albero);
		  * echo $miotree->get_html();
		  *
		  * </code>
		  * Si tratta ovviamente di un array di array, ogni sottoarray rappresenta un nodo in cui: <br />
		  * 'lazio'=>array('','Lazio'),<br />
		  * In questo caso 'lazio' e' il valore associato al nodo in cui 'Lazio' sarà  la descrizione che comparirà.<br />
		  * Non essendo valorizzato il primo valore questo nodo sarà  una "radice" in particolare non selezionabile<br />
		  * 'rm'=>array('lazio','Roma',0,'/icons/provincia.gif'),<br />
		  * In quest'altro caso 'rm'  e' il valore associato al nodo in cui 'Roma' sarà  la descrizione che comparirà.<br />
		  * Inoltre la presenza dello 0 indica che non è selezionabile e che <b>Qualora fosse una foglia</b> l'icona da usare è '/icons/provincia.gif' altrimenti non c'e' icona<br />
		  * Questo sarà  un "figlio" del nodo 'Lazio' ma non sarà  ancora selezionabile<br />
		  * 'H324'=>array('rm','Monterotondo',1),<br />
		  * In quest'altro caso 'H324' e' il valore associato al nodo in cui 'Monterotondo' sarà  la descrizione che comparirà.<br />
		  * Questo sarà  un "figlio" del nodo 'Roma' ed e' selezionabile (terzo valore =1 o =true), questo fa si che si possa scegliere di rendere selezionabili sia le foglie che i rami che le radici
		  *
			 * @param	 array $albero
	 	*
	 	*/
		   public function set_Opzioni($albero = Array()) {
					 parent::set_Opzioni($this->set_Opzioni_tree($albero));
					 return $this;
		  }



			/** @ignore*/
		  function &set_Opzioni_tree($albero) {
					 if (!$albero) return;

					 $this->albero=array();
					 $this->chiavi=array();
					 $this->opzioni=array();
					 $this->figli=array();
					 $this->opzionitree=&$albero;

					 $i=1;
					 foreach ($albero as $k=>$v) {$this->chiavi[myTag::htmlentities($k)]=$i;$i++;}

					 $ordine=array_keys($albero);
					 $i=1;
					 $opzioni=array();
					 foreach ($ordine as $k) {
								if (!$albero[$k] || !is_array($albero[$k])) $albero[$k]=array();
								$v=array_values($albero[$k]);

								//echo "<pre>";print_r($v);//
								$v[1]=$this->trasl($v[1])."<!-- $k -->";
								if ($v[2]) {$opzioni[$v[1]]=$k;
								              $w=array($i,isset($this->chiavi[myTag::htmlentities($v[0])]) ?(int) $this->chiavi[myTag::htmlentities($v[0])]:0,$v[1],$k);
											}
										else  $w=array($i,isset($this->chiavi[myTag::htmlentities($v[0])]) ?(int) $this->chiavi[myTag::htmlentities($v[0])]:0,$v[1]);

								if (isset($v[3])) $w[4]=$v[3];
								//if ($v[5]) $w[6]=$v[5];

								$this->albero[]= $w;
								$i++;

								$this->figli[$w[1]][$w[0]]=count($this->albero)-1;
					 }
					// echo "<pre>";print_r($this->albero);
					 return $opzioni;
		  //		  foreach ($opzioni as $i=>$v) $opzioni[]
		  }


		  /** 
		   * Aggiunge una singola opzione
		  * <code>
		  *			 $albero=array('lazio'=>array('','Lazio'),
		  *										  'rm'=>array('lazio','Roma',0,'/icons/provincia.gif'),
		  *											'fr'=>array('lazio','Frosinone'),
		  *											'ri'=>array('lazio','Rieti'),
		  *											'lt'=>array('lazio','Latina'),
		  *											'H501'=>array('rm','Roma' ,1,'/icons/comune.gif'),
		  *											'H244'=>array('rm','Mentana',1),
		  *											'H324'=>array('rm','Monterotondo',1),
		  *											'H421'=>array('fr','Arpino',1),
		  *											'H445'=>array('fr','Isola Liri',1,'/icons/comune.gif'),
		  *											'friuli'=>array('','Friuli Venezia Giulia'),
		  *											'ud'=>array('friuli','Udine'),
		  *											'pn'=>array('friuli','Pordenone',0,'/icons/provincia.gif'),
		  *											'C444'=>array('pn','Pordenone',1,'/icons/comune.gif'),
			*										  'CR34'=>array('pn','Altro comune in prov. Pordenone',1,'/icons/comune.gif')
		  *											);
		  * $miotree->set_opzioni($albero);
		  *
		  * $altra=array('H502','lazio','Latina',1,'/icons/provincia.gif');
		  * $miotree->add_opzione($albero,true);
		  *
		  * echo $miotree->get_html();
		  * </code>
		  *
		  * @param	 array opzione e' un array NON ASSOCIATIVO con (chiave,chiave_padre,selezionabile,descrizione,icona)
		  * @param	 boolean $fine se falso o omesso l'opzione si aggiunge in coda se true si aggiunge all'inizio del ramo
	 	*/

		   public function add_Opzione($opzione,$fine=false,$null=null) {
					 $V=array();$K=null;
					 foreach ($opzione as $k=>$v) if ($K===null) $K=$v;
					  								  	    else $V[$k]=$v;
                     if($K===null) return $this;   
					 if ($fine) {$this->opzionitree[$K]=$V;
							     $this->set_Opzioni($this->opzionitree);
								 }
						   else {$x=array($K=>$V);
							     foreach ($this->opzionitree as $k=>$v) $x[$k]=$v;
								 $this->set_Opzioni($x);
								 }
			return $this;
			//echo "<pre>";	print_r($this->opzionitree);
		  }




  		/**
		  * Effettua la potatura delle opzioni eliminando i rami secchi, cioe tutti quei rami che portano a foglie non selezionabili
		  * da usare ovviamento dopo set_opzioni
		  *
		  * @param	 boolean $potaRadicise vero anche radici non selezionabili vengono eliminate
		  */
		function &pota_Opzioni($potaRadici=false) {
					  //$selezionabili sono tutti i nodi con flag di selezionabilità a 1
			  $selezionabili=array();
			  foreach ($this->opzionitree as $k=>$v)
									{
									    $arra=array_values($v);
									    if ($arra[2]) $selezionabili[]=$k;
									}

			  $buoni=array();
			  //per ogni selezionabile
			  if($selezionabili) 
			         foreach ($selezionabili as $buono)
									do {
											$buoni[$buono]=true; //inserisce buono nei buoni
											$arra=array_values($this->opzionitree[$buono]);
											$buono=$arra[0];				 //prox_buono è padre del buono attuale
										 }
			         while ( isset($this->opzionitree[$buono]) && (!isset($buoni[$buono]) || !$buoni[$buono]));			//Smette di iterare se arrivato su un percorso già  battuto o se è radice
			  $albero=array();
			  if ($this->opzionitree) foreach ($this->opzionitree as $k=>$v) if (isset($buoni[$k]) && $buoni[$k]) $albero[$k]=$v;
			  if ($potaRadici) do {//foreach ($albero as $k=>$v) $livelli[$k]=count($this->get_percorso($k));
			  					   $eliminate=false;
									if ( count($albero))
											foreach ($albero as $k=>$v)
															{
															$v=array_values($v);
															if (!isset($albero[$v[0]]) && !$v[2]) //se è radice e non è selezionabile
																		 	 {//Radice candidata all'eliminazione ha fratelli?
																			  $hafratello=false;
																			  foreach ($albero as $kk=>$vv)
																								  { $vv=array_values($vv);
																									if ($vv[0]==$v[0] && $kk!=$k)
																											{//se $vv ha lo stesso padre di $v e non è lui e non è selezionabile
																											 $hafratello=true;
																		 		 							 break;
																		 							 		}
																									}
																 		 	  if (!$hafratello && isset($albero[$k])){
																					$eliminate=true;
																					unset($albero[$k]);
																					}
																			}
															}
								} while ($eliminate==true && count($albero));
			  $this->set_Opzioni($albero);
			  return $this;
		  }





		   public function get_opzioni() {
					 return $this->opzionitree;
		  }


  		protected function get_errore_diviso_singolo() {
					 	$valore=$this->get_value();
					 	if ($this->notnull && !$valore) return 'non può essere nullo';
					 	if($this->restrizioni_check && $valore)
					 		{ if(!$valore) $valore=array();
					 		  if(!is_array($valore)) $valore=array($valore);	
					 		  if(array_diff($valore,array_keys((array) $this->get_opzioni()))) return 'non è accettabile';
					 		}
		 }


	 /** 
	  * elimina dal/i valore/i impostati quelli non presenti nelle opzioni
		* attenziene a non usarla prima di aver settato tutte le opzioni valide
		* altrimenti si annulla il valore
		*/
		 public function clean_value() {
		                $selezionabili=array();
                        $opzioni=(array) $this->get_opzioni();
                        foreach ($opzioni as $v=>&$foglia) {
                                        $tupla=array_values($foglia);
                                        if($tupla[2]) $selezionabili[$v]=true;
                                     }

                        $valori=$this->get_value();
                        if (!is_array($valori)) $valori=array($valori);

                        $new_val=null;
                        foreach ($valori as &$v)
                                   if ($selezionabili[$v]) $new_val[]=$v;
                        $this->set_value($new_val);
                        return $this;
        }






	   /** 
	    * setta in automatico il primo valore valido se non ce ne sono settati
		 */
	   public function autovalue() {
			  $this->clean_value();
			  if (!strlen($this->get_value()))
			  				 foreach ($this->get_Opzioni() as $k=>$v) if ($v[2]) {$this->set_value($k);
			  																	  return;
			 							 										  }
	  }



	  /**
	  * Setta le opzioni del selettore
	  *
	  * @param	 mixed $db e' la connessione a DB da utilizzare
	  * @param	 string $qry la query da usare per reperire i dati in cui il primo campo è la chiave del nodo, il secondo e la chiave del padre il terzo è il Titolo del nodo e il quarto e' 1 o 0 a seconda che sia un nodo selezionabile
	  * @param	 boolean $registra  se true i valori vengono memorizzati in una variabile di sessione e la qry non si riesegue piu'
	  */
		   public function set_OpzioniQRY($db,$qry='',$registra=true) {
				$this->qry=$qry;
				$db=myAdoDBAdapter::getAdapter($db);
				IF ($_SESSION['myFields']['MyOpzioniRegistrate'][sha1(serialize($this->qry))] && $registra)
				                        {$this->opzioni=$_SESSION['myFields']['MyOpzioniRegistrate'][sha1(serialize($this->qry))];
										$opzioni=$this->opzioni;
										}
								 else {	 
								     if(is_array($qry)) $v=&$db->getarray($qry[0],$qry[1]);
								                   else $v=&$db->getarray($qry);
								     
								     $opzioni=$this->opzioni=&$v;
								  // if ($registra) $_SESSION['myFields']['MyOpzioniRegistrate'][md5($this->qry)]=$opzioni;
									 if ($registra) $this->registra_opzioni();
									 }
		 return  $this->set_Opzioni($opzioni);
  	  }



		/** 
		 * Restituisce array con i nodi dalla radice fino al nodo $nodo
		  * @param	 int|string $nodo
		  *  @return array
		  */
		   public function get_percorso($nodo) {
		  	$buoni=array();
		  	while (is_array($this->opzionitree) &&
		  			@isset($this->opzionitree[$nodo])) {
						$buoni[]=$nodo;
						$arra=array_values($this->opzionitree[$nodo]);
						$nodo=$arra[0];
						}
				return array_reverse($buoni);
		  }

		  
		  /** 
		   * Restituisce il valore relativo al padre di $nodo
		   * @param	 int|string $nodo
		   *  @return int|string
		   */
		   public function get_padre($nodo) {
		  	if (is_array($this->opzionitree) &&
		  			@isset($this->opzionitree[$nodo])) return $this->opzionitree[$nodo]['padre'];
		  }

		  
		  /**
	       * Restituisce array con i nodi dalla radice fino al nodo $nodo
		   * @param	 int|string $nodo
		   * @param  int $livello_max livello massimo di discesa (0 per tutti)
		   *  @return array=>array chiave livello valori elenco nodi figli
		   */
		   public function get_figli($nodo,$livello_max=1) {
		  	if (is_array($this->opzionitree)) 	return $this->get_figli_ric($nodo,$livello_max,1);
		  	return array();
		  }
		  
		  
		  function &get_figli_ric($nodo,$livello_max,$corrente=1) {
		  		if($corrente>$livello_max) return array();
		  	    $out=array();
		  		foreach($this->opzionitree as $k=>&$v) 
		  				if($v['padre']==$nodo) $out[$nodo]=$this->get_figli_ric($k, $livello_max,$corrente+1);
		  		return $out;	
		  }
		  
		  
		  
		  /** 
		   * Restituisce il codice relativo alla radice del nodo
		  * @param	 int|string $nodo
		  *  @return array
		  */
		   public function get_radice($nodo) {
		  		$buoni=$this->get_percorso($nodo);
				return $buoni[0];
		  }


		  /** 
		   * Se usato, l'albero si presenta già  completamente aperto
		  */
		   public function set_aperto() {
					 $this->aperto=true;
					 return $this;
		  }


		  /** 
		   * I nodi dell'albero diventano tutti foglie non selezionabili
			* utile se si vuole usare per costruire menu in cui le descizione di nodi sono già  dei link
		 */
		   public function set_soloalbero() {
					 $this->showlinks='';
					 return $this;
		  }


		/**
		 * Non più necessaria
		 * @deprecated
		 * 
		 */
		   public function unset_accessibilita() {
					 return $this;
		  }




			/** @ignore*/
		  function &Tree($Id,$arrName,  $startNode='') {
			  $this->nodes[$Id] =$arrName;

			  if (count($this->nodes[$Id])> 0)
				 {
				$startNode = 0;
				//if ($openNode != 0) $this->setOpenNodes($Id,$openNode);
				$recursedNodes=array($Id=>array());
				$aperti=$this->NodiAperti($Id);
				//echo "<pre>";print_r($aperti);
				return "<ul class='myTree{$Id} myTreeComponent myTreeHidden' style='list-style-type:none;padding:0' id='div{$Id}_' >".$this->addNode($Id,$aperti,$startNode, $recursedNodes).'</ul>';
				}
		  }




 		/** @ignore */
		  function &lastSibling ($Id,$node, $parentNode,&$figli) {
		  $lastChild = 0;
		  $n=count($figli);
			  //echo "<pre>";print_r($this->figli[$parentNode]);
		  for ($i = 0; $i < $n; $i++) {
								$nodeValues =$this->nodes[$Id][$figli[$i]];
					 if ($nodeValues[1] == $parentNode) $lastChild= $nodeValues[0];
		  }
		  if ($lastChild==$node) return true;
		  return false;
		  }






			/** @ignore */
		  function &NodiAperti($Id) {
					  $out=$v=array();
					  if (!$this->get_value() ) return $out;
					  if (is_array($this->get_value())) $aperti=$this->get_value();
												   else $aperti=array($this->get_value());
					   $aperti=array_flip($aperti);
					   for ($i=0;$i<count($this->nodes[$Id]);$i++)
					       if (isset($this->nodes[$Id][$i][3]) && isset($aperti[$this->nodes[$Id][$i][3]])) 
							 	                                   $v[]=$this->nodes[$Id][$i][0];

					  //								print_r($v);
					  for ($i=0;$i<count($v);$i++) {
									    $valore=$v[$i]-1;
											while (isset($this->nodes[$Id][$valore])) {
													  $out[$this->nodes[$Id][$valore][0]]=1;
													  $valore=$this->nodes[$Id][$valore][1]-1;

											}
					  }
				    foreach ($v as $i) unset($out[$i]);
		            return $out;
		            }




 /** @ignore */
		  function &addNode(&$Id,&$aperti,&$parentNode, &$recursedNodes) {
            	$stileIconaTree ="padding:0;margin:0;border:0;vertical-align:bottom";
            	$out=$OUT='';
				$figli=array_values($this->figli[$parentNode]);
				$n=count($figli);

		 		for ($i = 0; $i < $n; $i++) {
					 $nodeValues =$this->nodes[$Id][$figli[$i]];
					 if ($nodeValues[1] === $parentNode)
								{
								$out='';	
																//Scelta
																//		  $out.="<div >";
																//echo "<hr<pre>";print_r($this->nodes);
								$ls  =$this->lastSibling($Id,$nodeValues[0], $nodeValues[1],$figli);
								if (!$this->aperto && !$aperti[$nodeValues[0]])
																				   $ino=0;//$this->isNodeOpen($Id,$nodeValues[0]);
																			  ELSE $ino=1		  ;

						        if(!isset($this->figli[$nodeValues[0]])) $this->figli[$nodeValues[0]]=array();
								$hcn = count($this->figli[$nodeValues[0]]);

																//echo "<pre>";print_r($nodeValues);echo "$ls - $hcn";


								// Write out line & empty icons
								for ($g=0; $g<count($recursedNodes[$Id]); $g++)
										  if ($recursedNodes[$Id][$g] == 1) $out.="<img class='myTreeComponent' src=\"/".self::get_MyFormsPath()."js/imgTree/line.gif\" />";
																	  else  $out.="<span style='padding:0;padding-left:23px;margin:0'></span>";


								// put in array line & empty icons
								if ($ls) array_push($recursedNodes[$Id],0);
								    else array_push($recursedNodes[$Id],1);

								// Write out join icons

								if (!$hcn)
										 {
										  if ($ls) $out.="<img class='myTreeComponent'  src=\"/".self::get_MyFormsPath()."js/imgTree/join.gif\"	/>";
											  else $out.="<img class='myTreeComponent'  src=\"/".self::get_MyFormsPath()."js/imgTree/joinbottom.gif\" />";
											  if ( (isset($nodeValues[4]) && $nodeValues[4]) || isset($this->folders['foglia'])){
																						 if ($nodeValues[4]) $icona= $nodeValues[4];
																								      else  $icona= $this->folders['foglia'];
																											 		// else $icona=$this->folders['aperta'];
																						  $out.="<img class='myTreeComponent' src=\"$icona\" />";
																						 }
										}
								  else {
										  if ($ls){
													if ($ino) $icona='minusbottom.gif';
														 else $icona='plusbottom.gif';
													 $out.="<div style='display:inline' class='myTreeComponent' onclick=\"oc($Id, $nodeValues[0], 0)\" onkeypress=\"oc($Id, $nodeValues[0], 0)\"><img style='$stileIconaTree'  id=\"join".$Id."_" . $nodeValues[0] . "\" src='/".self::get_MyFormsPath()."js/imgTree/$icona' alt=\"Aprire/chiudere cartella\" />";
												  }
											 else {
														 if ($ino) $icona='minus.gif';
															else $icona='plus.gif';
														  $out.="<div style='display:inline' class='myTreeComponent' onclick=\"oc($Id, $nodeValues[0], 1)\" onkeypress=\"oc($Id, $nodeValues[0],1)\"><img style='$stileIconaTree'  id=\"join".$Id."_" . $nodeValues[0] . "\" src='/".self::get_MyFormsPath()."js/imgTree/$icona' alt=\"Aprire/chiudere cartella\" />";
												  }
										  if ($ino) $icona=$this->folders['aperta'];
											   else $icona=$this->folders['chiusa'];
										$out.="<img class='myTreeComponent' id=\"icon$Id"."_$nodeValues[0]\" src=\"$icona\"  alt=\"Aprire/chiudere cartella\" /></div>";
									}


                                  //  $out='<script type="text/javascript">'.self::my_js_documentwrite($out).'</script>';    


									if (isset($nodeValues[3]) && $nodeValues[3]!='')  
									                         $out.="<div style='display:inline' class='myTreeComponent'>".$this->get_html_singolo($nodeValues[2])."</div>";
													   else	 $out.="<div style='display:inline' class='myTreeComponent'>$nodeValues[2]</div>";

								//  $out.="<br style='clear:both' />";
													  
								  if (!$ino) $this->statoNodi[$nodeValues[0]]=0;
										else $this->statoNodi[$nodeValues[0]]=1;
										
								 $OUT.="<li class='myTree{$this->get_id_istanza()}_$nodeValues[0] myTreeComponent'>".$out.'</li>';		
								// If node has children write out divs and go deeper
				  				  if ($hcn && count($this->nodes[$Id])>0)
												  {//style='border:0;padding:0;margin:0;list-style:none;'
													//if (!$ino && $this->accessibile) $this->JS.="document.getElementById(\"div$Id"."_$nodeValues[0]\").style.display = 'none';\n";
													                         //  else  $this->JS.="document.getElementById(\"div$Id"."_$nodeValues[0]\").style.display = 'inline';\n";
								 				     if ($ino) $OUT.="<ul style='list-style-type:none;padding:0' class='myTree{$Id}_{$nodeValues[0]} myTreeComponent' id='div{$Id}_{$nodeValues[0]}'  >";
													      else $OUT.="<ul style='list-style-type:none;padding:0' class='myTree{$Id}_{$nodeValues[0]} myTreeComponent myTreeHidden' id='div{$Id}_{$nodeValues[0]}' >";
																			        // $this->JS.="document.getElementById(\"div$Id"."_$nodeValues[0]\").style.display = 'inline';\n";
																			         //}
                                                     
													 $OUT.=$this->addNode($Id,$aperti,$nodeValues[0], $recursedNodes);
													 $OUT.="</ul>";
													}
								
								// remove last line or empty icon
								array_pop($recursedNodes[$Id]);
				  }
		  }
		  
		  return $OUT;
		  }


 		/**
	* Alias di myMultiTree::setReloadJS()
		  */
		   public function Set_ReloadJS($Tipo='parametro',$evento='onclick',$opzioni='',$fragment='',$usa_random=true,$sostituisci=false) {
			return $this->SetReloadJS($Tipo,$evento,$opzioni,$fragment,$usa_random,$sostituisci);
		  }


		  /**
	        * se settato aggiunge il reload della pagina sull'evento passato, di default e' onclick
			* DA USARE SOLO DOPO AVER IMPOSTATO LE OPZIONI
			* @param	 'parametro'|'completo'|'azzera'|'submit' $Tipo  Il default e' 'parametro'
			* @param	 string $evento  Il nome dell'evento su cui attivare il reload
			* @param     array  $opzioni A cui applicare
			* @param	 string $fragment Eventuale fragment da aggiungere alla url
			* @param     boolean $usaRandom   se false non si aggiunge parametro random "tt"
			* @param	 boolean $sostituisci se true eventuali eventu preimpostati si sovrascrivono
			*
			* Supponiamo che questo selettore si chiami 'Tipo' e la url della pagina sia
			*					  http://www.fdsf.it?abc=32432&Tipo=424&altro=12345<br />
			*							con l'opzione 'parametro' si ricaricherà http://www.fdsf.it?abc=32432&Tipo=nuovo_valore<br />
			*							con l'opzione 'completo' si ricaricherà http://www.fdsf.it?abc=32432&Tipo=nuovo_valore&altro=12345<br />
			*							con l'opzione 'azzera' si ricaricherà http://www.fdsf.it?Tipo=nuovo_valore<br />
			*							con l'opzione 'submit' effettua l'invio in POST del form cosi' com'e'<br />
			*
			**/
 		   public function SetReloadJS($Tipo='parametro',$evento='onclick',$opzioni='',$fragment='',$usa_random=true,$sostituisci=false) {
			if (!$Tipo) $Tipo='parametro';
		    if ($Tipo=='submit'||$Tipo=='submit()')
							 		$this->setjs($opzioni,"submit()",$evento,$sostituisci);
						else foreach ($this->campi as &$campo) {
								  	  	$val=$campo->get_value();
										if(!$opzioni || in_array($val, $opzioni))
											//$this->setjs(array($val),"location.href='?$extra&amp;".$this->get_name()."=".rawurlencode($val)."$fragment'",$evento,$sostituisci);
											{
											 $this->reloadJSGET[$val]=array($Tipo,$usa_random,$fragment);
					  		   		 		 $this->setjs(array($val),"location.href=myLinks_{$campo->get_id_istanza()}(this.value);",$evento,$sostituisci);
											}

									}
		  // echo "<pre>";print_r($this->campi);
		  }



		  /**
			 *
			 * Calcola le url usate nel mySelect::setReloadJS() nelle modalità GET,
			 * utile per eventuali overriding
			 *
			 * @param	 'parametro'|'completo'|'azzera'|'submit' $Tipo  Il default e' 'parametro'
			 * @param  string $nome_campo nome del campo usato come riferimento per la costr. nuova url
			 * @param  boolean $usa_random se true aggiunge tt randomico
			 * @param string $url eventuale url da usare, se string avuota o omesso si usa $_SERVER['PHP_SELF']
			 * @param string $query_string eventuale string di parametri se FALSE non si usa omesso o stringa vuota usa $_SERVER["QUERY_STRING"]
			 * @param string $fragment eventuale fragment
			 *
			 * @return string
			 */

		   public function build_reload_urls($Tipo,$nome_campo,$usa_random,$url='',$query_string='',$fragment=''){
		  	$nuova=$valori=$parametri=array();
		  	if($query_string==='') $query_string=$_SERVER["QUERY_STRING"];
		  	if($query_string) @parse_str($query_string,$parametri);
		  	if(!$url) $url=$_SERVER['PHP_SELF'];
		  	$parametri=(array) $parametri;
		  	static $build;
		  	if($build[serialize(func_get_args())]) return $build[serialize(func_get_args())];
			if($parametri)
				foreach ($parametri as $par=>$val)
				  {
				  	 if ($Tipo=='parametro' && $par==$nome_campo) break;
					 if ($par!=$nome_campo && (is_array($val) || trim((string) $val)!=='')) $nuova[$par]=$val;
            	  }
			if ($Tipo=='azzera')  $nuova=array();
			if($usa_random) $nuova['tt']=self::unid();

			if($fragment) $fragment="#$fragment";
			foreach (array_keys($this->get_Opzioni()) as $val)
							{$nuova[$nome_campo]=$val;
							 $valori[$val]=$url.'?'.http_build_query($nuova).$fragment;
							}
			return $build[serialize(func_get_args())]=$valori;
		  }

		  /**
	* se assegna un JS ad un evento delle $opzioni di default e' onclick
			* DA USARE SOLO DOPO AVER IMPOSTATO LE OPZIONI
			* @param	 array $opzioni  E' un array con l'elenco delle CHIAVI su cui attivare l'evento, se vuoto si applica a tutte
	  	 	* @param	 string $JS  E' lo JavaScript da lanciare
			* @param	 string $evento  Il nome dell'evento su cui attivare lo JS
			* @param  boolean $sostituisci sostituisce eventuali precedenti eventi
	  		*/
		   public function Set_JS($opzioni='',$JS='',$evento='onclick',$sostituisci=false) {
		    	$this->setjs($opzioni,$JS,$evento,$sostituisci);
		  }

		  /**
	* se assegna un JS ad un evento delle $opzioni di default e' onclick
			* DA USARE SOLO DOPO AVER IMPOSTATO LE OPZIONI
	  	 * @param	 array $opzioni  E' un array con l'elenco delle CHIAVI su cui attivare l'evento, se vuoto si applica a tutte
	  	 * @param	 string $JS  E' lo JavaScript da lanciare
			* @param	 string $evento  Il nome dell'evento su cui attivare lo JS
			* @param  boolean $sostituisci sostituisce eventuali precedenti eventi
	  	*/
		   public function SetJS($opzioni='',$JS='',$evento='onclick',$sostituisci=false) {
		  	 if (!$opzioni || count($opzioni)==0)
		  	 							$this->set_attributo($evento,str_replace('"','\\"',$JS),$sostituisci);
 								  else  foreach ($this->campi as $id=>$v)
 								  	 	 	if (in_array($v->get_value(), $opzioni))
 								  	 	 			 $this->campi[$id]->set_attributo($evento,str_replace('"','\\"',$JS),$sostituisci);

		  }

}