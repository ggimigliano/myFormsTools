<?php
/**
 * Contains Gimi\myFormsTools\myMail.
 */

namespace Gimi\myFormsTools;


use Gimi\myFormsTools\PckmyFields\myField;



/**
 *  Questa classe permette la costruzione facilitata di mail da inviare, basato su PHPMailer
 *  
 */
	
class myMail extends \PHPMailer {
/**
 * @ignore
 */
	public $LastId,	$NEmbedded=array(),$FEmbedded=array(),
     $Priority          = 3, //Email priority (1 = High, 3 = Normal, 5 = low).
     $CharSet           = "iso-8859-1",
	 $ContentType        = "text/plain",
	 $Encoding          = "8bit",
	 $ErrorInfo         = "",
	 $From               = "root@localhost",
	 $FromName           = "Root User",

     $Sender            = "", // email (Return-Path)
     $Subject           = "",
     $Body               = "",
     $AltBody           = "",
     $WordWrap          = 78,
     $Mailer            = "smtp",
     $Sendmail          = "",
     $PluginDir         = "",
     $ConfirmReadingTo  = "",
     $Hostname          = "",
	 $SMTPAutoTLS = false;
	
     /**
      * @ignore
      */
	protected $SERVER;
	
	
	/**
     * Costruttore
     * @param string $nome_mittente				Nome descrittivo del mittente
     * @param string $email_mittente			Email del mittente
     * @param string|array $email_destinatari	L'email del destinatario o un array con tutte le mail o un array associativo che associa "email Destinatario"=>"Descrizione destinatario"
     * @param string $oggetto 					Oggetto della mail
     * @param string $testo   					Testo della mail, eventualmente in html (completo di tag body)
     * @param array $allegati					Array che associa al "nome del file (completo di percorso)"=> "un nome descrittivo" se stringa vuota si usa il nome del file senza percorso
     * @return void
     */
	 public function __construct($nome_mittente,$email_mittente,$email_destinatari,$oggetto='',$testo='',$allegati=array()){
		static $NEmbedded;
		$this->NEmbedded=&$NEmbedded;
		$this->SERVER['SERVER_ADDR']=(isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:(isset($_SERVER['LOCAL_ADDR'])?$_SERVER['LOCAL_ADDR']:null));
        $this->SERVER['REQUEST_URI']=$_SERVER['REQUEST_URI'];
        $this->SERVER['SERVER_NAME']=$_SERVER['SERVER_NAME'];
        $this->SERVER['DOCUMENT_ROOT']=$_SERVER['DOCUMENT_ROOT'];
        $this->SERVER['HTTP_HOST']=$_SERVER['HTTP_HOST'];
        
		$this->Priority =1;
		$this->PluginDir=dirname(__FILE__)."/phpmailer/";
		
		$f=new MyField();
		if($f->get_dizionario()) $lang=strtolower($f->get_dizionario()->get_al());
		                    else $lang='it';
		$this->SetLanguage($lang,"{$this->PluginDir}language/");


		$this->FromName =$nome_mittente;
		$this->From     =$email_mittente;
		
		parent::__construct(true);
		if (!is_array($email_destinatari)) $email_destinatari=array($email_destinatari);
		$m=array();
		foreach ($email_destinatari as $k=>$v)
			    try{
							 if(!trim((string) $v)) {$v=$k;$k=0;}
			                 if (is_string($k))  $this->AddAddress($k,$v);
							         elseif(!preg_match('@([^<]+)<([^>]+)>@',trim((string) $v),$m)) $this->AddAddress($v);
							                else $this->AddAddress($m[2],$m[1]);
			         } catch (\Exception $e) {}
            
        parent::__construct(false);
	    if (is_array($allegati))
					foreach ($allegati as $file=>$nome)
						                  $this->AddAttachment($file,$nome);
		
		$this->IsSMTP();
		if($oggetto!=='') $this->Subject=	$oggetto;
		if($testo!=='')   $this->Set_Testo($testo);
	}

	/**
	 * @ignore
	 */
	 public function __call($m,$v){
	   if(strtolower($m)==strtolower(__CLASS__)) {
	        $pars=array();
	        for ($i=0;$i<count($v);$i++) $pars[]="\$v[{$i}]";
	        eval("return $m::__construct(".implode(',',$pars).");");
	        return;
	       }
	}

	/**
     * Imposta la richiesta di conferma
     * @param string $email email a cui indirizzare la conferma apertura email,se omesso si usa l'indirizzo del mittente
     * @return void
     */
	 public function set_conferma($email='') {
		if(strpos($email,'@')===false)   $this->ConfirmReadingTo=$this->From;
						    		else $this->ConfirmReadingTo=$email;
	}


	/**
     * Setta l'oggetto della mail
     * @param string $oggetto
     * @return void
     */
	 public function set_oggetto($oggetto){
		$this->Subject=	$oggetto;
		return $this;
	}


	/**
     * Setta il testo delle mail
     * @param string $testo   Testo della mail, eventualmente in html (completo di tag body)
     * @return void
     */
	 public function set_Testo($html){
	    if(strip_tags($html)==$html) {$this->IsHTML(false);$this->Body=$html;} 
	                 else { if(stripos($html,'<body')===false) $html='<html><head></head><body>'.$html.'</body></html>';
	                                   elseif(stripos($html,'<head')===false) $html=str_ireplace('<body', '<head></head><body', $html);
	                        if(!preg_match('@<meta[^>]+Content-Type[^>]+>@Ui',$html)) $html=str_ireplace('</head', '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /></head', $html);
	                        
	                        $this->IsHTML(true);
	                        $this->Body=self::normalizeBreaks($this->IncorporaImmagini(array('img'=>'src'),$this->IncorporaCSS(array('link'=>'href'),$html)));
	                        $this->AltBody = self::normalizeBreaks($this->html2text($html));
	                        if (!$this->alternativeExists()) {
	                            $this->AltBody = 'Questa mail è in formato HTML, per vederla correttamente si prega di usare un altro programma' .
	                                self::CRLF . self::CRLF;
	                        }
	                      }
		return $this;
	}



	/**
     * Restituisce l'Id della mail inviata
     * @return string
     */
	 public function get_Id_mail(){
		return $this->LastId;
	}


	/**
	 * restituisce l'ultimo errore trovato
	 *
	 * @return string
	 */
	 public function get_last_error() {
		return $this->ErrorInfo;
	}



	/**
     * Invia la mail se c'e' errore lo restituisce
     * @return string
     */
	 public function Send() {
		try{
    		$this->ErrorInfo=false;
    			parent::__construct(true);
    			$out=parent::Send();
    			$this->NEmbedded=array();
    			parent::__construct(false);
    		if($this->ErrorInfo) return $this->ErrorInfo;
    						else return !$out;
	    } catch (\Exception $e) {parent::__construct(false);
	    						return $e->getMessage();
	    						}
	}


	 /**
     * Aggiunge un allegato alla mail
     * @param string $path E' il percorso del file da allegare
     * @param string $name E' il nome da dare al file allegato all'interno della mail
     * @return void
     */
	 public function AddAttachment($path, $name = "",$null1=null,$null2=null,$null3=null){return parent::AddAttachment($path,$name,"base64", $this->mime_content_type($path));}

    /**
     * Aggiunge un destinatario
     * @param string $address  indirizzo email
     * @param string $name     Eventuale nome descrittivo associato alla mail
     * @return void
     */
     public function AddAddress($address, $name = "") { return parent::AddAddress($address, $name);   }

    /**
     * Aggiunge un destinatario in conoscenza "Cc"
     * @param string $address  indirizzo email
     * @param string $name     Eventuale nome descrittivo associato alla mail
     * @return void
    */
     public function AddCC($address, $name = "") {parent::AddCC($address, $name);}

    /**
     * Aggiunge un destinatario in conoscenza nascosta "Ccn"
     * @param string $address  indirizzo email
     * @param string $name     Eventuale nome descrittivo associato alla mail
     * @return void
    */
     public function AddCCn($address, $name = "") { $this->AddBCC($address, $name);	  }

    /**
     * Aggiunge un destinatario a cui i rispondere
     * @param string $address  indirizzo email
     * @param string $name     Eventuale nome descrittivo associato alla mail
     * @return void
     */
     public function AddReplyTo($address, $name = "") { parent::AddReplyTo($address, $name);}

    /**
     * @ignore
     */
    public function html2text($html, $advanced = array('do_links' => 'inline','width' => 70)){
         if ($advanced) {
                        if(!class_exists('Html2Text',false)) include_once("{$this->PluginDir}extras/Html2Text.php");
                        \html2text::$ENCODING=strtoupper($this->CharSet);
                        $htmlconverter = new \html2text($html,$advanced);
                        return $htmlconverter->gettext();
                       }
          return html_entity_decode(
                    trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/si', '', $html))),
                    ENT_QUOTES,
                    $this->CharSet
                );
    }
    
	

	/** @ignore */
	 public function mime_content_type($filename){return self::filenameToType($filename);}


	/** @ignore */
     public function AddStringImage($file, $cid, $name = "", $encoding = "base64") {
        $this->addStringEmbeddedImage($file,$cid,$name,$encoding, $this->mime_content_type($name),'inline');
        return true;
    }

    
    /** @ignore */
    protected function buildPath($im) {
        $file=null;
    	if($im[0]=='/' && is_file("{$this->SERVER['DOCUMENT_ROOT']}$im")) $file="{$this->SERVER['DOCUMENT_ROOT']}$im";
    		elseif(is_file($im)) $file=$im;
    			elseif(strpos($im,'http')===0 || (strpos($im,"://{$this->SERVER['SERVER_NAME']}") ||
    				   							  strpos($im,"://{$this->SERVER['SERVER_ADDR']}")
												  )
    				  ) $file=$im;
    	if ($file!==null && is_file($file)) return $file;
    								   else return null;		
    }
    
    
    /** @ignore */
    protected function buildUrl($im) {
    if(is_file($im)) return $im;	
    	elseif($im[0]=='/') $im="http://{$this->SERVER['HTTP_HOST']}$im";
    		elseif(strpos($im,'http')===0 || (strpos($im,"://{$this->SERVER['SERVER_NAME']}") ||
    			   							  strpos($im,"://{$this->SERVER['SERVER_ADDR']}")
    										)
    			) return $im;
      	else return "http://{$this->SERVER['HTTP_HOST']}".dirname($this->SERVER["REQUEST_URI"])."/$im";
    return $im;	
    }

    
    /** @ignore */
    protected function EncodeFile($path, $encoding = 'base64') {
    	if(!isset($this->FEmbedded[$path])) $this->FEmbedded[$path]=parent::EncodeFile($path, $encoding);
    	return $this->FEmbedded[$path];
    }
    
    
    
  /** @ignore */
	 public function embedElement($im) {
		   $im=explode('?',$im);
           $im=$im[0];
		   if(!$this->NEmbedded[$im] && ($file=$this->buildPath($im))!==null)
								{
								 $i=count($this->NEmbedded)+1;
		                         $dominio=explode('@',$this->From,2);
		                         $dominio=preg_split('@[\>\<]+@',$dominio[1],2);
		                         $dominio=$dominio[0];
		                         if(!$dominio) $dominio='myforms.0';
                                 try{  $this->NEmbedded[$im]="myembedded{$i}@$dominio";
                                        $ok=$this->AddEmbeddedImage(realpath($file),$this->NEmbedded[$im],basename($file),'base64',$this->mime_content_type($file));
                                 	 } catch (\Exception $e) {$ok=false;}
                                if(!$ok) return false;
                                }
		   return "cid:{$this->NEmbedded[$im]}";
		}



	/** @ignore */
	 public function embedElements($elementi,$testo) {
		  foreach ($elementi as $attr=>$im)
				if($im)
				   {
				   $cid=$this->embedElement($im);
					if($cid)  $testo=preg_replace('~'.$attr.'=[\'"]'.$im.'[\'"]',"$attr='$cid'~mU", $testo);
						 else $testo=preg_replace('~'.$attr.'=[\'"]'.$im.'[\'"]',"$attr='{$this->buildUrl($im)}'", $testo);
					}
		return $testo;
	}


	/** @ignore */
	 public function CreateHeader() {
	    $trovato=null;
		try{
		$header=parent::CreateHeader();
		preg_match("/Message-ID: <(.*)>/i",$header,$trovato);
		if (count($trovato)==2) $this->LastId=$trovato[1];
						  else  $this->LastId=null;
				} catch (\Exception $e) {}

		return $header;
	}


	/*
	function Check_Id($mail_id,$user='',$pass=''){
		if (!is_array($mail_id)) $mail_id=array();
		$pop=new POP3();
		$pop->connect($this->Host);
		if (!$pop) return false;
		if ($user && $pass) $esito=$pop->login($user,$pass);
					   ELSE $esito=$pop->login($this->Username,$this->Password);
		if (!$esito) return false;
		$stato=$pop->get_office_status();
		for ($i=1;$i<$stato['count_mails'];$i++)
					{
					echo implode("\n",$pop->get_top($i));
					echo "\n------------------------------------------\n";
					}
		$pop->close();

	}
	*/

	

	/** @ignore */
	protected function IncorporaImmaginiCSS($txt) {
	    $elementi=array();
		try{
		if (preg_match_all('/url[\(\'|\("|\(](.*?)[\'\)|"\)|\)]/ix',$txt,$elementi) &&
			is_array($elementi[1])) {
					foreach ($elementi[1] as $i=>$im) if ($id=$this->embedElement($im)) $txt=str_replace($elementi[0][$i],"url({$id})", $txt);
			}
		} catch (\Exception $e) {}
		return $txt;
	}
	
	
	

	/** @ignore */
	 public function IncorporaCSS($tags=array('link'=>'href'),$txt='') {
	    $elementi=array();
		if (!$txt) $txt=$this->Body;
		$with_subst=$to_subst=array();
		try{
			foreach ($tags as $tag=>$par) 
				if(preg_match_all('/<'.$tag.' ([^>]*)'.$par.'\s*=\s*[\'"](.*?)[\'"]([^>]*)>/iSx',$txt,$elementi) &&
				   is_array($elementi[2])) 
					{
					foreach ($elementi[2] as $i=>$im)  
						if($file=$this->buildPath($im)) {
							$to_subst[]=$elementi[0][$i];
							$with_subst[]="<style>".file_get_contents($file).'</style>';
						}
						
					}
			} catch (\Exception $e) {}
			
	 $txt=(count($to_subst)>0?str_replace($to_subst, $with_subst, $txt):$txt);
	 
	 $with_subst=$to_subst=array();
	 if(preg_match_all('/<style[^>]*>([^<]+)<\/style>/iUS',$txt,$elementi) &&
				   is_array($elementi[1])) 
					{
						$to_subst[]=$elementi[0][$i];
						$with_subst[]="<style>".$this->IncorporaImmaginiCSS($elementi[0][$i]).'</style>';
					}
	 return $txt=(count($to_subst)>0?str_replace($to_subst, $with_subst, $txt):$txt);
	}
	
	


	/** @ignore */
	 public function IncorporaImmagini($tags=array('link'=>'href','img'=>'src'),$txt='') {
	    $elementi=array();
		 if (!$txt) $txt=$this->Body;
		 try{
    		 foreach ($tags as $tag=>$par) {
    		    if(preg_match_all('/<'.$tag.' ([^>]*)'.$par.'\s*=\s*[\'"](.*?)[\'"]([^>]*)>/iSx',$txt,$elementi) &&
    		       is_array($elementi[2]))
    		          foreach ($elementi[2] as $i=>$im)
    		               if ($id=$this->embedElement($im))
    		 	          	       	 $txt=str_replace($elementi[0][$i],"<$tag {$elementi[1][$i]} $par='$id' {$elementi[3][$i]}>", $txt);
		 }

		 
		 if (preg_match_all('/<([^>]*)url[\(\'|\("|\(](.*?)[\'\)|"\)|\)]([^>]*)>/ix',$txt,$elementi) &&
		     is_array($elementi[2])) {
		 		//	print_r($elementi);
		          foreach ($elementi[2] as $i=>$im)
		               if ($id=$this->embedElement($im))
		               			 $txt=str_replace($elementi[0][$i],
		 									 "<{$elementi[1][$i]}url({$id}){$elementi[3][$i]}>", $txt);
		 		}	
		   	} catch (\Exception $e) {}
		  
	     return $txt;
	}


	/**
	 * Sends mail via SMTP using PhpSMTP
	 * Returns false if there is a bad MAIL FROM, RCPT, or DATA input.
	 * @param string $header The message headers
	 * @param string $body The message body
	 * @uses SMTP
	 *
	 * @return bool
	 */
	protected function SmtpSend($header, $body) {
	    if(!class_exists('smtp')) require_once $this->PluginDir . 'class.smtp.php'; //unica modifica fatta
	    return parent::SmtpSend($header, $body);
	}
}