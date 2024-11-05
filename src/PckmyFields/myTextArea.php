<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myTextArea.
 */

namespace Gimi\myFormsTools\PckmyFields;



class myTextArea extends myText {
/** @ignore */
protected $scroll=array('overflow-x'=>'auto','overflow-y'=>'auto'),$rows,$cols,$rimuovi_spazi_doppi=false,
          $vietati=',submit,button,input,textarea,select,object,param,body,script,applet,frame,iframe,html,head,title,meta,form,!doctype,!--,link,var', //DEVE INIZIARE CON VIRGOLA
           $tagammessi='';


	  /**
  * 
	  *
	* @param	 string $nome E' il nome del campo
	  * @param	 string $valore Valore da assegnare all'attributo 'value'
	  * @param	 string $classe e' la classe css da utilizzare
	  */
		   public function __construct($nome,$valore='',$classe='') {
				myField::__construct($nome,$valore);
				if ($classe) $this->set_attributo('class',$classe);
				$this->set_MyType('MyTextArea');
				$this->set_attributo('cols',80);
				$this->set_attributo('rows',6);
		  }

		  /**
	* Setta il numero di colonne della textarea
			* @param	integer $cols
		  */
		   public function set_cols($cols) {
		  			  $this->cols=$cols;
					  $this->set_attributo('cols',$cols+1);
					  return $this;
		  }

		  /**
	* Setta il numero di righe della textarea
			* @param	integer $rows
		  */
		   public function set_rows($rows) {
					 $this->set_attributo('rows',$this->rows=$rows);
					 return $this;
		  }

		/**
	* restituisce numero di colonne della textarea
			* @return	integer
	  		*/
		   public function get_cols($cols) { return  $this->cols;}

		  /**
	* Restituisce il numero di righe della textarea
			* @return	integer
	  	  */
		   public function get_rows() { return $this->rows; }



		   public function get_Html () {
		  	 		 foreach ( $this->scroll as $prop=>$val) $this->set_style($prop,$val);
		  			 $get_html=isset($this->Metodo_ridefinito['get_Html']['metodo'])?$this->Metodo_ridefinito['get_Html']['metodo']:null; if ($get_html!='') return $this->$get_html(isset($this->Metodo_ridefinito['get_Html']['parametri'])?$this->Metodo_ridefinito['get_Html']['parametri']:null);
		  			 $x=$this->jsAutotab()."<textarea ".parent::stringa_attributi(array('value','opzioni','maxlength','size')).">";
					 $x.=$this->get_value_html();
					 return $this->send_html("$x</textarea>");
	 	  }

	 	  /**
	 	   * Abilita/disabilita lo scroll del testo
	 	   *
	 	   * @param boolean $orizzontale
	 	   * @param boolean $verticale
	 	   */
	 	   public function set_scroll($orizzontale=true,$verticale=true) {
	 	  	  $this->scroll['overflow-x']=($orizzontale?'scroll':'hidden');
	 	  	  $this->scroll['overflow-y']=($verticale?'scroll':'hidden');
	 	  	  return $this;
	 	  }

	  /** @ignore*/
   	    public function _get_html_show($pars, $tag = 'span', $content = NULL){
   	 	 if ($pars['campo'] && !$this->get_attributo['disabled']) $out=$this->_get_html_hidden($pars);
   	 	 $div=$this->_get_html_show_obj($pars,'div',strip_tags($this->get_value())!==$this->get_value()?$this->get_value():nl2br($this->get_value()));
		 $div->set_style('display', 'inline');
		 return $div.$out;
     	 }


	 /**
	* Non attiva in questa classe
			*/
			 public function set_autotab () {return $this;
		  }


	 /**
			* Setta i tag non ammessi e/o eventuali loro parametri, non ha effetto se invocato in concomitanza di set_TagAmmessi
			*
			* @param string $stringa
			*
			* es. 'iframe,table[bgcolor|background]' esclude il tag iframe ed i parametri bgcolor e background del tag table
			* In ogni caso vengono eliminati 'submit,button,input,textarea,select,object,param,body,script,applet,frame,iframe,html,head e tutti gli attibuti che iniziano per on
			*/

		   public function set_TagVietati($stringa){
					  $this->vietati.=','.$stringa;
					  return $this;
		  }


		  /**
		   * Setta i tag  ammessi e/o eventuali loro parametri annulla gli effetti di set_TagVietati()
		   *
		   * @param string $stringa
		   *
		   * es. 'iframe,table[bgcolor|background]' esclude il tag iframe ed i parametri bgcolor e background del tag table
		   * In ogni caso vengono eliminati 'submit,button,input,textarea,select,object,param,body,script,applet,frame,iframe,html,head e tutti gli attibuti che iniziano per on
		   */

		   public function set_TagAmmessi($stringa){
		  	$this->tagammessi.=','.$stringa;
		  	return $this;
		  }

		  function &get_value_DB(){
		      return $this->attributi['value'];
		  }
		  
		  function &get_value(){
		      return $this->attributi['value'];
		  }
		  
		  
		  /**
		   * @ignore
		   */
		   public function set_value($valore) {   
		      $valore=$this->rimuovi_spazi_doppi($valore);
		      $this->attributi['value']=$this->striptags(stripslashes(trim((string) $valore)));
		      return $this;
		  }

			/** @ignore*/
		  function &striptags($x,$qualivietati='') {
		  	if($this->unsetstriptags) return $x;
		  	if($this->tagammessi) {$qualivietati=$this->tagammessi;
		  							$escludi=false;
		  							}
		  		else{if (!$qualivietati) $qualivietati=$this->vietati;
					 $escludi=true;
		  			}

		  	//Costruisce array Vietati con tag/attributi non consentiti
		  	$vietati=explode(',',strtolower($qualivietati));
			foreach ($vietati as $i=>$tags)
						  if($tags)
								 {$Vietati=array();
			                      $tags=explode('[',$tags);
								  if (!isset($Vietati[$tags[0]])) $Vietati[$tags[0]]=array();
								  if (!isset($tags[1])) {$Vietati["/$tags[0]"]=array();
								                         continue;
								                        }
								  $Vietati[$tags[0]]=array_merge(array(),$Vietati[$tags[0]]);
								  foreach (explode('|',str_replace(']','',$tags[1])) as $attrib) 
								                        if($attrib) $Vietati[$tags[0]][$attrib]=$attrib;

								 }


			//while ($x!=($new=preg_replace('/<(\w+)[\r\t\n]([^>]*)>/','<\1 \2>',$x))) $x=$new;
			//while ($x!=($new=preg_replace('/<([^>]*)([\r\t\n]+)([^>]*)>/','<\1 \3>',$x))) $x=$new;
			$patterns=array();
			preg_match_all('/<([^>]+)>/is',$x,$patterns); //isola tutti i tag aperti

			if ($patterns[1]) //se trovati tag
				foreach ($patterns[1] as $i=>$tag)
				   if(trim((string) $patterns[0][$i])){
				    $da=$in=array();
					$tag=explode(' ',$tag,2);   //isola il nome del tag
					$tag[0]=trim(strtolower($tag[0])); //lo rende minuscolo e lo pulisce
					$newtag='';	//init newtag

					if (!(!isset($Vietati[$tag[0]]) xor $escludi) || count($Vietati[$tag[0]])) //se quel tag non è vietato o se ne vietano solo alcuni attributi
						{
						$newtag='<'.trim((string) $tag[0]); //comincia a costruire il nuovo tag
						if(!isset($tag[1])) $tag[1]='';
						$tag[1]=trim((string) $tag[1]);
						if ($tag[1]!=='')
								{
								 $tag[1]=' '.$tag[1].' ';  //aggiunge spazi ad elenco attributi
								 //while ($tag[1]!=($new=preg_replace('/[\s\r\t\n]*=[\s\r\t\n]*/','=',$tag[1]))) $tag[1]=$new; //elimina eventuali spazi o caratteri speciali tra tutti gli uguali

								 $DaEliminare=array('/\son\w+="[^"]+"/is',    			 //individua attributi che iniziano per on delimitati da "
							     				'/\son\w+=\'[^\']*(\\\')[^\']*\'/is',     //individua \' in attributi che iniziano per on delimitati da '
					 							'/\son\w+=\'[^\']+\'/is',				 //individua attributi che iniziano per on delimitati da '
					 							'/\son\w+=[^\s]+ /is'					 //individua attributi che iniziano per on senza senza delimitatore
					 							);
                                
								if($escludi) {
								    if (isset($Vietati[$tag[0]]) && is_array($Vietati[$tag[0]]) && count($Vietati[$tag[0]]))
										foreach ($Vietati[$tag[0]] as $attrib) {
											$DaEliminare[]='/\s'.$attrib.'\s*=\s*"[^"]+"/is';
											$DaEliminare[]='/\s'.$attrib.'\s*=\s*\'[^\']*(\\\')[^\']*\'/is';
						 					$DaEliminare[]='/\s'.$attrib.'\s*=\s*\'[^\']+\'/is';
						 					$DaEliminare[]='/\s'.$attrib.'\s*=\s*[^\s]+ /is';
						 				}
								}
								else {
									$DaEliminare[]='/\s(^'.implode('|^',$Vietati[$tag[0]]).')\s*=\s*"[^"]+"/is';
									$DaEliminare[]='/\s(^'.implode('|^',$Vietati[$tag[0]]).')\s*=\s*\'[^\']*(\\\')[^\']*\'/is';
									$DaEliminare[]='/\s(^'.implode('|^',$Vietati[$tag[0]]).')\s*=\s*\'[^\']+\'/is';
									$DaEliminare[]='/\s(^'.implode('|^',$Vietati[$tag[0]]).')\s*=\s*[^\s]+ /is';
								}

					 			$tag[1]=preg_replace($DaEliminare,'',$tag[1]);
								}
						if(!isset($tag[1])) $newtag.=">";
						              else { 		
						                    $tag[1]=trim((string) $tag[1]);
                    						if ($tag[1]!=='') $tag[1]=" $tag[1]";
                    						$newtag.="$tag[1]>";
						                   }
						}
					$da[]=$patterns[0][$i];
					$in[]=$newtag;
				 }
			if(isset($da)) $x=str_replace($da,$in,$x);
            return $x;
		  }
}