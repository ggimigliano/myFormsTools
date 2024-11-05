<?php
/**
 * Contains Gimi\myFormsTools\PckmyForms\PckmyJQuery\myJQMyFormDialog.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\PckmyForms;


use Gimi\myFormsTools\PckmyJQuery\Pckgeneric\myJQDialog;
                                                
 

/**
 * Estensione di {@link myJQDialog} specifica per myForms
 */
	
class myJQMyFormDialog extends myJQDialog{
/**
 * @ignore 
 */
	protected $form;	
/**
 * @ignore 
 */	function set_form($form){
		$this->form=$form;
	}
	
/**
 * @ignore 
 */function set_istance_defaults() {
		parent::set_istance_defaults();
		$this->modal=true;
		$this->draggable=false;
		$this->resizable=false;
		$this->title=$this->form->trasl('Attenzione');
		$this->buttons=array(array(
       						 'text'=>"Ok",
        					 'click'=>"function() { ".self::$identificatore."(this).dialog(\"close\"); }"
    						));
	}
	
}