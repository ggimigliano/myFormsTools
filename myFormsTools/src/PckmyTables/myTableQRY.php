<?php
/**
 * Contains Gimi\myFormsTools\PckmyTables\myTableQRY.
 */

namespace Gimi\myFormsTools\PckmyTables;


use Gimi\myFormsTools\PckmyDBAdapters\myAdoDBAdapter;
use Gimi\myFormsTools\PckmySessions\mySessions;
use PHPSQLParser\PHPSQLParser;
use PHPSQLParser\builders\SelectStatementBuilder;



/**
* Classe per la Gestione facilitata di tabelle html basate su query
*  
* es. Supponiamo di voler fare una visualizzazione paginata dei nominativi
* <code>
* //istanzio la classe passando connessione, query, se salvare il risultato della query in sessione, parametri aggiuntivi per la TABLE
* $mat=new myTableQry($conn,"Select id,cognome,nome,matricola from utenti where cognome<>'' order by cognome,nome",true,'border=1 width=100%');
* $mat->set_limit(5,$_GET['MyPage']); //setto 5 righe per pagina a partire da quella passata in _GET (si fa in automatico)
*
* echo $mat->get_html();	//visualizzo tabella
* $pager=$mat->get_pager(); //estrapolo il visualizzatore di pagine disponibili che crea l'apposito link da passare in _GET
* echo "Scegli Pag. ".$pager->get_html(); //visualizzo il visualizzatore
* </code>
**/
	
Class myTableQRY extends myTable{
/** @ignore */
protected   $conn,$sessione;


   /**
    * 
    * @param    mixed $conn  E' connessione DB
    * @param    string|array $qry  E' la query da lanciare oppure un array che ha  nella posizione 0 una query parametrica e nella posizione 1 un array con i parametri
    * @param    boolean $ForzaQRY  Opzionale se true si ripete la query ad ogni cambio pagina o se false(default) si riusano i valori gia estrapolati nella query precedente
    * @param    string $parsTable  Opzionale e' il nome della classe css da associare al tag TABLE
    * @param    string $parsTR  Opzionale e' il nome della classe css da associare al tag TR
    * @param    string $parsTD  Opzionale e' il nome della classe css da associare al tag TD
    * @param    string $parsTH Opzionale sono i parametri da associare al tag TH
    **/
	 public function __construct($conn,$qry,$ForzaQRY=false,$parsTable='',$parsTR='',$parsTD='',$parsTH='') {
	    require_once dirname(__FILE__) . '../../thrd/PHP-SQL-Parser/vendor/autoload.php';
	    
	    $this->conn=myAdoDBAdapter::getAdapter($conn);
		$this->sessione=new mySessions(get_class($this));
		//$conn->debug=1;
		$qry=new myTableQryArray($qry);
		$cached=@unserialize(@gzuncompress($this->sessione->get('infos')));
		$md5=md5($qry);
		if(isset($cached[$md5])) $cached=array();
						  else  {$cached=$cached[$md5];
								 $this->intestazioni=$cached['intestazioni'];
								}
		if ($_GET['MyPage'] && !$ForzaQRY  && $cached['valori']) $this->valori=&$cached['valori'];
						else{
							$cached=array($md5=>array('valori'=>'',
													  'intestazioni'=>'')
											);
							$mode=$this->setfetchmode(ADODB_FETCH_NUM);
							          $cached[$md5]['valori']=$this->conn->getarray($qry->qry,$qry->pars);
							$this->setfetchmode($mode);

							if($cached[$md5]['valori']) $cached[$md5]['intestazioni']=$this->fetch_qry_intestazioni($qry); 
				    	    	
				    		$this->set_matrix($cached[$md5]['valori'],true,$cached[$md5]['intestazioni']);
				    		if(!$ForzaQRY) $this->sessione->set('infos',gzcompress(serialize($cached),9));
									else   $this->sessione->set('infos',gzcompress(serialize(array($md5=>array('valori'=>'',
																						                       'intestazioni'=>$cached[$md5]['intestazioni'])
																			                       )
									                                                        )
									                                               ,9)
															   );
							}

    	$this->catch_id($parsTable);
    	$this->set_pars($parsTable,$parsTR,$parsTD,$parsTH);

    	}

    
    
    protected function setFetchMode($modo){
    		return $this->conn->setfetchmode($modo);
    	}
    	
    	
    
  #  	protected static function parentesi_congruenti($x,$stretta=true){
  #  	    $n=0;
  #  	    for ($i=0;$i<strlen($x);$i++) {
  #  	        if($x[$i]=='(') $n++;
  #  	             elseif($x[$i]==')') $n--;
  #  	       if($stretta) {
  #  	                     if($n>1||$n<0) return false;
  #  	                    }
  #  	             else   {
  #      	                 if($n<0) return false;
  #      	                }
  #  	       }
  #  	    return $n===0;
  #  	}
  #  	
  #  	#private static function rimuovi_condizioni__($m){ static $i;
    	#   $i++;
    	#   //echo $m[1],'<br>';
    	#   return  " _where_ 1=0 /*$i*/ and ". chr(0).$m[1].chr(1)." /*-$i*/ ";
    	#}
    #	
   # 	private static  function rimuovi_condizioni_($m){
   # 	    
   # 	    if(!self::parentesi_congruenti($m[1])) return $m[0];
   # 	    if(preg_match('@\bwhere\b.+@USsi', $m[1]))
   # 	       {
   # 	           $m[1]=preg_replace_callback('@\bwhere\b(.+)@Ssi', function($m){return self::rimuovi_condizioni__($m);}, $m[1]);
   # 	       }
#    	       }
# 	       }
#    	    return chr(0).$m[1]. chr(1);
#    	}
#    	
 #   	
 #   	protected function rimuovi_condizioni($q){
 #   		    
 #   	    $n=0;
 #   	    do {
#    	        $q=preg_replace_callback('@\(([^\\)^\\(]+)\)@USs', function($m){return self::rimuovi_condizioni_($m);}, $q,strlen($q),$n);
#    	    } while($n);
 #   	   $q=str_replace(array(chr(0),chr(1)),array('(',')'), $q);
  #  	   $q=preg_split("@\bwhere\b@Ssi",$q);
#    	   if(count($q)>1)
 #   	       foreach ($q as $i=>&$qq) 
  #  	           if($i>0)
   # 	                   {
    #	                    if(strpos(trim((string) $qq),'1=0 /*')!==0 && $this->parentesi_congruenti($qq,false)) 
    #	                                                                       {
    #	                                                                         $qq="1=0 /*0*/ and ({$qq}) /*-0*/";
    #	                                                                         $qq="1=0 /*0*/ and ({$qq}) /*-0*/";
#    	                                                                        }
 #   	                   }
  #  	   
   # 	   return str_replace(' _where_ ',' where ',implode(' where ',$q));
    #	}
    
  
          public static function rimuovi_testi($x){
              $x=  str_replace(array("\\'",'\"'), array(' ',' '), $x);
              $pa=strpos($x,"'");
              $pv=strpos($x,'"');
              if($pa==false && $pv==false) return $x;
              if($pa==false) $test=array('"');
                elseif($pv==false) $test=array("'");
                    elseif ($pv<$pa) $test=array('"',"'");
                        else $test=array("'",'"');
              
              foreach($test as $c) {
                              $p=-1;
                              do {$p2=false;
                                  $p1=strpos($x,$c,$p+1);
                                  if($p1!==false) {
                                                   $p2=strpos($x,$c,$p1+1);
                                                   if($p2!==false) 
                                                            {for($i=$p1;$i<=$p2;$i++) $x[$i]=' ';
                                                             $p=$p2;
                                                            }
                                                 }
                              } while($p2!==false);
              }
              return $x;
          }
    	
          protected static function scan_parsed(&$parsed,$action) {
             foreach($parsed as $k=>&$v) {
                        if(is_array($v)) self::scan_parsed($v,$action);
                        $pred=$v;
                        $v=$action($k,$v);
                        if($v===null && $pred!==null) unset($parsed[$k]);
                        }
          }
          
    	protected function rimuovi_condizioni($q,&$select=array()){
    	   
    	    $parsed=new PHPSQLParser($q);
    	    $select=$parsed->parsed['SELECT'];
    	    self::scan_parsed($parsed->parsed,function($k,$v){if($k==='WHERE') return (new PHPSQLParser("select 1 from dual where 1=0"))->parsed['WHERE'];  else return $v;});
    	    if(!isset($parsed->parsed['WHERE'])) $parsed->parsed['WHERE']=(new PHPSQLParser("select 1 from dual where 1=0"))->parsed['WHERE'];
    	    $select=$parsed->parsed;
    	    return  (new SelectStatementBuilder())->build($parsed->parsed);
    		}
    	
    	
   
    	
         
    	
    	/**
    	 * @ignore
    	 */
         protected function fetch_qry_intestazioni($qry){
             static $pred=ARRAY();
             if($qry->isParametric()) $K=sha1($qry->qry);
                                 else $K=sha1($qry);
             
             if(!isset($pred[$K])) {
                 $pred[$K]=$pars=array();
                 $mode=$this->setfetchmode(ADODB_FETCH_ASSOC);
                 $select=array();
                 $Q=$this->rimuovi_condizioni($qry->qry,$select);
                 if($qry->pars)
                              {  $fun=function($k,$v){static $n=0; if($k==='base_expr' && trim((string) $v)==='?') $n++; return $n;};
                                 self::scan_parsed($select, $fun);
                                 $np=$fun('','');
                                 $pars=array_slice($qry->pars,0,$np);
                             }
                 $rs=$this->conn->execute($Q,$pars);
                 for ($i=0;$i<$rs->_numOfFields;$i++) $pred[$K][]=$rs->FetchField($i)->name;
                 $this->setfetchmode($mode,true);
                }
             return $pred[$K];
         }
    	
    	
    /*
    	 protected  static function get_qry_parts($qry,$parti=array('select','from','where','order')){
    	       $qry.=' ';
    	       $out=array();
    	       foreach ($parti as $parte){
    	            $p=-1;
    	            do{ $p=stripos($qry,$parte,$p+1);
        	            $no=false;
        	            if($p===false) break;
        	            if(($p==0 || str_replace(array("\r","\t","\n"),' ',$qry[$p-1])!==' ') &&
        	               ($p==0 || $qry[$p-1]!=='(') &&
        	               ($p==0 || $qry[$p-1]!==')') &&
        	               
        	               str_replace(array("\r","\t","\n"),' ',$qry[$p+strlen($parte)+1])!==' ' &&
        	               $qry[$p+strlen($parte)+1]!=='(' &&
        	               $qry[$p+strlen($parte)+1]!==')') $no=true;
        	            
        	            
        	            $parentesi=0;
        	            for ($i=$p-1;$i>=0;$i--) 
        	                           {if($qry[$i]=='(') $parentesi++;
        	                            if($qry[$i]==')') $parentesi--;
        	                           }
        	                           
        	            if($parentesi!==0) $no=true; 
        	            
        	            $parentesi=0;
        	            for ($i=$p+strlen($parte);$i<strlen($qry);$i++)
                        	            {if($qry[$i]=='(') $parentesi++;
                        	             if($qry[$i]==')') $parentesi--;
                        	            }
        	            if($parentesi!==0) $no=true;
        	            
        	            if(!$no) $out[$parte]=$p;
    	            }   while ($no);
    	       }
    	 return $out;          
    	}*/
   
}