<?php


/**
 *
 * Classe per la gestione di finestre tramite Jquery
 * @package myJQuery
 * @deprecated
 * @see http://goodies.pixabay.com/jquery/tag-editor/demo.html
 */
use Gimi\myFormsTools\PckmyJQuery\myJQueryMyField;

class myJQTagEditor extends myJQueryMyField {
    
    /**
     * @ignore
     */static protected function init( &$widget) {
                    $widget='tagEditor';
                    self::add_src(self::$percorsoMyForm.'myDeprecated/js/tagEditor/jquery.tag-editor.min.js');
                    self::add_css(self::$percorsoMyForm.'myDeprecated/js/tagEditor/jquery.tag-editor.css');
                    
                }


}



?>