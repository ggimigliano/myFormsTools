<?php
/**
 * Contains Gimi\myFormsTools\PckmyTables\myTableQRY_Oracle.
 */

namespace Gimi\myFormsTools\PckmyTables;
use PHPSQLParser\PHPSQLParser;
use PHPSQLParser\builders\SelectStatementBuilder;



/**
*   myTableQRYPaginata ottimizzata per Oracle 10g
*
**/
	
Class myTableQRY_Oracle extends myTableQRYPaginata{
    protected  $n_rows=array();
    
     public function __construct($conn,$qry,$parsTable='',$parsTR='',$parsTD='',$parsTH='') {
   #     $conn->debug=1;$conn->profile=1;
        $conn->selectOffsetAlg1=1;
        return parent::__construct($conn,$qry,$parsTable,$parsTR,$parsTD,$parsTH);
        
    }
    
    
    
    
    
    /**
      * @param string $sql
     * @param int $nrows
     * @param int $offset
     * @param boolean $inputarr
     * @param number $secs2cache
     * @return mixed
     */
   protected  function SelectLimit($sql,$nrows=-1,$offset=-1, $inputarr=false,$secs2cache=0)
    {
        $nrows = (int) $nrows;
        $offset = (int) $offset;
        // Since the methods used to limit the number of returned rows rely
        // on modifying the provided SQL query, we can't work with prepared
        // statements so we just extract the SQL string.
        
      
       # $this->conn->debug=1;$this->conn->profile=1;
        if(is_array($sql)) $sql = $sql[0];
       
        $cols=$this->fetch_qry_intestazioni(new myTableQryArray(array($sql,$inputarr)));
        
         
        $with_name=$this->with_clausole($sql,'k');
        $with=$this->with_clausole($sql,1);
        
        $sql="select * from {$with_name}";
        // seems that oracle only supports 1 hint comment in 8i
        $hint = '';
        if ($this->conn->firstrows && $nrows > 0 && $nrows < 1000) {
            $hint = "FIRST_ROWS($nrows)";
            if (strpos($sql,'/*+') !== false)  {$sql = str_replace('/*+ ',"/*+$hint ",$sql);
                                                $sql = preg_replace('@\/\*\+FIRST_ROWS\(\s*[0-9]+\s*\)\s*(FIRST_ROWS[^/]+)@is','/*+ \1',$sql);
                                                }
                                        else  $sql = preg_replace('/^[ \t\n]*select/i',"SELECT /*+$hint*/",$sql);
           
            }
        
        $m=array();
        $versione=$this->conn->getone('SELECT BANNER FROM v$version');
        if($versione)   preg_match('@[0-9]{1,}\.[0-9]{1,}\.[0-9]{1,}\.[0-9]{1,}\.[0-9]{1,}@', $versione,$m);
        if(isset($m[0]) && $m[0]>='12.') { $inputarr['adodb_offset'] = $offset;
                                          if ($nrows<=0) $sql = "$with
                                                                      SELECT SUB1.*,
                                                                       CASE 
                                                                        WHEN (rownum=1) THEN  (select    /*+ RESULT_CACHE */ count(*) from $with_name)
                                                                                        ELSE  null
                                                                       END  TOT_COLONNE
                                                                        FROM
                                                                         (SELECT /*+ NO_PARALLEL */ * FROM
                                                                                $with_name
                                                                                OFFSET :adodb_offset ROWS
                                                                          ) SUB1
                                                                        ";
                                              
                                                      else { $inputarr['adodb_nrows'] = $nrows;
                                                             $sql = "$with
                                                                       SELECT SUB1.*,
                                                                       CASE 
                                                                        WHEN (rownum=1) THEN  (select  /*+ RESULT_CACHE */ count(*) from $with_name)
                                                                                        ELSE  null
                                                                       END  TOT_COLONNE 
                                                                        from (SELECT /*+ NO_PARALLEL */ * FROM
                                                                                $with_name
                                                                                OFFSET :adodb_offset ROWS
                                                                                FETCH NEXT :adodb_nrows ROWS ONLY
                                                                         ) SUB1";
                                                            }
                                          }
                                    else {
                                            $cols=$this->fetch_qry_intestazioni(new myTableQryArray(array($sql,$inputarr)));
                                            $hint = "/*+ {$hint} NO_PARALLEL */";
                                            $fields = implode(',', $cols);
                                            $sql = "$with, 
                                                    count_$with_name AS ( select  /*+ RESULT_CACHE */ count(*) TOT_COLONNE from $with_name )
                                                    select  $fields,TOT_COLONNE FROM
                                                        ( SELECT $hint $fields FROM
                                                                 (SELECT /*+ NO_PARALLEL*/ rownum as adodb_rownum, $fields FROM
                                                                     ($sql) SUB1 
                                                                  WHERE rownum <= :adodb_nrows
                                                                 ) SUB2
                                                         WHERE adodb_rownum >= :adodb_offset
                                                         ),count_$with_name";
                                            if ($nrows <= 0)    $nrows = 999999999999;
                                                           else $nrows += $offset;
                                            $offset += 1; // in Oracle rownum starts at 1
                                            $inputarr['adodb_nrows'] = $nrows;
                                            $inputarr['adodb_offset'] = $offset;
                                            
                                         }
      
       
        if ($secs2cache > 0)  $out= $this->conn->CacheExecute($secs2cache, $sql,$inputarr);
                         else $out= $this->conn->Execute($sql, $inputarr);
                         
        $K=$this->qry.'|'.$this->N.'|'.$this->PAG;
        $this->n_rows[$K]=0;
        if($out && $out->_numOfRows)
                 { $this->n_rows[$K]=$out->fields[$out->_numOfFields-1];
                   if(isset($out->_array) && is_array($out->_array))
                      foreach ($out->_array as &$row)  unset($row[$out->_numOfFields-1]);
                   unset($out->_fieldobjects[$out->_numOfFields]);
                   unset($out->fields[$out->_numOfFields-1]);
                   $intesta=array();
                    foreach ($out->_fieldobjects as $r) $intesta[]=$r->name;
                   $this->set_intestazioni($intesta);
                   $out->_numOfFields--;
                  }
         return $out;                        
          
    }
    
    
    
    protected function with_clausole($q,$cosa=null,&$restore=true ){
        static $cache=array();
        $K='X'.sha1($q);
        if(!isset($cache[$K])) 
                        { $q=preg_replace("@^select\s+@UiSs",'select /*+ parallel(4) */ ',trim((string) $q));
                          $n=count($cache)+1;
                          $cache[$K]=array("WITH TEMP_$n AS ($q )","TEMP_$n"); 
                          $restore=false;
                        }
        if($cosa=='k') return $cache[$K][1];
        return $cache[$K][0];
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
                        $unquotes=self::ric_noquotes($select);
                        
                        if($qry->pars) 
                                    {
                                    $unquotes=array_change_key_case(array_flip($unquotes));
                                    foreach ($qry->pars as $k=>&$v)  if(isset($unquotes[strtolower(":$k")]))  $pars[$k]=&$v;
                                    }
                                     
                        $rs=$this->conn->execute($Q,$pars);
                        for ($i=0;$i<$rs->_numOfFields;$i++) $pred[$K][]=$rs->FetchField($i)->name;
                    $this->setfetchmode($mode,true);
                 }
        return $pred[$K];
    }
   
    protected  static function ric_noquotes($x){
        $parts=array();
        if(!is_array($x)) return array();
        foreach ($x as $k=>$v) {
               if($k==='no_quotes') $parts=array_merge($parts,$v['parts']);
                    elseif(is_array($v))  $parts=array_merge($parts,self::ric_noquotes($v));
            }
        return $parts;
    }
    
    
    /**
     * Restituisce il numero di righe
     * @return   int
     **/
    
     public function get_N_rows() {
         if($this->is_large &&
             !$this->JQDt &&
             !$this->is_loading_json()
             ) return parent::get_N_rows();
         
        static $pred;
      
        $K=$this->qry.'|'.$this->N.'|'.$this->PAG;
        if(isset($this->n_rows[$K]))  return $this->n_rows[$K];
       
        if(isset($pred[$K])) return $pred[$K];
        if ($this->nrighe===null) {
            $qry=$this->qry;
            if($qry->isParametric()) $QRY=$qry->qry;
                                else $QRY=$qry;
      
            $parsed=new PHPSQLParser($QRY);
            self::scan_parsed($parsed->parsed,function($k,$v) {return $k==='ORDER'?null:$v; });
            $QRY=(new SelectStatementBuilder())->build($parsed->parsed);
            
            $pars=array();
            if($this->qry->pars)
                         { 
                         $unquotes=array_change_key_case(array_flip(self::ric_noquotes($parsed->parsed )));
                         foreach ($qry->pars as $k=>&$v)  if(isset($unquotes[strtolower(":$k")]))  $pars[$k]=&$v;
                        }
            $this->nrighe=$this->conn->getone("{$this->with_clausole($QRY)} select /*+ RESULT_CACHE */ count(*) from {$this->with_clausole($QRY,'k' )} ",$pars);
          }
        return $pred[$K]=$this->nrighe+$this->extrarighe;
        
    }
    
}