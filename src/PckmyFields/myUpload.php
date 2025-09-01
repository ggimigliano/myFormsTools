<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myUpload.
 */

namespace Gimi\myFormsTools\PckmyFields;

use Gimi\myFormsTools\PckmyArrayObject\myArrayObject;
use RdKafka\Exception;
use Gimi\myFormsTools\PckmyJQuery\myJQuery;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQTagIt;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQUploader;

class myUpload extends myUploadText {
/** @ignore */
protected $NODELETE=array(),$path,$ext_ammesse=array(),$nomeAttuale=array(), $da_rimuovere=array(),$descrizione, $originale, $extra,$show_info=array(true,true,true);

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
		  	    $name=preg_replace('/\[\]$/','',$this->get_name());  
		  	    if(isset($_POST[$name]['NODELETE'])) {  
										  	    	$this->NODELETE=$_POST[$name]['NODELETE'];
										  	    	unset($_POST[$name]);
										  	    }
		  }

		  /**
		   *
		   * @param int $min
		   * @param int $max
		   * @throws \Exception
		   */
		  public function set_molteplicita($min,$max){
		  	    if($min>$max) throw new \Exception("set_molteplicita({$min},{$max}) parametri incoerenti");
		  		$this->molteplicita=array('min'=>max(1,$min),
		  								  'max'=>min($max,ini_get('max_file_uploads'))
								  		);
				return $this;
		  }
		  
		  /**
		   * 
		   * @return array
		   */
		  public function get_molteplicita(){
		  	return $this->molteplicita;
		  }

			/**
			 * @ignore
			 */
			 public function __destruct(){
			 //   if($this->file &&  is_file($this->file['tmp_name'])) @unlink($this->file['tmp_name']);
				myField::__destruct();
			}

			
			/**
			 * @ignore
			 */
			public function __get($attributo){
				if(!$this->is_multiplo()) return  (isset($this->file[0][$attributo])?$this->file[0][$attributo]:null);
									 else return array_column($this->file,$attributo);
			}
			
 

        /**
         * @ignore
         */
        	 public function set_nome_attuale($nome) {
        		$this->nomeAttuale=$nome;
        		return $this;
        	}
        
        	public function set_download_script($percorso){
        		if(!is_callable($percorso)) $this->download_script=function ($file) use($percorso) {return $percorso;};
        		else $this->download_script=$percorso;
        		return $this;
        	}
        	
        	/**
        	 *
        	 * Restituisce informazioni di sitema sul file appena uploadato,
        	 * funziona solo se il file è stato correttamente appena uploadato e se prima e' stato fatto myforms::check_errore() o myUpload::errore()
        	 * @return myArrayObject @see myArrayObject
        	 */
        	public function get_info_uploaded(){
        		return  new myArrayObject($this->file);
        	}
        	

        	/**
              * Restituisce l'estensione del file Uploadato
    		  *  @return string
    		  */
        	 public function get_ext() {
        	 	return $this->ext;
        	}

        	/**
        	 * Non ammessa
        	 * @throws Exception
        	 */
        	public function set_ext($ext){
        		
        		throw new \Exception('set_molteplicita inapplicabile per questo tipo oggetto');
        	}
        	
        	 public function set_id($id,$chk=''){
        	    return myField::set_id($id);
        	}

        	/**
        	* @param	 array|string $nomiFile E' il nome del file attualmente in uso con percorso relativo a dir di lavoro (vedi set_dir) se non trova il file fallisce
        	* @param	 boolean $forzaValore Se true forza il valore inserito MA NON FA ALCUNA VERIFICA CHE SIA VERITIERO
        	*/
        	  public function set_value($nomiFile='',$forzaValore=false) {
        	  	   $this->nomeAttuale=array();
        	  	   if(!$nomiFile) $nomiFile=array();
        	  	   		elseif($nomiFile && !is_array($nomiFile)) $nomiFile=array($nomiFile);
        	  	   		
        	  	   
        	  	   foreach ($nomiFile as $nomeFile) 
        	  	   				 {
        	  		    	 	    if (!$nomeFile) continue;
        	  		    	 	    $nomeFile=str_replace('\\','/',$nomeFile);
				             		if(is_file($this->path.$nomeFile)) $nomeFile=$this->path.$nomeFile;
				             		
				             		if ($forzaValore || is_file($nomeFile) )
				             							{		$file=array();
				             									if(is_file($nomeFile))
				             											{ $file['size']=@filesize($nomeFile);
				             											  $file['tmp_name']=@realpath($nomeFile);
				             											}
				             										else {$file['size']=-1;
				             											  $file['tmp_name']= $this->path.basename($nomeFile);
				             											}
				             									$file['name']=basename($nomeFile);
				             									$file['ext']=strtolower(pathinfo( $nomeFile, PATHINFO_EXTENSION));
				             									$file['error']=0;
				             									$file['src']='int';
				             									$file['type']=$this->get_MimeType(strtolower(pathinfo( $nomeFile, PATHINFO_EXTENSION)));
				             									
				             									$this->file[$this->hash($file)]=$file;
				             									$this->nomeAttuale[]=$nomeFile;
				             							}
				           	  	}
				        
				    if(!$this->is_multiplo() && $this->file) $this->file=array(array_values($this->file)[0]);
        			return $this;					
        	 }

        	 public function is_multiplo(){
        	 	return $this->get_molteplicita()['max']>0;
        	 }

        	 public   function &get_value(){
       	   	 $out=array();
       	   	 foreach ($this->file as $k=>$v) $out[$k]=$v['name'];
       	   	 return $out;
         }


         public  function &get_value_db(){
           	return implode(', ',array_map(function ($n){return "'".addslashes($n)."'";}, $this->get_value()));
         }
        	 

	 	/**
		 * Restituisce un messaggio se l'attributo value non soddisfa condizioni di notnull,maxlength,minlength
		 *  @return string
	     */
		protected function get_errore_diviso_singolo() {
			 
		  	 if (isset($this->myFields['FILES'][$this->get_name()]['name']))
								{$j=0; 
								 $file=&$this->myFields['FILES'][$this->get_name()];
								 if(!is_array($file['name'])) 
										{$file['src']='ext';
										 $file['ext']=strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)); 
										 $this->file=array($file);
										 }
								 else foreach ($file['name'] as $i=>$name) 
								 		if(trim($name) && $file['size'][$i]>0) 
								 			{
											$f=array('ext'=>strtolower(pathinfo($name, PATHINFO_EXTENSION)),
						 							 'src'=>'ext',
													 'name'=>$file['name'][$i],
						 							 'type'=>$file['type'][$i],
						 							 'size'=>$file['size'][$i],										 							
													 'error'=>$file['error'][$i],
								 					 'tmp_name'=>$file['tmp_name'][$i] 
								 				   );
								 			$this->file[$this->is_multiplo()?$this->hash($f):$j++]=$f;
								 			}
							  	}
											
				$this->da_rimuovere=$da_rimuovere=array(); 
				if($this->file)  
					foreach ($this->file as $k=>$file)	 
						if($file['src']=='int' && !in_array($k,$this->NODELETE))  $da_rimuovere[$k]=$file['tmp_name'];
						 
					    
				if(!$this->file && $this->notnull) return ' deve essere allegato';
					elseif($this->file && !$this->notnull && !$this->is_multiplo()){
						 if(count($this->file)<$this->get_molteplicita()['min'])  return array(' sono stati caricati %1% files ne sono richiesti almeno %2%',count($this->file),$this->get_molteplicita()['min']);
			    		 if(count($this->file)>$this->get_molteplicita()['max'])  return array(' sono stati caricati %1% files ne sono richiesti al massimo %2%',count($this->file),$this->get_molteplicita()['max']);
						}
			    
			    $return=null;
			    foreach ($this->file as $file) 
			    	{
			    	if ($file['src']=='int') continue;
			    	$extra=$this->get_molteplicita()['max']==0?'':' al file "%2%"'; 
			    	if ($file['error']==2)  {$return=array(' non può essere più grande di %1%bytes'.$extra,self::ricalcolasize($this->maxlength,true,1),$file['name']);break;}
			   	 		elseif ($file['error']>0)  	 {$return=array(' non inviato correttamente%1%'.$extra,'',$file['name']);break;}
			    			elseif ($this->ext_ammesse && !isset($this->ext_ammesse[strtolower(pathinfo($file['name'], PATHINFO_EXTENSION))])) {$return=array(' non è accettabile, estensioni ammesse: %1%'.$extra,implode(',',array_flip($this->ext_ammesse)),$file['name']);break;}
			    				elseif ($this->maxlength && $file['size']>$this->maxlength) {$return=array(' non può essere più grande di %1%bytes'.$extra,self::ricalcolasize($this->maxlength),$file['name']);break;}
			    					elseif ($this->minlength && $file['size']<$this->minlength) {$return=array(' non può essere più piccolo di %1%bytes'.$extra,self::ricalcolasize($this->minlength),$file['name']);break;}
			    	}
			    	
			  
			    if(!$return && $da_rimuovere) $this->da_rimuovere=$da_rimuovere;
			    return $return;
		  }

		/**
		 * Elenco nomi file deselezionati
		 * @return array
		 */
		  public function get_da_rimuovere(){
		  	return $this->da_rimuovere;
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


        	/**
        	 * Elimina il/i file correnti oppure solo quelli passati
        	 */
        	 public function Elimina ($nomi=array()) {
        	 	if(!$nomi) $nomi=$this->tmp_name;
        	 	foreach ($nomi as $k=>$nomeAttuale) 
        	 		 	 	 if (!@unlink($this->file[$k]['tmp_name']) )
        	 		 	 	 	   return array("Errore nell'eliminazione del file '%1%'",basename($nomeAttuale));
		        			 
				$this->file=array();
				$this->nomeAttuale=array(); 
        	}
        
        	/**Salva il file su file system USARE SOLO DOPO CHECK_ERRORE
        	* @param  array|string $nomi se omesso usa tutti i file uploadati, se non ultiplo il nome del file da usare nel salvataggio, se smultiplo l'elenco dei nomi completo di un percorso ASSOLUTO (FA FEDE LA CHIAVE RESTUTUITA DA get_value() )
        	* @param  boolean $ForzaDir Forza la creazione delle directory che formano il percorso inserito in $nome
        	* @param  boolean $ForzaPath se true si prende in considerazionela path indicata nel parametro $nome
        	* @return string se tutto OK niente altrimenti un messaggio d'errore
        	*/
        	 public function Salva ($nomi=null,$ForzaDir=true,$ForzaPath=false,$Path='',$add_ext=false) {
        	   if(!$Path) $docroot=$this->path;
        	   	 	 else $docroot=$Path;
        	  
        	   if($nomi && is_string($nomi)) $nomi=array($nomi);
		        	   					else $nomi=array_combine(array_keys($this->file),$this->name);
		        	   								
		      foreach ($nomi as $k=>$nome) {
		      		$v=$this->file[$k];
        	   		if($v['src']=='ext')
        	   				  {
        	   				   $nome=str_replace('\\','/',$nome);
        	   				   if($add_ext) $nome=preg_replace('@\.[a-zA-Z]{2,4}$@','',$nome).'.'.$v['ext'];
				        	          	   				   
				        	   if (dirname($nome)==='.') $newabs=$docroot.$nome;
				        	   		 	     else $newabs=$docroot.dirname($_SERVER['PHP_SELF']).'/'.$nome;
				        	   
				        	   if (strpos($newabs,'//')===0) $newabs='/'.str_replace('//','/',$newabs);
				        	   							else $newabs=str_replace('//','/',$newabs);
				     		
				           	   if ($ForzaPath)  $newabs=$nome;
				           	   if ($ForzaDir) { $dir=dirname($newabs);
				           	                    if(!is_dir($dir)) @mkdir($dir, 0770,true);
				           	                  }
				           	   if  (!@copy($v['tmp_name'],$newabs) || 
				           	   		!@is_file($newabs))  return $this->trasl("Impossibile salvare il file %1% in %2%",array('%1%'=>$v['tmp_name'],'%2%'=>$newabs)) ;
				        	   $this->file[$k]['tmp_name']=realpath($newabs);
				        	   $this->file[$k]['src']='int';
				        	  } 
		      		}
        	    $this->set_nome_attuale($nomi);
        	}
        
        
        	/**Salva il file su file system conservando estensione del file uploadato USARE SOLO DOPO CHECK_ERRORE
        	 * @param	 string $nome e' il nome con cui completo di un percorso ASSOLUTO senza estensione
        	 * @param	 boolean $ForzaDir Forza la creazione delle directory che formano il percorso inserito in $nome
        	 * @return	string se tutto OK niente altrimenti un messaggio d'errore
        	 */
        	 public function Salva_con_ext ($nome=null,$ForzaDir=true,$ForzaPath=false,$Path='') {
        	 	return $this->Salva($nome,$ForzaDir,$ForzaPath,$Path,true);
          }
        
        
        
        
        	 public function set_path($path){
        		$this->path=str_replace('//','/',$path.'/');
        		return $this;
        	}
        
        	 public function ricalcola_percorso($percorso) {
        		return $percorso;
        	}
        
        
        	private function hash($hash){
        		return 'x'.sha1(md5($hash['name']).'|'.md5($hash['size']));
        	}
        
          /**
	        *  Restituisce il campo in html pronto per la visualizzazione
        	*  @return string
            */
             private function get_html_orig () {
                 $get_html=$icona='';
                 $out='';
                 $this->get_errore_diviso_singolo();
                 if(isset($this->Metodo_ridefinito['get_Html']['metodo'])) $get_html=$this->Metodo_ridefinito['get_Html']['metodo'];
        		 if ($get_html!='') return $this->$get_html(isset($this->Metodo_ridefinito['get_Html']['parametri'])?$this->Metodo_ridefinito['get_Html']['parametri']:null);
        		 
        		 
        		 foreach ($this->file as $k=>$file)
        			 if ($file['src']=='int') 
        		 		{$id_file='_'.spl_object_hash($this).'_'.$k;
        		 		 $nomeAttuale=$file['name'];
		        		 $hash=$this->hash($file);
		        		 if ($nomeAttuale && (
		        		 			is_file($percorso=$nomeAttuale) ||
		        			  	    is_file($percorso=$this->path.$nomeAttuale)
		        		 			)
		        			  	)
		        		       { 
        			  			 $descrizione=basename($nomeAttuale)."\n".self::ricalcolasize(filesize($percorso))."bytes";
         					 	 $v=explode('.',$nomeAttuale);
        					     $icona=strtolower($v[count($v)-1]);
        				   	     $root= dirname(__FILE__).'/../MyUploadIcones/';
        		 		  	     if (!is_file("$root$icona.gif")) $icona='folder';
        						 $root= "/".self::get_MyFormsPath()."MyUploadIcones/$icona.gif";
        						 
        						 $download_script= $this->get_download_url($file);
        						 
        						 if ($this->show_info[0]) $icona="<a href=\"$download_script\" {$this->blank} title=\"".myTag::htmlentities($descrizione)."\"><img src='$root' alt=\"".myTag::htmlentities($descrizione)."\" style='border:0' />".basename($nomeAttuale).'</a>';
        						 					 else $icona='';
        						 if ($this->extra || $icona) $icona.=$this->extra;
        						 $name=preg_replace('/\[\]$/','',$this->get_name());
        						 $icona.="<input type='hidden' id='hidden_for_{$id_file}' name='{$name}[NODELETE][]' value='{$hash}' />";
        						 }
		        		
		        		
		        		$out.= "
 								<li class='tagit-choice ui-widget-content ui-state-default ui-corner-all tagit-choice-editable' ><a class='tagit-close' onclick='$(this).parent().remove()'>
									<span  style='cursor: pointer' class='ui-icon ui-icon-close'></span>
									&nbsp;$icona
									 
									<span class='tagit-label'>".
										$this->jsAutotab().
			        					$this->send_html("<span><input id='{$id_file}' ".$this->stringa_attributi(array('id','value'),1)."  onchange=\"document.getElementById('hidden_for_{$id_file}').disabled=true;document.getElementById('rst_{$id_file}').style.display='inline'\" /></span>").
			        					"<input style='display:none' id='rst_{$id_file}' type='button' value='{$this->Trasl('Ripristina')}' onclick=\"document.getElementById('{$id_file}').value = ''; document.getElementById('hidden_for_{$id_file}').disabled=false; this.style.display='none';return false;\" >".
			        					($this->show_info[1]?"(max&nbsp;".self::ricalcolasize($this->get_max(),true,1)."bytes)":'').
									"</span>
								</li>";
        		 		}
        		 return (new myJQTagIt()).$out;        		 		
        	 }
        
        
        	 
        	 public function &get_html(){
        	 	
        	 	foreach ($this->myJQueries as $obj)
        	 		if($obj instanceof myJQUploader) return $this->send_html("<div class='myUploaderDD' id=\"uploader-{$this->get_id()}\"></div>");
        	 	
        	 	if($this->get_showonly()) return $this->_get_html_show(null);
        	 	if($this->get_molteplicita()['max']==0) $this->set_molteplicita(1, 1);
        	 	$id=$this->get_id();
        	 	$b=new myBottone('Add',$this->trasl('Aggiungi un file'),'ui-button ui-corner-all');
        	 	
        	 			
        	 	$b->set_style('font-size', '80%');
        	 	$b->add_myJQuery((new myJQuery())->add_code("$('#{$b->get_id()}').on('click',function(){
																			if($('#blocks_{$id} input[type=file]').length<{$this->get_molteplicita()['max']}) $('#blocks_{$id}').show().append($('#block_{$id}').html());
																			if($('#blocks_{$id} input[type=file]').length=={$this->get_molteplicita()['max']}) $('#{$b->get_id()}').hide();
																		  });"));
        	 	if(!$this->file && $this->notnull) $b->add_myJQuery((new myJQuery())->add_code("$('#{$b->get_id()}').click(); "));
        	 	
        	 	$this->show_info[1]=false;
        	 	$name=$this->get_name();
        	 	$this->set_name($name.'[]',false);
        	 	$html=$this->get_html_orig(true);
        	 	$html="<ul class='ui-widget ui-widget-content ui-corner-all' style='".($html?'':"display:none;")."list-style-type:none;margin-left:0;padding-left:0' id='blocks_{$id}'>
			                $html
			                </ul>
			                <ul style='display:none'  id='block_{$id}' >
							   <li class='tagit-choice ui-widget-content ui-state-default ui-corner-all tagit-choice-editable'  style='display:inline-block'>
									<a class='tagit-close' onclick=\" $(this).parent().remove();  $('#{$b->get_id()}').show();\" style='line-height:2em'><span  style='cursor: pointer' class='ui-icon ui-icon-close'></span></a>
									<span class='tagit-label' >".
        									 $this->send_html("<input ".$this->stringa_attributi(array('value','id'),1)." />").
        									($this->show_info[1]?"(max&nbsp;".self::ricalcolasize($this->get_max(),true,1)."bytes)":'').
		        					"</span>
								</li>
						   </ul>
							$b&nbsp;max&nbsp;{$this->get_molteplicita()['max']}&nbsp;files,&nbsp;max&nbsp;".self::ricalcolasize($this->get_max(),true,1)."bytes per file
							<input type=\"hidden\" name=\"MAX_FILE_SIZE\"  value=\"".self::$maxGlobal."\" />";
				$this->set_name($name);
				return $html;
        	 }
        
        	 
        	
          	 
           /** @ignore*/
            public function _get_html_show($pars, $null = null, $null2 = NULL){
            	$out='';
            	foreach ($this->file as $file)
            		if ($file['src']=='int')	
            			{
            				$descrizione=basename($file['name'])."\n".self::ricalcolasize($file['size'])."bytes";
            				$icona=$file['ext'];
		            		$root= dirname(__FILE__).'/../MyUploadIcones/';
		            		if (!is_file("$root$icona.gif")) $icona='folder';
		            		$root= "/".self::get_MyFormsPath()."MyUploadIcones/$icona.gif";
		            		
		            		$download_script= $this->get_download_url($file);
		            		
		            		if ($this->show_info[0]) $icona="<a href=\"$download_script\" {$this->blank} title=\"".myTag::htmlentities($descrizione)."\"><img src='$root' alt=\"".myTag::htmlentities($descrizione)."\" style='border:0' />".basename($file['name']).'</a>';
		            							else $icona='';
		            		if ($this->extra || $icona) $icona.=$this->extra;
		            		$out.="<li>$icona</li>";
		            	}
            	
            	return $out?"<ul>$out</ul>":$this->trasl('Nessuno');
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