<?php
/**
 * Contains Gimi\myFormsTools\PckmyTables\myTableQRY_MySQL.
 */

namespace Gimi\myFormsTools\PckmyTables;

use PHPSQLParser\PHPSQLParser;
use PHPSQLParser\builders\SelectStatementBuilder;


/**
*   myTableQRYPaginata ottimizzata per mysql
*
**/
	
Class myTableQRY_MySQL extends myTableQRYPaginata{
    protected  $n_rows=array();
    
    
    protected  function &SelectLimit($sql,$nrows=-1,$offset=-1, $inputarr=false,$secs2cache=0)
    {
        $nrows = (int) $nrows;
        $offset = (int) $offset;
        if ($nrows < 0) $nrows = '18446744073709551615';
        $inputarr[]=$nrows;
        $inputarr[]=$offset;
        
        if(is_array($sql)) $sql = $sql[0];
        $K=$this->qry.'|'.$this->N.'|'.$this->PAG;
        $this->n_rows[$K]=0;
        $versione=intval($this->conn->getone('SELECT VERSION()'));
        $parsed=new PHPSQLParser("$sql limit ?,?");
        $builder= new SelectStatementBuilder()  ;
        if ( $versione<8) 
                        { 
                            $sql=preg_replace('#^select\s+#SsiU',"Select SQL_CALC_FOUND_ROWS ",$builder->build(array('SELECT'=>$parsed->parsed['SELECT'])));
                            foreach ($parsed->parsed as $tk=>$st)
                                if($tk!='SELECT')  $sql.=$builder->build(array($tk=>$st));
                                
                            
                            if ($secs2cache > 0)  $out= $this->conn->CacheExecute($secs2cache, $sql ,$inputarr);
                                            else $out= $this->conn->Execute($sql, $inputarr);
                                         
                            $this->n_rows[$K]=$this->conn->getone("select found_rows()");
                           
                        }
                  else {
                        
                        $sql=$builder->build(array('SELECT'=>$parsed->parsed['SELECT'])).', COUNT(*) OVER() ';
                        foreach ($parsed->parsed as $tk=>$st)
                               if($tk!='SELECT')  $sql.=$builder->build(array($tk=>$st));
                            
                             
                        
                        if ($secs2cache > 0)  $out= $this->conn->CacheExecute($secs2cache, $sql,$inputarr);
                                         else $out= $this->conn->Execute($sql, $inputarr);
                                        
                         if($out && $out->_numOfRows) { 
                                        $this->n_rows[$K]=$out->fields[$out->_numOfFields-1];
                                        
                                        if(isset($out->_array) && is_array($out->_array)) 
                                                    foreach ($out->_array as &$row)  unset($row[$out->_numOfFields-1]);
                                        unset($out->_fieldobjects[$out->_numOfFields]);
                                        unset($out->fields[$out->_numOfFields-1]);
                                        $intesta=array();
                                        if(isset($out->_fieldobjects) && 
                                                is_array($out->_fieldobjects)) 
                                                        foreach ($out->_fieldobjects as $r) $intesta[]=$r->name;
                                        $this->set_intestazioni($intesta);
                                        $out->_numOfFields--;
                                    }
                        }
        return $out;
        
    }
    
    
     
    
    /**
     * Restituisce il numero di righe
     * @return   int
     **/
     public function get_N_rows() {
        $K=$this->qry.'|'.$this->N.'|'.$this->PAG;
        if(isset($this->n_rows[$K]))  return $this->n_rows[$K];
        return parent::get_N_rows();   
    }
    
}