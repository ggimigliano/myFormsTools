<?php
/**
 * Contains Gimi\myFormsTools\PckmyTables\myTableQryArray.
 */

namespace Gimi\myFormsTools\PckmyTables;






/**
 * @ignore
 *
 */
	
class myTableQryArray {
protected $parti=array('',array());    
     public function __construct($qry) {
        if (is_array($qry)) $this->parti=array(&$qry[0],&$qry[1]);
                       else $this->parti=array($qry,array()); 
    }
    
     public function isParametric(){
        return count($this->parti[1])>0;
    }
    
     public function __get($x){
        switch (strtolower($x)) {
            case 'qry':
            case 'query':
                    return $this->parti[0];
            break;
            
            case 'pars':
            case 'parametri':
                return $this->parti[1];
                break;
        }
        return null;
    }
    
     public function __toString(){
        if($this->isParametric()) return $this->parti[0].'/* '.serialize($this->parti[1]).' */ '; 
                            else  return $this->parti[0];
    }
    
}