<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myText.
 */

namespace Gimi\myFormsTools\PckmyFields;


use Gimi\myFormsTools\myCSS;
use Gimi\myFormsTools\PckmyJQuery\myJQuery;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQInputMask;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQTagIt;

class myText extends myField {
/** @ignore */
    protected $MaiMin='',$maxlengthcomplessiva,$unsetstriptags=false,$molteplicita=array('grafica'=>null,'total_maxlength'=>null,'use_textarea'=>null,'n'=>null,'delim'=>null,'quote'=>null,'total_maxlength'=>null,'size'=>null),$masked=false,$rimuovi_spazi_doppi=true;

/**
  *
  * 
  * @param	 string $nome E' il nome del campo
  * @param	 string $valore Valore da assegnare come default
  * @param	 string $classe e' la classe css da utilizzare
  */
   public function __construct($nome,$valore='',$classe='') {
	parent::__construct($nome,$valore);
	$this->set_attributo('type','text');
	if ($classe) $this->set_attributo('class',$classe);
	$this->set_MyType('MyText');
  }


  /**
   * Imposta maschera di inserimento, in pratica abilita un'eventuale facilitazione di inserimento ove disponibile
   */
   public function set_mask($stato=true){
      $this->masked=$stato;
      if($stato==false) $this->set_hidden();
                   else $this->unset_hidden();
      return $this;
      
  }

  /**
   * Imposta la rimozione degli spazi doppi
   * @param boolean $stato
   * @return myText
   */
   public function set_no_spazi_doppi($stato=true){
      $this->rimuovi_spazi_doppi=$stato;
      return $this;
  }
  
  
  /**
	* Elimina l'impostazione  Maiuscolo o Minuscolo
*/
   public function unset_MaiMin() {
  	$this->set_MaiMin('');
  }



   /**
	* Imposta Maiuscolo o Minuscolo(tramite css)
	* @param 	 'maiuscolo'|'minuscolo' $modo
   */
   public function set_MaiMin($modo='maiuscolo') {
	$modo=strtolower(trim((string) $modo));
	switch ($modo) {
		case 'maiuscolo':
				$this->set_style('text-transform','uppercase');
				break;
		case 'minuscolo':
				$this->set_style('text-transform','lowercase');
				break;
		default:$this->set_style('text-transform','');
				$modo=''; 
				break;
	}
	$this->MaiMin=$modo;
	return $this;
  }


 /**
	* Setta la lunghezza max del campo, da verificare nel metodo Errore
  * @param	integer $maxlength 
  * @param	integer $size e' la lunghezza effettiva del campo da visualizzare
  */
	 public function set_maxlength($maxlength,$size="") {
	  $this->maxlength=$maxlength;
	  $this->set_attributo('maxlength',$maxlength);
	  if (!$size) $this->set_attributo('size',min((int) ($maxlength*1.2),70));
		 	 else $this->set_attributo('size',$size);
	return $this;
  }


   /**
   * restituisce la lunghezza del campo in caratteri
   *
   * @return int
   */
    public function get_size(){
   		return $this->attributi['size'];
   }


   /**
    * Imposta la lunghezza del campo in caratteri
    *
    * @return int
    */
    public function set_size($size){
        $this->attributi['size']=$size;
        return $this;
   }

  /**
	* Setta la autotabulazione del campo*/
	 public function set_autotab () {
	 parent::set_autotab();
	 if($this->myJQueries) 	foreach ($this->myJQueries as $JQ)	 if($JQ instanceof myJQInputMask) {$JQMask=true;break;}
	 if(!$JQMask) {$onKeyup=$this->attributi['onkeyup'];
	 			   $onKeyup.="if(this.maxLength==this.value.length) MyTabulazione(this);";
	 			   $this->SetJS($onKeyup,'onkeyup',false);
	 			   }
	 	 	else $JQ->set_oncomplete("function(){MyTabulazione(self.get(0));}",true);

	 return $this;
  }

  /**
   * @ignore
   */
  protected function &rimuovi_spazi_doppi(&$valore){
      $valore=trim((string) $valore);
      if(!$this->rimuovi_spazi_doppi) return $valore;
      $sost=false;
      do{
          $valore=str_replace('  ',' ',$valore,$sost);
      } while ($sost>0);
      return $valore;
  }

  /**
   * Disabilita completamente la pulizia dei tag
   *
   */
   public function unset_striptags(){
  	 $this->unsetstriptags=true;
  }

 /** @ignore*/
  function &striptags($testo) {
  	   if (!$this->unsetstriptags) $testo=preg_replace('@<[a-zA-Z]{1,10}[^<]*>@USs','',$testo);
  	   return $testo;
  	}


 Function &get_value_db() {
 						$valore= $this->get_value();
 						if(isset($this->molteplicita['separator'])) {
 						             $valore=$this->implode_values(explode($this->molteplicita['separator'],$valore));
 						             }
 						return $valore;
 						}


 function &get_value() {
 						$valore= $this->striptags(parent::get_value());
 						if ($this->MaiMin=='maiuscolo') $valore=self::changeCase($valore,'m');
 						if ($this->MaiMin=='minuscolo') $valore=self::changeCase($valore,'l');
 						if(isset($this->molteplicita['separator']) && $this->molteplicita['separator']!='') $valore=implode($this->molteplicita['separator'],$this->explode_value($valore));
 						return $valore;
 					  }
        
 					  
        /**
         * @ignore
         */
  public function set_value($valore) {   
                                $valore=$this->rimuovi_spazi_doppi($valore);
                                if ($this->MaiMin=='maiuscolo') $valore=self::changeCase($valore,'m');
                                if ($this->MaiMin=='minuscolo') $valore=self::changeCase($valore,'l');
 								$valore=stripslashes($valore);
 								$this->attributi['value']=$this->striptags($valore);
 								return $this;
 							 }


     /**
	* Restituisce la lunghezza max impostata
      *  @return integer
      */
       public function get_maxlength() { return $this->maxlength; }


  /**
	* Setta la lunghezza min del campo, da verificare nel metodo Errore
	* @param	integer $minlength  
  */
   public function set_minlength($minlength) {  $this->minlength=$minlength; return $this;}


  /**
	* Restituisce la lunghezza min impostata
  *  @return integer
  */
   public function get_minlength() { return $this->minlength; }

  /**
   * @ignore
   */
  protected function implode_values($values){
      if($this->molteplicita['delim']) 
            foreach ($values as $i=>&$v) {
                    $v=trim(str_replace(array("\r","\t","\n"),array('','',''),$v));
                    if($v==='') unset($values[$i]);
                            else {
                                $v=str_replace($this->molteplicita['delim'],$this->molteplicita['quote'].$this->molteplicita['delim'],$v);
                                $v=$this->molteplicita['delim'].$v.$this->molteplicita['delim'];
                                }
                    }
      
     return implode($this->molteplicita['separator'],$values);
  }
  
  
 
  
  /**
   * @ignore
   */
  protected function explode_value($value){
      $value=trim((string) $value);
      if($this->molteplicita['delim']) 
                { $values=explode($this->molteplicita['delim'].$this->molteplicita['separator'].$this->molteplicita['delim'],$value);
                  foreach ($values as $i=>&$v) {
                                $v=str_replace($this->molteplicita['quote'].$this->molteplicita['delim'],$this->molteplicita['delim'],$v);
                                if($v[0]==$this->molteplicita['delim']) $v[0]=' ';
                                if($v[strlen($v)-1]==$this->molteplicita['delim']) $v[strlen($v)-1]=' ';
                                $v=trim((string) $v);
                                if($v==='') unset($values[$i]);
                                }
                  return array_values($values);
                }
        return explode($this->molteplicita['separator'],$value);
  }
  

   public function get_errore_diviso() {
  	 if (!$this->molteplicita['n']) return $this->get_errore_diviso_singolo();
  	 					else {
  	 					    $values=explode($this->molteplicita['separator'],trim((string) $this->get_value()));
  	 						foreach ($values as $i=>&$val)  {$val=trim((string) $val);
  	 														if ($val==='') unset($values[$i]);
  	 													   }
  	 						$values=array_unique($values);
  	 						if ($this->molteplicita['n']>0 && is_array($values) && count($values)>$this->molteplicita['n']) return array("non può contenere più di %1% valori diversi",$this->molteplicita['n']);
  	 						
  	 						$compact_value=implode($this->molteplicita['separator'],$values);
  	 						$errore=null;
							$class=clone ($this);
							$class->set_molteplicita(0);
							foreach ($values as $i=>$valore)
							                           {
							                            $class->set_value($valore);
							                            $errore=$class->get_errore_diviso_singolo();
							                            if($errore) 
							                                     {$errore=array("%1% al %2% valore",$errore,myInt::ordinale($i+1));
							                                      break;
							                                     }
													   }
							if(!$errore &&
			     					       $this->notnull &&
				    				       strlen($compact_value)==0) $errore=array('non può essere nullo');
							
				    	    if(!$errore &&
				    				           $this->molteplicita['total_maxlength'][0] &&
				    				           strlen($compact_value)>$this->molteplicita['total_maxlength'][0]) $errore=array('non può contenere più di %1% caratteri',$this->molteplicita['total_maxlength'][0]);
				    	    if (!$errore) $this->set_value($compact_value);
						    }
  	 					  
    
	return $errore;
  }



  /**
   * Imposta la possibilità di inserire piu' valori nello stesso campo
   * Di default la lunghezza max per singolo valore diventa la lunghezza precedentemente impostata,
   * e quella complessiva (se omessa) e' la molteplicita * la lunghezza max (piu' i divisori).
   * <code>
   *  di conseguenza se un campo nasce di 50 caratteri e
   *  vogliamo metterci dentro 3 valori al piu' 20 cadauno,
   *  il che potrebbe portare ad una lunghezza max 60>50 e comunque inaccettabile
   *
   *  $f=new myform();
   *  $f->add_campo(new myemail('email'))->set_maxlength(20)  //max 20 per il campo
   * 									 ->set_molteplicita(3,",'\",50) //20 diventa la lunghezza del singolo valore e 50 complessivi (compresi divisori)
   *
   *  .....
   *  echo $f->campo('email')->get_value() //in questo modo inserendo pollo, l'ala avremo: 'pollo','l\'ala'
   * </code>
   *
   * @param int $max numero massimo di mail, se omesso o 0 non viene tenuto in considerazione
   * @param string[1..3] $separaQuota il primo carattere è il separatore il secondo se presente indica il carattere in cui racchiudere i valori es '" il terzo es \ è il carattere usato per quotare i caratteri usati per racchiudere ma anche presenti nel valore di default è \
   * @param int   $maxlength Complessiva massima lunghezza complessiva contando tutti i valori e relativi divisori
   * @param bool|myJQTagIt  $grafica  direttiva se usare elenco javascript o no, incompatibile con $textarea=true
   * @param bool  $textarea forza la visualizzazione in textarea (se true setta $grafica a false)
   * @return myText
   */
   public function set_molteplicita($max=0,$separaQuota=';',$maxlengthComplessiva='',$grafica=true,$textarea=false){
  	if($max<1) {$this->molteplicita=array('grafica'=>null,'total_maxlength'=>null,'use_textarea'=>null,'n'=>null,'delim'=>null,'quote'=>null,'total_maxlength'=>null,'size'=>null);return $this;}
  	$this->molteplicita['grafica']=$grafica;
  	$this->molteplicita['n']=$max;
  	if($textarea)  {$this->molteplicita['use_textarea']=true;
  					$this->molteplicita['grafica']=false;
  					$separaQuota="\n";
  					}
  	$chardelimquote=0;
  	$this->molteplicita['separator']=$separaQuota[0];
  	if(strlen($separaQuota)==2) {$this->molteplicita['delim']=$separaQuota[1];
  	                             $this->molteplicita['quote']='\\';
  	                             $chardelimquote=(2+3)*$max;
  	                             }
  	                             
  	if(strlen($separaQuota)==3) $this->molteplicita['quote']=$separaQuota[2];
  	//if( $this->molteplicita['quote']=='\\') $this->molteplicita['quote']='\\\\';
  	if (!$maxlengthComplessiva)  $this->molteplicita['total_maxlength']=array($this->maxlength*$max+$max-1+$chardelimquote,isset($this->attributi['size'])?$this->attributi['size']:null);
                          	else $this->molteplicita['total_maxlength']=array($maxlengthComplessiva,isset($this->attributi['size'])?$this->attributi['size']:null);
  					   	    
  	$this->set_value($this->get_value());				   	    
   	return $this;
  }

	 	  
	 /**
	  * Permette  di cambiare il funzionamento della grafica JS di myText::set_molteplicita() su determinati eventi
	  * NON APPLICABILE IN CASO DI USO myJQTagIt O METODO set_mask() 
	  * <code>
	  *   $t=new myText('campo');
	  *   $t->set_molteplicita(5);
	  *   //Di norma valore non si usa perche' il valore passato coincide con il testo digitato
	  *   //In questo caso l'elenco visualizza il testo digitato ma al server si manda il testo seguito da *
	  *   $t->set_js_eventi_molteplicita('onupdate','function(testo){return [testo,testo+"*'";}');
	  *   //se la funzione ondelete restituisce false se ne blocca l'effetto, in questo caso si cancella solo quello che inizia per ciao 
	  *	  $t->set_js_eventi_molteplicita('ondelete','function(testo){return testo.indexOf("ciao")==0);
	  * </code>
	  * 
	  * @param 'onupdate'|'ondelete' $evento indica 
	  * @param string $js codice javascript
	  * 
	  */	  
	  public function set_js_eventi_molteplicita($evento,$js) {
	 	if(!$this->molteplicita['n']) return false;
	 	$this->molteplicita['events'][$evento]=$js;
	 	return $this;
	 } 	  

	 
	 /**
	  * Restiuisce/setta icona da mostrare al lato del campo testo che , se cliccata aggiunge quanto digitato all'elenco
	  * Se usata prima di myText::set_molteplicita() non ha effetto e restituisce false
	  * NON APPLICABILE IN CASO DI USO myJQTagIt O METODO set_mask()
	 * <code>
	  *   $t=new myText('campo');
	  *   $t->set_molteplicita(5);
	  *	  $t->get_obj_icon_molteplicita('');//Imposta l'icona a stringa vuota, di fatto la toglie
	  *   //oppure
	  *   $t=new myText('campo');
	  *   $t->set_molteplicita(5);
	  *   $icona=$t->get_obj_add_icon_molteplicita();
	  *   $icona->set_tooltip('Clicca qui per aggiungere');
	  * </code>
	  * @param myIcon|string $toSet se inserito si valorizza con il valore passato 
	  * @return boolean|myField
	  */
	 function &get_obj_icon_add_molteplicita($toSet=null){
	 	if(!$this->molteplicita['n']) return false;
	 	if(count(func_get_args())==1) return $this->molteplicita['icon']=$toSet;
	 	if(isset($this->molteplicita['icon'])) return $this->molteplicita['icon'];
	 	$this->molteplicita['icon']=new myIcon("/".self::get_MyFormsPath()."icone/insert.png",$this->trasl('Premere qui per aggiungere un valore all\'elenco.'),'iconMolteplicita_class__'.$this->get_id());
	 	$this->molteplicita['icon']->set_id('Molteplicita_icon__'.$this->get_id());
	 	$this->molteplicita['icon']->set_attributo('title',$this->trasl('Premere qui per aggiungere un valore all\'elenco.'));
	 	$this->molteplicita['icon']->set_style('cursor','hand');
	 //	$this->molteplicita['icon']->add_myJQuery(new myJQTooltip())->set_fumetto();
	 	return $this->molteplicita['icon'];
	 	}	  	  
	 	  
	 	
	 	/**
	 	 * Restiuisce/setta icona da mostrare al lato del campo testo che , se cliccata aggiunge quanto digitato all'elenco
	 	 * Se usata prima di myText::set_molteplicita() non ha effetto e restituisce false
	 	 * NON APPLICABILE IN CASO DI USO myJQTagIt O METODO set_mask()
	 	 * <code>
	 	 *   $t=new myText('campo');
	 	 *   $t->set_molteplicita(5);
	 	 *	  $t->get_obj_icon_molteplicita('');//Imposta l'icona a stringa vuota, di fatto la toglie
	 	 *   //oppure
	 	 *   $t=new myText('campo');
	 	 *   $t->set_molteplicita(5);
	 	 *   $icona=$t->get_obj_del_icon_molteplicita();
	 	 *   $icona->set_tooltip('Clicca qui per rimuovere');
	 	 * </code>
	 	 * @param myIcon|string $toSet se inserito si valorizza con il valore passato
	 	 * @return boolean|myField
	 	 */
	 	function &get_obj_icon_del_molteplicita($toSet=null){
	 	    if(!$this->molteplicita['n']) return false;
	 	    if(count(func_get_args())==1) return $this->molteplicita['icondel']=$toSet;
	 	    if(isset($this->molteplicita['icondel'])) return $this->molteplicita['icondel'];
	 	    $this->molteplicita['icondel']=new myIcon("/".self::get_MyFormsPath()."icone/delete.png",$this->trasl('Per cancellare un valore fare click nell\'elenco e poi premere questa icona'),'iconMolteplicita_class__'.$this->get_id());
	 	    $this->molteplicita['icondel']->set_id('Molteplicita_icondel__'.$this->get_id());
	 	    $this->molteplicita['icondel']->set_attributo('title',$this->trasl('Per cancellare un valore fare click nell\'elenco e poi premere questa icona'));
	 	    $this->molteplicita['icondel']->set_style('cursor','hand');
	 	    //	$this->molteplicita['icon']->add_myJQuery(new myJQTooltip())->set_fumetto();
	 	    return $this->molteplicita['icondel'];
	 	}
	 	
	 	
	 	/**
	 	 * Restiuisce/setta il campo testo in cui inserire i dati da aggiungere all'elenco
	 	 * Se usata prima di myText::set_molteplicita() non ha effetto e restituisce false
	 	 * NON APPLICABILE IN CASO DI USO myJQTagIt O METODO set_mask()
	 	 * <code>
	 	 *   $t=new myText('campo');
	 	 *   $t->set_molteplicita(5);
	 	 *  
	 	 *   //A questo punto 
	 	 *   $testo=new myText('mio_campo');
	 	 *   $testo->add_myJQuery(new myJQTooltip())->set_fumetto();
	 	 *   $t->get_obj_text_molteplicita($testo); //forza l'uso di questo myText
	 	 *   
	 	 *   //stesso effetto facendo
	 	 *   $t->get_obj_text_molteplicita()->add_myJQuery(new myJQTooltip())->set_fumetto();
	 	 *   
	 	 * </code>
	 	 * @param myIcon|string $toSet se inserito si valorizza con il valore passato
	 	 * @return boolean|myField
	 	 */
	 function &get_obj_text_molteplicita( $toSet=null){
	 	if(!$this->molteplicita['n']) return false;
	 	if(count(func_get_args())==1) return $this->molteplicita['Txt']=$toSet;
	 	if(isset($this->molteplicita['Txt'])) return $this->molteplicita['Txt'];
	 	$classe=get_class($this);
	 	$this->molteplicita['Txt']=new $classe("",'','Molteplicita_Txt_class__'.$this->get_id());
	 	$this->molteplicita['Txt']->set_id('Molteplicita_Txt__'.$this->get_id());
	 	$this->molteplicita['Txt']->set_tooltip($this->trasl('Scrivere qui e poi premere invio per aggiungere un valore all\'elenco.'));
	 //	$this->molteplicita['Txt']->add_myJQuery(new myJQTooltip())->set_fumetto();
	 	return $this->molteplicita['Txt'];
	 	}	  

	 	
	 	/**
	 	 * Restiuisce/setta il campo elenco in cui verranno aggiunti i dati digitati
	 	 * Funziona in modo analogo a myText::get_obj_text_molteplicita() e myText::get_obj_icon_add_molteplicita()  e myText::get_obj_icon_del_molteplicita()
	 	 * NON APPLICABILE IN CASO DI USO myJQTagIt O METODO set_mask()
	 	*/
	 function &get_obj_elenco_molteplicita(MySelect $toSet=null){
	 	if(!$this->molteplicita['n']) return false;
	 	if(count(func_get_args())==1)return $this->molteplicita['Elenco']=$toSet;
	 	if(isset($this->molteplicita['Elenco'])) return $this->molteplicita['Elenco'];
	 	$this->molteplicita['Elenco']=new mySelect('',array(),'','Molteplicita_Elenco_class__'.$this->get_id());
	 	$this->molteplicita['Elenco']->set_attributo('size',$this->molteplicita['n'])
	 			->set_style('width','100%')
	 			->set_style('overflow-y','hide')
	 			->set_id('Molteplicita_Elenco__'.$this->get_id())
	 			->set_tooltip($this->trasl('Per eliminare un elemento selezionarlo nell\'elenco poi fare click sull\'icona di cancellazione o premere il tasto canc'));
	// 	$this->molteplicita['Elenco']->add_myJQuery(new myJQTooltip())->set_fumetto();
	 	return $this->molteplicita['Elenco'];
  		 
	 }
	 
	 /** @ignore */
	 protected function _get_html_show($pars, $tag = 'span', $content = NULL){
	 	if(!$this->molteplicita['n']) return parent::_get_html_show($pars);
	 	if ($pars['campo'] && !$this->get_attributo['disabled']) $out=$this->_get_html_hidden($pars);
	 	$valori=explode($this->molteplicita['separator'],$this->get_value());
	 	if($valori && $valori!=array(0=>''))
	 	           { if(count($valori)==1) return $this->get_value();
	 	             foreach ($valori as &$valore) $valore="<li>$valore</li>";
	 	             $ul=$this->_get_html_show_obj($pars,'ul',implode('',$valori));
	 				 return $ul.$out;
	 				}
	 }
  
 
  
   public function get_Html () {
        $get_html=isset($this->Metodo_ridefinito['get_Html']['metodo'])?$this->Metodo_ridefinito['get_Html']['metodo']:null; if ($get_html!='') return $this->$get_html(isset($this->Metodo_ridefinito['get_Html']['parametri'])?$this->Metodo_ridefinito['get_Html']['parametri']:null);
  	    $maxlength_singolo=$this->get_maxlength();
  	    if(!$maxlength_singolo && isset($this->attributi['maxlength']))  $maxlength_singolo=$this->attributi['maxlength'];
  	    if(!$maxlength_singolo && isset($this->maxlengthcomplessiva[1])) $maxlength_singolo=$this->maxlengthcomplessiva[1];
  	    if (isset($this->molteplicita['total_maxlength']) && 
  	        $this->molteplicita['total_maxlength'] && $this->maxlengthcomplessiva) 
  	                                     $this->set_maxlength($this->molteplicita['total_maxlength'][0],$this->maxlengthcomplessiva[1]);
  	    if ( (!isset($this->molteplicita['grafica']) || !$this->molteplicita['grafica']) && 
  	         (!isset($this->molteplicita['use_textarea']) || !$this->molteplicita['use_textarea'])
  	        ) 
  		            {   $this->html5Settings();
  		                return parent::get_html();
  		            }
  		 		else
  		 			{ 
  					 $this->set_style('overflow-y','hidden');
  					 $this->set_style('overflow-x','auto');
  					 $toolTip=$this->get_Tooltip();
  					 //$this->set_value(str_replace("\n\n","\n",str_replace($this->molteplicita['separator'],$this->molteplicita['separator']."\n",$this->get_value())));

		  			 $textarea="{$this->jsAutotab()}<textarea id='{$this->get_id()}__textarea' cols='".($maxlength_singolo+2)."'  rows='{$this->molteplicita['n']}' ".parent::stringa_attributi(array('value','opzioni','maxlength','size')).">{$this->get_value_html()}</textarea>";
		  			 
  		 			 if ($this->molteplicita['use_textarea']) return $textarea;
  		 			
  		 			 if($this->masked && $this->molteplicita['grafica'] && !($this->molteplicita['grafica'] instanceof myJQuery)) $this->molteplicita['grafica']=new myJQTagIt();
  		 			 if($this->molteplicita['grafica'] instanceof myJQuery) 
  		 			        {   //$valori=$this->explode_value($this->get_value());
  		 			       
  		 			           // $this->set_value(implode($this->molteplicita['separator'],$valori));
  		 			            $this->add_myJQuery(new  myJQInputMask(''));
  		 			            $jq=$this->add_myJQuery($this->molteplicita['grafica']);
  		 			            /*if($jq instanceof \myJQTagEditor) {
                                      		 			            if($maxlength_singolo) $jq->maxLength=$maxlength_singolo;
                                      		 			            $jq->delimiter="'".$this->molteplicita['separator']."'"; 
                                      		 			            if($this->MaiMin=='minuscolo') $jq->forceLowercase=true;
                                      		 			            if($toolTip) $jq->placeholder="'".addslashes($toolTip)."'";
  		 			                                             }*/
  		 			            if($jq instanceof myJQTagIt) {  if(isset($this->attributi['readonly']) &&$this->attributi['readonly']) $jq->readOnly=true; 
                                                                $jq->allowDuplicates=false;
                                                                $jq->caseSensitive =false;
                                                                $jq->tagLimit =$this->molteplicita['n'];
                                                                $jq->singleFieldDelimiter="'".$this->molteplicita['separator']."'";
                                                                $reprocess='';
                                                                if($jq->preprocessTag) $reprocess.="val=( {$jq->preprocessTag} )(val);";
                                                                if($this->MaiMin)      $reprocess.="val=val.".($this->MaiMin=='minuscolo'?'toLowerCase()':'toUpperCase()').";";
                                                                
                                                                if($reprocess) $jq->preprocessTag ="function(val) { $reprocess; return val; }";
                                                                
                                                                $reprocess='';
                                                                if($jq->beforeTagAdded)       $reprocess.="if(!( {$jq->beforeTagAdded} )(val)) return false;";
                                                                $chkjs= $this->get_js_chk(";");
                                                                $chkjs=self::utf8_decode_recursive($chkjs);
                                                                if($chkjs) $reprocess=" var errore= (function(valore){
                                                                                                                        $chkjs
                                                                                                                     })(val);  
                                                                                        if(errore) return false;
                                                                                        ";
                                                                if($maxlength_singolo) $reprocess.="if(val.length>{$maxlength_singolo}) return false; ";
                                                                $jq->beforeTagAdded ="function(event,ui) { if (!ui.duringInitialization) {var val=ui.tagLabel;  $reprocess } }";
                                                                
                                                                
                              		 			                if($toolTip) $jq->placeholderText="'".addslashes($toolTip)."'";
                              		 			                $jq->add_code(myCSS::get_css_jscode('.tagit-new input{width:'.strlen($jq->placeholderText).'em}',true));
  		 			                                          }
                                $this->html5Settings();
  		 			            return parent::get_html();
  		 			        }
  		 			 
  		 			 
  		 			 //if(!$this->myFields['static']['common']) {$jsCommon=$this->get_js_common();$this->myFields['static']['common']=true;}
  		 			 $jsCommon=$this->get_js_common();
  		 			 
  		 			 $this->set_hidden();
  		 			 $valori=$this->explode_value($this->get_value());
  		 			 $x=$this->get_obj_elenco_molteplicita();
  		 			 
  		 			 if(
  		 			    (!isset($this->attributi['readonly']) || !$this->attributi['readonly']) && 
  		 			    (!isset($this->attributi['disabled']) || !!$this->attributi['disabled']) 
  		 			   )
  		 			        {
	 			            $testo=$this->get_obj_text_molteplicita();
	 			            $testo->set_attributo('size',!isset($this->attributi['size'])?null:$this->attributi['size'])
	 			                  ->set_attributo('maxlength',!isset($this->attributi['maxlength'])?null:$this->attributi['maxlength'])
          		 	   	 		  ->unset_attributo('name')
          		 			      ->set_js("return myMultiTextJSkeypress(event,'{$this->get_id()}',document.getElementById('{$x->get_id()}'),this, this);",'onkeydown');
          		 			 //		->set_js("if (this.options[this.selectedIndex].text).replace(' ','')=='') this.value=this.options[this.selectedIndex].text",'onblur');
	 			             }
	 			             $blk='';
	 			             for ($i=0;$i<($testo?(isset($this->molteplicita['n'])?$this->molteplicita['n']:0):count($valori));$i++)
	 			 					   {$blk.=' ';
	 			 					    $x->add_Opzione((isset($valori[$i])?$valori[$i]:"").$blk,$i);
	 			 					   }
          		 			 $x->unset_attributo('name');
          		 			 if(!$testo)
          		 			 		{$x->set_attributo('size',count($valori));
          		 			 		 if($this->attributi['disabled']) {$x->set_disabled();
          		 			 		 							   	   $this->set_value('');
          		 			 		 								  }
          		 			 		}
          		 			 	else {$x->set_js("document.getElementById('{$testo->get_id()}').value=trim(myMultiTextJSElements['{$this->get_id()}'].onupdate(this.options[this.selectedIndex].text,this.options[this.selectedIndex].value)[0]);document.getElementById('{$testo->get_id()}').focus();",'ondblclick')
          		 			 	        ->set_js("return myMultiTextJSkeypress(event, '{$this->get_id()}' , this ,  document.getElementById('{$testo->get_id()}') ,this);",'onkeydown');
          		 			 	     }
        
          		 			 $icon=$this->get_obj_icon_add_molteplicita();	
          		 			 if($testo && $icon) $icon->set_js("myMultiTextJS('{$testo->get_id()}',myMultiTextJSElements['{$this->get_id()}'],'add')");
          		 			 
          		 			 $icondel=$this->get_obj_icon_del_molteplicita();
          		 			 if($testo && $icondel) $icondel->set_js("myMultiTextJS('{$testo->get_id()}',myMultiTextJSElements['{$this->get_id()}'],'remove')");
        
          		 			 $output=  (str_replace(array("\r","\t"),'',$this->send_html(parent::get_html()).
          		 			 					($testo?"{$testo->get_html()}".($icon?$icon:'').($icondel?$icondel:'').
          		 			 					"<br />":'').
          		 			 					$x->get_html()));
          		 			 
          		 			 if(!isset($this->molteplicita['events']['ondelete']) ) $this->molteplicita['events']['ondelete']='';
          		 			 if(!isset($this->molteplicita['events']['onupdate']) ) $this->molteplicita['events']['onupdate']='';
          		 			 
          		 			 return "<div id='{$this->get_id()}__molteplicita'>$jsCommon
          		 			 ".(!$testo?'':"<script type='text/javascript'>
          		 			        myMultiTextJSElements['{$this->get_id()}']=
        				  		 			 {   'messaggi':{'inelenco':'".addslashes($this->trasl("già  inserito nell'elenco"))."',
        				  		 			 				 'pieno':'".addslashes($this->trasl("Non si possono aggiungere ulteriori valori"))."',
        				  		 			 				 'assente':'".addslashes($this->trasl("Nessun valore selezionato"))."'
          		 											},
        				  		 			 	'ids':['{$x->get_id()}','{$this->get_id()}','{$testo->get_id()}'],
        				  		 			 	'separator': \"".str_replace(array('"',"\n","\t","\r"),array('\"','\\n','\\t','\\r'),$this->molteplicita['separator'])."\",
        				  		 			 											 'ondelete':".($this->molteplicita['events']['ondelete']?$this->molteplicita['events']['ondelete']:'function(testo){return  [testo,testo]}').",
        				  		 			 											 'onupdate':".($this->molteplicita['events']['onupdate']?$this->molteplicita['events']['onupdate']:'function(testo){return  [testo,testo]}')."
        				  		 			 }
          		 			 </script>"). $output."<noscript>$textarea</noscript>
          		 			 ".(!$testo?'':"<script type='text/javascript'>
          		 			 		myMultiTextJSPreset(myMultiTextJSElements['{$this->get_id()}']);
          		 			  </script>").
          		 			 "</div>";
  		 			}
	 	  }


}