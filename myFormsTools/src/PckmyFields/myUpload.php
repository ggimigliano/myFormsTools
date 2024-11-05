<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myUpload.
 */

namespace Gimi\myFormsTools\PckmyFields;

class myUpload extends myUploadText {
/** @ignore */
protected $path,$file,$ext_ammesse=array(),$nomeAttuale, $descrizione, $originale, $extra,$show_info=array(true,true,true);

/**
 * @property array $file attributo in sola lettura con le info sul file corrente o appena uploadato ( dopo check_errore())
 */

	/**
	  *
	  * 
	  * @param	 string $nome E' il nome del campo
	  * @param	 string $nomeFile E' il nome del file attualmente in uso COMPLETO DI PERCORSO ASSOLUTO
	  * @param	 string $classe e' la classe css da utilizzare
	  */

		   public function __construct($nome='',$nomeFile="",$classe=''){
		  		myField::__construct($nome);
		  		if (!isset($this->myFields['FILES']) && $_FILES) $this->myFields['FILES']=$_FILES;
		  		if ($classe) $this->set_attributo('class',$classe);
				$this->set_attributo('type','file');
				$this->set_MyType('MyUpload');
				$this->path=self::get_PathSito();
				$this->set_value($nomeFile);
		  	    if(!self::$maxGlobal) self::$maxGlobal= self::calcola_max_upload();
		  	    $this->set_max(self::$maxGlobal);
		  	    $this->set_blank_per_download(true);
		  }


			/**
			 * @ignore
			 */
			 public function __destruct(){
			 //   if($this->file &&  is_file($this->file['tmp_name'])) @unlink($this->file['tmp_name']);
				myField::__destruct();
			}

			

			/**
			 * Azzera le informazioni sul file corrente
			 */
			 public function reset_originale(){
			 $this->originale='';
			}

        /**
         * @ignore
         */
        	 public function set_nome_attuale($nome) {
        		$this->nomeAttuale=$nome;
        		return $this;
        	}
        


        	/**
              * Restituisce l'estensione del file Uploadato
    		  *  @return string
    		  */
        	 public function get_ext() {
        	     return strtolower(pathinfo($this->get_value(), PATHINFO_EXTENSION));
        	}

        	
        	 public function set_id($id,$chk=''){
        	    return myField::set_id($id);
        	}

        	/**
        	* @param	 string $nomeFile E' il nome del file attualmente in uso con percorso relativo a dir di lavoro (vedi set_dir) se non trova il file fallisce
        	* @param	 boolean $forzaValore Se true forza il valore inserito MA NON FA ALCUNA VERIFICA CHE SIA VERITIERO
        	*/
        	  public function set_value($nomeFile='',$forzaValore=false) {
        	        myField::set_value($nomeFile);
        	 	    if (!$nomeFile) return $this;
          	 		if (is_file($this->path.$nomeFile))
        								{
        								$this->file['size']=@filesize($this->path.$nomeFile);
        								$this->nomeAttuale=$nomeFile;
             							}
        			if ($forzaValore)	{
        								$this->file['size']=@filesize($nomeFile);
        								$this->nomeAttuale=$nomeFile;
        								}
        			return $this;					
        	 }


       	 function &get_value(){
        	     return myField::get_value();
        	 }


           function &get_value_db(){
        	     return myField::get_value();
        	 }
        	 

	 /**
	* Restituisce un messaggio se l'attributo value non soddisfa condizioni di notnull,maxlength,minlength
		  *  @return string
		  */
		  protected function get_errore_diviso_singolo() {
		  //	print_r($this->myFields['FILES']);exit;
				if (isset($this->myFields['FILES'][$this->get_name()]['name']))
								{$this->file=$this->myFields['FILES'][$this->get_name()];
				                  myField::set_value($this->myFields['FILES'][$this->get_name()]['name']);
							    }

                 if (isset($this->file['error']) && $this->file['error']>=1 && $this->file['error']<=2)  return array(' non può essere più grande di %1%bytes',self::ricalcolasize($this->maxlength,true,1));
                 elseif (isset($this->file['error']) && $this->file['error']>2) {$this->remove_session();	 return ' non inviato correttamente';}
						 elseif (!isset($this->file['size'])) {
    						 								if ($this->notnull) return ' deve essere allegato';
    						 							    }
						  else{	if ($this->ext_ammesse && !isset($this->ext_ammesse[$this->get_ext()])) return array(' non è accettabile, estensioni ammesse: %1%',implode(',',array_flip($this->ext_ammesse)));
						  		if ($this->maxlength && $this->get_size()>$this->maxlength) return array(' non può essere più grande di %1%bytes',self::ricalcolasize($this->maxlength));
								if ($this->minlength && $this->get_size()<$this->minlength) return array(' non può essere più piccolo di %1%bytes',self::ricalcolasize($this->minlength));
						     }

		  }


        
         /**
	* setta un titolo d far comparire sul link al file, se omesso si usa il nome del file
        	* @param	 string $testo
        	*/
        	 public function set_Descrizione($testo) {
        		 $this->descrizione=$testo;
        		 return $this;
        	}
        
        
        	 public function set_show_info($anteprima=true,$max=true){
        		$this->show_info=func_get_args();
        		return $this;
        	}


        	/**Elimina il file corrente
        	 */
        	 public function Elimina () {
        			 $oldabs=realpath( $this->path."/".$this->nomeAttuale);
        			 if (!is_file($oldabs)) $oldabs=realpath($this->nomeAttuale);
        			 @unlink($oldabs);
        			 if (is_file($oldabs)) return "Errore nell'eliminazione del file";
        			 $x='';
        			 parent::set_value($x);
        			 $this->file=null;
        	}
        
        	/**Salva il file su file system USARE SOLO DOPO CHECK_ERRORE
        			  * @param	 string $nome e' il nome con cui completo di un percorso ASSOLUTO
        			  * @param	 boolean $ForzaDir Forza la creazione delle directory che formano il percorso inserito in $nome
        			  * @param   boolean $ForzaPath se true si prende in considerazionela path indicata nel parametro $nome
        	 * @return	string se tutto OK niente altrimenti un messaggio d'errore
        	 */
        	 public function Salva ($nome,$ForzaDir=true,$ForzaPath=false,$Path='') {
        	   if(!$Path) $docroot= $this->path;
        	   	 	else $docroot=$Path;
        
        	   $nome=str_replace('\\','/',$nome);
        	   if ($nome[0]=='/') $newabs=$docroot.$nome;
        	   		 	     else $newabs=$docroot.dirname($_SERVER['PHP_SELF']).'/'.$nome;
        	   if (strpos($newabs,'//')===0) $newabs='/'.str_replace('//','/',$newabs);
        	   							else $newabs=str_replace('//','/',$newabs);
        
           	  // $oldabs=realpath($docroot.$this->nomeAttuale);
           	  // $newabs=str_replace(array('\\','//'),array('/','/'),$newabs);
           	  // $newabs=str_replace(array('//','//'),array('/','/'),$newabs);
           	   if ($ForzaPath)  $newabs=$nome;
           	   if ($ForzaDir) { $dir=dirname($newabs);
           	                    if(!is_dir($dir)) @mkdir($dir, 0770,true);
           	                  }
        	  // echo "{$this->file['tmp_name']},$newabs";
        	   if ($this->file['tmp_name'] && (!@copy($this->file['tmp_name'],$newabs) || !@is_file($newabs))) return $this->trasl("Impossibile salvare il file %1% in %2%",array('%1%'=>$this->file['tmp_name'],'%2%'=>$newabs)) ;
        	   $this->set_value($nome,$ForzaDir,$ForzaPath);
        	}
        
        
        	/**Salva il file su file system conservando etensione del file uploadato USARE SOLO DOPO CHECK_ERRORE
        			  * @param	 string $nome e' il nome con cui completo di un percorso ASSOLUTO senza estensione
        			  * @param	 boolean $ForzaDir Forza la creazione delle directory che formano il percorso inserito in $nome
        	 * @return	string se tutto OK niente altrimenti un messaggio d'errore
        	 */
        	 public function Salva_con_ext ($nome,$ForzaDir=true,$ForzaPath=false,$Path='') {
        	   if ($this->get_ext()) return $this->Salva($nome.".".$this->get_ext(),$ForzaDir,$ForzaPath,$Path);
          }
        
        
        
        
        	 public function set_path($path){
        		$this->path=$path;
        		return $this;
        	}
        
        	 public function ricalcola_percorso($percorso) {
        		return $percorso;
        	}
        
        
        
          /**
	        *  Restituisce il campo in html pronto per la visualizzazione
        	*  @return string
            */
             public function get_Html () {
                $get_html=$icona='';
                 $this->get_errore_diviso_singolo();
                 if(isset($this->Metodo_ridefinito['get_Html']['metodo'])) $get_html=$this->Metodo_ridefinito['get_Html']['metodo'];
        		 if ($get_html!='') return $this->$get_html(isset($this->Metodo_ridefinito['get_Html']['parametri'])?$this->Metodo_ridefinito['get_Html']['parametri']:null);
        		 if ($this->nomeAttuale && (
        		 			is_file($percorso=$this->nomeAttuale) ||
        			  	    is_file($percorso=$this->path.$this->nomeAttuale)
        		 			)
        			  	)
        			  			 {
        			  			 if (!$this->descrizione) $this->descrizione=basename($this->nomeAttuale)."\n".self::ricalcolasize(filesize($percorso))."bytes";
         					 	 $v=explode('.',$this->nomeAttuale);
        					     $icona=strtolower($v[count($v)-1]);
        				   	     $root= dirname(__FILE__).'/MyUploadIcones/';
        		 		  	     if (!is_file("$root$icona.gif")) $icona='folder';
        						 $root= "/".self::get_MyFormsPath()."MyUploadIcones/$icona.gif";
        						 
        						 $download_script=myTag::htmlentities($this->ricalcola_percorso($percorso));
        						 if($this->download_script) { $fun=&$this->download_script;
        						                              $download_script=$fun($download_script);
        						                            }
        						        
        						 if ($this->show_info[0]) $icona="<a href=\"$download_script\" {$this->blank} title=\"".myTag::htmlentities($this->descrizione)."\">File:<img src='$root' alt=\"".myTag::htmlentities($this->descrizione)."\" style='border:0' /></a>&nbsp;";
        						 					 else $icona='';
        						 if ($this->extra || $icona) $icona.="$this->extra<br />";
        						 }
        
        				return $this->jsAutotab()."$icona<span>
        												<input type=\"hidden\" name=\"MAX_FILE_SIZE\"  value=\"".self::$maxGlobal."\" />".
        												$this->send_html("<input ".$this->stringa_attributi(array('value'),1)." />").($this->show_info[1]?"(max&nbsp;".self::ricalcolasize($this->get_max(),true,1)."bytes)":'')."</span>";
        	 }
        
        
        
        
           /** @ignore*/
            public function _get_html_show($pars, $null = null, $null2 = NULL){
               if (is_file($percorso=self::get_PathSito().'/'.$this->nomeAttuale)  ||
                    is_file($percorso=$this->path.$this->nomeAttuale))
        						 {
        						 if (!$this->descrizione) $this->descrizione=basename($this->nomeAttuale)."\n".self::ricalcolasize(filesize($percorso))."bytes";
        						 $v=explode('.',$this->nomeAttuale);
        						 $icona=strtolower($v[count($v)-1]);
        						 $root= dirname(__FILE__).'/MyUploadIcones/';
        						 if (!is_file("$root$icona.gif")) $icona='folder';
        						 $root= "/".self::get_MyFormsPath()."MyUploadIcones/$icona.gif";
        						 
        						 $download_script=myTag::htmlentities($this->ricalcola_percorso($percorso));
        						 if($this->download_script) { $fun=&$this->download_script;
                                    						  $download_script=$fun($download_script);
                                    						 }
        						 
        						 
        						 return $icona="<a href=\"$download_script\" {$this->blank} title=\"".myTag::htmlentities($this->descrizione)."\">{$this->trasl("File attuale")}:<img src='$root' alt=\"".myTag::htmlentities($this->descrizione)."\" style='border:0em' /></a>&nbsp;<br />";
        						 }
           }
        
        
        
           /** @ignore */
            protected function _get_html_hidden($pars){}
            /** @ignore */
           // protected function &restore_session(){}
            /** @ignore */
          //  protected function remove_session(){}
            /** @ignore */
           // protected function store_session($data){}
            
}