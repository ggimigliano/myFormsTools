<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\Pckgeneric\myJQAlert.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\Pckgeneric;

use Gimi\myFormsTools\PckmyFields\myField;
                                                
class myJQAlert extends myJQDialog {
  public function set_testo_ok($text){
 $this->buttons["'".str_replace("'","\\'",$text)."'"]="function(){{$this->JQvar()}( this ).dialog('close');return true;}";
 }
 
  public function prepara_codice(){ 
 $this->dialogClass="no-close";
 $this->add_common_code($this->get_add_style(".no-close .ui-dialog-titlebar-close{display:none}",true));
 parent::prepara_codice();
 }
 
  public function get_html(){
  	$dizionario=(new myField())->get_dizionario();
  	if(!isset($this->title) ) $this->title=($dizionario && $this->trasl('Attenzione')) ?$this->trasl('Attenzione'):'Attenzione';
  	if(!is_array($this->buttons)) $this->set_testo_ok( ($dizionario && $dizionario->trasl('Chiudi')?$dizionario->trasl('Chiudi'):'Chiudi'));
                                
   return myJQDialog::get_html(); 
 }
}