<?php
/**
 * Contains Gimi\myFormsTools\PckmyTables\myTableQRYPaginata.
 */

namespace Gimi\myFormsTools\PckmyTables;


use Gimi\myFormsTools\PckmyDBAdapters\myAdoDBAdapter;
use Gimi\myFormsTools\PckmyJQuery\PckmyTables\myJQDataTable;
use Gimi\myFormsTools\PckmyUtils\myCharset;
use Gimi\myFormsTools\PckmyUtils\myFormAJAXRequest;
use PHPSQLParser\PHPSQLParser;
use PHPSQLParser\builders\SelectStatementBuilder;



/**
*   myTableQRY ottimizzata per query lunghe che richiedono la paginazione non
*   c'a' forzatura di query nel costruttore perche' i dati vengono letti di volta in volta quando viene chiamato il set_limit.
*   Il DB utilizzato deve supportare selezione da riga a riga es. selectlimit
**/
	
Class myTableQRYPaginata extends myTableQRY{
/** @ignore */
protected $conn,$qry,$nrighe=null, $extrarighe=0,$is_large=array();


   /**
  * 
    * @param    mixed $conn  E' connessione DB
    * @param    string|array $qry  E' la query da lanciare oppure un array che ha  nella posizione 0 una query parametrica e nella posizione 1 un array con i parametri
    * @param    string $parsTable  Opzionale e' il nome della classe css da associare al tag TABLE
    * @param    string $parsTR  Opzionale e' il nome della classe css da associare al tag TR
    * @param    string $parsTD  Opzionale e' il nome della classe css da associare al tag TD
    * @param    string $parsTD  Opzionale e' il nome della classe css da associare al tag TH
    **/
	 public function __construct($conn,$qry,$parsTable='',$parsTR='',$parsTD='',$parsTH='') {
	    require_once dirname(__FILE__) . '../../thrd/PHP-SQL-Parser/vendor/autoload.php';
	    
	    $qry=new myTableQryArray($qry);
	    $this->qry=$qry;
	    $this->conn= myAdoDBAdapter::getAdapter($conn);
		$this->catch_id($parsTable);
		$this->set_pars($parsTable,$parsTR,$parsTD,$parsTH);
	}

	public function set_large_mode($righe_per_pagina_possibili=array(5,10,25,50,100)){
	     $this->is_large=array('is_large'=>true,
	                           'righe_per_pagina'=>$righe_per_pagina_possibili);
	     return $this;
	}
	
	function &get_body_json($colonne='',$righe='') {
	       $usa_ricalcola_cella=$this->isOverridden('ricalcola_cella');
	       $dati=array();
	       foreach ($this->valori as $i=>&$riga)
	                       foreach (array_keys($riga) as $j)  
	                                       {
                        	                if($usa_ricalcola_cella) $dati[$i][$j]=$this->ricalcola_cella($i,$j);
                        	                                    else $dati[$i][$j]=$this->valori[$i][$j];
                                 	        if('UTF-8'!=$this->codifica)  $dati[$i][$j]=myCharset::utf8_encode($dati[$i][$j]);
                        	               }
	        return $dati;
	}
   

  /**
      * Restituisce il numero di righe
      * @return   int
     **/

      public function get_N_rows() {
      	if ($this->nrighe===null) {
                                   $parsed=new PHPSQLParser($this->qry->qry);
                                   $parametri=$this->qry->pars;
                                   self::scan_parsed($parsed->parsed,function($k,$v) {return $k==='ORDER'?null:$v; });
                                   if(isset($parsed->parsed['SELECT']) &&  count($parametri)>0)
                                              { $to_delete=array();
                                                self::scan_parsed($parsed->parsed['SELECT'],function($k,$v) use(&$parametri,&$to_delete) {
                                                                                                                if($k==='no_quotes' && 
                                                                                                                   is_array($v) && 
                                                                                                                   isset($v['delim']) && $v['delim']=='' &&
                                                                                                                   isset($v['parts']) && is_array($v['parts']) && count($v['parts'])==1) 
                                                                                                                                {   
                                                                                                                                    if($v['parts'][0]==='?') array_shift($parametri);
                                                                                                                                    if($v['parts'][0][0]===':') $to_delete[substr($v['parts'][0], 1)]=1;
                                                                                                                                 }
                                                                                                                  return $v;
                                                                                                                });
                                
                                                foreach ($parsed->parsed  as $parte=>$parts)
                                                      if($parte!='SELECT') 
                                                          self::scan_parsed($parts,function($k,$v) use(&$parametri,&$to_delete){
                                                                                                                   if($k==='no_quotes' &&
                                                                                                                          is_array($v) &&
                                                                                                                          isset($v['delim']) && $v['delim']=='' &&
                                                                                                                          isset($v['parts']) && is_array($v['parts']) && count($v['parts'])==1)
                                                                                                                                              {
                                                                                                                                               if($v['parts'][0][0]===':' && isset($to_delete[substr($v['parts'][0], 1)])) unset($to_delete[substr($v['parts'][0], 1)]);
                                                                                                                                              }
                                                                                                                      return $v;
                                                                                                                  });
                                                  foreach (array_keys($to_delete) as $k) unset($parametri[$k]);       
                                                  $parsed->parsed['SELECT']=array(array('expr_type' => 'const', 'alias'=>null,'base_expr'=>1,'sub_tree'=>null,'delim'=>''));
                                                  }
                                       $Q=(new SelectStatementBuilder())->build($parsed->parsed);
                                       $this->nrighe=$this->conn->getone("select /*+ RESULT_CACHE */ count(*) from ( $Q  )  mysubDir",$parametri);
                                   }
       return  $this->nrighe+$this->extrarighe;

    }





    /**
      * Inserisce la 'riga' nella posizione pos
      * USARE SOLO DOPO @see set_limit()
       * @param    array $riga
      * @param    int $pos Opzionale, se non c'e' si accoda
     **/
    public function ins_row ($riga,$pos='') {
   	 if (isset($this->valori[$pos])) $this->extrarighe++;
   	 parent::ins_row ($riga,$pos);
   	 return $this;
   	}

   	
   	protected function selectLimit($qry,$n,$offset,$pars=array()){
   	    return $this->conn->selectlimit($qry,$n,$offset,$pars);
   	}

   	  	
   	protected function set_limit_large($N,$pag=0){
   	    if($this->N===$N && $this->PAG===$pag) return $this;
   	    if(!in_array($N,  $this->is_large['righe_per_pagina'])) $N=$this->is_large['righe_per_pagina'][0];
   	 
   	    if ($pag==-1) { $righe=myTableQRYPaginata::get_N_rows();
   	                    $pag=intval($righe /$N);
   	                    if($pag*$N!=$righe) $pag++; 
   	                  }
   	            elseif($pag<=0) $pag=1;
   	    
   	    $this->N=$N;
   	    $this->PAG=$pag;
   	    $mode=$this->setfetchmode(ADODB_FETCH_NUM);
   	   
   	    if(!$this->intestazioni) $this->set_intestazioni($this->fetch_qry_intestazioni($this->qry));
   	 
   	    
   	    $RS=myTableQRYPaginata::selectlimit($this->qry->qry, $N*2+1 , (int)($N*($pag-1)) , $this->qry->pars);
   	    if($RS->EOF &&  $pag>1) 
                   	    {   $righe=myTableQRYPaginata::get_N_rows();
                       	    $pag=intval($righe /$N);
                       	    if($pag*$N!=$righe) $pag++; 
                       	    $RS=myTableQRYPaginata::selectlimit($this->qry->qry, $N*2+1 , (int)($N*($pag-1)) , $this->qry->pars);
   	                        $this->PAG=$pag;
   	                    }
   	    $this->valori=array();
   	  // 
   	    $this->is_large['next_page']=0;
   	    $this->is_large['next_pages']=0;
   	    $this->nrighe=0;
   	    
   	    if($RS) { 
   	             $valori=$RS->getarray();
   	             
   	             $i=(int)($N*($pag-1));
   	             $this->nrighe=$i+count($valori);
   	             for($t=0;$t<min($N,count($valori));$t++)   $this->valori[$i++]=&$valori[$t];
   	 
   	             $this->is_large['next_page']=count($valori)>$N;
       	         $this->is_large['next_pages']=count($valori)>2*$N;
       	         }
        $this->setfetchmode($mode);
        return $this;
   	}
   	
   	
   	function get_pager($classe='',$reload='completo'){
   	    if(!$this->is_large ||
   	        $this->JQDt ||
   	        $this->is_loading_json() ||
   	        !$this->N
   	        ) return parent::get_pager($classe,$reload);
   	  $pager=new myTablePager($classe,$reload, $this->PAG,$this->N,  $this->is_large['next_page'],  $this->is_large['next_pages']);
   	  return $pager->set_opzioni_rigexpag( $this->is_large['righe_per_pagina'],$classe) ; 
   	}
   	
   	

	/**
     * Setta il numero di righe da visualizzare per pagina ed il numero di pagina da cui partire
     * <b> E' in questa fase cha avviene il recupero dei dati dal DB
     *
     * @param    int $N Numero di righe per pagina se =0 si prenderanno tutte le righe ed il pager sara' nullo
     * @param    int $pag Il numero della pagina che verra' visualizzata da 1 in poi
     **/
     public function set_limit($N,$pag='') {
        if($this->is_large &&
           !$this->JQDt && 
           !$this->is_loading_json() 
           ) return $this->set_limit_large($N,$pag);
        if($this->is_loading_json()=='post') {
                         $pars=$_POST['myJQDatatable'][$this->get_ajax_par_name()];
                         $N=$pars['length'];
                         $pag=1+intval($pars['start']/$N);
                       
                         if(!$this->intestazioni) $this->set_intestazioni($this->fetch_qry_intestazioni($this->qry));
                        
                         $nrighe=$this->get_N_rows();
                         myJQDataTable::set_ServerSidePars($this->get_ajax_par_name(),'unfiltered',$nrighe);
                         if($pars['order']) 
                                        { $versi=array();
                                          foreach ($pars['order'] as $i=>$verso)
                                                  {  $fun=$this->serverSide['sort'][$verso['column']];
                                                        if($fun) $versi[]=str_replace('?',$this->intestazioni[$verso['column']],$fun).' '.$verso['dir'];
                                                            else $versi[]=$this->intestazioni[$verso['column']].' '.$verso['dir'];
                                                }
                                          
                                          $parsed=new PHPSQLParser($this->qry->qry);
                                          unset($parsed->parsed['ORDER']);
                                          
                                          $this->qry->qry= "select * from (
                                                                          ".(new SelectStatementBuilder())->build($parsed->parsed)."
                                                                          ) mySubOrder 
                                                                            order by " .implode(',',$versi);
                                         }    
                                      
                         myJQDataTable::set_ServerSidePars($this->get_ajax_par_name(),'filtered',$nrighe);
                        }   
           
     if ($pag<=0) $pag=1;
     if($this->N===$N && $this->PAG===$pag) return $this;
     $this->N=$N;
     $this->PAG=$pag;
     
     $mode=$this->setfetchmode(ADODB_FETCH_NUM);
     if(!$N && $this->uses_json() && !$this->is_loading_json() && !$this->intestazioni) 
                     {
                     $this->set_intestazioni($this->fetch_qry_intestazioni($this->qry));
                     return $this;
                    }
                 
                 
	 if(!$N) {  
	           if(!$this->intestazioni) $this->set_intestazioni($this->fetch_qry_intestazioni($this->qry)); 
	           $valori=$this->conn->getarray($this->qry,$this->qry->pars);
			   parent::set_matrix($valori,true,$this->intestazioni);
			   $this->nrighe=count($valori);
			   $this->setfetchmode($mode);
	 		   return $this;
	 		 } 
	// $this->conn->firstrows=false;
	 		;
	 $RS=$this->selectlimit($this->qry->qry, $N , (int)($N*($pag-1)), $this->qry->pars);
     IF (!$RS)
	 		  {$LastPag=1 + (int)($this->get_N_rows()/$N);
	  		  if ($LastPag!=$pag) $this->set_limit($N,$LastPag);
	  		  $this->setfetchmode($mode);
	  		  myJQDataTable::set_ServerSidePars($this->get_ajax_par_name(),'filtered',$this->get_N_rows());
	 		  return $this;
	 		  }
     $i=(int)($N*($pag-1));
    
     $valori=$RS->getarray();
     $lette=count($valori);
     
     $this->valori=array();
     for($t=0;$t<min($N,count($valori));$t++)   $this->valori[$i++]=&$valori[$t];
	 
	 if(!$this->intestazioni) $this->set_intestazioni($this->fetch_qry_intestazioni($this->qry));
	 if ($N*($pag-1)==0 && (!$N || $lette<$N)) $N=$this->nrighe=$lette;
     
	 if($this->is_loading_json()=='post')   myJQDataTable::set_ServerSidePars($this->get_ajax_par_name(),'filtered',$this->get_N_rows());
	 
	 $this->setfetchmode($mode);
	 if(myFormAJAXRequest::isAJAXCall() ) {  $this->outMode['modo']='jsonJQDT';
	                                        $this->send_output();
	                                     }
	 return $this;
	}

		

}