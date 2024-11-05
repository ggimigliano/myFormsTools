<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\Pckgeneric\myJQConfirm.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\Pckgeneric;

                                               
use Gimi\myFormsTools\PckmyFields\myField;

class myJQConfirm extends myJQAlert {
    
     public function set_testo_ko($text){
        $this->buttons["'".str_replace("'","\\'",$text)."'"]="function(){  {$this->myJQVarName()}_confirmed=false; {$this->JQvar()}( this ).dialog('close');return false;}";
    }
    
    
     public function set_testo_ok($text){
        $this->buttons["'".str_replace("'","\\'",$text)."'"]="function(){
                                                                             {$this->myJQVarName()}_confirmed=true;
                                                                             //console.log('<{$this->myJQVarName()}_confirmed');
                                                                             try{ {$this->myJQVarName()}_click.trigger('click');} catch (err){}
                                                                            {$this->JQvar()}( this ).dialog('close');
                                                                            }";
    }
    
     public function get_html(){
        if(!is_array($this->buttons))
                {$f=new MyField();
                $this->set_testo_ok(($f->get_dizionario() && $f->get_dizionario()->trasl('Conferma')?$f->get_dizionario()->trasl('Conferma'):'Conferma'));
                $this->set_testo_ko(($f->get_dizionario() && $f->get_dizionario()->trasl('Annulla')?$f->get_dizionario()->trasl('Annulla'):'Annulla'));
                }
        return myJQDialog::get_html()."<script> {$this->myJQVarName()}_confirmed=false;</script>";
    }
}
