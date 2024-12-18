<?php
/**
 * Contains Gimi\myFormsTools\PckmyAjax\_myRicercaAjax.
 */

namespace Gimi\myFormsTools\PckmyAjax;


use Gimi\myFormsTools\PckmyFields\myText;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQAutocomplete;



/** Classe Astratta*/
	
abstract class myRicercaAjaxModel{
    /** @ignore*/
    protected $myAutocompleter,$campo_output,$showonly,$id,$myText,$function='';    
    
    /** @ignore*/
     public function __construct($myText){
        if (is_object($myText))	$this->myText=&$myText;
                           else $this->myText=new myText($myText,func_get_arg(1),func_get_arg(2));
        $this->id=$this->myText->get_id_istanza();
        $this->myAutocompleter=new myJQAutocomplete();
    }
    
    /** @ignore*/
     public function myRicercaAjax(&$myText){
        return myRicercaAjaxModel::__construct($myText);
    }
}