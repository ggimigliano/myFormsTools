<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myPopUp.
 */

namespace Gimi\myFormsTools\PckmyFields;




class myPopUp extends myHidden  {
/** @ignore */
	protected $valori, $popup,$extraJSpopup,$add_hidden=true;

 
	/**
	  * 
	  * Questo oggetto permette di creare dei campi nascosti "ma visibili"
	  * Si puo' usare in alternativa ad un mySelect quando le opzioni sono veramente tante
	  * e si deve associare una Descrizione ad un codice
	  * Compare un myLink cliccando il quale si apre una popup in essa si sceglie il
	  * valore e automaticamente si chiude settando sia il codice che la descrizione
	  * nella finestra chiamante
	  *
	  * Supponiamo di voler inserire un campo in cui compaiano un cognome ed un nome
	  * ma che internamente associ una matricola.
	  *
	  * Nello script occorrerà scrivere
	  * <code>
	  * $valore_default=array('12345'=>'Gianluca Gimigliano'); //setto un valore di default
	  * $oggetto= new myPopUp("matricola",$valore_default);	 //istanzio campo con impostanto come default i miei dati
	  * $linkpopup=new myLink('/cercapersone.php','Click qui per cercare persona');  //creo il link da usare per aprire la popup
	  * $oggetto->set_popup($linkpopup); //aggancio il link da usare all'oggetto
	  *  echo $oggetto->get_html();
	  * </code>
	  *
	  *
	  * Nello script chiamato (nell'esempio e' cercapersone.php) gerrà passato in GET un parametro chiamato
	  * id_MyPopUp. Supponendo che il risultato della ricerca nella popup porti un elenco di nomi,
	  * questi dovranno essere della forma
	  * <code>
	  * echo "<li onclick='opener.MyFieldPopUPsetVal(\"$_GET[id_MyPopUp]\",\"13213\",\"Mario Rossi')\")'>Matricola: 13213 Nome:Mario Rossi</li>";
	  * echo "<li onclick='opener.MyFieldPopUPsetVal(\"$_GET[id_MyPopUp]\",\"32145\",\"Mario Bianchi\")'>Matricola: 32145 Nome:Mario Bianchi</li>";
	  * //il comando Javascript MyFieldPopUPsetVal risiede nel chiamante, per questo si invoca
	  * //attraverso opener.MyFieldPopUPsetVal() e riceve tre parametri:
	  * //	l'id del campo da settare (che e' passato in GET con il nome di id_MyPopUp)
	  * //	la chiave (in questo caso la matricola)
	  * //	la descrizione da far comparire (in questo caso cognome e nome)
	  * </code>
	  *
	  *
  	  * 
	  * @param	 string $nome E' il nome del campo
	  * @param	 array() $valore Codice=>Descrizione da assegnare come default
	  * @param	 string $classe e' la classe css da utilizzare
	  */
		   public function __construct($nome,$valore='',$classe='') {
				myHidden::__construct($nome,$valore,$classe);
				$this->set_MyType('MyPopUp');
				$this->Prevede_label=true;
		  }


	  /**
	  * @param	 array() $valore Codice=>Descrizione da assegnare come default
	  */
		   public function set_value($valore) {
			if (is_array($valore))
								{$this->valori=$valore;
								 foreach (array_keys($valore) as $k) return  parent::set_value($k);
								}
						 else return parent::set_value($valore);

		  }

		  /**
		  * return string descrizione attuale
		 */
		   public function get_Descrizione() {
		    $valore=$this->valori;
			foreach ($valore as $v) return $v;
		  }


		  /**
	* alias di get_Descrizione
		  * return string descrizione attuale
		 */
		   public function get_titolo() {
			return $this->get_Descrizione();
		  }


		   public function set_readonly($var = true) {
			$this->set_showonly();
		  }

		   public function set_disabled($var = true) {
			$this->set_showonly();
		  }

		   public function is_hidden() {
		  	return $this->Metodo_ridefinito['get_Html']['metodo']=='_get_html_hidden';
		  }

          /** @ignore */
          protected function _get_html_show($pars, $tag = 'span', $content = NULL){
           	 if ($pars['campo'] && !$this->attributi['disabled']) $out=$this->_get_html_hidden($pars);
           	 $span=$this->_get_html_show_obj($pars,'span',$this->get_titolo());
           	 return $span.$out;
           }



		  /**
		  * setta la popup sulla base del myLink che linka lo script da lanciare nella popup
		  * @param	 myLink $myLink E' il link
		  * @param	 int larg larghezza della popup
		  * @param	 int alt altezza della popup
		  * @param	 string extraJS comandi extra che debbono essere eseguiti nello javascript MyFieldPopUPsetVal
		  *
		  * es. vogliamo che la popup si chiuda automaticamente e ci sia un alert 'ciao' appena si fa una scelta
		  * <code>
		  *   $x->set_popup($miolink,'','','MyPopUp.close();alert("ciao"); '); //MyPopUp è la variabile globale javascript che individua la popup, cosi' come k e' il valore e v la descrizione del mypopup scelti
		  * </code>
		 */
		   public function set_popup(MyLink $myLink,$larg=200,$alt=300,$extraJS='') {
			$myLink->set_attributo('id',$this->get_id());

			$this->extraJSpopup=$extraJS;

			$this->popup=array('link'=>$myLink,'larg'=>$larg,'alt'=>$alt);
			return $this;
		  }


		/**
		  * elimina l'impostazione popup
		 */
		   public function unset_popup() {
				unset($this->popup);
		  }



     public static function js_always_top($forza_get=false) {
    	$f=new myField();
    	if (!isset($f->myFields['static']['js_src']['js_always_top']))
       				   {$f->myFields['static']['js_src']['js_always_top']=true;
    					return "<script  type='text/javascript'>
    								MyPopUp=null;
    								MyPredEvents=new Array(6);


    								function testFocuses(){
    								    if (MyPopUp!=null && !MyPopUp.closed)setTimeout(testFocuses,500);
    								    								else unsetFocuses();
       				   				}

    								function unsetFocuses(){
    								   MyPopUp=null;
    								   body=document.getElementsByTagName(\"body\")[0];
			    					   if(body){
			    					   			body.onfocus=MyPredEvents[0];
				 					   			body.onclick=MyPredEvents[1];
				 					   			body.onkeypress=MyPredEvents[2];
				 					   			}
				 					   this.window.onfocus=MyPredEvents[3];
				 					   this.window.onclick=MyPredEvents[4];
				 					   this.window.onkeypress=MyPredEvents[5];
    								}


									function setFocuses() {
									   if(MyPopUp==null) return;
									   MyPopUp.focus();
									   body=document.getElementsByTagName(\"body\")[0];
			    					   if(body){MyPredEvents[0]=body.onfocus;
			    					   			body.onfocus=myFocus;
			    					   			MyPredEvents[1]=body.onclick;
				 					   			body.onclick=myFocus;
				 					   			MyPredEvents[2]=body.onkeypress;
				 					   			body.onkeypress=myFocus;
				 					   			}
				 					   MyPredEvents[3]=this.window.onfocus;
				 					   this.window.onfocus=myFocus;
				 					   MyPredEvents[4]=this.window.onclick;
				 					   this.window.onclick=myFocus;
				 					   MyPredEvents[5]=this.window.onkeypress;
				 					   this.window.onkeypress=myFocus;

				 					   MyPopUp.focus();
				 					   testFocuses();

				 					}


				 					function myFocus(){
									   if(MyPopUp==null || MyPopUp.closed)
									   		{
									   		   return true;
									   		}
									 //  blur();
									   try { MyPopUp.moveBy(1,0);
									   		 MyPopUp.moveBy(-1,0);
									   		 MyPopUp.focus();
											  e = window.event;
									 		   if (e.cancelBubble)  e.cancelBubble = true;
											 	   		 		  else  e.stopPropagation();
										   	} catch (Exception) {}
									   return false;
		    						}
								 </script>";

    						  }
    }



	 public function js(){
	  if (!isset($this->myFields['static']['js_src'][get_class($this)]))
       {$this->myFields['static']['js_src'][get_class($this)]=true;
		return "<script  type='text/javascript'>
		    //<!--
		function MyFieldPopUPsetVal(id,k,v)
						{
					 	eval('MyFieldPopUPsetVal_'+id+'(id,k,v);');
						}

		function MyFieldPopUP(link,larg,alt,id_nascosto,value,left,top,extra) {
                         
                         if(!link.style.display || link.style.display!='hidden')
						  { 
                          var hyd='';
                              try{
    						   	  hyd=document.getElementById('hidden_'+id_nascosto);
                                  hyd=hyd.value;
                                 } catch (Exception) {hyd=value;}
                            try{
                                if(!extra) extra='directories=0,resizable=1,toolbar=0,menubar=0,location=0,scrollbars=1,titlebar=0';
    				  		 	MyPopUp= window.open(link.href+(hyd!=''?'&'+'val_MyPopUp='+hyd:''), 'popup', extra+',top='+top+',left='+left+',width='+larg+',height='+alt);
    						 	setFocuses();
    						 	MyPopUp.focus();
                                } catch (Exception) {}
						  }

						 try{window.event.returnValue = false;
								} catch (Exception) {}
						 try{window.event.cancelBubble = true;
								} catch (Exception) {}
						 try {window.event.preventDefault();
								} catch (Exception) {}
						 try{window.event.stopPropagation();
							   } catch (Exception) {}
                        try{
					            eval('link.href=mypopup_url_'+id_nascosto+';');
                            } catch (Exception) {}
					    }
		    //-->
		</script>
		";
    	}
	}

	/**
	 * Aggiunge un campo nascosto
	 * @param boolean $status
	 */
	 public function set_add_hidden_field($status=true){
	    $this->add_hidden=$status;
	    return $this;
	    
	}
	
     public function get_html() {
		 $get_html=isset($this->Metodo_ridefinito['get_Html']['metodo'])?$this->Metodo_ridefinito['get_Html']['metodo']:null; if ($get_html!='') return $this->$get_html(isset($this->Metodo_ridefinito['get_Html']['parametri'])?$this->Metodo_ridefinito['get_Html']['parametri']:null);

		 if($this->popup ) {
		 	$myLink=&$this->popup['link'];
 		 	$url=$myLink->get_Url();
		 	if (strpos($url,'=')) $url.="&id_MyPopUp=".$this->get_id();
						 	 else $url.="?id_MyPopUp=".$this->get_id();
			$myLink->set_attributo('Href',$url);
		 	$myLink->set_attributo("onmouseup","MyFieldPopUP(this,{$this->popup['larg']},{$this->popup['alt']},'{$this->get_id()}','{$this->get_value()}');return false;");
		 	$myLink->set_attributo("onkeyup","MyFieldPopUP(this,{$this->popup['larg']},{$this->popup['alt']},'{$this->get_id()}','{$this->get_value()}');return false;");
		 	
		 }

         
		 $js=$this->js();
		 $js.="<script  type='text/javascript'>
		          //<!--
		 			var mypopup_url_{$this->get_id()} =\"$url\";
					function MyFieldPopUPsetVal_{$this->get_id()}(id,k,v)
							{
							lbl=document.getElementById(id);
						    if(lbl.innerText) lbl.innerText=v;
							if(lbl.textContent) lbl.textContent=v;
							  try{	
                                      hyd=document.getElementById('hidden_'+id);
                					  hyd.value=k;
                                  } catch (Exception) {}
							".$this->extraJSpopup."
							}
		          //-->			    
			  </script>";

		if ( $this->popup) $js.=self::js_always_top();
        static $done=array();
        $id=$this->get_id();
        $html_hidden='';
        if((!isset($done[$id]) || !$done[$id]) && $this->add_hidden) 
                        {
		                  $this->set_attributo('id',"hidden_".$id);
		                  $html_hidden=parent::get_html();
		                  $this->set_attributo('id',$id);
		                  $done[$id]=true;
                        }
		                  

		$jsCommon=$this->get_js_common();//if(!$this->myFields['static']['common']) {$jsCommon=$this->get_js_common();$this->myFields['static']['common']=true;}

		return (!$this->con_js?'':$this->jsAutotab().$js.$jsCommon).
			   ($this->popup['link']?$this->popup['link']->get_html($this->get_Descrizione()):$this->get_Descrizione()).
			    $this->send_html($html_hidden);
	 }


		  /**
       	* Restituisce un messaggio se l'attributo value non soddisfa condizioni di notnull,maxlength,minlength
		  *  @return string
		  */
		  protected function get_errore_diviso_singolo() {
					 return myField::get_errore_diviso_singolo();
		  }
}