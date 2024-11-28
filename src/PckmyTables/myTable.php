<?php
/**
 * Contains Gimi\myFormsTools\PckmyTables\myTable.
 */

namespace Gimi\myFormsTools\PckmyTables;



use Gimi\myFormsTools\PckmyFields\myIcon;
use Gimi\myFormsTools\PckmyFields\mySelect;
use Gimi\myFormsTools\PckmyFields\mySingleRadio;
use Gimi\myFormsTools\PckmyFields\myCheck;
use Gimi\myFormsTools\PckmyFields\myUUID;
use Gimi\myFormsTools\PckmyForms\myForm;
use Gimi\myFormsTools\PckmyJQuery\myJQuery;
use Gimi\myFormsTools\PckmyJQuery\PckmyTables\myJQDataTable;
use Gimi\myFormsTools\PckmyPlugins\myTable_Plugin_for_add_col_icone;
use Gimi\myFormsTools\PckmyUtils\myCharset;





/**
*   Classe per la Gestione facilitata di tabelle html, vedi myTableFormQry
**/
	
Class myTable extends myMatrix{
/**
 @ignore 
 */
protected   $caption='',$parametri_table, $parametri_tr,$parametri_td,$parametri_th, $N, $PAG, $id, $sortJS,$myJQueries=array(),
$jQuery,$sortJquery,$opzioniOrdJquery,$cssJquery,$opzioneOrdine,$zebra,$arrayColoreRighe,$evRiga,$filtro,$serverSide=array(),
 $opzioniJquery,$Fisso,$soloDati,
 $outMode=array('modo'=>'html','intestazione'=>true),
 $static_vals=array(),
 $JQDt,$JS;



   /**
    * 
    * @param    array $tabella  Opzionale e' un array di righe, le righe vengono reindicizzate da zero a prescindere dalle chiavi
    * @param    string $parsTable  Opzionale sono i parametri di default da associare al tag TABLE
    * @param    string $parsTR  Opzionale sono i parametri di default da associare da associare al tag TR
    * @param    string $parsTD Opzionale sono i parametri da associare al tag TD
    * @param    string $parsTH Opzionale sono i parametri da associare al tag TH
    **/
	 public function __construct($tabella='',$parsTable='',$parsTR='',$parsTD='',$parsTH='') {
		parent::__construct($tabella);
		$this->catch_id($parsTable);
		$this->set_pars($parsTable,$parsTR,$parsTD,$parsTH);
	}

	
	public function set_titolo($titolo){
	    $this->caption=$titolo;
	    return $this;
	}
	

  
  /**
   * Imposta la modalità di restituzione dell'output del &get_html($colonne = '', $righe = '') e get_output() 
   * @param string $modo xml|html|json|serialize|var_export (json forza charset ad UTF-8)
   * @param boolean $usa_intestazione di default a false i dati chiavi numeriche se true l'intestazione viene inserita come prima riga 
   * @return myTable
   */
   public function set_output_mode($modo,$anche_intestazione=false){
      if($modo=='json' || $modo=='jsonJQDT') $this->set_charset('UTF-8');
      $this->outMode=array('modo'=>$modo,'intestazione'=>$anche_intestazione);
      return $this;
  }
/**
 * @ignore
 */
  protected  function uses_json(){
      return $this->JQDt!==null;
  }


  /**
   * @ignore
   */
  protected function get_ajax_par_name(){
      return '_'.md5(realpath(__FILE__)).get_class($this);
  }
  
  
  
  /**
   * @ignore
   */
   protected function is_loading_json(){
      if(isset($_POST['myJQDatatable'][$this->get_ajax_par_name()])) return 'post';
      if(isset($_GET[$this->get_ajax_par_name()]))  return 'get';
      return false;
  }
  
  
    /**
  * @ignore
  */
  protected function is_ajax_mode(){
      return $this->JQDt && $this->JQDt->serverSide;
  }

  
  /**
   * 
   * @param myJQDataTable $jqdt
   * @param array|boolean $serverSide se true è server side oppure si può passare array('sorting'=>array(1=>' round(?)')) in pratica una funzione sql che parametrizza il campopr l'ordinamento
   * @param int N righe per pagina se usato sovrascrive precedente
   * @param string dom di default è '<"top"i>rt<"bottom"flp><"clear">'
   * @return myJQDataTable
   */
  function &set_ajaxload(myJQDataTable $jqdt=null,$serverSide=null,$N=null,$dom='<"top"i>rt<"bottom"flp><"clear">') {
         if(!$this->get_id())  $this->set_id(str_replace('-','_',myUUID::v4()));
         if(!$jqdt) $jqdt=new myJQDataTable('#'.$this->get_id(),$dom);
         $evento=$this->get_ajax_par_name();
         $this->JQDt=$this->add_myJQuery($jqdt);
         $this->JQDt->set_traduzione("loadingRecords",'<div style="text-align:center"><img src="/'.self::get_MyFormsPath().'icone/spinner/redmond.gif" style="min-width:50px"></div>');
         $this->JQDt->set_traduzione("processing",'<div style="text-align:center"><img src="/'.self::get_MyFormsPath().'icone/spinner/redmond.gif" style="min-width:50px"></div>');
         $this->JQDt->paging=true;
         $this->JQDt->info=true;
         $this->JQDt->deferRender=true;
         $this->JQDt->processing=true;
         $this->serverSide=$serverSide;
         $this->JQDt->set_hidden_beforeLoading();
         if($serverSide!==null) { 
                           $this->JQDt->serverSide=true;
                           $intervalli=array(array(),array());
                           $n=1;
                           $pars= myJQDataTable::get_ServerSidePars($this->get_ajax_par_name());
                           if($pars['length']>0) $N=$pars['length'];
                           $arr=array(10,25,50);
                           if(!$N) $N=10;
                                elseif($N<10) $arr=array($N,10,25,50);
                                    elseif($N<25) $arr=array($N,25,50);
                           do{
                            foreach ($arr as $l) {
                                if($n*$l>$this->get_N_rows()) break(2);
                                if($l>=$N)  {$intervalli[0][]=$l*$n;
                                             $intervalli[1][]=$l*$n;
                                            }
                                }
                            $n*=10;
                           } while($arr[0]*$n<$this->get_N_rows());
                         //  die($N.'');
                           sort($intervalli[0]);
                           sort($intervalli[1]);
                           $intervalli[0][]=$this->get_N_rows();
                           $intervalli[1][]="'Tutti'";
                           if($this->get_N_rows()<=$N) {$this->JQDt->pageLength=$this->get_N_rows();
                                                        $this->JQDt->paging=false;
                                                       }
                                                 else {$this->JQDt->pageLength=$N;
                                                       $this->JQDt->lengthMenu=$intervalli;
                                                       }
                                                 
                           if($this->get_N_rows()<2)   $this->JQDt->ordering=false;
                          
                           $this->JQDt->ajax=array("type"=>'POST',
                                                   "url"=>"?{$_SERVER["QUERY_STRING"]}&$evento=1",
                                                   "data"=> "function (d){ 
                                                                           d.myJQDatatable={'$evento':{}};
                                                                           for (var key in d)
                                                                                     if(key!='myJQDatatable')
                                                                                              {
                                                                                               d.myJQDatatable['$evento'][key]=d[key];
                                                                                               delete(d[key]);
                                                                                              }
                                                                           var post=".myJQuery::encode_array($_POST,'{}').";
                                                                           for (var key in post)
                                                                                       if(key!='myJQDatatable')
                                                                                              {d[key]=post[key];
                                                                                              }
                                                                          //  console.log(d);
                                                                           }"
                                                   );
                          
                          }
            else{if($_POST)  $this->JQDt->ajax=array("type"=>'POST',
                                             "url"=>"?{$_SERVER["QUERY_STRING"]}&$evento=1",
                                             "data"=>$_POST,
                                             "dataType"=>'json'    
                                            );  
                else $this->JQDt->ajax=array("type"=>'GET',
                                              "url"=>"?{$_SERVER["QUERY_STRING"]}&$evento=1",
                                             "dataType"=>'json'    
                                            );  
                $this->set_limit(0);
                }
         if($this->is_loading_json()) $this->outMode['modo']='jsonJQDT';
         return $this->JQDt;
  }
  
  
 /**
 * @ignore
 */
   public function __toString(){
  	return $this->get_html();
  }
/**
 * @ignore
 */
	protected function catch_id(&$testo) {
	 $m=array();
	 if(preg_match('#\s*id\s*=\s*["\']{0,1}([^"\']+)["\']{0,1}#i',$testo,$m))
	 																	 {$this->id=$m[1];
	 																	  $testo=str_replace($m[0],' ',$testo);
	 																	  }
	 																else $this->set_id("_".str_replace('-','_',myUUID::v4()));
    
	 return $this;
	}



	 public function set_id($id) {
		$this->id=$id;
	}


	 public function get_id() {
		return $this->id;
	}



	function &add_myJQuery(myJQuery &$myJQ) {
	    foreach ($this->myJQueries as $k=>$jq) if($jq instanceof myJQDataTable) unset($this->myJQueries[$k]);
	    if(method_exists($myJQ, 'application')) $myJQ->application($this);
		$k=spl_object_hash($myJQ);
		$this->myJQueries[$k]=&$myJQ;
		return $this->myJQueries[$k];
	}



	/**
	 * @ignore
	 */
	protected function &build_html($html){
	    $js='';
	    if($this->myJQueries) foreach ($this->myJQueries as $myjq) $js.=$myjq;
	    return $html.$js;
	}




  /**
    * Setta parametri di default per i tag della tabella es. class='css'
    * @param    string $parsTable  Opzionale sono i parametri di default da associare al tag TABLE
    * @param    string $parsTR  Opzionale sono i parametri di default da associare da associare al tag TR
    * @param    string $parsTD Opzionale sono i parametri da associare al tag TD
    * @param    string $parsTH Opzionale sono i parametri da associare al tag TH
    **/
	 public function set_pars($parsTable='',$parsTR='',$parsTD='',$parsTH='') {
		if ($parsTable) $this->parametri_table=$parsTable;
		if ($parsTR) $this->parametri_tr=$parsTR;
		if ($parsTD) $this->parametri_td=$parsTD;
		if ($parsTH) $this->parametri_th=$parsTH;
		return $this;
	}

   /**
    * Viene chiamata per ogni cella quando si costruisce l'html della tabella
    * 
    * Estendendo questa classe e riscrivendo questo metodo e' possibile inserire regole di visualizzazione personalizzate in base a riga,colonna
    * es. se si vuole che il dato della colonna 6 sia un link che usa il valore della colonna 7
    * 
    * <code>Class prova extends myTable {
    * 		function &ricalcola_cella($riga,$colonna) {
	* 				if ($colonna!=6) return parent::ricalcola_cella($riga,$colonna);
	* 							else return '&lt;a href="link.php?parametro='.$this->get_cell($riga,7).'"&gt;'.$this->get_cell($riga,6).'&lt;/a&gt;';
	*  		}
	* }
	* </code>
	* 
	* @param    int $riga
    * @param    int $colonna
    * @return   string Il valore che deve comparire nella cella della tabella
    **/
	function &ricalcola_cella($riga,$colonna) {
		return $this->get_cell($riga,$colonna);
	}

	/**
    * Viene chiamata per ogni cella quando si costruisce l'html della tabella
    * 
    * Estendendo questa classe e  riscrivendo questo metodo e' possibile inserire regole di visualizzazione personalizzate in base a riga,colonna,tag
    * Attenzione, se si ricalcola un tag per qualsiasi motivo i parametri di default impostati con set_pars non verranno utilizzati,
    * occorrera' quindi riscriverli
    * es. se si vuole alternare la classe CSS usata per le righe
    * 
    * <code>
    * Class prova extends myTable {
    * 	function ricalcola_TAG($riga,$colonna,$tag) {
	*  		if ($tag!='TR')  return parent::ricalcola_TAG($riga,$colonna,$tag);
	*      		elseif ($riga%2==0) return "class='ClasseXPari'";
	*           		else return "class='ClasseXDispari'";
    *   }
	* }
	* </code>
	* 
    * @param    int $riga
    * @param    int $colonna
    * @param    string $tag Puo' essere 'TR','TD','TABLE'
    * @return   string l'html opzionale da aggiungere al tag
    **/
	 public function ricalcola_TAG($riga,$colonna,$tag) {
		if ($tag=='TABLE' && $this->parametri_table) return $this->parametri_table;
		if ($tag=='TR' && $this->parametri_tr) return $this->parametri_tr;
		if ($tag=='TD' && $this->parametri_td) return $this->parametri_td;
	}


	/**
    * Viene chiamata per ogni cella quando si costruisce l'html dell'intestazione della tabella
    * 
    * Estendendo questa classe e riscrivendo questo metodo e' possibile inserire regole di visualizzazione personalizzate in base a colonna,tag
    * Attenzione, se si ricalcola un tag per qualsiasi motivo i parametri di default impostati con set_pars non verranno utilizzati,
    * occorrera' quindi riscriverli
    * N.B. IL PARAMETRO IN ENTRATA $tag E' CONSIDERATO "TD" ANCHE SE DI FATTO E' UN "TH"
    *
    * es. se si vuole alternare l'allineamento dell'intestazione
    *<code>
    * Class prova extends myTable {
    *  function ricalcola_TAG_intestazione($colonna,$tag) {
	* 	  if ($tag!='TD')  return parent::ricalcola_TAG_intestazione($colonna,$tag);
	* 				elseif ($riga%2==0) return "align='left'";
	* 						   	  else  return "align='right'";
	* }
	*}
	*</code>
    * @param    int $colonna
    * @param    string $tag Puo' essere 'TR','TD'
    * @return   string l'html opzionale da aggiungere al tag
    **/
	function &ricalcola_TAG_intestazione($colonna,$tag) {
		if ($tag=='TR' && $this->parametri_tr) return $this->parametri_tr;
		 elseif ($tag=='TD')
		 			{ if($this->parametri_th) return $this->parametri_th;
									    else  return $this->parametri_td;
		 			}
	}

	/**
     * Setta le intestazioni della tabella
     * @param    array $intestazioni E' un array con le "scritte" da associare ad ogni colonna
     **/
     public function set_intestazioni($intestazioni) {
	 if (is_array($intestazioni)) $this->intestazioni=array_values($intestazioni);
	 return $this;
	}


	/**
     * Restituisce le intestazioni della tabella
     * @return    array $intestazioni
     **/
     public function get_intestazioni() {
	 return $this->intestazioni;
	}


	/**
     * Setta l'intestazione di una colonna della tabella
     * @param    int $colonna
     * @param    string $intestazione E' la "scritte" da associare a $colonna
     **/
     public function set_intestazione($colonna,$intestazione) {
	 $this->intestazioni[$colonna]=$intestazione;
	 return $this;
	}


	/**
     * Inserisce una colonna di icone nella posizione pos
     * @param    string $intestazione E' la "scritte" da associare a $colonna
     * @param    int $pos se non c'e' si accoda
    **/
	 public function add_intestazione($intestazione,$pos='') {
		if ($this->intestazioni)
			{
			if ($pos==='') $this->intestazioni[]=$intestazione;
				ELSE 	{foreach ($this->intestazioni as $i=>$v) {
							 if ($i==$pos) $nuovo[]=$intestazione;
							 $nuovo[]=$v;
							}
						$this->intestazioni=$nuovo;
						}
			}
		return $this;
	}

	/**
     * Inserisce una colonna di icone nella posizione pos
     * 
     *  <code>
     *  $valori=array( array(1,'pippo'),
     *  			   array(2,'topolino'),
     *  			   array(3,'pluto')
     *  			);
  	 * 	$tabella=new myTable($valori);
 	 * 	$i=new MyIcon('/icone/edit.png');    //creo un'icona
 	 *  $i->set_link(new MyLink('gestione.php','Modifica dati'));  //le assegno un link ad un script che per cancellare chiede le chiavi in _GET
 	 *	$tabella->add_col_icone($i,  array('codice'=>0) );
 	 * </code>
 	 *
     * @param    MyIcon $Icona
 	 * @param    array  $parametri E' un array associativo con il nome del parametro e la colonna da cui prendere il valore da usare per arricchire il link dell'icona
     * @param    int $pos se non c'e' si accoda
     **/
	 public function add_col_icone($Icona,$parametri='',$pos='') {
	  
	 /* if (is_array($parametri) && $link=$Icona->get_link()) $newLink=$link->clonami();
	  for ($i=0;$i<$this->get_N_rows();$i++) {
	  	    if ($newLink)
	  				   {
	  					$href=$link->get_attributo('href');
	  					$url='';
	  					$time=time();
	  					if (is_array($parametri))
	  							foreach ($parametri as $NomeCol=>$NumCol) {
	  							  if(is_array($NumCol)){
	  							  			 $numCol=array_shift($NumCol);
	  							  			 $obj=array_shift($NumCol);
	  							  			 if($obj instanceof myTable_Plugin_for_add_col_icone) $val=$obj->add_col_val($NomeCol,$this->get_cell($i,$numCol));
	  							  			 											   else   $val=$this->get_cell($i,$numCol);
	  							  			 $url.="&amp;$NomeCol=".rawurlencode($val);
	  							  			}
	  									else $url.="&amp;$NomeCol=".rawurlencode($this->get_cell($i,$NumCol));
	  							}
	  					$href.=(strpos($href,'?')!==false?'&amp;':'?')."ttt=$time$url";
	  					$newLink->set_attributo('href',$href);
	  					$Icona->set_link($newLink);
	  					$x[$i]=$Icona->get_Html();
	  				   }
	  		}

	  $this->ins_col($x,$pos);*/
	  if (!is_array($parametri) || !($link=$Icona->get_link())) return $this;
	  $newLink=$link->clonami();
	  $jss=array();
	  preg_match_all('@\<\!\-\- JS\+ \-\-\>.*\<\!\-\- JS\- \-\-\>@USs',$Icona->get_Html(), $jss);
	  if($jss) foreach ($jss[0] as $js) $this->JS.=$js;
	  if($this->PAG && $this->N  && !$this->get_N_rows()) {//se passa qui inserisce righe fittizie con la sola icona(!)
					$start=	(int) $this->N*($this->PAG-1);
					$stop=  $start+min(count($this->valori),$this->N);
					}
				else  {
					$start=0;
					$stop=$this->get_N_rows();
					}
	  for ($i=$start;$i<$stop;$i++)
			{
			$href=$link->get_attributo('href');
			$url='';
			$time=time();
			if (is_array($parametri))
				foreach ($parametri as $NomeCol=>$NumCol) 
					if(!is_array($NumCol)) $url.="&amp;$NomeCol=".rawurlencode($this->get_cell($i,$NumCol));
						else{
							$numCol=array_shift($NumCol);
							$obj=array_shift($NumCol);
							if($obj instanceof myTable_Plugin_for_add_col_icone) $val=$obj->add_col_val($NomeCol,$this->get_cell($i,$numCol));
																		  else   $val=$this->get_cell($i,$numCol);
							$url.="&amp;$NomeCol=".rawurlencode($val);
							}
			$href.=(strpos($href,'?')!==false?'&amp;':'?')."tt=$time$url";
			$newLink->set_attributo('href',$href);
			$Icona->set_link($newLink);
			$icona_html=$Icona->get_Html();
			if($jss) $icona_html= preg_replace('@\<\!\-\- JS\+ \-\-\>.*\<\!\-\- JS\- \-\-\>@USs','',$icona_html);
			if($pos==='') $this->valori[$i][]=$icona_html;
			        else  self::ins_array($this->valori[$i],$pos,$icona_html);
			}
	/*  if(!$pos) $this->valori[0][]='';
			else  $this->ins_array($this->valori[0],$pos,'');*/
	  return $this;
	}



	/**
	 * Inserisce una colonna di check nella posizione pos
	 * 
	 * es.
	 * <code>
	 * $t=new mytable(array(array(0.1,1,'c'),array(0.2,2,'c'),array(0.3,3,'c')));
	 * $t->add_col_check(new myCheck('check'),'',0,
	 * 										function($chk,$table,$riga)
	 *											   {
	 *												$chk->set_id($riga);
	 * 												$chk->set_value($table->get_cell($riga,0)+$table->get_cell($riga,1));
	 *												}
	 *					 					);
	 * $t->add_col_check(new MySingleRadio('radio'),3,0);
	 *
	 * </code>
	 * @param myCheck|mySingleRadio $Check
	 * @param int|array $colonnae Se intero la check si valorizza con il valore della $colnnae-esima colonna
	 * 							  se a' un array il valore e' sempre 1 ma l'attributo "name" del check viene costruito come array associativo usando come chiavi il contenuto delle celle indicate in $colonnae
	 * @param int $pos se non c'e'' si accoda
	 * @param function() $function funzione anonima di personalizzazione (se usata si ignora $colonnae), se impostata viene invocata per ogni $Check passando nel primo parametro l'istanza del check e nel secondo l'istanza della tabella stessa e nel terzo il numero di riga corrente
	 */
	 public function add_col_check(myCheck $Check,$colonnae='',$pos='',$function='')
		{
		if($this->PAG && $this->N) {
				$start=	$this->N*($this->PAG-1);
				$stop=  $start+min(count($this->valori),$this->N);
			}
			else  {
				$start=0;
				$stop=$this->get_N_rows();
			}	
			
		for ($i=$start;$i<$stop;$i++) {
			$chk=clone $Check;
			if($function!=='') $function($chk,$this,$i);
				else{$newname=$Check->get_name();
					 if (!is_array($colonnae)) $chk->set_value($this->get_cell($i,$colonnae));
							else {$chk->set_value(1);
								  foreach ($colonnae as $NumCol)
											$newname.="[{$this->get_cell($i,$NumCol)}]";
								 }
					  $chk->set_name($newname,true);
					}
			//$x[$i]=$chk;
			if($pos==='') $this->valori[$i][]=$chk;
					else  self::ins_array($this->valori[$i],$pos,$chk);
		}
	//	$this->ins_col($x,$pos);
	return $this;
	}



	/**
     * Setta il numero di righe da visualizzare per pagina ed il numero di pagina da cui partire
     * @param    int $N Numero di righe per pagina
     * @param    int $pag Il numero della pagina che verrà visualizzata da 1 in poi
     **/
     public function set_limit($N,$pag='') {
       if($this->is_loading_json()=='post') {
        
                         $pars=myJQDataTable::get_ServerSidePars($this->get_ajax_par_name());
                        
                         $N=$pars['length'];
                         $pag=1+intval($pars['start']/$N);
                         if($pars['ordine']) $this->USort($pars['ordine']);
                              
                         myJQDataTable::set_ServerSidePars($this->get_ajax_par_name(),'unfiltered',count($this->valori));
                         if($pars['ricerca']) 
                                      { $new=array();
                                        foreach ($this->valori as &$riga) {
                                            $ok=false;
                                            foreach ($riga as &$cell) 
                                                foreach ($pars['ricerca'] as &$ricerca) {
                                                    if( ($ricerca['type']!='regex' && stripos($cell,$ricerca['val'])===0)
                                                                            ||
                                                        ($ricerca['type']=='regex' && preg_match("@{$ricerca['val']}@i", $cell))
                                                                    
                                                        ) {$ok=true;break(2);}
                                                }  
                                            if($ok) $new[]=&$riga;
                                           }
                                        $this->valori=&$new;   
                                    }    
                        myJQDataTable::set_ServerSidePars($this->get_ajax_par_name(),'filtered',count($this->valori));
                        }   
	 $this->N=$N;
	 if ($pag<=0) $pag=1;
	 if ($pag*$N>$this->get_N_rows()+$N) $pag=(int)($this->get_N_rows()/$N);
	 if ($pag<=0) $pag=1;
	 $this->PAG=$pag;
	 return $this;
	}




	/**
	 * Restituisce il selettore con la scelta di tutte le pagine disponibili
	 * @param    string $class Opzionale e' la classe CSS da associale al selettore
	 * @param    string $reload Indica il tipo di reload da applicare completo|submit|... ecc.
	 * @return   MySelect
	 **/
	 public function get_pager($classe='',$reload='completo') {
	    if (!$this->PAG) $this->PAG=1;
	    $nrighe=$this->get_N_rows();
	
	    $mysel=new MySelect('MyPage',$this->PAG);
	    if (!$nrighe|| !$this->N) return $mysel;
	    $n_x_pag=$this->N;
	    $pagina=$this->PAG;
	    $tot_pagine=intval($nrighe/$n_x_pag)+(($nrighe%$n_x_pag)!==0?1:0);
	    
	    for($j=$pagina;$j>1;$j--)           {$min=$pags[$j]=$j;if($pagina-$j>8 && $j%10===0) break;}
	    for($j=$pagina;$j<$tot_pagine;$j++) {$max=$pags[$j]=$j;if($j-$pagina>8 && $j%10===0) break;}
	    $step=1;
	    $nmin=$nmax=null;
	    if(isset($pags)) do {$n=count($pags);
                            $step*=10;
                    	    if(isset($min)) for($j=$min;$j>1;$j-=$step)           
                    	                    {$nmin=$pags[$j]=$j;
                    	                     if($pagina-$j>8*$step && ($j%(10*$step))===0) break;
                    	                   }
                    	    if(isset($max))for($j=$max;$j<$tot_pagine;$j+=$step)
                                           {$nmax=$pags[$j]=$j;
                                           if($j-$pagina>8*$step && ($j%(10*$step))===0) break;
                                           }
                            $min=$nmin;$max=$nmax;
                	    } while ($n!=count($pags));
	    $pags[1]=1;
	    $pags[$tot_pagine]=$tot_pagine;
	    ksort($pags);
	    
	    
	    if (is_array($pags)) {foreach (array_keys($pags) as $i) $pags[$i]="$i di $tot_pagine";
                         	  $mysel->set_opzioni(array_flip($pags));
                    	      if ($reload) $mysel->SetReloadJS($reload);
                    	    }
	    return $mysel;
	    
	}
	



/*
	protected function build_html($parametri,$riga){
	
		$i=&$parametri['i'];
		if($i<$parametri['max']) {
		  if ((!$parametri['righe'] || isset($parametri['righe'][$i])) && is_array($riga))
			{
			$rigahtml='';
			foreach ($parametri['colonne'] as &$j) {
				 $tag=$this->ricalcola_TAG($i,$j,'TD');
				 if($tag!==false) $rigahtml.='<td headers="colonna_'.$j.'" '.$tag.'>'.($parametri['usa_ricalcola_cella']?$this->ricalcola_cella($i,$j):$riga[$j]).'</td>';
				}
		
			$ric_tr=&$this->ricalcola_TAG($i,$j,'TR');
			if($ric_tr!==false) $parametri['out'].='<tr'.($ric_tr?" $ric_tr":'').">$rigahtml</tr>\n";
			}
		}
		$i++;	
		
		return $parametri;	
	}
*/

	/**
     * Restituisce il corpo della tabella in html senza <table> ed eventuale intestazione
     * @param    array $colonne E' opzionale e se inserito indica i numeri delle colonne da visualizzare (viene rispettato l'ordine)
     * @param    array $righe E' opzionale e se inserito indica i numeri delle righe da visualizzare (viene rispettato l'ordine)
     * @param    boolean $compresoTable viene inserito o meno il tag /TABLE
     * @return   string
     **/
    function &get_body_html($colonne='',$righe='',$compresoTable=false) {
     if (is_array($righe)) $righe=array_flip((array) $righe);
     if (!$colonne) $colonne=array_flip(range(0, $this->get_N_cols()-1));

     if (!$this->N) $this->N=$this->get_N_rows();
     if (!$this->PAG) $this->PAG=1;
     $i=($this->PAG-1)*$this->N;
     $n=$this->get_N_rows();
     if ($compresoTable) $out= "\n<table ".($this->id?'id="'.$this->id.'" ':'').$this->ricalcola_TAG(-1,-1,'TABLE')." ><tbody>";
     			    else $out='';

     $usa_ricalcola_cella=$this->outMode['modo']=='xml' || $this->codifica_default!=$this->codifica || $this->isOverridden('ricalcola_cella');
    // $t=microtime(1);
    /* 
     $parametri=array('out'=>$out,
     				  'usa_ricalcola_cella'=>$usa_ricalcola_cella,
     				  'righe'=>$righe,
     				  'i'=>$i,
     				  'max'=>min($this->PAG*$this->N,$n),
     				  'colonne'=>$colonne);
     $parametri=array_reduce($this->valori,array($this,'build_html'),$parametri);
     $out=&$parametri['out'];
     */
     while ($i<min($this->PAG*$this->N,$n))
	 	{
	 	if ((!$righe || isset($righe[$i])) &&  isset($this->valori[$i]) && is_array($this->valori[$i]))
	     {
	 	 $rigahtml='';
		 foreach ($colonne as &$j) {
	 	   						$tag=$this->ricalcola_TAG($i,$j,'TD');
	 	   						if($tag!==false) 
	 	   							$rigahtml.='<td headers="colonna_'.$j.'" '.$tag.'>'.
	 	   										($usa_ricalcola_cella?$this->ricalcola_cella($i,$j):
	 	   												(isset($this->valori[$i][$j])?$this->valori[$i][$j]:'')).'</td>';
	 	   					}

	 	 $ric_tr=&$this->ricalcola_TAG($i,$j,'TR');
	 	 //if(!preg_match('@\sid\s*=\s*@S', $ric_tr)) $ric_tr.="id='".$this->get_id().'_row_'.($i+1)."'";
		 if($ric_tr!==false) $out.='<tr'.($ric_tr?" $ric_tr":'').">$rigahtml</tr>\n";
	    }
	   $i++;
	 }
    // die(microtime(1)-$t);
    
	return myForm::clean_ridondanze_html($this->JS.$out.($compresoTable?"\n</tbody></table>".$this->get_ordineJS():''));
	}

	function &get_body_xml($colonne='',$righe='',$compresoTable=false) {
	       return preg_replace('@<script[^>]*></script>@U','',$this->get_body_html($colonne,$righe,$compresoTable));
	   	}
	
	
	
	function &get_body_json($colonne='',$righe='') {
	    $usa_ricalcola_cella=$this->isOverridden('ricalcola_cella');
	        
	    if(!$colonne && 
	       !$righe && 
	       !$usa_ricalcola_cella && 
	        $this->codifica=='UTF-8' && 
	       !$this->N) return $this->valori;

	    if (!$this->N)   $this->N=$this->get_N_rows();
	    if (!$this->PAG) $this->PAG=1;
	    $i=($this->PAG-1)*$this->N;
	    
	    $n=$this->get_N_rows();
	    
	    if (is_array($righe)) $righe=array_flip((array) $righe);
	    if (!$colonne) $colonne=array_flip(range(0, $this->get_N_cols()-1));
	   
	    $dati=array();
	    while ($i<min($this->PAG*$this->N,$n))
	    {
	        if ((!$righe || isset($righe[$i])) && is_array($this->valori[$i]))
	           {$k=0;
	            foreach ($colonne as &$j)  {
	                      if($usa_ricalcola_cella) $dati[$i][$k]=$this->ricalcola_cella($i,$j);
	                                          else $dati[$i][$k]=$this->valori[$i][$j];
	                      if('UTF-8'!=$this->codifica)  $dati[$i][$k]=myCharset::utf8_encode($dati[$i][$k]);
	                      $k++;
	                   }
	          }
	        $i++;
	    }
	   
	    return $dati;
	}
	
	
	
	/**
	* Setta l'ordinamento della tabella tramite Javascript
	* 
	* Funziona solo se poi si usa il metodo &get_html($colonne = '', $righe = '');
    * es. se si vuole ordinare una tabella di 4 colonne per la prima che a' un numero e la terza che e' un data
    * <code>
    * $criteri=array('number','','date','');
    * $tabella->set_ordineJS($criteri);
    * tabella->get_html();
   	* </code>
	* @param    array $criteri e' un array che associa alla colonna il tipo di dato number,string,date,
	**/
	 public function set_ordineJS($criteri) {
	 $this->sortJS='<script type="text/javascript">';
	 $pars=array();
	 foreach ($criteri as $val) {
	 							$val=trim(strtolower($val));
	 							if ($val=='') $pars[]='"None"';
	 							elseif($val=='number') $pars[]='"Number"';
	 							elseif($val=='string') $pars[]='"CaseInsensitiveString"';
	 							elseif($val=='date') $pars[]='"DateIt"';
	 							}
	 $this->sortJS.="new SortableTable(document.getElementById('$this->id'),[".implode(',',$pars)."]);
	 				</script>
	 				";
	 return $this;
	}


	/**
     * Restituisce la sola riga d'intestazione in html
     * @param    array $colonne E' opzionale e se inserito indica i numeri delle colonne da visualizzare
     * @param    boolean $compresoTable viene inserito o meno il tag TABLE
     * @return   string
    **/
    function &get_header_html($colonne='',$compresoTable=false) {
     $rigahtml=$out='';
     if ($compresoTable) $out= "\n<table ".($this->id?'id="'.$this->id.'" ':'').$this->ricalcola_TAG(-1,-1,'TABLE')." >";
     if (!$this->intestazioni || !$this->outMode['intestazione']) return $out;
     if($this->caption) $out.="<caption>{$this->caption}</caption>";
     $this->valori[-1]=$this->intestazioni;
     if (is_array($this->intestazioni)) {
     	 if (!$colonne) $colonne=array_keys($this->intestazioni);
     	 foreach ($colonne as $j) {    
     	                                $tag=$this->ricalcola_TAG_intestazione($j,'TD');
     	                                if($tag!==false) $rigahtml.='<th id="colonna_'.$j.'" scope="col" '.$tag.'>'.(isset($this->intestazioni[$j])?$this->intestazioni[$j]:'').'</th>';
	 	 						  }
     }
     $ric_tr=&$this->ricalcola_TAG_intestazione($j,'TR');
     if($ric_tr!==false) $intesta="\n<tr".($ric_tr?" $ric_tr":'').">$rigahtml</tr>\n";
     unset($this->valori[-1]);
     return $out."<thead".($this->sortJS?" class='MysortedTable'":'').">$intesta</thead>\n";
	}

	/**
	 * Restituisce la sola riga d'intestazione in xml
	 * @param    array $colonne E' opzionale e se inserito indica i numeri delle colonne da visualizzare
	 * @param    boolean $compresoTable viene inserito o meno il tag TABLE
	 * @return   string
	 **/
	function &get_header_xml($colonne='',$compresoTable=false) {
	   return '<'.'?xml version="1.0" encoding="'.$this->codifica.'"?'.'>'.
	               preg_replace('@<script[^>]*></script>@U','',$this->get_header_html($colonne,$compresoTable));
	 }
	
	/**
	 * Restituisce la sola riga d'intestazione in json
	 * @param    array $colonne E' opzionale e se inserito indica i numeri delle colonne da visualizzare
	 * @return   string
	 **/
	function &get_header_json($colonne='') {
	    if (!$this->intestazioni || !$this->outMode['intestazione']) return null;
	    $dati=array();
	    if (is_array($this->intestazioni)) {
	                    if (!$colonne) $colonne=array_keys($this->intestazioni);
            	        foreach ($colonne as $j) $dati[]=$this->intestazioni[$j];
            	       }
	    return $dati;
	}	
	
	
	/**
	 * Restituisce la tabella in html
 	 * @param    array $colonne E' opzionale e se inserito indica i numeri delle colonne da visualizzare
     * @param    array $righe E' opzionale e se inserito indica i numeri delle righe da visualizzare
     * @return   string
     */
    function &get_html($colonne='',$righe='') {
        
        if($this->soloDati &&  $this->soloDati->isTrue(null)) 
                    {   
                        if($this->JQDt && $this->is_loading_json()) $this->set_output_mode('jsonJQDT',false);
                        $this->soloDati=null;
                       // unset($this->myJQueries[spl_object_hash($this->JQDt)]);//evita di riagganciare jquery
                        $this->send_output();
                    }
                    
        //
        switch ($this->outMode['modo']){
            case 'html':
                    if($this->JQDt) $html=myForm::clean_ridondanze_html($this->get_header_html($colonne,true).'</table>');
                            elseif($this->outMode['intestazione']) 
                                            $html=myForm::clean_ridondanze_html($this->get_header_html($colonne,true)."<tbody>".$this->get_body_html($colonne,$righe,false).'</tbody></table>');
                                    else    $html=myForm::clean_ridondanze_html($this->get_header_html($colonne,true).$this->get_body_html($colonne,$righe,false).'</table>');
                    return $this->build_html($html);
            case 'xml':
                     if($this->outMode['intestazione']) return $this->get_header_xml($colonne,true).'<tbody>'.$this->get_body_xml($colonne,$righe,false).'</tbody></table>';
                                                   else return $this->get_header_xml($colonne,true).$this->get_body_xml($colonne,$righe,false).'</table>';
            
           case 'jsonJQDT':
               if($this->is_loading_json()=='post') 
                                {  myJQDataTable::set_ServerSidePars($this->get_ajax_par_name(), 'unfiltered', $this->get_N_rows());
                                   myJQDataTable::set_ServerSidePars($this->get_ajax_par_name(), 'filtered', $this->get_N_rows());
                                   return myJQDataTable::build_json($this->get_ajax_par_name(),$this->get_body_json($colonne,$righe));    
                                }
            case 'json':$d=$this->get_header_json($colonne);
                        if($d!==null) $v= array_merge(array($d),$this->get_body_json($colonne,$righe));
                                 else $v=$this->get_body_json($colonne,$righe);
                                 return json_encode($v,intval( JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
                        
            
            case 'var_export':
            case 'serialize':
                        $d=$this->get_header_json($colonne);
                        if($d!==null) $v= array_merge(array($d),$this->get_body_json($colonne,$righe));
                                 else $v=$this->get_body_json($colonne,$righe);
                        switch ($this->outMode['modo']) {
                            case 'var_export' :return var_export($v,1);
                            case 'serialize' :return serialize($v);
                        }
               }                           
	}
	
	protected static function header_links(&$html,$rev_path=false){
	    $links=$m=array();
	    foreach (explode('<',$html) as $tag)
	            if(stripos($tag,'img')===0)
	            {
	                $tag=substr($tag, 3,strpos($tag,'>')-1);
	                if(preg_match('@src\s*=\s*(.+)@', $tag,$m)) {
	                       $path= substr($m[1],1,strpos($m[1],$m[1][0],1)-1);
	                       $links[$rev_path?str_replace('\/','/',$path):$path]='image';
	                       }
	            }
	        elseif(stripos($tag,'script')===0)
    	        {
    	            $tag=substr($tag, 3,strpos($tag,'>')-1);
    	            if(preg_match('@src\s*=\s*(.+)>@', $tag,$m)) {
    	                $path= substr($m[1],1,strpos($m[1],$m[1][0],1)-1);
    	                $links[$rev_path?str_replace('\/','/',$path):$path]='script';
    	               }
    	                
    	        }
	 foreach ($links as $link=>$type) if(strlen($link)>2) header("Link: <$link>; rel=preload; as=$type",false);
	        
	}

	/**
	* Invia al client il file Word (determina la chiusura dello script)
	 * @param   string $nomeFile
	 * @param   boolean $exit se e' true viene eseguito un exit al termine della send, altrimenti lo script chiamante prosegue (in questo caso non dare echo o altre visualizzazioni dopo la send)
	 * @param   boolean $forza_download
	 */
	 public function send_output($nomeFile='',$exit=true,$forza_download=true) {
	    header("Expires: " . gmdate("D, d M Y H:i:s",time()+30) . " GMT");
	    header("Cache-Control: post-check=0, pre-check=0, private");
	    header('X-Content-Type-Options: nosniff');
	    
	    if($nomeFile) {
	            header("Content-Description: File Transfer");
	            header("Content-Transfer-Encoding: binary");
	            if($forza_download) header("Content-Disposition: attachment; filename=\"".basename($nomeFile)."\"");
	                        else    header("Content-Disposition: inline; filename=\"".basename($nomeFile)."\"");
	           }
	                       
	    switch ($this->outMode['modo']) {
	        case 'xml' : header("Content-Type: text/xml; charset={$this->codifica}"); break;
	        case 'jsonJQDT':
	        case 'json': header("Content-Type: text/plain; charset=utf-8"); break;
	        case 'html': header("Content-Type: text/html;  charset={$this->codifica}");
	        break;
	    }
	   
	    $tutto=$this->get_html();
	    if($this->outMode['modo']=='html') self::header_links($tutto,false);
	    if($this->outMode['modo']=='json' || $this->outMode['modo']=='jsonJQDT') self::header_links($tutto,true);
	//    exit;
	    $size=strlen($tutto);
	    if($size>1024 && strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') )
     						{
				          	header("Content-Encoding: gzip");
				          	header("Original-Size: ".$size);
							$tutto= "\x1f\x8b\x08\x00\x00\x00\x00\x00". substr(gzcompress($tutto, 9), 0, - 4). pack('V', crc32($tutto)). pack('V', $size);
							$size=strlen($tutto);	
     						}
     	header("Content-Length: ".$size);
     	if($exit) die($tutto);
     	     else echo $tutto;
	}
	
	
	/**
	 * Alias di get_html
	 * @see self::get_html
	 */
    function &get_output($colonne='',$righe=''){
       return $this->get_html($colonne,$righe);      
    }

	 /**  */
	  public function get_ordineJS(){
	   static $__myTables;
	   if ($this->sortJquery){
	   		return $this->sortJquery;
	   }
	   else{
       if ($this->sortJS && !$__myTables['SortTableJS'])
       			{
		 	 	$js="<script  type=\"text/JavaScript\" src='/".self::get_MyFormsPath()."js/sortabletable.js'></script>
		 	 	<style type=\"text/css\">
						<!--
						.MysortedTable th {cursor: pointer;cursor: hand; white-space: nowrap}
						.sort-arrow {width:13px;height:13px;background-position:center;background-repeat:no-repeat;border:0;margin:0 2px;}
						.sort-arrow.descending {background-image:url('/".self::get_MyFormsPath()."icone/downsimple.png');}
						.sort-arrow.ascending  {background-image:url('/".self::get_MyFormsPath()."icone/upsimple.png');}
						-->
					 </style>";
		  	 	$__myTables['SortTableJS']=1;
		 		}

     	return $js.$this->sortJS;
	   }
	 }



	/**
	 * Importa un myTable da html
	 * 
	 * Ecco come importare la seconda tabella contenuta in questo html e poi aggiungerci una riga
	 * <code>
	 * $html="
	 *	<table>
	 *		<tr><th>A</th><th>B</th></tr>
	 *		<tr><td>1</td><td>2</td></tr>
	 *		<tr><td>11</td><td>22</td></tr>
	 *	  </table>
	 *
	 *		<table style='background:blue' cellspacing='2'>
	 *		<tr style='color:blue'>
	 *			<th style='color:white'>A</th>
	 *			<th style='color:white'>B</th>
	 *			<th style='color:white'>C</th>
	 *		</tr>
	 *		<tr style='color:blue'>
	 *			<td style='background:white'>1</td>
	 *			<td style='background:white'>2</td>
	 *			<td style='background:white'>3</td>
	 *		</tr>
	 *		<tr style='color:blue'>
	 *			<td style='background:white'>11</td>
	 *			<td style='background:white'>22</td>
	 *			<td style='background:white'>33</td>
	 *		</tr>
	 *		</table>";
	 *
	 * $t=new myTable();
	 * $t->importa_html($html,2)
	 *   ->ins_row(array(111,222,333));
	 * echo $t;
	 *</code>
	 *
	 *
	 * @param string $html      html da lavorare
	 * @param int $quale		numero d'ordine della tabella da importare
	 * @param boolean $accoda	se true accoda i valori a quelli correnti (ma solo per le prime n colonne dove n e' il numero di colonne gia' in memoria)
	 * @param boolean $solodati se true importa solo i dati altrimenti anche gli attributi a table,th,tr,td (ma solo se comuni a tutti i tag come da esempio)
	 */
	 public function importa_html(&$html,$quale=1,$accoda=false,$solodati=false) {
	    $m=$mm=array();
	    $salta=false;
		if (!$accoda)   $this->set_matrix($m);
		if (!$solodati) {
						$this->parametri_th='';
						$this->parametri_tr='';
						$this->parametri_td='';
						$this->parametri_table='';
						}

		if(preg_match_all("~<table([^>]*)>.*</table>~Uis",$html,$m))
		  if(($table=$m[0][$quale-1]))
			{
			 if(!$solodati)	$this->parametri_table=$m[1][$quale-1];
			 $table=preg_replace(array('@<thead[^>]*>@Uis',
			 				   '@</thead>@Uis',
			 					   '@<tbody[^>]*>@Uis',
			 					   '@</tbody>@Uis',
			 					   '@<tfoot[^>]*>@Uis',
			 					   '@</tfoot>@Uis'),'',$table);
			 if(preg_match_all("~<th([^>]*)>(.*)</th>~Uis",$table,$m) && $m[1])
			 							 {
			 							  foreach ($m[1] as &$v) $v=trim((string) $v);
			 							  $this->set_intestazioni((array) $m[2]);
			 							  if(!$solodati && $m[1])
			 							  				{
			 							  				  if (implode(chr(0),$m[1])===implode(chr(0),array_fill(0,count((array)$m[1]),$m[1][0]))) $this->parametri_th=trim((string) $m[1][0]);
			 							  				}
			 							  $salta=true;
			 							}

			 if(preg_match_all("~<tr([^>]*)>(.*)</tr[^>]*>~Uis",$table,$m))
			 		{unset($m[0]);
			 		 if($salta) unset($m[2][0]);
			 		 if(!$solodati && $m[1]) {
			 						  		   if (implode(chr(0),$m[1])===implode(chr(0),array_fill(0,count((array)$m[1]),$m[1][0])))   $this->parametri_tr=trim((string) $m[1][0]);
			 		 						 }
			 		 unset($m[1]);
			 		 $attr_td=array();
			 		 foreach ($m[2] as &$riga)
			 		 		if(preg_match_all('~<td([^>]*)>(.*)</td[^>]*>~SUis',$riga,$mm) && $mm[2])
			 		 				{ if(!$solodati) $attr_td=array_merge($attr_td,$mm[1]);
			 		 				  $this->ins_row($mm[2]);
			 		 				}

			 		  if(!$solodati && $attr_td)
			 		  				{
			 						  if (implode(chr(0),$attr_td)===implode(chr(0),array_fill(0,count((array)$attr_td),$attr_td[0])))   $this->parametri_td=trim((string) $attr_td[0]);
			 		 				}
			 		}

			}

		return $this;
	}



 /**  */
    public function clonami() {
   		if (PHP_VERSION >= 5) return clone($this);
    					else  return (unserialize(serialize($this)));
    }





}