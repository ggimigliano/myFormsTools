<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQInputMaskExt.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\PckmyFields;



                                                


/**
     * Imposta il codice js da avviare al completament
     * @param string $oncomplete
     * @param boolean $aggiungi se true si accoda al codice precedente, altrimenti lo sostituisce
     */
	
class myJQInputMaskExt extends myJQInputMask {




/**
 * @ignore
 */
protected function build_options() {
    if(!$this->maschera) return;
    $this->pars['alias']=$this->maschera;
    return $this->quote($this->pars);
}

}