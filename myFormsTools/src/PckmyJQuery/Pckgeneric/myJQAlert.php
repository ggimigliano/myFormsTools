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
 if(!is_array($this->buttons)) {$f=new myField();
                                 $this->set_testo_ok(($f->get_dizionario() && $f->get_dizionario()->trasl('Chiudi')?$f->get_dizionario()->trasl('Chiudi'):'Chiudi'));
                                 }
 return myJQDialog::get_html(); 
 }
}