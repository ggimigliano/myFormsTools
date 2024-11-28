<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\mySelect.
 */

namespace Gimi\myFormsTools\PckmyFields;



use Gimi\myFormsTools\PckmyDBAdapters\myAdoDBAdapter;
use Gimi\myFormsTools\PckmyForms\myForm;
use Gimi\myFormsTools\PckmyJQuery\myJQuery;



/**
	  * Imposta attributi delle singole opzioni es style, class
	  *
	  * @param array $opzioni Titoli (non i valori) delle opzioni da personalizzare
	  * @param string $attributo  nome dell'attributo da settare
	  * @param string $valore	  valore dell'attributo
	  */
	
class mySelect extends myField {
/**
 @ignore */
protected $qry,$accessibile=false,$optgroups=array(),$opzioni=array(),$default='',$reloaJSGET='',$pulsante,$attributi_opzioni,$ajaxloader=array(),$restrizioni_check=true;
/**
 @ignore */
protected static $all_ajaxloader=array();



	 /**
      * 
	  *
			 * @param	 string $nome E' il nome del campo
	  * @param	 int/string $valore da assegnare come default
	  * @param	 array $opzioni E' un array associativo con le opzioni Titolo=>valore
	  * @param	 string $classe e' la classe css da utilizzare
	  */
	   public function __construct($nome,$valori=array(),$opzioni=array(),$classe=''){

				myField::__construct($nome,$valori);
		  /*

	 function __construct($nome,$valore='',$opzioni=array(),$classe=''){
				if ($valore && !is_array($valore)) $valore=array($valore);
					 myField::__construct($nome,$valore);
			 */
				if ($classe) $this->set_attributo('class',$classe);
				if (is_array($nome) && $nome['opzioni']) $opzioni=$nome['opzioni'];
				if (is_array($opzioni) && count($opzioni)) $this->set_Opzioni($opzioni);
				//if (!$valori) $this->set_Domanda('&nbsp;');
				$this->set_MyType('MySelect');
		  }


		  /**
		   * Imposta l'attributo 'value'
		   *
		   * @param	 array|string $valore Valore da assegnare come defaulto array (non assoc) di valori nel caso di campo multivalori
		   */
		   public function set_value($valore) {
		    $val=null;
		
		  	if (is_array($valore)) foreach ($valore as $v) $val[]=stripslashes($v);
		  					else{   $valore=(string) $valore;
		  							if(trim((string) $valore)==='') $val='';
		  										else $val=stripslashes($valore);
		  							}
		  	$this->attributi['value']=$val;
		  	return $this;
		  }

		 
		  /**
		   * assegna un JS ad un evento, di default e' onclick
		   * @param	 string $JS  E' lo JavaScript da lanciare
		   * @param	 string $evento  Il nome dell'evento su cui attivare lo JS
		   * @param    boolean $sostituisci sostituisce eventuali eventi precedenti
		   */
		   public function SetJS($JS,$evento='onchange',$sostituisci=true,$null=null) {
		      return parent::SetJS($JS,$evento,$sostituisci,$null);
		  }
		  
		  /**
		   * alias di @see setjs
		   */
		   public function set_js($JS,$evento='onchange',$sostituisci=true,$null=null) {
		      return $this->setjs($JS,$evento,$sostituisci);
		  }
		  


		  /**
		   * Permette di rendere il check_errore piu' o meno restrittivo
		   */
		   public function set_restrizioni_check($stato){
		  	$this->restrizioni_check=$stato;
		  	return $this;
		  }


		   public function get_value_from_titolo($titolo){
		      $opt=$this->get_Opzioni();
		      return $opt[$titolo];
		  }
		  
		  /**
		   * Se true aggiunge alternativa testale in caso di reload 
		   * @param boolean $stato
		   * @return mySelect
		   */
		   public function set_accessibile($stato){
		      $this->accessibile=$stato;
		      return $this;
		  }
		  
		  
		  /**
		   * 
		   * @param string $titolo
		   * @param boolean $anche_formula
		   */
		   public function set_value_from_titolo($titolo,$anche_formula=false){
		      $v=$this->get_value_from_titolo($titolo);
		      if($v!==null) {
		                      $this->set_value($v);
		                      if($anche_formula) $this->set_formula("'".addslashes($v)."'");
		                    }
               return $this;		                    
		  }

		 /**
		  * Imposta script di precaricamento, viene usata solo in abbinamento con set_ajaxloader_input()
		  *
		  * @param string $script
		  * @param string $forza_reload
		  * @param 'change'|'blur' $evento su cui scatenare il reloading
		  * @param string $onloading codice js da lanciare prima del caricamento
		  * @param string $onloaded  codice js da lanciare dopo il caricamento
		  * @param string $icona_caricamento
		  */
		  public function set_ajaxloader($script,$forza_reload=false,$evento='change',$onloading='',$onloaded='',$icona_caricamento=''){
			$this->ajaxloader=array('src'=>$script.(strpos($script,'?')===false?'?':'&'),
									'campi'=>array(),
									'forza'=>$forza_reload,
									'evento'=>$evento,
									'onbeforeload'=>$onloading,
									'onafterload'=>$onloaded,
									'icona'=>$icona_caricamento);
			
			if(!class_exists('myJQFake',false)) {eval ('c'.'lass myJQFake extends Gimi\\myFormsTools\\PckmyJQuery\\myJQuery {}');
			                                     eval ('$this->add_myJQuery(new myJQFake());');
			                                     }
			return $this;
		}

		/**
		 * Imposta i campi da passare a allo script ajax impostato con set_ajaxloader()
		 * @param string $nome
		 * @param myField $campo
		 * @return mySelect
		 */
	     public function set_ajaxloader_input($nome,&$campo){
	    	if(!isset($this->ajaxloader['src'])) die ("Usare set_ajaxloader() prima di set_campo_input()");
		  	$this->ajaxloader['campi'][$nome]=$campo;
		  	self::$all_ajaxloader[$campo->get_id_istanza()][]=$this;
  			return $this;
  		}



		  /**
	* Setta le opzioni
	  	 * @param	 array $opzioni E' un array associativo con le opzioni Titolo=>valore
	      */
	      public function set_Opzioni($opzioni=array()) {
	     		$this->opzioni=$opzioni;
	     		return $this;
		  }


		  /**
	* elimina dal/i valore/i impostati quelli non presenti nelle opzioni
			 * attenziene a non usarla prima di aver settato tutte le opzioni valide
			 * altrimenti si annulla il valore
			 */
		   public function clean_value() {
				$opzioni=@array_flip((array) $this->get_opzioni());
				$valore=$this->get_value();
			    if ($opzioni && !isset($opzioni[$valore])) $this->set_value(null);
				return $this;
		  }



		  /**
	* setta in automatico il primo valore valido se non ce ne sono settati
			 */
		   public function autovalue() {
			  $this->clean_value();
			  if (!strlen($this->get_value()))
									 {//echo "<pre>";
									  foreach ($this->get_Opzioni() as $v)
									           {
									            $this->set_value($v);
									            break;
									           }
									 }
			return $this;
		  }


		  /**
	* Aggiunge una singola opzione
	  		* @param	 string $titolo e' il titolo da visualizzare
	  		* @param	 string $valore e' il valore da associare al titolo
	  		* @param	 boolean $inizio se falso o omesso l'opzione si aggiunge in coda se true si aggiunge all'inizio
	 		*/
	 	  public function add_Opzione($titolo,$valore,$inizio=false) {
	 	 		$this->opzioni=$this->get_Opzioni();
				//		  echo "<pre>$inizio";print_r($this->opzioni);
			   if (!$inizio) $this->opzioni[$titolo]=$valore;
					  else {$opzioni=array($titolo=>$valore);
							if (is_array($this->opzioni)) foreach ($this->opzioni as $id=>$val) $opzioni[$id]=$val;
							$this->opzioni=$opzioni;
						   }
              return $this;
	 	  }

		  function &get_value() {
					 $valori=parent::get_value();
					 if (!is_array($valori)) return $valori;
										elseif (count($valori)>1) return $valori;
											 else {list($valore) = $valori;
												   return $valore;
												  }
		  }


    /**
	* Restituisce il titolo associato al valore
	  * @param	  string $valore se omesso si usa il valore predefinito del selettore
	  * @return	 string $titolo e' il titolo visualizzato
	  */
		   public function get_Titolo($valore='') {
					 $opzioni=array_flip($this->get_opzioni());
					 return isset($opzioni[($valore?$valore:$this->get_value())])?$opzioni[($valore?$valore:$this->get_value())]:null;
		  }


	/**
	* Restituisce l'array associativo con le opzioni
	  * @return array
	  */
		   public function get_Opzioni() {
					 if (! $this->opzioni) return array();
					 $array=array();
					 foreach ($this->opzioni as $i=>&$v) $array[$this->trasl($i)]=$v;
					 return $array;
		  }


	  /**
	* Imposta il valore di default del menu' che verrà associato ad un valore nullo
	  *  (se e' un carattere verrà ripetutto per tutta la lunghezza del menu')
	  * @param	 string $domanda
	  */
		   public function set_Domanda($domanda) {
					 $this->default=$domanda;
					 return $this;
		  }

		   public function unset_Domanda() {
		      $this->default=null;
		      return $this;
		  }
		  
		  
		   public function get_Domanda(){
		              return $this->default;
		  }

		  /** @ignore*/
		  protected function get_html_pulsante_Accessibilita(){
		  	$pulsante=new myPulsante('',($this->pulsante?$this->pulsante:$this->trasl('Premere qui per proseguire')));
		  	$pulsante->set_Tooltip($this->pulsante?$this->pulsante:$this->trasl("Premendo questo pulsante per aggiornare"));
			if(!isset($this->myFields['NSelettori'])) $this->myFields['NSelettori']=0;
			$this->myFields['NSelettori']++;
			$pulsante->set_id("indicatore_selettorevuoto_".$this->myFields['NSelettori']);
			return $pulsante;		
		  }
		  

		/** @ignore*/
		  public function get_html_Accessibilita($evento) {
		     if ($evento)
				 	{ // $out.="<span style='display:none'><br /></span>";
				 	    $out=$links='';
				 	if(!isset($this->myFields['static']['common'])) $this->myFields['static']['common']='';    
				 	$commonStatic=$this->myFields['static']['common'];
					if (stripos($evento,'submit')===0)
													  {
													   $out.=$this->get_html_pulsante_Accessibilita();
													   $this->myFields['static']['common']=$commonStatic;
													  }
											    elseif($this->reloaJSGET)
													  	     {$links=$this->build_reload_urls($this->reloaJSGET[0],
													  	     								  $this->get_name(),
													  	     								   $this->reloaJSGET[1],
													  	     								   '',
													  	     								   '',
													  	     									$this->reloaJSGET[2]);
													  	      foreach ($this->get_Opzioni() as $titolo=>$valore)
																	  			{	$link = new myLink(myTag::htmlentities($links[$valore],ENT_COMPAT|ENT_NOQUOTES|ENT_XHTML),$this->trasl('Scegliere l\'opzione'));
																					$link->set_attributo('id',"{$this->get_id()}_{$valore}");
																					if ($valore===$this->get_value())  {
																														$link->set_attributo('style','font-weight:bold');
																														$out.="<li><span style='display:none'>{$this->trasl('Selezionato')} </span>".$link->get_html($titolo)."</li>\n";
																													  }
																									 			 else $out.="<li>".$link->get_html($titolo)."</li>\n";
																				    }
															  $out='<ul>'.$out.'</ul>';
															 }
															
						return  ($this->accessibile?"<noscript id='noscript_{$this->get_id()}'>$out</noscript>":'').
						       ($links?
					   			"<script type='text/javascript'>var myLinks_{$this->get_id_istanza()}=function(val){return ".json_encode($links)."[val];}</script>
					   			":'');
					  }

		  }


	  public function set_autotab() {
				$this->autotab=true;
				return $this;
	 }


	 /**
	  * Imposta attributi delle singole opzioni es style, class
	  *
	  * @param array $opzioni Titoli (non i valori) delle opzioni da personalizzare
	  * @param string $attributo  nome dell'attributo da settare
	  * @param string $valore	  valore dell'attributo
	  */
	  public function set_attributo_opzioni($opzioni,$attributo,$valore) {
				foreach ($opzioni as $v)
							{
							if (!$this->attributi_opzioni[$v]) $this->attributi_opzioni[$v]=new myTag();
							$this->attributi_opzioni[$v]->set_attributo($attributo,$valore);
							}
				return $this;
	 }




  /** @ignore */
  protected function _get_html_show($pars, $tag = 'span', $content = NULL){
   	 if ($pars['campo'] && !$this->get_attributo('disabled')) $out=$this->_get_html_hidden($pars);
   	 $span=$this->_get_html_show_obj($pars,$tag,$this->get_titolo());
   	 return $span.$out;
   }


   /**
    * @ignore
    */
   private  function build_colgroup($val,$nome,$verso='>') {
       $html='';
       if($verso=='>') 
                foreach (array_reverse($this->optgroups) as $opz)
                               if( ($opz['valori'] && $opz['al'].''==="$val") ||
                                   (!$opz['valori'] && $opz['al'].''==="$nome")
                                   ) $html.="</optgroup>";
       if($verso=='<') foreach ($this->optgroups as $opz) 
                               if(   ($opz['valori'] && $opz['dal'].''==="$val") ||
        				            (!$opz['valori'] && $opz['dal'].''==="$nome")
        					       )  $html.=str_replace('/>','>',$opz['tag']->get_html());
       return $html;     
   }
   
      
    public function get_Html() {
                   $this->html5Settings();
                   $jsAjaxLoader=$indicatore_attesa=$loadJs=$jsAjaxLoaderExtra=$jsCampiDipendenti='';
   	               $attributi=$this->attributi;
   	               $get_html=isset($this->Metodo_ridefinito['get_Html']['metodo'])?$this->Metodo_ridefinito['get_Html']['metodo']:null; 
   				   if ($get_html!='') return $this->$get_html(isset($this->Metodo_ridefinito['get_Html']['parametri'])?$this->Metodo_ridefinito['get_Html']['parametri']:null);
				   $this->opzioni=$this->get_Opzioni();
				  /* print_r($this->attributi);
				   echo"<hr>Null",($this->get_value()===null);
				   exit;*/
				   if(!$this->default && count($this->opzioni) && $this->get_value()===null )
				                             {$opz=$this->get_Opzioni();
				                              $opz=array_shift($opz);
				                              $this->set_value($opz);
				                             }
				                        
				   $jsCommon=$this->get_js_common();
				   if(isset($this->attributi['onchange'])) $onChange=$this->attributi['onchange'];
				                                      else $onChange=false;
				   if($this->con_js && isset($this->ajaxloader['src']) && !$this->is_hidden() && !isset($this->attributi['disabled']))
				   	   {
				   		$loadJs="myAjaxLoader_{$this->get_id()}_{$this->get_id_istanza()}";
				   		$monitor_eventi="myAjaxLoader_Monitor_{$this->get_id_istanza()}";
				   		
				   		$pulsante=$this->get_html_pulsante_Accessibilita();
				   		$pulsante->set_hidden();
				  		$id_pulsante=$pulsante->get_id();
				   		$indicatore_attesa="<img id='indicatore_attesa_{$this->get_id()}' src=\"".($this->ajaxloader['icona']?$this->ajaxloader['icona']:"/".self::get_MyFormsPath()."icone/load.gif")."\" style='display:none' />
				   							$pulsante";
				   							
				   		$jsAjaxLoader="
				   			<script type=\"text/javascript\">
				   			             // <!-- 
				   						 var $monitor_eventi=0;
				   						 var try_$loadJs={};
				   						 function $loadJs(e){
				   							var element=document.getElementById('{$this->get_id()}');
				   							$monitor_eventi++;
				   							var qstr='valore='+element.value;
				   							var chiamabile=false;
				   							var val=null;
				   							event_$loadJs=e; 
				   						";

				   		if($this->ajaxloader['campi'])
				   			foreach ($this->ajaxloader['campi'] as $c=>&$obj)
				   							$jsAjaxLoader.="val=myGetValueCampo('{$obj->get_id()}','".myForm::getcast_js($obj)."',function(){return null;});
				   							if(val!=undefined) chiamabile=true;
				   							qstr+='&parametri[$c]='+val;
				   						 ";
				   		if(!$this->ajaxloader['onafterload']) $this->ajaxloader['onafterload']="self.element.focus();
                                                                                                if(self.element.options.length==1 && 
                                                                                                   self.element.onchange) 
                                                                                                            {
                                                                                                               if (typeof self.element.onchange === 'function' ) self.element.onchange();
                                                                                                                                                            else (function(){self.element.onchange;})();
                                                                                                            }
                                                                                                ";
				   		
				   		if(isset(self::$all_ajaxloader[$this->get_id_istanza()]))
				   			foreach (self::$all_ajaxloader[$this->get_id_istanza()] as &$obj) {
				   	   						$jsCampiDipendenti.="\nmyAjaxLoader_{$obj->get_id()}_{$obj->get_id_istanza()}();\n";
				   							}
						

				   		$jsAjaxLoader.="    //if(!chiamabile) return;
				   							element.disabled=true;
				   							startStopForm(element,function(element){return element.disabled!=true});
				   							document.getElementById('indicatore_attesa_{$this->get_id()}').style.display='inline!important';
											{$this->ajaxloader['onbeforeload']};
											try_$loadJs={'try_n':0,
														 'element':element,
														 'cache':true,
														 'call':function(){
														 		var self=this;	
														 		".myJQuery::JQvar().".ajax({url:'{$this->ajaxloader['src']}'+qstr,
														 									cache:self.cache, 
																				 			dataType:'html' //Forza l'uso di html per evitare problemi con IE
				   	   																		})
																 .always(function(loaded,esito) {
																 			if(esito!='success' || !selectOptionsSetFromXml(document.getElementById('{$this->get_id()}'),loaded)) 
                                                                                        {self.try_n++;
																 			             if(self.try_n<=5) {self.cache=false;return self.call();}
                                                                                        }
																 			document.getElementById('indicatore_attesa_{$this->get_id()}').style.display='none';
																 			var ok=false;
																			if(self.try_n>=5) {
																							    self.element.style.display='none';
																							   ".myJQuery::JQvar()."(document.getElementById('{$id_pulsante}').form).unbind('submit');
	      																					   ".myJQuery::JQvar()."('#{$id_pulsante}').css('display','inline'); 	
	      																					    document.getElementById('{$this->get_id()}').form.submit();
				   	   																		  }
																 						else { ".myJQuery::JQvar()."('#{$id_pulsante}').css('display','none'); 
																 							    document.getElementById('indicatore_attesa_{$this->get_id()}').style.display='none';
	      														 								$jsCampiDipendenti
										      													ok=true;
										      												}
										      								
				      														if(--$monitor_eventi==0) self.element.form.disabled=false;
				      														self.element.disabled=false;
				      														self.try_n=0;
										      								try{			
																	 		 	if (event_$loadJs.cancelBubble)  event_$loadJs.cancelBubble = true;
																			   		 		       		   else  event_$loadJs.stopPropagation();
																			   	if(ok) { {$this->ajaxloader['onafterload']}; }
																		   	  } catch (Exception) {}
									      																		 
																		})
																 }
														  };		
											   try_$loadJs.call();							
											
								}";
								
											
					    foreach ($this->ajaxloader['campi'] as $c=>&$obj)
						      	if($obj) $jsAjaxLoaderExtraEvents=" myEventManager.add('{$this->ajaxloader['evento']}',$loadJs,document.getElementById('{$obj->get_id()}'));
										      													";
										      													
				   		if($jsAjaxLoaderExtra || $jsAjaxLoaderExtraEvents) $jsAjaxLoader.=" if(window.addEventListener) window.addEventListener('load',function(){ $jsAjaxLoaderExtra;  },false);
				   														else if(window.attachEvent) window.attachEvent('onload',function(){ $jsAjaxLoaderExtra;  });
				   														$jsAjaxLoaderExtraEvents;
				   											";
				   		$jsAjaxLoader.="
				   		    // -->
				   		    </script>";
				   		$onChange='submit';
				   		
				   	}

				   	
				 if($this->con_js && !$onChange && !isset($this->attributi['onkeyup']) && $this->autotab)
													 {
													 parent::set_autotab();
													 $this->SetJS("MyTabulazione(this)",'onchange',false);
													 }


				  $valore=$this->get_value();
				  $default=$this->default;
				  if (is_array($this->opzioni))
					 {  $maxlen=0;$opz='';
					 	foreach ($this->get_opzioni()  as $nome=>$val)
							  {
							    if (isset($this->attributi_opzioni[$nome])) $attributi_opzione=$this->attributi_opzioni[$nome]->stringa_attributi(array('value'),true,true);
																else $attributi_opzione='';
																if (($valore!==null && trim((string) $valore)===trim((string) $val)) || !isset($this->attributi['readonly'])) 
												    { $commenti=array();
												      preg_match_all('@<!--.*-->@' , $nome,$commenti);
												      if(!$commenti) $commenti=array(0=>array());
												      $opz.=$this->build_colgroup($val, $nome,'<').
								                              "<option $attributi_opzione value=\"".myTag::htmlentities(myTag::nonhtmlentities($val))."\" ".(trim((string) $valore)===trim((string)$val)?'selected="selected"':'').">".myTag::htmlentities(myTag::nonhtmlentities(preg_replace('@<!--.*-->@','',$nome))).implode("",$commenti[0])."</option>".
							                               $this->build_colgroup($val, $nome,'>');
												    }
								
								if ($valore!==null && trim((string) $valore)===trim((string) $val) && $this->notnull) $default='';
								$maxlen=max($maxlen,strlen($nome));
							  }
							  
						      if ($default && count($this->opzioni)>=1)
								 {if (strlen($default)==1) $default=str_pad('', $maxlen , $default);
								  $opz=$this->build_colgroup('', $default,'<').
								            "<option value='' $attributi_opzione selected=\"selected\">$default</option>".
								       $this->build_colgroup('', $default,'>').
								       $opz;
								  }
					}
			  
				if(!$this->con_js) $this->myJQueries=array();
				
				
				$parti=explode('<!-- JS+ -->',$this->send_html("<select ".parent::stringa_attributi(array('value','opzioni')).">$opz</select>"),2);
				$parti[]='';
				$opz=&$parti[0];
				if (!$onChange || stripos($onChange,'location.href')!==0 || isset($this->attributi['readonly']))
									$out="$jsAjaxLoader$opz$indicatore_attesa".$this->get_html_Accessibilita($onChange);
							  else {$sost=array('<'=>'&lt;','>'=>'&gt;','"'=>'\\"');
						        	  if($this->con_js) $out='<script type="text/javascript">
												testo="'.strtr("$jsAjaxLoader$opz",$sost).'";
										     	testo=testo.replace(/&lt;/g,String.fromCharCode(60));
											    testo=testo.replace(/&gt;/g,String.fromCharCode(62));
												document.write(testo);
										  </script>'.
								    $this->get_html_Accessibilita($onChange);
								   }
				$this->attributi=$attributi;
				return ($this->con_js?$this->jsAutotab().$jsCommon:'').$out.$parti[1];
	 }


	 /**
	  * Passa in minuscolo (ma con la prima lettera di ogni parola maiuscola)
	  * facendo ovewriting di questo metodo si possono personalizzare le regole di "aggiustamento" delle opzioni
	  * @param	 string $cosa
	  * @param	 int $lungMax opzionale indica lunghezza massima della stringa (si tronca sulla parola)
	  */
	 public function Minuscolo ($cosa,$lungMax='') {
	  $v=explode(' ',str_replace('_',' ',trim((string) $cosa)));
	  $x='';
	  for ($i=0; $i<count($v);$i++)
				{
				 if (strlen($v[$i])>4 && $x!='') $v[$i]=ucfirst(trim((string) $v[$i]));
				 if ($x!='') $x.=' ';
				 $x.=$v[$i];
	  }
	  $cosa=trim((string) $x);
	  if ($lungMax) $cosa=$this->Abbrevia($cosa,$lungMax);
	  return $cosa;
	 }


	  /**
	  * Abbrevia una stringa
	  * facendo ovewriting di questo metodo si possono personalizzare le regole di "aggiustamento" delle opzioni
	  * @param	 string $cosa
	  * @param	 int $lungMax indica lunghezza massima della stringa (si tronca sulla parola)
	  */
	  public function Abbrevia ($cosa,$lungMax) {
		if (strlen($cosa)>$lungMax){$i=$lungMax-3;
									while ($i>0 && !preg_match('# |,|.|-#',$cosa[$i])) {$i--;}
									if ($i<=0) $i=$lungMax-3;
									$cosa=substr($cosa,0,$i)."...";
									}
		return $cosa;
	 }

	/**
	  * Abbrevia tutte le opzioni
	  * @param	 int $lungMax opzionale indica lunghezza massima della stringa (si tronca sulla parola)
	  */

	  public function set_Abbrevia_Opzioni($lungMax='') {
	    $new=array();
		if (is_array($this->opzioni)) foreach ($this->opzioni as $id=>$val) $new[$this->Abbrevia($id,$lungMax)]=$val;
		unset($this->opzioni);
		$this->opzioni=$new;
		return $this;
	 }
	 
	 /**
	  * Definisce un gruppo di opzioni da racchiudere in apposita Label restituisce il myTag che verra usato per essere eventualmente personalizzato
	  * <code>
	  *   $s=new mySelect('alimenti');
	  *   $s->set_Opzioni(array('Pane'=>1,'Pasta'=>2,'Pollo'=>3,'Vitello'=>4));
	  *   $s->set_optgroup('Carboidrati', 'Pane', 'Pasta')->set_style('color','red');
	  *   $s->set_optgroup('Proteine', 3, 4,true)->set_style('color','blue');
	  *   
	  *   echo $s;
	  * </code>
	  * @param string $label 
	  * @param string $dal   Descrizione della prima opzione del gruppo 
	  * @param string $al    Descrizione dell'ultima opzione del gruppo
	  * @param string $usa_valori se true non si usano le descrizioni ma i valori delle opzioni
	  * @return myTag
	  */
	 function &set_optgroup($label,$dal,$al,$usa_valori=false) {
	     $tag=new myTag('optgroup');
	     $tag->set_attributo('label',$label);
	     $this->optgroups[$label]=array('dal'=>$dal,'al'=>$al,'valori'=>$usa_valori,'tag'=>&$tag);
	     return $this->optgroups[$label]['tag'];
	 }

	  /**
	  * Passa in minuscolo tutte le opzioni
	  * @param	 int $lungMax opzionale indica lunghezza massima della stringa (si tronca sulla parola)
	  */
	  public function set_Miniscolo_Opzioni($lungMax='') {
	    $new=array();
		if (is_array($this->opzioni)) foreach ($this->opzioni as $id=>$val) $new[$this->Minuscolo($id,$lungMax)]=$val;
		unset($this->opzioni);
		$this->opzioni=$new;
		return $this;
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
		  }


		  /**
	* se settato aggiunge il reload della pagina sull'evento passato, di default e' onChange
			* @param	 'parametro'|'completo'|'azzera'|'submit' $Tipo  Il default e' 'parametro'
			* @param	 string $evento  Il nome dell'evento su cui attivare il reload
			*  @param  string $fragment eventuale frafment da aggiungere alla url (solo per reload in get)
			*   Supponiamo che questo selettore si chiami 'Tipo' e la url della pagina sia
			*					  http://www.fdsf.it?abc=32432&Tipo=424&altro=12345<br />
			*							con l'opzione 'parametro' si ricaricherà http://www.fdsf.it?abc=32432&Tipo=nuovo_valore<br />
			*							con l'opzione 'completo' si ricaricherà http://www.fdsf.it?abc=32432&Tipo=nuovo_valore&altro=12345<br />
			 *							con l'opzione 'azzera' si ricaricherà http://www.fdsf.it?Tipo=nuovo_valore<br />
			 *							con l'opzione 'submit' effettua l'invio in POST del form cosi' com'e'<br />
			 *
			*/
		   public function SetReloadJS($Tipo='parametro',$evento='onchange',$fragment='',$usa_random=true,$sostituisci=false) {
			if (!$Tipo) $Tipo='parametro';
			$evento=strtolower($evento);
			if ($Tipo=='submit') $this->set_js("submit()",$evento,false);
						   else  {$this->set_js("location.href=myLinks_{$this->get_id_istanza()}(this.selectedIndex==undefined?'':this.options[this.selectedIndex].value);",$evento,$sostituisci);
						          $this->reloaJSGET=array($Tipo,$usa_random,$fragment);
						   		  }
			return $this;
		  }


		  /**
			 *
			 * Calcola le url usate nel mySelect::setReloadJS() nelle modalità GET,
			 * utile per eventuali overriding
			 *
			 * @param	'parametro'|'completo'|'azzera'|'submit' $Tipo  Il default e' 'parametro'
			 * @param  string $nome_campo nome del campo usato come riferimento per la costr. nuova url
			 * @param  boolean $usa_random se true aggiunge tt randomico
			 * @param string $url eventuale url da usare, se string avuota o omesso si usa $_SERVER['PHP_SELF']
			 * @param string $query_string eventuale string di parametri se FALSE non si usa omesso o stringa vuota usa $_SERVER["QUERY_STRING"]
			 * @param string $fragment eventuale fragment
			 *
			 * @return string
			 */

		   public function build_reload_urls($Tipo,$nome_campo,$usa_random,$url='',$query_string='',$fragment=''){
		  	$nuova=$parametri=$valori=array();
		  	if($query_string==='') $query_string=$_SERVER["QUERY_STRING"];
		  	if($query_string) @parse_str($query_string,$parametri);
		  	if(!$url) $url=$_SERVER['PHP_SELF'];
		  	$parametri=(array) $parametri;
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
			$opzioni=$this->get_Opzioni();
			if($this->default && !in_array('', $opzioni,true)) $opzioni=array_merge(array($this->default=>''),$opzioni);
			foreach ($opzioni as $val)
							{$nuova[$nome_campo]=$val;
						  	 $valori[$val]=$url.'?'.http_build_query($nuova).$fragment;
							}
			return $build[serialize(func_get_args())]=$valori;
		  }


		  /**
		   * alias di
		   * @see mySelect::setreloadjs()
		   */
		   public function set_reloadjs($Tipo='parametro',$evento='onchange',$fragment='',$usa_random=true,$sostituisci=false){
		  	return $this->SetReloadJS($Tipo,$evento,$fragment,$usa_random,$sostituisci);
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
				$opzioni=array();
				IF (isset($_SESSION['myFields']['MyOpzioniRegistrate'][sha1(serialize($this->qry))]) && $registra)
												{$this->opzioni=$_SESSION['myFields']['MyOpzioniRegistrate'][sha1(serialize($this->qry))];
												$opzioni=$this->opzioni;
												}
										 else {	 
										     if(is_array($qry)) $v=&$db->getarray($qry[0],$qry[1]);
										                   else $v=&$db->getarray($qry);
										         $n=count($v);
												 for ($i=0;$i<$n;++$i)
														if (is_array($v[$i])) $opzioni[array_shift($v[$i])]=array_shift($v[$i]);
		
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


 /**
	* Restituisce un messaggio se l'attributo value non soddisfa condizioni di notnull,maxlength,minlength
		  *  @return string
		  */
		  protected function get_errore_diviso_singolo() {
		      if ($this->notnull &&    (    
                    		              ( is_array($this->get_value()) && !count($this->get_value()))
                    		                                          ||
                    		              ( !is_array($this->get_value()) && trim((string) $this->get_value())==='')
                    		           )
		                              ) return 'non può essere nullo';
					 if($this->restrizioni_check && $this->get_value())
					            {if($this->get_opzioni())	$opz=array_flip($this->get_opzioni());
					                                   else $opz=array();
					 			 if(!isset($opz[$this->get_value()])) return 'non è accettabile';
					 			}
					 return parent::get_errore_diviso_singolo();
					 }




	 public function get_js_chk($js = ''){
  		if($this->get_showonly()===2 || !$this->notnull) return "return null;";
  		if($this->get_showonly()===0) $js="valore=trim(''+document.getElementById('{$this->get_id()}').options[document.getElementById('{$this->get_id()}').selectedIndex].value);";
  								else  $js="valore=trim(''+document.getElementById('{$this->get_id()}').value);";
  		$js.="if(strlen(valore)==0) return \"{$this->trasl('non può essere nullo')}\";\n";
		return $js;
  	}

}