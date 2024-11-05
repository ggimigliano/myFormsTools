<?php
/**
 * Contains Gimi\myFormsTools\PckmyPlugins\mySecurizer.
 */

namespace Gimi\myFormsTools\PckmyPlugins;


use Gimi\myFormsTools\PckmyFields\myUUID;
use Gimi\myFormsTools\PckmySessions\mySessions;

                                                


/**
 * 
 * Questa classe è un singleton permette di cifrare dei parametri da inviare in GET o POST avendone poi la decodifica in automatico 
 * <code>
 *  session_start();
 *   
 *  $secure=mySecurizer::getInstance();
 *  try{
 *       $secure->restore_get_post(); //se e' stato premuto F5 ripristina GET e POST a valori non codificati
 *      }  //se trova un erroee nella decodifica del GET e POST
 *       catch(Exception $e){	myForm::forza_errore("Errore interno"); } //forza errore nelle myForms	 
 *  $FORM->set_secure($secure); //aggancia la codifica ai campi nascosti della $FORM
 *
 * </code>
 */
	
class mySecurizer  {
/** @ignore */
private $session=array(),$encoded=array(),$eccezioni=array(),$lastStop;
/** @ignore */
private static $current_checksum,$istanza;

			

            /**
             * 
             * @param string $namespace    namespace per evitare conflitti con codifiche fatte altrove
             * @param mySessions $Sessione necessaria per tenere traccia delle codifiche fatte, se umessa ne crea una sua
             */
              public static function getInstance($namespace='',mySessions $Sessione=null) {
                 if(!self::$istanza) self::$istanza=new mySecurizer($namespace,$Sessione);
                 return  self::$istanza;
             }


			/**
			 * @param string $namespace    namespace per evitare conflitti con codifiche fatte altrove 
			 * @param mySessions $Sessione necessaria per tenere traccia delle codifiche fatte, se umessa ne crea una sua
			 */
			 private function __construct($namespace='',mySessions $Sessione=null) {
				if($Sessione) $this->session=$Sessione;
					else {if(!$namespace) $namespace=__CLASS__;
						  $this->session=new mySessions($namespace);
				 	     }
				 	     
				self::$istanza=$this; 	     
				static::checksum_user_sent(); 
				$this->encoded=$this->session->get('encoded');
				$this->session->set('checksum_user_sent',self::$current_checksum);
				//echo '<pre>';print_r($this->encoded);;
			}
			
			
			private static function myUUID(){
			    static $predUUIDs;
			    do{  $uid=myUUID::v4();}while(isset($predUUIDs[$uid]));
			    $predUUIDs[$uid]=1;
			    return $uid;
			}

			/**
			 * 
			 * Restituisce true se e' stato premuto "Aggiorna" sul browser
			 * @return bool
			 */
			function isF5(){
				return $this->session->get('checksum_user_sent')==self::$current_checksum;
			}
			
			
			 public function seemsSecurized($v){
			    $v=@unserialize(@base64_decode($v));
			    return is_array($v) && isset($v['val']) && isset($v['chk']) && isset($v['id']);
			}
			
			
		
			
			
			
			/**
			 * Genera un checksum in funzione dello stato (get,post)
			 * @return string 
			 */
		     public static function checksum_user_sent(){
		        $files=array();
		    	if(!self::$current_checksum) {
					if ($_FILES) 
							 {$files=$_FILES;
					  		  foreach (array_keys($_FILES) as $nome) unset($files[$nome]['tmp_name']);
							 }
				   self::$current_checksum=static::build_chk(serialize($_GET).serialize($_POST).serialize($files));
				}
		    	return self::$current_checksum;
			}
			
			
			private static function build_chk($v){
			    return sha1(md5($v).'$'.$v.'§'.md5(strrev($v)));
			}
			
			
			 public function unset_key($campo,$always=true) {
			    if($always) $this->eccezioni[$campo]=1;
			    if(isset($this->encoded[self::$current_checksum]) && 
			       isset($this->encoded[self::$current_checksum][$campo])) unset($this->encoded[self::$current_checksum][$campo]);			
			    $this->session->set('encoded',$this->encoded);
			    return $this;
			}
			
			
			/**
			 * Codifica un parametro restituendo il nome codificato per quel parametro
			 * <code>
			 *  $s=new mySecurizer('mions',new mySessions('miaproc'));
			 *  $s->encode('a',1');    //abbina xxxxxxxxx=>yyyyyyyyyyy
			 *  $s->encode('a','2');   //abbina xxxxxxxxx=>zzzzzzzzzzz	
			 *  //mentre
			 * </code>
			 * @param string $campo Nome parametro
			 * @param mixed $v Valore parametro
			 * @return string
			 */
			 public function encode($campo,$v) {
			    if($this->eccezioni[$campo]) return $v;
			    static $primaencode;
			    if(!$primaencode){
			                       while(count($this->encoded)>10) array_shift($this->encoded);
			                       $this->encoded=array();
			                      $this->encoded[self::$current_checksum]=array();
			                      $primaencode=true;
			                     }

			    $v=serialize($v);
			    $chk=self::build_chk($v);
			    $id=static::myUUID();
			    $isOld=false;
				if(isset($this->encoded[self::$current_checksum][$campo]))
				       foreach ( $this->encoded[self::$current_checksum][$campo] as &$prec) 
				                  if((isset($prec['chk']) && $prec['chk']===$chk) ||  
				                     (isset($prec['chk']) && $prec['val']===$v))
    				                   {$id=$prec['id'];
				                        $isOld=true;
				                        break;
				                       }
				       
				if(!$isOld) {
			                 if(strlen($v)>1024*100) $this->encoded[self::$current_checksum][$campo][]=array('id'=>$id,'chk'=>$chk,'len'=>strlen($v));
                                          else  $this->encoded[self::$current_checksum][$campo][]=array('id'=>$id,'val'=>&$v);
                             $this->session->set('encoded',$this->encoded);
				            }
                
                if(strlen($v)<=1024*100) $out=array('id'=>$id);
                              else $out=array('id'=>$id,'val'=>&$v, 'chk'=>$chk);
                return base64_encode(serialize($out));
			}
			
		
			/**
			 * Decodifica un array di parametri precedentemente codificati,
			 * se un campo codificato risulta avere un valore diverso da quello atteso restituisce falso
			 * @param array $array
			 * @return int|null se ok un null se fallita 
			 */
			 public function is_decodable_array( &$array){
			    $id_codifica=-1;$esito=true;
			    if($array) 
			     	foreach ($this->encoded as $id_codifica=>&$codifiche)
    					foreach ($array as $campo=>&$id) {
    					        //   echo "TEST $id_codifica $campo<br>";
    					         $esito=$this->decode($campo,$id,$codifiche);
    						     if($esito===null) break(1);
    					       }
				return $esito===null?null:$id_codifica;		
			}
			
			/**
			 * A differenza di @see mySecurizer::is_decodable_array()
			 * Questa funzione restituisce un array codificato con i valori 
			 * dell'ultima codifica, se l'array risulta modificato lancia un'eccezione
			 * N.B. DEVE ESSERE FATTA PRIMA DI INIZIARE A CODIFICARE NUOVI CAMPI ALTRIMENTI L'ULTIMA CODIFICA VIENE CANCELLATA
			 * @throws \Exception
			 * @param array $array
			 * @return void
			 */
			 public function restore_array( &$array){
				if($array) {$esito=$this->is_decodable_array($array);
				            if($esito===null) throw new \Exception($this->lastStop[0].'|'.$this->lastStop[1]);
				            foreach ($array as $campo=>&$id) $id=$this->decode($campo,$id,$this->encoded[$esito]);
				            }
				return $this;		
			}
			
			
			/**
			 * Decodifica il POST
			 * N.B. DEVE ESSERE FATTA PRIMA DI INIZIARE A CODIFICARE NUOVI CAMPI ALTRIMENTI L'ULTIMA CODIFICA VIENE CANCELLATA
			 * @see mySecurizer::is_decodable_array()
			 * @param boolean $distruttivo se true una volta usata una chiave non si puo' piu' usare per decodificare
			 * @return boolean false se qualche valore e' codificato male
			 */
			 public function is_decodable_post(){
				return $this->is_decodable_array($_POST);
			}
			
			
			/**
			 * Decodifica il GET
			 * N.B. DEVE ESSERE FATTA PRIMA DI INIZIARE A CODIFICARE NUOVI CAMPI ALTRIMENTI L'ULTIMA CODIFICA VIENE CANCELLATA
			 * @see mySecurizer::is_decodable_array()
			 * @param boolean $distruttivo se true una volta usata una chiave non si puo' piu' usare per decodificare
			 * @return false se qualche valore e' codificato male
			 */
			 public function is_decodable_get(){
				return $this->is_decodable_array($_GET);
			}
			
			/**
			 * Decodifica GET e POST nell'ordine
			 * N.B. DEVE ESSERE FATTA PRIMA DI INIZIARE A CODIFICARE NUOVI CAMPI ALTRIMENTI L'ULTIMA CODIFICA VIENE CANCELLATA
			 * @see mySecurizer::is_decodable_array()
			 * @param boolean $distruttivo se true una volta usata una chiave non si puo' piu' usare per decodificare
			 * @return false se qualche valore e' codificato male
			 */
			 public function is_decodable_get_post(){
				return $this->is_decodable_get() && $this->is_decodable_post();
			}

			
			/**
			 * Effettua restore di POST
			 * N.B. DEVE ESSERE FATTA PRIMA DI INIZIARE A CODIFICARE NUOVI CAMPI ALTRIMENTI L'ULTIMA CODIFICA VIENE CANCELLATA
			 * @see mySecurizer::restore_array()
			 */
			 public function restore_post(){
				return $this->restore_array($_POST);
			}
			
			
			/**
			 * Effettua restore di GET
			 * N.B. DEVE ESSERE FATTA PRIMA DI INIZIARE A CODIFICARE NUOVI CAMPI ALTRIMENTI L'ULTIMA CODIFICA VIENE CANCELLATA
			 * @see mySecurizer::restore_array()
			 */
			 public function restore_get(){
				return $this->restore_array($_GET);
			}
			
			
			
			/**
			 * Effettua restore di GET e POST
			 * N.B. DEVE ESSERE FATTA PRIMA DI INIZIARE A CODIFICARE NUOVI CAMPI ALTRIMENTI L'ULTIMA CODIFICA VIENE CANCELLATA
			 * @see mySecurizer::restore_array()
			 */
			 public function restore_get_post(){
				 $this->restore_get();
				 $this->restore_post();
				 return $this;
			}
			
			/**
			 * Decodifica un parametro codificato
			 * N.B. DEVE ESSERE FATTA PRIMA DI INIZIARE A CODIFICARE NUOVI CAMPI ALTRIMENTI L'ULTIMA CODIFICA VIENE CANCELLATA
			 * @param string $campo nome del campo codificato
			 * @param string $val   valore codificato
			 * @return mixed null in caso di errore oppure il valore decodificato
			 */
			function &decode($campo,$id,&$codifica=array()) {
			    if(!$codifica) $codifica=$this->encoded[self::$current_checksum];
				if(!isset($codifica[$campo])) return $id;
				$this->lastStop=null;
			    foreach ($codifica[$campo] as &$snt) 
			          {$rcvd=@unserialize(base64_decode($id));
			           if(!is_array($rcvd)) {//nel caso lo stesso campo arrivi non serializzato per effetto di altri form...
                			                 if( (isset($snt['val']) && unserialize($snt['val'])==$id) 
            			                                                     ||
                			                     (isset($snt['chk']) && $snt['len']===strlen($id) && $snt['chk']===static::build_chk($id)) ) return $id;
			                                 $this->lastStop=array($campo,'Non deserializzabile:'.$id.'!='.$snt['val']);
			                                 continue;
			                                 }  
					 
					   if($snt['id']==$rcvd['id']) 
					                     {
					                      if(isset($snt['val'])) $val=@unserialize($snt['val']);
					                                       else { if($rcvd['chk']!==$snt['chk'] ||
					                                                 $snt['len']!==strlen($rcvd['val']) ||
					                                                 $rcvd['chk']!==static::build_chk($rcvd['val'])
					                                                 ) {$this->lastStop=array($campo,'chk non valido:'.$val);
					                                                                                                 continue;
					                                                                                                 }  
					                                              $val=@unserialize($rcvd['val']);
					                                            }
					                                       
					                                       
					                      if($val!==null) return $val;
					                                 else {$this->lastStoparray($campo,'Dato anomalo:'.$val);
					                                       continue;
					                                      }                                        
					                     }
					  $this->lastStop=array($campo,"Chiave non trovata o diversa {$snt['id']}=={$rcvd['id']}");      
					 }
				 return null;	 
			}	 
			
			
}