<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myUploadText.
 */

namespace Gimi\myFormsTools\PckmyFields;



use Gimi\myFormsTools\PckmyUtils\myFormAJAXRequest;
use Gimi\myFormsTools\PckmyArrayObject\myArrayObject;



/**
 * 
 * DA usare abbinato a campi BINLOG
 * 
 */
	
class myUploadText extends myField {
/** @ignore */
	protected $molteplicita=array('min'=>0,'max'=>0),$blank='_blank',$remove_chk,$uuid,$curFile=0,$file=array(),$ext_ammesse=array(), $descrizione,  $extra,$show_info=array(true,true,true),$download_script=false,$ext,$bodyFile;
/** @ignore */
protected static $sessione,$maxGlobal;
/**
 * @property array $file attributo in sola lettura con le info sul file corrente o appena uploadato ( dopo check_errore())
 */


/**
 * @ignore
 */
    public static function calcola_max_upload(){
            return min(self::ricalcolasize(ini_get('upload_max_filesize'),false),
		  		       self::ricalcolasize(ini_get('post_max_size'),false));
        }

	/**
	  *
	  * DA usare abbinato a campi BINLOG 
	  *  
	  * @param	 string $nome E' il nome del campo
	  * @param	 string $nomeFile E' il nome del file attualmente in uso COMPLETO DI PERCORSO ASSOLUTO
	  * @param	 string $classe e' la classe css da utilizzare
	  */

		 public function __construct($nome='',$valore="",$classe=''){
		  		myField::__construct($nome,'');
		  
		  		if (!isset($this->myFields['FILES']) && $_FILES) $this->myFields['FILES']=$_FILES;
		  		if ($classe) $this->set_attributo('class',$classe);
				$this->set_attributo('type','file');
				$this->set_MyType('MyUploadText');
				
				if(!self::$maxGlobal) self::$maxGlobal= self::calcola_max_upload();
		  		$this->set_max(self::$maxGlobal);
			     $this->get_remove_check();		
			     $this->set_value($valore);
			}

		/**
		 * 
		 * @param int $min
		 * @param int $max
		 * @throws \Exception
		 */
		  public function set_molteplicita($min,$max){
	
			    throw new \Exception('set_molteplicita inapplicabile per questo tipo oggetto');
			}

		/**
		 * Imposta l'estensione di default per il file
		 * @param string $ext
		 */
		 public function set_ext($ext){
		        $this->ext=$ext;
			    return $this;
	   	}

	   	function &stringa_attributi($v=array(),$Esclusi=true,$novalue=false){
	   	    $ext=$this->get_ext_ammesse();
	   	    if($ext) $this->set_attributo('accept','.'.implode(' ,.',$ext));
	   	    return parent::stringa_attributi($v,$Esclusi,$novalue);
	   	}
	   	
	   	/*
	   	function set_id($id,$chk=''){
	   	    parent::set_id($id);
	    	if($chk) { $info=$this->restore_session();
            		    if($info && $info['chk']==$chk) 
            			     	{
            					$this->set_Descrizione($info['desc']);
            					$this->set_ext($info['ext']);
            					$this->bodyFile=@gzuncompress($info['content']);
            					}
	    	          }
	   	}
	   	*/

		  /**
	* Non attiva in questa classe
			*/
	 	 public function set_readonly($stato=true){
				$this->show_info[1]=!$stato;
				return $this;
		  }


		


	/**
	* Setta la dimensione max file Uploadato
	  * @param	integer $max e' la dimensione in byte
	  */
	   public function set_max($maxlength) {
	                if(!self::$maxGlobal) self::$maxGlobal= self::calcola_max_upload();
	                $maxlength=self::ricalcolasize($maxlength,false);
	                if($maxlength>self::$maxGlobal) $maxlength=self::$maxGlobal;
	                $this->maxlength=$maxlength;
					return $this;
	  }


		  /**
			* Restituisce la dimensione max file Uploadato
		  *  @return integer
		  */
	    public function get_max() {
					  return $this->maxlength;
		  }


		  /**
	* Setta la dimensione min file Uploadato
			* @param	integer $min e' la dimensione in byte
	  */
		   public function set_min($min) {
					  $this->minlength=$min;
					  return $this;
		  }

		  /**
		   * @ignore
		   */
		   public function __get($attributo){
		   	return isset($this->file[$this->curFile])?$this->file[$this->curFile]:null;
		  }

		  /**
			* Restituisce la dimensione max file Uploadato
		  *  @return integer
		  */

		   public function get_min() {
					  return $this->minlength;
		  }
		  
		

	    /**
	       * Restituisce un messaggio se l'attributo value non soddisfa condizioni di notnull,maxlength,minlength
		  *  @return string
		  */
		  protected function get_errore_diviso_singolo() {
		      if(isset($this->file[$this->curFile]['reloaded'])) return;
		      if(isset($_POST[$this->get_remove_check()->get_name()]))
		  					{ 
		  					  if($this->notnull)   return ' non può essere nullo';
		  					 // $this->remove_session();
		  					  $this->bodyFile='';
		  					  return;
		  					}
		  	   
				  	    if (isset($this->myFields['FILES'][$this->get_name().'_file']['name'])) 
				  	   							  $this->file[$this->curFile]=$this->myFields['FILES'][$this->get_name().'_file'];
				  	   						
				  	    if (!isset($this->file[$this->curFile]) && $this->get_value()) return;
				  	    if (isset($this->file[$this->curFile]['error']) && 
				  	              $this->file[$this->curFile]['error']>=1 &&
						  		  $this->file[$this->curFile]['error']<=2)  {/*$this->remove_session();*/ return array(' non può essere più grande di %1%bytes',self::ricalcolasize($this->maxlength,true,1));}
						 elseif (isset($this->file[$this->curFile]['error']) && 
						         $this->file[$this->curFile]['error']>2) {/*$this->remove_session();*/	 return ' non inviato correttamente';}
						   elseif (!isset($this->file[$this->curFile]['size']) || $this->file[$this->curFile]['size']==0) 
						                                        {
								 								if ($this->notnull) return ' deve essere allegato';
								 							    }
							 elseif(isset($this->file[$this->curFile]['name'])){
								     $v=array_reverse(explode('.',strtolower($this->file[$this->curFile]['name'])));
								     $ext=null;
				 					 foreach (array_keys($this->ext_ammesse) as $estensione) {
		                                                		 					 	 $estensione=array_reverse(explode('.',strtolower($estensione)));
		                                                		 					 	 foreach ($estensione as $i=>$pe) if($pe!=$v[$i]) continue (2);
		                                                		 					 	 $ext=implode(".",array_reverse($estensione));
		                                                		 					 	 break;
		                                                							 }
		
								  	 if ($this->ext_ammesse && !$ext){/*$this->remove_session();*/ return array(' non è accettabile, estensioni ammesse: %1%',implode(',',array_flip($this->ext_ammesse)));}
								  	 if ($this->maxlength && $this->file[$this->curFile]['size']>$this->maxlength){/*$this->remove_session();*/ return array(' non può essere più grande di %1%bytes',self::ricalcolasize($this->maxlength));}
								  	 if ($this->minlength && $this->file[$this->curFile]['size']<$this->minlength){/*$this->remove_session();*/ return array(' non può essere più piccolo di %1%bytes',self::ricalcolasize($this->minlength));}
									 if(!$ext) $ext=$v[0];
		
									 $this->set_ext($ext);
									 $this->bodyFile=@file_get_contents($this->myFields['FILES'][$this->get_name().'_file']['tmp_name']);
									 		 //$this->storing_session();
									 }
		  }
	
			   
			 public function set_value($valore,$forza=false) {
		       if(!$forza && strlen($valore)>0 && preg_match('@^[a-zA-Z0-9/+]+[=]{0,2}$@', $valore)) 
		                                      {$valori=@unserialize(@gzuncompress(@base64_decode($valore)));
		                                       if($valori){$this->bodyFile=&$valori['val'];
                		                                   $this->ext=&$valori['ext'];
                		                                   $this->file[$this->curFile]=array('reloaded'=>true);       
                		                                   return $this;
		                                                 }
		                                      }
		       
		       $this->bodyFile=&$valore;
		       if($valore) {
		    		        $exts=self::deduci_ext_da_file($this->bodyFile);
            		        if($exts) {
                    		            $this->ext='';
                    		            foreach ($exts as $ext) if(strlen($ext)>strlen($this->ext)) $this->ext=$ext;
                    		        }
                     	  }
 				return $this; 
 			}

 			function &get_value(){
 				return $this->bodyFile;
 			}
 			
 			
 			function &get_value_DB(){
 			    return $this->bodyFile;
 			}

		  /**
		   * 
		   * Restituisce informazioni di sitema sul file appena uploadato,
		   * funziona solo se il file è stato correttamente appena uploadato e se prima e' stato fatto myforms::check_errore() o myUpload::errore()
		   * @return myArrayObject @see myArrayObject
		   */
		   public function get_info_uploaded(){
		     if(!is_array($this->file[$this->curFile])) return $this->file[$this->curFile]=array();
		  	 return  new myArrayObject($this->file[$this->curFile]);
		  }



	/**
	* setta le estensioni ammesse
		  * @param	 array $estensioni E' un array con l'elenco delle estensioni ammesse (senza punto davanti)
		  */
	 public function set_ext_ammesse($estensioni) {
		 $this->ext_ammesse=array_flip(explode(',',strtolower(implode(',',$estensioni))));
		 return $this;
	}

	/**
	* Restituisce le estensioni ammesse
		  *  @return array
		  */
	 public function get_ext_ammesse() {
	  return array_flip($this->ext_ammesse);
	}

 
	
	 public function get_ext() {
	    return $this->ext;
	}

    /**
	* setta un titolo da far comparire sul link al file, da usare dopo check_errore
	* @param	 string $testo
	*/
	 public function set_Descrizione($testo) {
		 $this->descrizione=trim((string) $testo);
		 return $this;
	}


	 public function get_Descrizione() {
	    return ($this->descrizione?$this->descrizione:'file');
	}

	 public function set_show_info($anteprima=true,$max=true){
		$this->show_info=func_get_args();
		return $this;
	}




  /**
	* Imposta dell'HTML da visualizzare dopo l'icona del file
			  * @param	 string $html
			  */
     public function set_extra_html($html){
			  $this->extra=$html;
			  return $this;
		  }

	/**
	 * Restituisce il mimetype corrispondente all'estensione
	 * 
	 * @param string $ext
	 * @param boolean $effettiva se true restituisce quella trovata (anche null se non trovata), se true in caso di fallimento restituisce 'application/octet-stream'
	 * @return string
	 */	  
	 public static function get_MimeType($ext,$effettiva=false) {
	    $exts=self::get_MimeTypes();
	    if($effettiva) return isset($exts[strtolower($ext)])?$exts[strtolower($ext)]:'';
                  else return isset($exts[strtolower($ext)])?$exts[strtolower($ext)]:'application/octet-stream';
	}	  
		  
	/**
	 * Restituisce array con tutti i mimetype gestiti
	 * 
	 * @return string[]
	 */	  
	  public static function get_MimeTypes()
		  {
		      return array(
		          'odp'   => 'application/vnd.oasis.opendocument.presentation',
		          'odt'   => 'application/vnd.oasis.opendocument.text',
		          'ods'   => 'application/vnd.oasis.opendocument.spreadsheet',
		          'odb'   => 'application/vnd.oasis.opendocument.base',
		          'hqx'   => 'application/mac-binhex40',
		          'cpt'   => 'application/mac-compactpro',
		          'csv'   => 'text/x-comma-separated-values',
		          'bin'   => 'application/octet-stream',
		          'dms'   => 'application/octet-stream',
		          'lha'   => 'application/octet-stream',
		          'lzh'   => 'application/octet-stream',
		          'exe'   => 'application/octet-stream',
		          'class' => 'application/octet-stream',
		          'psd'   => 'application/x-photoshop',
		          'so'    => 'application/octet-stream',
		          'sea'   => 'application/octet-stream',
		          'dll'   => 'application/octet-stream',
		          'oda'   => 'application/oda',
		          'ai'    => 'application/pdf',
		          'pdf'   => 'application/pdf',
		          'ps'    => 'application/postscript',
		          'eps'   => 'application/postscript',
		          'smi'   => 'application/smil',
		          'smil'  => 'application/smil',
		          'mif'   => 'application/vnd.mif',
		          'xls'   => 'application/vnd.ms-excel',
		          'ppt'   => 'application/powerpoint',
		          'pptx'  => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		          'wbxml' => 'application/wbxml',
		          'wmlc'  => 'application/wmlc',
		          'dcr'   => 'application/x-director',
		          'dir'   => 'application/x-director',
		          'dxr'   => 'application/x-director',
		          'dvi'   => 'application/x-dvi',
		          'gtar'  => 'application/x-gtar',
		          'gz'    => 'application/x-gzip',
		          'gzip'  => 'application/x-gzip',
		          'php'   => 'application/x-httpd-php',
		          'php4'  => 'application/x-httpd-php',
		          'php3'  => 'application/x-httpd-php',
		          'phtml' => 'application/x-httpd-php',
		          'phps'  => 'application/x-httpd-php-source',
		          'js'    => 'application/javascript',
		          'swf'   => 'application/x-shockwave-flash',
		          'sit'   => 'application/x-stuffit',
		          'tar'   => 'application/x-tar',
		          'tgz'   => 'application/x-tar',
		          'z'     => 'application/x-compress',
		          'xhtml' => 'application/xhtml+xml',
		          'xht'   => 'application/xhtml+xml',
		          'zip'   => 'application/x-zip',
		          'rar'   => 'application/x-rar',
		          'mid'   => 'audio/midi',
		          'midi'  => 'audio/midi',
		          'mpga'  => 'audio/mpeg',
		          'mp2'   => 'audio/mpeg',
		          'mp3'   => 'audio/mpeg',
		          'aif'   => 'audio/x-aiff',
		          'aiff'  => 'audio/x-aiff',
		          'aifc'  => 'audio/x-aiff',
		          'ram'   => 'audio/x-pn-realaudio',
		          'rm'    => 'audio/x-pn-realaudio',
		          'rpm'   => 'audio/x-pn-realaudio-plugin',
		          'ra'    => 'audio/x-realaudio',
		          'rv'    => 'video/vnd.rn-realvideo',
		          'wav'   => 'audio/x-wav',
		          'jpeg'  => 'image/jpeg',
		          'jpe'   => 'image/jpeg',
		          'jpg'   => 'image/jpeg',
		          'png'   => 'image/png',
		          'gif'   => 'image/gif',
		          'bmp'   => 'image/bmp',
		          'tiff'  => 'image/tiff',
		          'tif'   => 'image/tiff',
		          'svg'   => 'image/svg+xml',
		          'css'   => 'text/css',
		          'html'  => 'text/html',
		          'htm'   => 'text/html',
		          'shtml' => 'text/html',
		          'txt'   => 'text/plain',
		          'text'  => 'text/plain',
		          'log'   => 'text/plain',
		          'rtx'   => 'text/richtext',
		          'rtf'   => 'text/rtf',
		          'xml'   => 'application/xml',
		          'xsl'   => 'application/xml',
		          'mpeg'  => 'video/mpeg',
		          'mpg'   => 'video/mpeg',
		          'mpe'   => 'video/mpeg',
		          'qt'    => 'video/quicktime',
		          'mov'   => 'video/quicktime',
		          'avi'   => 'video/x-msvideo',
		          'movie' => 'video/x-sgi-movie',
		          'doc'   => 'application/msword',
		          'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		          'dot'   => 'application/msword',
		          'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		          'word'  => 'application/msword',
		          'xl'    => 'application/excel',
		          'eml'   => 'message/rfc822',
		          'json'  => 'application/json',
		          'pem'   => 'application/x-x509-user-cert',
		          'p10'   => 'application/x-pkcs10',
		          'p12'   => 'application/x-pkcs12',
		          'p7a'   => 'application/x-pkcs7-signature',
		          'p7c'   => 'application/pkcs7-mime',
		          'p7m'   => 'application/pkcs7-mime',
		          'p7r'   => 'application/x-pkcs7-certreqresp',
		          'p7s'   => 'application/pkcs7-signature',
		          'crt'   => 'application/x-x509-ca-cert',
		          'crl'   => 'application/pkix-crl',
		          'der'   => 'application/x-x509-ca-cert',
		          'kdb'   => 'application/octet-stream',
		          'pgp'   => 'application/pgp',
		          'gpg'   => 'application/gpg-keys',
		          'sst'   => 'application/octet-stream',
		          'csr'   => 'application/octet-stream',
		          'rsa'   => 'application/x-pkcs7',
		          'cer'   => 'application/pkix-cert',
		          '3g2'   => 'video/3gpp2',
		          '3gp'   => 'video/3gp',
		          'mp4'   => 'video/mp4',
		          'm4a'   => 'audio/x-m4a',
		          'f4v'   => 'video/mp4',
		          'webm'  => 'video/webm',
		          'aac'   => 'audio/x-acc',
		          'm4u'   => 'application/vnd.mpegurl',
		          'm3u'   => 'text/plain',
		          'xspf'  => 'application/xspf+xml',
		          'vlc'   => 'application/videolan',
		          'wmv'   => 'video/x-ms-wmv',
		          'au'    => 'audio/x-au',
		          'ac3'   => 'audio/ac3',
		          'flac'  => 'audio/x-flac',
		          'ogg'   => 'audio/ogg',
		          'kmz'   => 'application/vnd.google-earth.kmz',
		          'kml'   => 'application/vnd.google-earth.kml+xml',
		          'ics'   => 'text/calendar',
		          'zsh'   => 'text/x-scriptzsh',
		          '7zip'  => 'application/x-7z-compressed',
		          'cdr'   => 'application/cdr',
		          'wma'   => 'audio/x-ms-wma',
		          'jar'   => 'application/java-archive',
		      );
		  }	  
	  
      /**
       * Restutuisce le possibili estensioni del file
       * @param  string $body
       * @return array 
       */
	 public static function deduci_ext_da_file(&$body){
	    
	    $mimes=static::get_MimeTypes();
	    $ext='';$exts=array();
	    $finfo='Finfo';
	    if (class_exists($finfo,false)) {
	         $finfo=new $finfo(FILEINFO_MIME_TYPE);
	         $bdy=$body;
	         $mime=$finfo->buffer($bdy);
	         if($mime && $mime!='application/octet-stream') 
	               { $mime=explode(';',$mime);
        	         $mime=trim((string) $mime[0]);
        	         
        	         if($mime=='application/x-gzip' && ($v=@gzuncompress($body))) 
        	                         {
                        	         foreach (static::deduci_ext_da_file($v) as $ex) $exts[]="$ex.gz"; 
                        	         if($exts) return $exts;
                        	        }
                    foreach ($mimes as $ex=>$mimes) if($mime==$mimes) $exts[]=$ex;
                    if($exts) return $exts;
        	        }
	    }
	  
	    if (strpos(trim((string) $body),"%PDF-")===0 &&  strrpos($body,'%%EOF')>strlen($body)-10) return array('pdf');
	    if (strpos($body,'ÐÏ')===0) {
	        $header=  @unpack ('A8ident/h32uid/vrevision/vversion/vbyteOrder/vssz/vsssz/x10/VsatSize/VdirSecId/x4/VminStreamSize/lssatSecId/VssatSize/lmsatSecId/VmsatSize',
	                           substr ($body, 0, 0x200)    );
	        if ($header['ident']== "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1" && $header['byteOrder'] == 0xfffe) 
	               {
	                if (strpos($body,'Word.Document.')!==false && strpos($body,'MSWordDoc')!==false) return array('doc');
	                if (strpos(preg_replace('#[^A-Z^a-z^@]*#','',$body),'MicrosoftExcel@')!==false) return array('xls');
	                if (strpos($body,'MSPublisher.')!==false) return array('pub');
	               }
	    }
	   
	    
	    if(!is_dir(__MYFORM_DATACACHE__.'/tmp_'.__CLASS__)) @mkdir(__MYFORM_DATACACHE__.'/tmp_'.__CLASS__,0770,true);
	    $tmp=tempnam(__MYFORM_DATACACHE__.'/tmp_'.__CLASS__,'test');
	    file_put_contents($tmp, $body);
	    
	    $ext='';
	    if (strpos($body,'PK')===0 ){
	        if(!is_callable('zip_open')) {
	                if(strpos($body,'word/settings.xml')!==false &&
            	       strpos($body,'word/fontTable.xml')!==false &&
            	       strpos($body,'word/webSettings.xml')!==false &&
            	       strpos($body,'word/document.xml')!==false ) $ext='docx';
            	    if(strpos($body,'xl/workbook.xml')!==false &&
            	       strpos($body,'xl/styles.xml')!==false ) $ext='xlsx';
        	       }
        	    else{
	                $zip = zip_open($tmp);
    	    		if ($zip && !is_numeric($zip)) 
            		          {$ext='zip';
            		           while ($zip_entry = zip_read($zip)) {
            		                  if (zip_entry_open($zip, $zip_entry) == FALSE) continue;
            		                  if (zip_entry_name($zip_entry)== "mimetype") 
            		                                      {
            		                                         $mimefile=zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
            		                                         $exts=array();
                                                             foreach ($mimes as $ex=>$mime) if($mime==$mimefile) $exts[]=$ex;
                                                             if($exts) return $exts;
                                                          }
            		                  if (zip_entry_name($zip_entry)== "[Content_Types].xml")
            		                                  {$info=simplexml_load_string(zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
            		                                      {
            		                                       foreach ($info as $ramo)
            		                                              switch ($ramo['PartName']) {
            		                                                  case '/word/document.xml':
            		                                                              $ext='docx';
            		                                                          break;
            		                                                  
            		                                                  case '/xl/workbook.xml':
            		                                                              $ext='xlsx';
            		                                                          break;
            		                                              }
            		                                       }
            		                                   }
            		                  @zip_entry_close($zip_entry);
            		                  if($ext!='zip') break;
            		              }
            		           @zip_close($zip);
	                           }
	               }
	         
	    }
	 
	    
        if(!$ext) {
                    $size = @getimagesize($tmp);
                    if(is_array($size)) 
                            {$exts=array();
                             foreach ($mimes as $ex=>$mime) if($mime==$size['mime']) $exts[]=$ex;
                             if($exts) return $exts;
                            }
                   }
        if (!$ext && bin2hex(substr($body,0,2)) == '1f8b' && ($v=@gzuncompress($body))) 
                                                                            {  $exts=array(); 
                                                                               foreach (static::deduci_ext_da_file($v) as $ex) $exts[]="$ex.gz";
                                                                               if($exts) return $exts;
                                                                            }
        if (!$ext && strpos($body,'<?'.'xml ')===0 && $body[strlen($body)-1]=='>' && @simplexml_load_string($body)) $ext='xml';
        if(!$ext && preg_match('@<[a-zA-Z]+[^>]*>.*<\/[a-zA-Z]+[^>]*>@USs', $body)) 
                   {$err=error_reporting(0);
                     $doc = new \DOMDocument();
                     if($doc->loadHTML($body)) 
                            {$html=$doc->saveHTML();
                             if(strip_tags($html)!==preg_replace('@<(p|body|!DOCTYPE|html|/p||/body|/html)[^>]*>@','',$html)) $ext='htm';
                            }
                    error_reporting($err);
                   }
                    
        unlink($tmp);
        if(!$ext) return array('application/octet-stream');
        return array($ext);                   
	}	  
/**
 * 
 * @param string $valore da convertire
 * @param false|true|'1' $consuffisso se true suffisso per esteso se 1 solo prima lettera
 * @param int $decimali numero di cifre decimali (solo se $consuffisso non è false e $valore>1M)
 * @return string
 */
	 public static function ricalcolasize($a,$consuffisso=true,$decimali=0) {
		$unim = array("","Kilo","Mega","Giga","Tera","Peta");
		if ($consuffisso) {
			$c = 0;
    		while ($a>=1024) {
        		$c++;
        		$a = $a/1024;
    			}
    		if($c<2 || ($a - floor($a))==0) $decimali=0;
    		return number_format(round($a,$decimali),$decimali,".",",")."&nbsp;".($consuffisso===1?$unim[$c][0]:$unim[$c]);
			}
		else {
			  unset($unim[0]);
			  $out=0;
		      $a=strtoupper($a);
		      foreach ($unim as &$nome) $nome=$nome[0];
		      $unim=array_flip($unim);
		      $p=1024;
			  foreach (array_keys($unim) as $pref)
			                 {$unim[$pref]="*$p";
			                  $p=$p*1024;
			                 }
              $a=strtr($a,$unim);
              eval("\$out={$a};");
              return $out;
		     }
	}
	
	public function get_download_url($file){
		$fun=&$this->download_script;
		if(!$fun || !is_callable($fun)) 
				$fun=function ($file) 
							{			 
								return "/".self::get_MyFormsPath()."scripts/uploaded.php?file=".urlencode(self::ppnEncrypt(serialize($file)));
							};
							 
		return $fun($file);
	}

	
	/**
	 * passare il codice di una funzione che riceve il l'arrai associativo con le info del file e restituisce l'url per il download
	 * @param \Closure  $funzione
	 * @return \Gimi\myFormsTools\PckmyFields\myUploadText
	 */
	 public function set_download_script($funzione){
	 	if(!is_callable($funzione)) $this->download_script=function ($file) use($funzione) {return $funzione;};
	 							else $this->download_script=$funzione;
		return $this;
	}
	
	
	
	private static function ppkey(){
		return '$M1y2U3p4èl5o6aç§°d+"';
	}
	
	public static function ppnEncrypt( $data  )
	{
		$method = 'aes-256-gcm';
		$key =hash('sha256', self::ppkey(), true);
		$iv = openssl_random_pseudo_bytes( openssl_cipher_iv_length( $method ) );
		$tag = ""; // openssl_encrypt will fill this
		$result = openssl_encrypt( $data , $method , $key , OPENSSL_RAW_DATA , $iv , $tag , "" , 16 );
		return base64_encode( $iv.$tag.$result );
	}
	
	
	public static function ppnDecrypt( $data  )
	{
		$method = 'aes-256-gcm';
		$data = base64_decode( $data );
		$key =  hash('sha256', self::ppkey(), true);
		$ivLength = openssl_cipher_iv_length( $method );
		$iv = substr( $data , 0 , $ivLength );
		$tag = substr( $data , $ivLength , 16 );
		$text = substr( $data , $ivLength+16 );
		return openssl_decrypt( $text , $method , $key , OPENSSL_RAW_DATA , $iv , $tag );
	}
	
	
	/**
	 * Restituisce il check usato per visualizzare l'opzione di rimozione del file
	 * @return myCheck
	 */
	 public function get_remove_check(){
	    if(!$this->remove_chk) {
	                    $this->remove_chk=new mySelect($this->get_name().'_remove','disabled',array($this->trasl('No')=>'disabled',$this->trasl('Sì')=>''));
                	    $this->remove_chk->set_js("document.getElementById('{$this->get_id()}').disabled=document.getElementById('{$this->remove_chk->get_id()}').selectedIndex==0;
                	                               if(document.getElementById('{$this->get_id()}').disabled) 
                	                                                                   document.getElementById('upl_div_{$this->get_id()}').style.display='none';
                                                                                 else  document.getElementById('upl_div_{$this->get_id()}').style.display='inline';",'onchange');
                	    }
	    if(!$this->get_value()) $this->remove_chk->set_value('disabled');
	                      else  $this->remove_chk->set_value('');
	    return $this->remove_chk;
	}	
	
	
	
       /**
    	 * Imposta aperttura in altra finestra del link per il download
    	 * @param string $stato
    	 */
    	 public function set_blank_per_download($stato=true){
    	    if($stato) $this->blank=" onclick='window.open(this.href, \"Allegato\", \"resizable=1,toolbar=1,menubar=1,location=1,scrollbars=1,resize=1\");return false' onkeypress='window.open(this.href, \"Allegato\", \"resizable=1,toolbar=1,menubar=1,location=1,scrollbars=1,resize=1\");return false' ";
    	          else $this->blank='';
    	    return $this;       
    	}
	
	
	/**
	* Restituisce il campo in html pronto per la visualizzazione
	 *  @return string
	 */
	 public function get_Html () {
	    $nofile=$icona=$name=$jsNofile =null;
	    $this->get_errore_diviso_singolo();
		$get_html=isset($this->Metodo_ridefinito['get_Html']['metodo'])?$this->Metodo_ridefinito['get_Html']['metodo']:null; if ($get_html!='') return $this->$get_html(isset($this->Metodo_ridefinito['get_Html']['parametri'])?$this->Metodo_ridefinito['get_Html']['parametri']:null);
		
		
		
		if ($this->get_value()) 
				  { if(!$this->ext) {$exts=static::deduci_ext_da_file($this->get_value());
				                     $this->ext='';
				                     foreach ($exts as $ext) if(strlen($ext)>strlen($this->ext)) $this->ext=$ext;
				                    }
				
				    $this->file[$this->curFile]['fromPOST']=$this->get_name();
				   
				    $download_script=$this->get_download_url($this->file[$this->curFile]);
				    $icona=$this->ext;
				    $descrizione='';
					if (!$this->descrizione) $descrizione="Download";
								  	    else $descrizione=$this->descrizione;
					$descrizione.=' '.self::ricalcolasize(strlen($this->get_value()))."bytes";
					$root= dirname(__FILE__).'/../MyUploadIcones/';
					if (!is_file("$root$icona.gif")) $icona='folder';
					$root= "/".self::get_MyFormsPath()."MyUploadIcones/$icona.gif";
					
					if ($this->show_info[0])  $icona="{$this->trasl("File attuale")}:<input type='image' onkeydown=\"".myFormAJAXRequest::get_disable_varname()."=true;PredPost=this.form.action;this.form.action='{$download_script}'\" onmousedown=\"".myFormAJAXRequest::get_disable_varname()."=true;PredPost=this.form.action;this.form.action='{$download_script}'\" onblur='this.form.action;this.form.action=PredPost;".myFormAJAXRequest::get_disable_varname()."=false'  {$this->blank} src='$root' alt=\"".myTag::htmlentities($this->descrizione)."\" title=\"".myTag::htmlentities($descrizione)."\" style='border:0' />&nbsp;";
										 else $icona='';
					if ($this->extra || $icona) $icona.="$this->extra ";
				}
	
		if(!$this->get_notnull()) {
                        			$c=$this->get_remove_check();
                        			$jsNofile=$c->get_attributo('onchange');
                        			$nofile="<label for=\"{$c->get_id()}\">&nbsp;{$this->trasl('Allega file')}&nbsp;</label>$c";
                        		   }
        $parti=explode('[',$this->get_name(),2);
        if(is_array($parti) )  {$name=$parti[0].'_file';
                                if(isset($parti[1])) $name.="[{$parti[1]}";
                                }
		return $this->jsAutotab().$this->send_html("$nofile
                                    				<div id='upl_div_{$this->get_id()}' style='display:inline'>$icona
                                        				<span>
                                        					 <input type='hidden'  {$this->stringa_attributi(array('id','type','title','value'),1)} value=\"".base64_encode(gzcompress($this->bodyFile,8))."\">
                                        					 <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"".self::$maxGlobal."\" />
                                        					 <input name=\"{$name}\" oninput=\"document.getElementById('_{$this->get_id()}').value=''\" ".$this->stringa_attributi(array('value','name'),1)." />".($this->show_info[1]?"(max&nbsp;".self::ricalcolasize($this->get_max(),true,1)."bytes)":'')."
                                        				</span>
                                    				</div>").
                                    				"<script type='text/javascript'>
                        		                      //<!-- 
                        		                      $jsNofile
                        		                      //-->
                        		                      </script>";
	 }
	
	 
	 
	
	
   /** @ignore*/
	 public function _get_html_show($pars, $null = null, $null2 = NULL){
	    
	   
		if ($this->get_value()!='') 
				  { $this->file[$this->curFile]['fromPOST']=$this->get_name();
				  	$download_script=$this->get_download_url($this->get_info_uploaded()[$this->curFile]);      
					$icona=$this->ext;
					if (!$this->descrizione) $descrizione="Download";
								  	    else $descrizione=$this->descrizione;
					$descrizione.=' '.self::ricalcolasize(strlen($this->get_value()))."bytes";
					
					$root= dirname(__FILE__).'/../MyUploadIcones/';
					if (!is_file("$root$icona.gif")) $icona='folder';
					$root= "/".self::get_MyFormsPath()."MyUploadIcones/$icona.gif";
					
					 
		      		if ($this->show_info[0])  $icona="{$this->trasl("File attuale")}:<input type='image' onkeydown=\"".myFormAJAXRequest::get_disable_varname()."=true;PredPost=this.form.action;this.form.action='{$download_script}'\" onmousedown=\"".myFormAJAXRequest::get_disable_varname()."=true;PredPost=this.form.action;this.form.action='{$download_script}'\" onblur='this.form.action;this.form.action=PredPost;".myFormAJAXRequest::get_disable_varname()."=false'  {$this->blank} src='$root' alt=\"".myTag::htmlentities($this->descrizione)."\" title=\"".myTag::htmlentities($descrizione)."\" style='border:0' />&nbsp;";
										 else $icona='';
					if ($this->extra || $icona) $icona.="$this->extra ";
					
					
				  }
	
			return "<div id='upl_div_{$this->get_id()}' style='display:inline'>$icona
                                        					<input type='hidden'  {$this->stringa_attributi(array('id','type','title','value'),1)} value=\"".base64_encode(gzcompress($this->bodyFile,8))."\">
                                        			</div>";
		}
	
	

   /** @ignore */
    protected function _get_html_hidden($pars){
    		return "<input name=\"{$this->get_name()}_file\"  />";
   }


}