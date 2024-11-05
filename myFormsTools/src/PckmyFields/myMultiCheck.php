<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myMultiCheck.
 */

namespace Gimi\myFormsTools\PckmyFields;
 
use Gimi\myFormsTools\PckmyDBAdapters\myAdoDBAdapter;
use Gimi\myFormsTools\PckmyJQuery\myJQuery;


class myMultiCheck extends myField {
/** @ignore */
protected $campoAutocomplete,$campi=array(),$accapo=array(null,null),$opzioni=array(),$reloadJSGET, $showlinks, $nbrsp,$xmlTagCampo,$posizionelabel='d',$restrizioni_check=true;

      /**
      * @param	 string $nome E' il nome del campo
	  * @param	 array $valori Valori da assegnare come default
	  * @param	 array $opzioni E' un array associativo con le opzioni Titolo=>valore
	  * @param	 string $classe e' la classe css da utilizzare
	  */
	   public function __construct($nome,$valori=array(),$opzioni=array(),$classe=''){
				if ($valori && !is_array($valori)) $valori=explode(',',$valori);
										elseif (!$valori) $valori=array();
				myField::__construct($nome,$valori);
				if ($classe) $this->set_attributo('class',$classe);
				if (is_array($nome) && $nome['opzioni']) $opzioni=$nome['opzioni'];
				if (count($opzioni)>0) $this->set_Opzioni($opzioni);
				$this->set_MyType('MyMultiCheck');
				$this->Richiede_tag_label=false;
			  }

			  
		  /**
        	* Setta la caratteristica di readonly
			*/
	 	 public function set_readonly($var = true){
				if (is_array($this->campi)) foreach (array_keys($this->campi) as $id) $this->campi[$id]->set_readonly($var);
				return $this;
		  }


		  /**
		   * Permette di rendere il check_errore piu' o meno restrittivo
		   */
		   public function set_restrizioni_check($stato){
    		  	$this->restrizioni_check=$stato;
    		  	return $this;
		  }


		    public function isMultiple(){return true;}

 		 
 		 /**
 		  * Imposta l'effetto autocomplete per le opzioni
 		  * 
 		  * @param string $label
 		  * @param myText $MyText
 		  * @param string $jsfunc
 		  * @param number $carMin
 		  * @return myMultiCheck
 		  */
 		  public function set_autocomplete($label='',myText $myText=NULL,$jsfunc='',$carMin=1) {
 		     if(!$myText) $myText=new myText('');
 		     if(!$jsfunc) $jsfunc="function(campo,lab,val){return lab.toLowerCase().replace(/ /g,'').indexOf(campo.trim().toLowerCase().replace(/ /g,''))>=0}";
 		     $this->campoAutocomplete=array($myText,$jsfunc,$carMin,$label);
 		     return $this;
 		 }

 		 
 		 
 		 protected function get_html_autocomplete(){	 
 		     if(!isset($this->campoAutocomplete[0]) || !($this->campoAutocomplete[0] instanceof myText)) return '';    
 		     $myText=$this->campoAutocomplete[0];
 		     $jsfunc=$this->campoAutocomplete[1];
 		     $label=$this->campoAutocomplete[3];
 		     $jq=new myJQuery("#{$myText->get_id()}");
 		     $jq->add_code("{$jq->JQid()}.keyup(
 		                         function(){
 		                                    // if({$jq->JQid()}.val().length<{$this->campoAutocomplete[2]} ) return true;
 		                                     {$jq->JQvar()}(\"[id^='{$this->get_id()}']:not(:checked):visible\").each(
 		                                                                             function(){
 		                                                                               if(
                                                                                           !($jsfunc)({$jq->JQid()}.val(),{$jq->JQvar()}('label[for='+{$jq->JQvar()}( this ).prop('id')+']').text(),{$jq->JQvar()}( this ).val())
                                                                                         )   {$jq->JQvar()}( this ).parent().stop().slideUp(700);    
 		                                                                                }
 		                                                                             );
                                              {$jq->JQvar()}(\"[id^='{$this->get_id()}']:hidden\").each(
 		                                                                             function(){
 		                                                                               if(
                                                                                           ($jsfunc)({$jq->JQid()}.val(),{$jq->JQvar()}('label[for='+{$jq->JQvar()}( this ).prop('id')+']').text(),{$jq->JQvar()}( this ).val())
                                                                                         )   {$jq->JQvar()}( this ).parent().stop().slideDown(700);
 		                                                                                }
 		                                                                             );                    
 		                                 }
 		                     );");
 		                     
             $jq->add_code(" {$jq->JQvar()}(\"[id^='{$this->get_id()}']:not(:checked)\").parent().hide();");
            // $jq->add_code(" {$jq->JQid()}.first(':not(script)').first(':not(script)').css({'position':'fixed','z-index':'1000'});");
 		     $myText->add_myJQuery($jq);
 	         return ($label?"<label for='{$myText->get_id()}'>$label</label>":'').$myText;//"<input {$myText->stringa_attributi(array('name'))}>*".;     	     
 		 }
 		 
 		 
 		  public function get_titolo($valore=''){
 		 	if(!$valore) $valore=$this->get_value();
 		 	$vals=array_flip($this->get_Opzioni());
 		 	return $vals[$valore];
 		 }
 		 

		  /**
		   * Imposta la mposizione delle label rispetto al check
		   *
		   * @param 's'|'d' $posizione s=sinistra d=destra
		   */
		   public function set_posizione_label($posizione){
		  	 $posizione=strtolower($posizione);
		  	 if($posizione[0]=='s' || $posizione[0]=='d') $this->posizionelabel=$posizione[0];
		  	 return $this;
		  }

		  public function richiede_tag_label(){
 			return	false;
 		 }


		   public function set_disabled($var = true){
				if (is_array($this->campi)) foreach (array_keys($this->campi) as $id) $this->campi[$id]->set_disabled($var);
				return $this;
		  }

			/** @ignore*/
		  function &get_html_Accessibilita($evento) {
		             $out='';
					 if ($evento) { $out.="<span style='position: absolute;	top: -10000em;	left:-10000em;width: 0em;height:0em;clear:none;'><br /></span>";
									 $pulsante=new myPulsante('',($this->pulsante?$this->pulsante:'Aggiorna pagina'));
									 $pulsante->set_Tooltip("Premere qui per ".($this->pulsante?$this->pulsante:'aggiornare la pagina'));
									 $this->myFields['NSelettori']++;
									 $pulsante->set_attributo('id',"puls_".$this->myFields['NSelettori']);
									 if (stripos($evento,'submit')===0) $out.="<span style='position: absolute;	top: -10000em;	left:-10000em;width: 0em;height:0em;clear:none;'><br /></span>".$pulsante->get_Html();
									 $out=($out?"<noscript>$out</noscript>":'');
								  }
					 return $out;
		  }



	

	 function &get_value(){
	 	$val=parent::get_value();
	 	if(is_string($val) && trim((string) $val)!=='') $val=explode(',',$val);
	 	if(is_array($val)) $val=array_unique(array_values($val));
	 	              else $val=array();
	 	return $val;
	 }


	 /**
	* elimina dal/i valore/i impostati quelli non presenti nelle opzioni
		* attenziene a non usarla prima di aver settato tutte le opzioni valide
		* altrimenti si annulla il valore
		*/
	  public function clean_value() {
				$opzioni=@array_flip($this->get_opzioni());
				if (!$opzioni) $opzioni=array();
			 	$valori=$this->get_value();
				if (is_array($valori)) $valori=@array_flip($valori);
								  else $valori=array($valori=>1);
				$new_val=null;
				
				foreach (array_keys($valori) as $v) if (key_exists($v,$opzioni)) $new_val[]=$v;
		 		parent::set_value($new_val);
	  }




	   /**
	* setta in automatico il primo valore valido se non ce ne sono settati
		 */
	   public function autovalue() {
			  $this->clean_value();
			  if (strlen($this->get_value())==0)
									 {$opz=$this->get_Opzioni();
									  $opz=each($opz);
									   if (count($opz)) $this->set_value($opz[1]);
									 }
			  return $this;
	  }



	 /**
	* Setta ogni quante righe/colonne deve  andare accapo la visualizzazione,
	  * se inseriti entrambi comanda le $colonne
	  * @param   int $colonne
	  * @param	 int $righe
	  */
	  public function set_accapo($colonne=0,$righe=0) {
	 		if ($colonne>0) $this->accapo=array($colonne,null);
	 			 elseif ($righe>0) $this->accapo=array(null,$righe);
	 		return $this;
	 }


	 /**
	  * Restituisce ogni quante opzioni andare accapo
	  * @return int
	  */
	  public function get_accapo(){
	 	if($this->accapo[0]!==null) return $this->accapo[0];
	    if($this->accapo[1]!==null) return count($this->get_Opzioni())/$this->accapo[1];
	    return 1;
	 }




	/**
	* Restituisce l'array associativo con le opzioni
	  * @return array
	 */
	 public function get_Opzioni() {
		$array=array();
		if($this->opzioni) foreach ($this->opzioni as $i=>&$v) $array[$this->trasl($i)]=$v;
		return $array;
	}




   /**
	  * Imposta una proprietà css e relativo valore es:
	  * <code>
	  *   $f->set_style('color','red');   //imposta colore rosso
	  *   $f->set_style('font-weight','bold'); //imposta grassetto
	  *
	  *   //si puo' sostituire con
	  *   $f->set_attributo('style','color:red;font-weight:bold'); //ma occhio alla sintassi
	  * </code>
	  *
	  * @param	 string $proprieta
	  * @param	 string $valore
	  */
  public function set_style($proprieta,$valore){
 	if ($this->campi) foreach (array_keys($this->campi) as $name) $this->campi[$name]->set_style($proprieta,$valore);
 	return $this;
 }



	 public function set_name($nome, $cambia_id = true) {
		if (strpos($this->get_name(),'[]')===false) $nome.="[]";
		parent::set_name($nome,$cambia_id);
		if ($this->campi) foreach (array_keys($this->campi) as $name)
			 if ($this->campi[$name]->get_name())
			 	  $this->campi[$name]->set_name($nome);
		return $this;
		//echo "<pre>";	print_r($this->campi);
	}


	 public function get_name() {
		$nome=parent::get_name();
		$nome=explode('[]',(string) $nome,2);
		return $nome=$nome[0];
	}

/**
 * Utile per overriding viene invocata poco prima della costruzione del singolo campo
 * 
 * @param string $nome_campo
 * @param myField $campo
 */
	 public function alter_campo_singolo(&$nome_campo,myField &$campo){}

	  /** @ignore*/
	  public function get_html_singolo($nome_campo,$noLabel=false,$attrLabel=array()) {
	    $out=$nascosto='';
	    if(!isset($this->myFields['static']['ids_'.get_class($this)])) $this->myFields['static']['ids_'.get_class($this)]=array($this->get_id()=>0);
	    if(!isset($this->myFields['static']['ids_'.get_class($this)][$this->get_id()])) $this->myFields['static']['ids_'.get_class($this)][$this->get_id()]=0;
	    $id=++$this->myFields['static']['ids_'.get_class($this)][$this->get_id()];
		$attr=$this->get_attributi();
	
		$campo=$this->campi[$nome_campo]->clonami();
		$campo->set_using_hidden(false);
	//	if($this->get_id().'_'.$id=='EventualiComunicazioni_TipoEventualiComunicazioni_1') {print_r($campo);exit;}
		
		$attributi_specifici_dele_singolo=array('name'=>1,'value'=>1,'class'=>1,'title'=>1);
		$jsLabel=array();
		if ($attr)
			foreach ($attr as $n=>$v)
			    if (!isset($attributi_specifici_dele_singolo[$n]) && strpos(";{$campo->get_attributo($n)};",";$v;")===false)
				            {   
				                $campo->set_attributo($n,trim((string) $campo->get_attributo($n))!==''?$campo->get_attributo($n).';'.$v:$v);
			                }

							
		$campo->set_attributo('id',$this->get_id().'_'.$id);
		$valori=$this->get_value();
		if (is_array($valori)) $valori=@array_flip($valori);
						  else $valori=array($valori=>1);
		if (isset($valori[$campo->get_value()])) $campo->set_checked();
											else $campo->set_unchecked();
		
    	if(!$this->isMultiple() || count($this->get_Opzioni())<=1) $campo->set_notnull($this->get_notnull());
    	
      	if ($campo->get_attributo('readonly'))
							{//$campo->unset_attributo('name');
							 $funzioni=null;
							 $campo->unset_attributo('onclick');
 						 	 $campo->unset_attributo('onkeypress');
							 $campo->unset_attributo('onchange');
							 
							 if ($campo->get_checked()) $nascosto="<input type='hidden' {$campo->stringa_attributi(array('name','id','value'),false)} />";
							 					  else  $nascosto='';
							 $campo->unset_attributo('name');
							 $campo->unset_attributo('readonly');
							 $campo->set_attributo('id',$campo->get_id().'_disabled');
							 $campo->set_disabled();
							}
						else {$funzioni=array();
							  foreach ($campo->get_attributi() as $attr=>$valore)
												if (stripos($attr,'on')===0)
																		{
																		// $chiamate=explode(';',$this->attributi[$attr]);
																		    if(isset($this->attributi[$attr]) && strpos(";{$this->attributi[$attr]};",";$valore;")===false) $valore="{$this->attributi[$attr]};$valore";
																	/*	 In caso di eval da problemi
																	     $valore=str_replace('this.',"('".addslashes($campo->get_attributo('id'))."').",$valore);
																		 $valore=str_replace('this,',"('".addslashes($campo->get_attributo('id'))."'),",$valore);
																		 $valore=str_replace('this)',"('".addslashes($campo->get_attributo('id'))."'))",$valore);
																	*/
																   	     $funzioni[$attr]="$valore;";
																		}
							// $jsLabel['onclick']="('".addslashes($campo->get_attributo('id'))."').checked=('".addslashes($campo->get_attributo('id'))."').checked";
							//$jsLabel['onkeypress']=$jsLabel['onclick'];

							}
          $this->alter_campo_singolo($nome_campo, $campo);
	     if ($noLabel)  $out=$campo->get_html();
	              else {$t=new myTag();
		                if(!isset($this->attributi['class'])) $this->attributi['class']='';
		                if(trim((string) $campo->get_attributo('title'))==='') $campo->set_attributo('title',$this->trasl($this->get_attributo('title')));
		                if(trim((string) $campo->get_attributo('title'))==='') $campo->set_attributo('title',$this->trasl(strip_tags($nome_campo)));
		                $t->attributi=array_merge($jsLabel,$attrLabel,array('title'=>$campo->get_attributo('title'),
		                                                                    'style'=>$campo->get_attributo('style'),
		  																	'class'=>"myLabel_{$this->attributi['class']} {$campo->get_attributo('class')}"));
		  				if($funzioni!==null && is_array($funzioni)) 
		  				        foreach (array_keys($funzioni) as  $attr) 
		  				                    $t->set_attributo($attr,"myEventManager.fire(".preg_replace('@^on@','',$attr).", '{$campo->get_attributo('id')}')");
						if($this->posizionelabel=='d') $out=$campo->get_html()."&nbsp;<label for='{$campo->get_attributo('id')}' id='lbl_{$campo->get_attributo('id')}' ".$t->stringa_attributi('',true,true).">{$this->trasl($nome_campo)}</label>";
												else   $out="<label for='{$campo->get_attributo('id')}'  ".$t->stringa_attributi('',true,true).">{$this->trasl($nome_campo)}</label>".$campo->get_html();
						}
		$out.=$nascosto;

		if (!isset($this->showlinks) &&
		    (isset($this->attributi['readonly']) || 
		       !isset($this->reloadJSGET[$campo->get_value()]) ||
		       !$this->reloadJSGET[$campo->get_value()] ||
		        (isset($this->attributi['onclick']) && $this->attributi['onclick']=='submit()')))
											  return $out;
										else {
											  $outScript="\n<script type=\"text/javascript\">\n
											                         //<!--
											                         ".$this->my_js_documentwrite($out,true)."
											                             //-->
											                      </script>\n";
											  if ($this->reloadJSGET) {if($campo->get_name() && $this->reloadJSGET[$campo->get_value()])
											  										{$urls=$this->build_reload_urls($this->reloadJSGET[$campo->get_value()][0],
											  																		str_replace('[]','',$campo->get_name()),
											  																		$this->reloadJSGET[$campo->get_value()][1],
											  																		'','',
											  																		$this->reloadJSGET[$campo->get_value()][2]
											  																		);
											  						   				 $url=$urls[$campo->get_value()];
											  										}
											  						   }
											 elseif($this->showlinks) {if($campo->get_name())
															 			 {$fragment=explode('#',$this->showlinks[0],2);
															 			  if(count($fragment)==1)
															 			  			 $url=explode('?',$this->showlinks[0],2);
															 			  		else $url=explode('?',$fragment[0],2);
															 			  $fragment=$fragment[1];
															 			  $urls=$this->build_reload_urls(
															 			  		'parametro',
															 	 				 str_replace('[]','',$campo->get_name()),
															 	 				 $this->showlinks[1],
															 	 				 $url[0],
															 			  		 $url[1],
															 	 				 $fragment
															 	 				);
															 			  $url=$urls[$campo->get_value()];
															 	 	    }
															 	 }
											  $link = new myLink((!$url?'#':myTag::htmlentities($url)),$this->trasl('Click qui per scegliere l\'opzione'));
											  if ($attrLabel) $link->set_attributi($attrLabel);
											  $link->unset_attributo('name');
											  $link->unset_attributo('onmouseup');
 						 					  $link->unset_attributo('onkeyup');
							 				  $link->unset_attributo('onchange');
											  if ($campo->get_checked())
											  				{
															$link->set_attributo('style','font-weight:bold');
															$outLink="<span style='position: absolute;	top: -10000em;	left:-10000em;width: 0em;height:0em;clear:none;'>Scelto</span>".($url?$link->get_html($this->trasl($nome_campo)):$this->trasl($nome_campo));
															}
													  else $outLink=($url?$link->get_html($this->trasl($nome_campo)):$this->trasl($nome_campo));

											if (isset($this->showlinks)) return $outLink;
										          	else {   $url=strstr($url,'&'.str_replace('[]','',$campo->get_name()),true);
											                 return ($outLink?"<noscript>$outLink</noscript>
                                                                    <script type='text/javascript'>
                                                                        var myLinks_{$campo->get_id_istanza()}=function(val)
                                                                            { 
                                                                            var componenti_{$this->get_id_istanza()}=document.getElementsByName('{$campo->get_name()}');
                                                                            var jlink=\"{$url}\";
                                                                            for(var i=0; i<componenti_{$this->get_id_istanza()}.length;i++) {
                                                                                                    var comp=componenti_{$this->get_id_istanza()}[i];
                                                                                                    if(comp.checked)  jlink+='&{$campo->get_name()}='+comp.value;
                                                                                                    }
                                                                            
                                                                            return jlink;
                                                                            }
                                                                    </script>":'').$outScript;
											               }
											}
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
			unset($parametri['ttt']);
			unset($parametri['tt']);
			static $build;
			if(isset($build[serialize(func_get_args())])) return $build[serialize(func_get_args())];
			
			if($parametri)
				foreach ($parametri as $par=>$val)
				  {
				  	 if ($Tipo=='parametro' && $par==$nome_campo) break;
					  if ($par!=$nome_campo && (is_array($val) || trim((string) $val)!=='')) $nuova[$par]=$val;

				  }
			if ($Tipo=='azzera')  $nuova=array();
			if($usa_random) $nuova['tt']=self::unid();

			if($fragment) $fragment="#$fragment";
			foreach ($this->get_Opzioni() as $val)
							{$nuova[$nome_campo]=$val;
							 $valori[$val]=$url.'?'.http_build_query($nuova).$fragment;
							}
			return $build[serialize(func_get_args())]=$valori;
		  }



   /** @ignore*/
    public function _get_html_show($pars, $tag = 'span', $content = NULL){
            $this->clean_value();
            $opz=array();
            $out='';
            if (count($this->opzioni)==0 || ($vals=$this->get_value())===null ) return;
           	if (!is_array($vals)) $vals=array($vals);
           	$vals=array_flip($vals);
           	foreach ($this->campi as $nome=>$val)
        			 if($nome && isset($vals[$val->get_value()]))
        			  { $opz[]=$nome;
        			  if(!isset($this->myFields['static']['ids_'.get_class($this)][$this->get_id()])) $this->myFields['static']['ids_'.get_class($this)][$this->get_id()]=0;
        			    $id=++$this->myFields['static']['ids_'.get_class($this)][$this->get_id()];
        			    if ($pars['campo'] &&
        			        (!isset($val->get_attributo['disabled']) || !$val->get_attributo['disabled']))
        			    		{$x=new myHidden($val->get_name(),$val->get_value());
        			    		 $x->set_attributo('id',$this->get_id().'_'.$id);
        			    		 if($this->secure) $x->set_security($this->secure);
        						 $out.=$x->get_Html();
        			    		}
        			  }
        
        	if (count($opz)==1) return $opz[0].$out;
        	if($opz) $ul=$this->_get_html_show_obj($pars,'ul',"<li>".implode('</li><li>',(array)$opz)."</li>");
        	return $ul.$out;
   	}




  	 /** @ignore */
   	protected function _get_html_($pars){
   		$this->clean_value();
   		$v=(array)$this->get_value();
   		foreach ($v as $val) {$h=new myHidden($this->get_name(),$val);
   		                      if($this->secure) $h->set_security($this->secure);
   		                      $out.=$h;
   		                       }
   		return $out;
    }



	function &get_xml_value(){
		$xmlTagCampo=$this->xmlTagCampo;
		if (!$xmlTagCampo) {
							 $xmlTagCampo=explode('[',($this->xml['']?$this->xml['']:$this->get_name()));
							 $xmlTagCampo=$xmlTagCampo[0].'_valore';
							}




		$attr=$this->get_attributi();
		$attributi_specifici_dele_singolo=array('name'=>1,'id'=>1,'value'=>1);

		$valori=$this->get_value();
		if (is_array($valori)) $valori=@array_flip($valori);
					      else $valori=array($valori=>1);

		foreach ($this->campi as $v)
					{
					  $campo=$v->clonami();
					  if ($attr) foreach ($attr as $n=>$v) if (!$campo->get_attributo($n) && !$attributi_specifici_dele_singolo[$n]) $campo->set_attributo($n,$v);

					  if (isset($valori[$campo->get_value()])) $campo->set_checked();
														  else $campo->set_unchecked();
					  $campo->set_parametri_xml($xmlTagCampo);
					  $out.=$campo->get_xml();
					}
		return $out;
	}

	 /**
	* Restituisce il campo in XML iin UTF-8 pronto per la visualizzazione, di default gli attributi non vengono presi in considerazione ed il nome del campo diventa il tag che lo racchiude mentre il contenuto e' il valore restituito da @see get_xml per personalizzare gli attributi usare @see set_attributi_xml
   *  @return string
   */
   function &get_xml() {
   		if(!$this->abilitaXML) return;
   	    if (is_array($this->xml))
   	        foreach ($this->xml as $nome=>$usare)
   	    		if ($nome && trim((string) $this->parametri[$nome])) $parametri.=' '.strtolower($usare)."=\"".(\Gimi\myFormsTools\PckmyUtils\PHP8::htmlspecialchars($this->parametri[$nome],null,'UTF-8')).'"';

   		$tag=($this->xml['']?$this->xml['']:$this->get_name());
   		$tag=explode('[',$tag,2);
        if(strlen($v=$this->get_xml_value()))
        								 return "<$tag[0]$parametri>$v</$tag[0]>";
        						 	elseif(!$this->facoltativoXML) return "<$tag[0]$parametri />";
  }


  /**
	 * Imposta eventuali variazioni sull'XML prodotto da @see get_xml
	 *
	 * @param string $tagName e' il tag name che verrà usato nell'xml al posto del nome del campo (se omesso si usa il nome del campo
	 * @param array $attributi è l'array associativo che indica la trascodifica degli attributi da visualizzare, la chiave è il nome dell'attributo HTNL il valore e' l'attributo XML da usare
	 * 							puo' anche non essere associativo, in quel caso indica gli attributi da visualizzare
	 * @param string $tagContenuto e' il nome del tag da usare nei tag che rappresentano i valori
	 */
	public function set_parametri_xml($tagName='',$attributi=array(),$tagContenuto=''){
		parent::set_parametri_xml($tagName,$attributi);
		$this->xmlTagCampo=$tagContenuto;
		return $this;
	}


    public function get_campi_interni(){
   	 return $this->campi;
   }

   /**
    * Se gli si passa true l'html sarà  costruito con div anziche' table
    * @param boolean $stato
    */
    public function set_usaDIV($stato=true){
   	$this->usaDIV=$stato;return $this;
   }

   
   
 /*  protected  function html5Settings($attrs=array()) {
       $j=new myjquery();
       $j->add_code(  "    var requiredCheckboxes = $(\"input[name^='{$this->get_name()}[]']:checkbox[required]\");
                           requiredCheckboxes.change(function(){
                               if(requiredCheckboxes.is(':checked')) {
                                   requiredCheckboxes.removeAttr('required');
                               } else {
                                   requiredCheckboxes.attr('required', 'required');
                               }
                     });");
       $this->add_myJQuery($j);
   }*/ 

   function &get_Html($noLabel=false,$attributi_td="") {
        $out='';
        $this->html5Settings();
  	 	if($this->campoAutocomplete || $this->usaDIV) return $this->get_Html_div($noLabel,$attributi_td);
   	    $jsCommon=$this->get_js_common();//if(!$this->myFields['static']['common']) {$jsCommon=$this->get_js_common();$this->myFields['static']['common']=true;}
		if(!$attributi_td)
   	    		{
   	    		  if($this->posizionelabel=='d') $attributi_td=" style='text-align:left' ";
   	    		  							else $attributi_td=" style='text-align:right' ";
   	    		}
   	    $get_html=isset($this->Metodo_ridefinito['get_Html']['metodo'])?$this->Metodo_ridefinito['get_Html']['metodo']:null; if ($get_html) return $jsCommon.$this->$get_html($this->Metodo_ridefinito['get_Html']['parametri']);
   	 	if (count($this->opzioni)==0) return $out;
		$riga=0;
		$i=0;
		$x=array();
		foreach (array_keys($this->campi) as $nome)
			  {if($nome==='') continue;
			   if ($this->autotab) $this->campi[$nome]->set_autotab();
			   $x[$riga][]=$this->get_html_singolo($nome,$noLabel);
			   if (++$i % $this->get_accapo()==0) $riga++;
			  }
		
		 $out=$this->get_hidden_aggiuntivo()
		 	 ."<table class='none' style='border:0px;margin:0px;padding:0px' id=\"tbl_{$this->get_id()}\" >";
		 if (is_array($x)) foreach ($x as &$riga)  $out.="<tr><td $attributi_td class='myMultiElement'>".implode("</td><td $attributi_td class='myMultiElement'>",$riga)."</td></tr>";
		 $out.="</table>";
		 if(isset($this->attributi['onclick']) && $this->attributi['onclick']) $out.=$this->get_html_Accessibilita($this->attributi['onclick']);
		 $out.=$jsCommon;
		 return $this->send_html($out);
	 }

	 /**
	  * @ignore
	  */
	 protected function get_hidden_aggiuntivo(){
	 	if(!isset($this->myFields['static']['get_hidden_aggiuntivo'][$this->get_name()])) 
	 				return $this->myFields['static']['get_hidden_aggiuntivo'][$this->get_name()]="<input type='hidden' name='{$this->get_name()}' />";
	 }


	 function &get_Html_div($noLabel=false,$attributi_td="") {
	     $out='';
	    if($this->campoAutocomplete) $this->set_accapo(1);
	 	$jsCommon=$this->get_js_common();//if(!$this->myFields['static']['common']) {$jsCommon=$this->get_js_common();$this->myFields['static']['common']=true;}
	 	if(!$attributi_td)
		 	{
		 		if($this->posizionelabel=='d') $attributi_td=" style='text-align:left' ";
		 		                          else $attributi_td=" style='text-align:right' ";
		 	}
		
		if(isset($this->Metodo_ridefinito['get_Html']['metodo'])) {
		      $get_html=$this->Metodo_ridefinito['get_Html']['metodo']; 
		      if ($get_html) return $jsCommon.$this->$get_html(isset($this->Metodo_ridefinito['get_Html']['parametri'])?$this->Metodo_ridefinito['get_Html']['parametri']:null);
		      }
	 	if (count($this->opzioni)==0) return $out;
	 	$riga=0;
	 	$i=0;

	 	$div=new myTag('div');
	 	$v=$div->parse_attributi_tag($attributi_td)	;
	 	$v['style'].=";float:left;margin-right:1em";
	 	$v['class']='myMultiElement';
	 	$x=array();
	 	foreach (array_keys($this->campi) as $nome)
				 	{if(trim((string) $nome)==='') continue;
				 	 if ($this->autotab) $this->campi[$nome]->set_autotab();
				 	 $x[$riga][]=new myTag('div',$v,$this->get_html_singolo($nome,$noLabel));
				 	 if (++$i % $this->get_accapo()==0) $riga++;
				 	}


	 	$out="<div id=\"tbl_{$this->get_id()}\" >";
	 	if (is_array($x)) foreach ($x as &$riga)  $out.=implode("",$riga);
	 	$out.="<br style='clear:both' /></div>";
	 	 	/* 	Non più necessario ($this->attributi && isset($this->attributi['onclick']))?
	 	                  $this->get_html_Accessibilita($this->attributi['onclick']):''; 
	 	                  */
	 	$out= $jsCommon."<input type='hidden' name='{$this->get_name()}' />".$this->get_html_autocomplete().$out;
	 	return $this->send_html($out);
	 }





	/**
	  * Passa in minuscolo (ma con la prima lettera di ogni parola maiuscola)
	  * facendo ovewriting di questo metodo si possono personalizzare le regole di "aggiustamento" delle opzioni
	  * @param	 string $cosa
	  */
	 public function Minuscolo ($cosa) {
	  $v=explode("_",trim((string) $cosa));
	  $x='';
		for ($i=0; $i<count($v);$i++)
				{$v[$i]=ucfirst(strtolower(trim((string) $v[$i])));
				 if (strlen($v[$i])<=3 && $x!='') $v[$i]=strtolower($v[$i]);
				 if ($x!='') $x.=' ';
				 $x.=$v[$i];
	            }
	  $cosa=trim((string) $x);
	  return $cosa;
	 }



	 /**
	* Passa in minuscolo (ma con la prima lettera di ogni parola maiuscola) tutte le opzioni usando il metodo "Minuscolo($cosa)"
		  */
		   public function UcWordsOpzioni() {
			if (is_array($this->opzioni))
					 {$v=array();
					  foreach ($this->opzioni as $nome=>$val) $v[$this->Minuscolo($nome)]=$val;
					  $this->opzioni=$v;
					 }
			return $this;
		  }



		  /**
	* se settato aggiunge il reload della pagina sull'evento passato, di default e' onClick
			* DA USARE SOLO DOPO AVER IMPOSTATO LE OPZIONI
			* @param	 'parametro'|'completo'|'azzera'|'submit' $Tipo  Il default e' 'parametro'
			* @param	 string $evento  Il nome dell'evento su cui attivare il reload
			*			  Supponiamo che questo selettore si chiami 'Tipo' e la url della pagina sia
			*					  http://www.fdsf.it?abc=32432&Tipo=424&altro=12345<br />
			*							con l'opzione 'parametro' si ricaricherà http://www.fdsf.it?abc=32432&Tipo=nuovo_valore<br />
			*							con l'opzione 'completo' si ricaricherà http://www.fdsf.it?abc=32432&Tipo=nuovo_valore&altro=12345<br />
			*							con l'opzione 'azzera' si ricaricherà http://www.fdsf.it?Tipo=nuovo_valore<br />
			*							con l'opzione 'submit' effettua l'invio in POST del form cosi' com'e'<br />
			*
			**/
		   public function SetReloadJS($Tipo='parametro',$evento='onclick',$opzioni=array(),$fragment='',$usa_random=true,$sostituisci=false) {
			 if (!$opzioni || count($opzioni)==0) {
			 									   $opzioni=array_values($this->get_opzioni());
			 									   //print_r($this->get_opzioni());
			 									  }
		  // echo "<pre>";print_r($this->nuovaUrl);

            if($this->campi)
			  foreach ($this->campi as $opz=>&$campo)
		       if (in_array($campo->get_value(),$opzioni)!==false)
		        {
				if ($Tipo=='submit')  $this->campi[$opz]->set_attributo($evento,"submit()");
					  		  else	{ $this->reloadJSGET[$campo->get_value()]=array($Tipo,$usa_random,$fragment);
					  		   		  $this->campi[$opz]->set_js("location.href=myLinks_{$campo->get_id_istanza()}(this.value);",$evento,$sostituisci);
					  		        }
		    	}
		  // echo "<pre>";print_r($this->campi);
			return $this;
		  }

		/**
		 * alias di @see setreloadjs()
		 */
		   public function set_reloadjs($Tipo='parametro',$evento='onclick',$opzioni=array(),$fragment='',$usa_random=true,$sostituisci=false) {
		     return $this->setreloadjs($Tipo,$evento,$opzioni,$fragment,$usa_random,$sostituisci);
		  }


		/**
	* se assegna un JS ad un evento delle $opzioni di default e' onClick
		* DA USARE SOLO DOPO AVER IMPOSTATO LE OPZIONI
	  	* @param	 array $opzioni  E' un array con l'elenco delle CHIAVI su cui attivare l'evento, se vuoto si applica a tutte
	  	* @param	 string $JS  E' lo JavaScript da lanciare
		* @param	 string $evento  Il nome dell'evento su cui attivare lo JS
		* @param    boolean $sostituisci sostituisce eventuali eventi precedenti
 	  	*/
		   public function SetJS($opzioni='',$JS='',$evento='onclick',$sostituisci=true) {
			if (!$opzioni || count($opzioni)==0) {parent::SetJS($JS,$evento);
												  return $this;
												 }
	 	  	// foreach ($opzioni as $opz) if (isset($this->campi[$opz])) $this->campi[$opz]->SetJS($JS,$evento);
	 	  	foreach ($this->campi as $opz=>&$v)
		       if (in_array($v->get_value(),$opzioni)!==false)
		        {   
		        	$this->campi[$opz]->SetJS($JS,$evento,$sostituisci);
		        }
		    return $this;
		 }

		  
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
		     foreach ($this->campi as $nome=>$campo) {
		         $out=array();
		         $out['titolo']=$nome;
		         $out['valore']=$campo->get_value();
		         $out['id']=$campo->get_id();
		         foreach ($campo->get_attributi() as $attr=>$valore)
		                 if($fun_nome_attr($attr) && $fun_val_attr($nome)) 
    		                      {$valore="{$this->attributi[$attr]};$valore";
    		                       $out['attributi'][$attr]="$valore;";
    		                      }
		         if(isset($out['attributi'])) $funzioni[]=$out;             
		         }
		     return $funzioni;    
		 }
		 

        /**
		 * alias di @see setjs()
		 */
		   public function set_js($opzioni,$JS='',$evento='onclick',$sostituisci=true) {
		     return $this->setjs($opzioni,$JS,$evento,$sostituisci);
		  }


	  /**
	    * Setta le opzioni
	  	* @param	 array $opzioni E' un array associativo con le opzioni Titolo=>valore
	 	*/
		   public function set_Opzioni($opzioni=array()) {
		  			 $this->opzioni=$opzioni;
		  			 if(is_array($this->opzioni)) foreach ($this->opzioni as &$w) $v=trim((string) $w);
					 $valori=$this->get_value();
					 if (is_array($valori)) $valori=@array_flip($valori);
									   else $valori=array($valori=>1);

					 unset($this->myFields['campo__istanze'][$this->get_name()]);
					 $attr=array();
					 if (is_array($this->campi)) foreach ($this->campi as $i=>$v)  $attr[$i]=$v->get_attributi();
					 $this->campi=array();

					 if ($opzioni) 
					     foreach ($opzioni as $val=>&$key)
							  {          if(!isset($this->attributi['class'])) $this->attributi['class']='';
										 $this->campi[$val]=new myCheck($this->attributi['name'],$key,$this->attributi['class']);

										 if (isset($attr[$val])) {unset($attr[$val]['id']);
																 $this->campi[$val]->set_attributo($attr[$val]);
																}

										 if (isset($valori[$this->campi[$val]->get_value()]))
																							$this->campi[$val]->set_checked();
																					 else $this->campi[$val]->set_unchecked();
                                         if(!isset($this->myFields['campo__istanze'][$this->get_name()])) $this->myFields['campo__istanze'][$this->get_name()]=0;
										 $this->myFields['campo__istanze'][$this->get_name()]--; //altrimenti l'id cresce 2 volte
							  			 $this->campi[$val]->set_name($this->get_name().'[]');
							  }
		  return $this;
		  }





    /**
	* Aggiunge una singola opzione
	  * @param	 string $titolo e' il titolo da visualizzare
	  * @param	 string $valore e' il valore da associare al titolo
	  * @param	 boolean $fine se falso o omesso l'opzione si aggiunge in coda se true si aggiunge all'inizio
	 */
	  public function add_Opzione($titolo,$valore,$fine=false) {
	 	$this->notrasl=true;
		$opzioni=$this->get_Opzioni();
		$this->notrasl=false;
		if (!$fine) {$n_opzioni=$opzioni;
					$n_opzioni[$titolo]=$valore;
					}
				else {$n_opzioni[$titolo]=$valore;
					if (is_array($opzioni)) foreach ($opzioni as $id=>$val) $n_opzioni[$id]=$val;
					}
		return $this->set_Opzioni($n_opzioni);
	}




	/**
	  * Setta le opzioni del selettore
	  *
	 * @param	 mixed $db e' la connessione a DB da utilizzare
	  * @param	 string $qry la query da usare per reperire i dati in cui il primo campo è Titolo ed il secondo e' il valore del selettore
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
										for ($i=0;$i<count($v);$i++)
										if (is_array($v[$i]))
													 {$w=array_values($v[$i]);
										  			  $opzioni[$w[0]]=$w[1];
										 			 }
								}
			$this->set_Opzioni($opzioni);
			if ($registra) $this->registra_opzioni();
			return $this;
		  }




	/**
	  * Registra le opzioni in sessione
	 */
	  public function registra_opzioni() {
			 $_SESSION['myFields']['MyOpzioniRegistrate'][sha1(serialize($this->qry))]=$this->opzioni;
		  }


	  /**
	  * Elimina le opzioni dalla sessione
	 */
		   public function deregistra_opzioni() {
			unset($_SESSION['myFields']['MyOpzioniRegistrate'][sha1(serialize($this->qry))]);
		  }


	 protected function get_errore_diviso_singolo() {
					 	$valore=$this->get_value();
					 	if ($this->notnull && !$valore) return 'non può essere nullo';
					 	if(!$valore) $valore=array();
					 	if($this->restrizioni_check && $valore)
					 		{$opzioni=$this->get_Opzioni();
					 		if(!$opzioni) $opzioni=array();
					 			 	else $opzioni=array_values($opzioni);
							
					 		if(array_diff($valore,$opzioni))
					 					{//print_r($valore);print_r($opzioni);exit;
					 						return 'non è accettabile';
					 					}
					 		}
		 }


		  public function get_js_chk($js = ''){
  		  if ($this->notnull) return "if(strlen(myGetValueCampo('{$this->get_id()}','radio',null))==0) return \"{$this->trasl('non può essere nullo')}\";";
  		}


}