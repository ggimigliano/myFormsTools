<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQTagIt.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\PckmyFields;


use Gimi\myFormsTools\PckmyJQuery\myJQueryMyField;
                                                


/**
 *
 * Classe per la gestione di finestre tramite Jquery
 * @see https://github.com/aehlke/tag-it
 */
	
class myJQTagIt extends myJQueryMyField {
    
    /**
     * @ignore
     */
   static protected function init( &$widget) {
    $widget='tagit';
    self::add_src(self::$percorsoMyForm.'jquery/tag-it/tag-it.min.js');
    self::add_css(self::$percorsoMyForm.'jquery/tag-it/jquery.tagit.css');
    }

     public function set_valori_ammessi($vals=array()){
        $this->availableTags=$vals;
        $this->autocomplete=" {delay: 0, minLength: 1}";
        $this->beforeTagAdded  ="function( val) {
                                                return (".self::encode_array($vals,'[]',false).").includes(val);
                                            }";
        return $this;
    }

     public function set_istance_defaults(){
        $this->allowDuplicates=false;
        $this->add_common_code(self::get_add_style(".tagit{overflow:hidden!important} .tagit-new{overflow:hidden!important}",true));
        parent::set_istance_defaults();
    }
}