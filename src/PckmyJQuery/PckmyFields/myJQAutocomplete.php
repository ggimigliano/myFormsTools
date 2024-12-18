<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQAutocomplete.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\PckmyFields;



use Gimi\myFormsTools\PckmyFields\myField;
use Gimi\myFormsTools\PckmyJQuery\myJQuery;
use Gimi\myFormsTools\PckmyJQuery\myJQueryMyField;
                                                


/**
 *
 * Classe  produrre effetto autocompletamento del codice di un generico {@link myField}
 * <code>
 * $f=new myform();
 * $f->add_campo(new myText('cognome'));
 * $f->campo('cognome')->add_myJQuery(new myJQAutocomplete())
 * 					   ->set_source($conn->getassoc("select distinct cognome n1,cognome n2 from persone order by cognome"))
 * 					   ->set_tiping(2);
 *
 * </code>
 *
 * @see http://jqueryui.com/demos/autocomplete/
 */
	
class myJQAutocomplete extends myJQueryMyField {
/**
 * @ignore 
 */
protected    $campo_output='',$function='',$campi_input=array(),$clean_term
	, $chiudi=array(),$modalita='post'
	, $valori_input=array('normali'=>array(),'js'=>array()),$scroll=null,$sorgente,$hmax,$css_font_default='{font-size:11px}',$css_font_class=array('.ui-autocomplete')
	, $filtroDefault=null;
	
	/**
	 * @ignore
	 */
	static protected function  init(&$widget) {
		$widget='autocomplete';
	//	self::add_src("jquery/ui/jquery.ui.position.js");
	//	self::add_src("jquery/ui/jquery.ui.autocomplete.js");
	}

	
	/**
	 * (non-PHPdoc)
	 * @see myJQueryMyField::application()
	 */
	  public function application(&$myField){
	    if($myField->get_myJQueries())
					foreach ($myField->get_myJQueries() as $myJQ)
								if(method_exists($myJQ,'get_placeholder') && $myJQ->get_placeholder()) 
											$this->clean_term="request.term=str_replace('{$myJQ->get_placeholder()}','',request.term);
																";
		return parent::application($myField);										
	}
	 


	/**
	 *
	 * Imposta i n. di caratteri dopo cui la scelta si attiva
	 * @param int $caratteri
	 */
    public function set_tiping($caratteri=2){
    	$this->minLength=$caratteri;
    	return $this;
    }

    
  

    /**
     * Alias di myJQAutocomplete::set_source() ma con forzatura del primo tipo che e' sempre array
     * @param array $opzioni
     * @param myField $campo
     * @param string $valori Parametri da inviare in post in aggiunta
	 * @param string $valorijs Eventuali altri parametri da valorizzare tramite js es $parametriJS=array('pippo'=>'documento.getElementById('ciccio').value');
     */
    public function set_opzioni(array $opzioni,myField $campo=null ){
     	$this->set_source($opzioni,$campo);
    	return $this;
    }

    /**
     * Imposta una sorgente di dati
     * @param array|string $source se array usa quei dati se string lo considera url usata con  {@link myAjax}
     * @param myField|string $campo eventuale campo o id html in cui inserire il valore scelto 
     * @param string $valori Parametri da inviare in post in aggiunta
	 * @param string $valorijs Eventuali altri parametri da valorizzare tramite js es $parametriJS=array('pippo'=>'documento.getElementById('ciccio').value');
     * @return myJQAutocomplete
     */
	public function set_source($source,$campo='',$valori=array(),$valorijs=array()){
		$this->sorgente=$source;
		if(is_string($this->sorgente) && !$this->filtroDefault) $this->filtroDefault="function (elemento,digitato) { return true; }";
		$this->set_campo_output($campo);
	  	$this->valori_input['normali']=$valori;
    	$this->valori_input['js']=$valorijs;
  	    return $this;
	}
	
	/**
	 * Imposta un campo di output
	 * @param myField|string $campo eventuale campo o id html in cui inserire il valore scelto to
	 * @return myJQAutocomplete
	 */
	 public function set_campo_output($campo){
		$this->campo_output=$campo;
		return $this;
	}
	

	/**
	 * @ignore
	 */
	 public function get_common_defaults(){
	 	  return array();
	}

	/**
	 * @ignore
	 */
	 public function set_common_defaults(array $defaults){
		$this->add_common_code("myAutoCompleterCache = {}");
	}

	/**
	 * Imposta il protocollo di trasmissione dei parametri
	 * @param 'get'|'post'  $modalita 
	 */
	 public function set_mode($modalita){
	    $this->modalita=$modalita;
	    return $this;
	}
	
	/**
	 * Imposta il testo della riga di chiusura della pagina,
	 * se omesso non verrà mostrato quindi sarà  impossibile chiudere la finistra senza effettuare una scelta
	 *
	 * @param string  $messaggio
	 * @param boolean $gia_tradotto se true non si cercerà di tradurlo
	 */
	 public function set_messaggio_chiusura($messaggio=':: Chiudi Finestra ::',$gia_tradotto=true) {
		if($gia_tradotto) $this->chiusura=$messaggio;
		array($messaggio,$gia_tradotto);
		return $this;
	}
	
	
	/**
	 * Imposta un filtro js sui dati, Di default e' "inizia per.. case insensitive" ma si puo' personalizzare inserendo una funzione JS
	 * che riceve nell'ordine il valore in cui cercare e la stranga digitata, restituisce true se valido 
	 * 
	 * Es. se si vuole impostare un "contiene case sensitive" 
	 * 	  <code>
	 *  	$a=new myAutocompleter(...)
	 *      .....
	 *      $a->set_filtro_js("function(elemento,digitato) {return elemento.indexOf(digitato)>=0}");
	 *  </code>
	 *  
	 *  @param string $body
	 */
	  public function set_filtro_js($body) {
		$this->filtroDefault=$body;
		return $this;
	}

	/**
	 * @ignore
	 */
	 public function set_istance_defaults(){
	    //$opzioni['showOn']='button';
	    $var="val_".spl_object_hash($this);
	    $this->set_event('select',"if(ui.item.value!={$this->JQid()}.val()) {$this->JQid()}.trigger('change');",$var,'function(event,ui)');
		
		$istanza=$this->get_id_istanza();
	    $open=$this->get_istance_defaults('open');
		if($this->scroll===null) $this->set_altezza_scroll('');
		if(!isset($open->opzioni['icona']) || !$open->opzioni['icona']) $this->set_indicatore_attesa('');
		if(!isset($open->opzioni['css'])   || !$open->opzioni['css'])   $open->opzioni['css']='';
		foreach ($this->css_font_class as $class)  $open->opzioni['css'].=self::get_add_style($class.$this->css_font_default,true);
		                  
	    $jq=self::$identificatore;
	    if(!$this->filtroDefault) $this->filtroDefault="function (elemento,digitato)
                             {
                                if (typeof elemento === 'string') return digitato=='' || strtolower(elemento).indexOf(strtolower(digitato))==0;
                                                             else return digitato=='' || strtolower(elemento.label).indexOf(strtolower(digitato))==0;
                             }";
	    
		if (is_string($this->sorgente))
					{
					 $this->campi_input=myJQAutocompleteUtilsStatic::estrai_ids($this->campi_input);
					 $parametri=$this->prop('name')."+'='+".$this->val()."+myJQAutocomplete_accoda_parametri_ajax(new Array('".implode("','",(array) $this->campi_input)."'),new Array('".implode("','",array_keys((array)$this->campi_input))."'))";
					 foreach ($this->valori_input['normali'] as $k=>$v) $parametri.="+'&$k=".addslashes(rawurldecode($v))."'";
					 foreach ($this->valori_input['js'] as $k=>$v)  $parametri.="+'&$k='+".$v;
					 $parametri_cache="myJQAutocomplete_accoda_parametri_ajax(new Array('".implode("','",(array) $this->campi_input)."'),new Array('".implode("','",array_keys((array)$this->campi_input))."'))";					 
					 $search="function( request, response ) {
                                  {$this->clean_term}
					 		      var idx=trim('{$istanza}|'+{$parametri_cache}+'|'+trim(request.term));
					 			  if(myAutoCompleterCache[idx]) 
					 			          {
					                      MyMappa_{$istanza}=myAutoCompleterCache[idx][1];			
					 				      response( myAutoCompleterCache[idx][0] );
					                     } 
									 else{myAutoCompleterCache[idx]=[[],{}];															
								    	".myJQuery::__callStatic($this->modalita,
											array($this->sorgente,
												  $parametri,	
									        	  "function(data,status,info){
																	
					 							  					var i=0;
					 							  					var req=request.term;
											                        var idx=trim('{$istanza}|'+{$parametri_cache}+'|'+trim(''+request.term));
											                      
                                                                    if(info.responseJSON ||   //is json 
                                                                       (data.charAt(0)=='{' && data.charAt(data.length-1)=='}')) //is xhtml 
                                                                              { 
                                                                               if(!info.responseJSON) data=eval('(' + data + ')');
																				
                                                                               {$jq}.each(data, function(label, value)
									        	  								 					{
                                                                                                     if (typeof label === 'number' && !isNaN(label)) label=value;  
									        	  								 					 if(({$this->filtroDefault})(label==undefined || !trim(label)?'':trim(label),
									        	  								 					 							   req==undefined || !trim(req)?'':trim(req))) 
									        	  								 					 			{
												        	  								 					 myAutoCompleterCache[idx][0][i]={label:label,value:".($this->campo_output?'label':'value')."}
								 							  					 								 myAutoCompleterCache[idx][1][label]=".(!$this->campo_output?'label':'value').";
								 							  													 i++;
								 							  													 }
									        	  													 }
  																							);
																 				}
					 							  				 		 else {
					 							  								var founds=$jq(data).find('li');
					 							  								for(var i=0;i<founds.length;i++) {
					 							  										if(!({$this->filtroDefault})(founds[i].innerHTML==undefined || !trim(founds[i].innerHTML)?'':trim(founds[i].innerHTML),
					 							  																					 req==undefined || !trim(req)?'':trim(req))) continue;
					 							  										myAutoCompleterCache[idx][0][i]={label:founds[i].innerHTML,value:".($this->campo_output?'founds[i].innerHTML':'founds[i].id')."}
					 							  					 					myAutoCompleterCache[idx][1][founds[i].innerHTML]=".(!$this->campo_output?'founds[i].innerHTML':'founds[i].id').";
					 							  										}
					 							  								}
					 							  				  MyMappa_{$istanza}=myAutoCompleterCache[idx][1];
                                                                  response( myAutoCompleterCache[idx][0] ); 
					 							  				  }"
 												 )
									        )."
					                       }
					                   }";
					 							  				 				 
					 if(isset($this->opzioni['source'][spl_object_hash($this)]) && $this->opzioni['source']!='') 
					 		{ 
					 			$search=" function( request, response ) {
					 								var parent={$this->opzioni['source'][spl_object_hash($this)]};
					 								var my={$search};
					 								if(parent(request, response)) return my(request, response);
					 														 else return false;					 
					 						}";
					 		} 				 							  				 				 
					 $this->source=$search;
					}
			elseif (is_array($this->sorgente))
					{
					$associativo=self::is_associativo($this->sorgente);
					$info=array();
					if($associativo)  foreach ($this->sorgente as $k=>$v) $info["'".addslashes($k)."'"]=array('label'=>"'".addslashes($v)."'",'value'=>$k);
					            else  foreach ($this->sorgente as $v)     $info["'".addslashes($v)."'"]=$v;
					$this->set_event('open'," MyMappa_{$istanza}=". self::encode_array($info) ,'chiavi');
					
				    if(!$associativo)  
					        $this->source="function( request, response ) {
									{$this->clean_term}
									if(request.term.length<{$this->JQVar()}('{$this->get_id()}').autocomplete('option','minLength'))
																															{response(false);
																															 return false;}
																															 
									var options=". self::encode_array(array_values($info)).";
									var selected=[];
									var req=request.term;
	                               	for (let i in options)
												if(
													 ({$this->filtroDefault})(options[i]==undefined || !trim(options[i])?'':trim(options[i]),
													 								  req==undefined || !trim(req)?'':trim(req))
												  )	selected[selected.length]=options[i];
									response(selected);
									}
					 			   ";
					     else  $this->source="function( request, response ) {
									{$this->clean_term}
									if(request.term.length<{$this->JQVar()}('{$this->get_id()}').autocomplete('option','minLength'))
																															{response(false);
																															 return false;}
																															 
									var options=". self::encode_array(array_values($info)).";
									var selected=[];
									var req=request.term;
	                               	for (let i in options)
												if(
												     ({$this->filtroDefault})(options[i]==undefined?'':options[i], req==undefined?'':req)
												  )	selected[selected.length]=options[i];
									response(selected);
									}
					 			   ";
					
					}

		if($this->campo_output)
				{
				if($this->myField instanceof myField ||
				 (is_object($this->myField) && method_exists($this->myField, 'get_id'))) $ID=$this->myField->get_id();
												 else $ID=$this->myField;
												 
				if($this->campo_output instanceof myField ||
				 (is_object($this->campo_output) && method_exists($this->campo_output, 'get_id'))) 
													  $ID_OUT=$this->campo_output->get_id();
												 else $ID_OUT=$this->campo_output;
												
			    //    $this->set_event('open',"document.getElementById('{$ID_OUT}').value='';");
			    $this->add_code(" var myAutocompleteHandlerCMPKeypress{$ID}='';
                                  if($jq('{$this->get_id()}').val())   myAutocompleteHandlerCMPKeypress{$ID}=$jq('{$this->get_id()}').val();
                                  var myAutocompleteHandlerKeypress{$ID}=
			    				  					 function(){
                                                                 if(myAutocompleteHandlerCMPKeypress{$ID}!=$jq('{$this->get_id()}').val())
			    				  										{
			    				  										 if(myAutocompleteHandlerCMPKeypress{$ID}!='') document.getElementById('{$ID_OUT}').value='';
			    														 $jq('{$this->get_id()}').unbind('keyup',myAutocompleteHandlerKeypress{$ID});
																		} //su cambio del valore azzera il contenuto
																}
			    				  $jq('{$this->get_id()}').bind('keyup',myAutocompleteHandlerKeypress{$ID});
			    				 ");
				}
		if($this->function)
				$this->set_event('select'," {{$this->function}(MyMappa_{$istanza}[ui.item.value],ui.item.value,event);} ");
		 elseif($this->campo_output)
	 			$this->set_event('select'," {{$jq}('#{$ID_OUT}').val(MyMappa_{$istanza}[ui.item.value]);} ");
	 	
	 	if(is_object($this->myField)) $this->set_event('close'," myEventManager.fire('keyup','{$this->myField->get_id()}');  ",spl_object_hash($this));
		if(is_object($this->myField) && $this->myField->get_disabled()) $this->disabled=true;
	//	if(!$this->options['minLength']) $this->minLength=3;
	
	}


	/**
	 * Imposta un campo da passare tramite ajax {@link myJQAutocomplete::set_source()}
	 * @param string $nome     nome con cui verrà passato in POST
	 * @param myField $campo   campo da cui prelevare il dato
	 * @return myJQAutocomplete
	 */
	 public function set_campo_input($nome,&$campo){
	    $this->campi_input[$nome]=$campo;
  		return $this;
  	}

  	/**
	 * Imposta un campo da passare tramite ajax {@link myJQAutocomplete::set_source()}
	 * @param string $nome     nome con cui verrà passato in POST
	 * @param string id_campo   id del campo da cui prelevare il dato
	 * @return myJQAutocomplete
	 */
	 public function set_id_campo_input($nome,$id_campo){
  		$this->campi_input[$nome]=$id_campo;
  		return $this;
  	}


/**
 * Funzione per personalizzare un trigger sull'evento di scelta la funzione riceve in ingresso
 * due parametri (digitato e trovato in elenco) e se restiruisce boolean viene inserita l'opzione
 * @param string $function nome di una funzione JS o codice da eseguire
 */
	 public function set_onselect_function($function) {
		$this->function=$function;
		return $this;
	}

	/**
	 * Impone altezza massima alla finestra di scroll
	 * @param int $px pixels
	 * @return myJQAutocomplete
	 */
	 public function set_altezza_scroll($px){
	 	$this->scroll=$px;
	 	$this->set_event('open',myJQAutocompleteUtilsStatic::altezza_scroll_code($px),'scroll');
		return $this;
	}



	/**
	 * Cosa deve comparire durante l'attesa di aggiornamento
	 * @param string $tag e' la url dell'icona da visualizzare o semplocemente l'html da visualizzare
	 */
	 public function set_indicatore_attesa($url=''){
	 $this->set_event('open',myJQAutocompleteUtilsStatic::indicatore_attesa_code($url),'icona');
	 //$this->set_event('close',self::get_add_html('head',"<style>.ui-autocomplete-loading{background:none}</style>"),'icona');
	 return $this;
	}




}