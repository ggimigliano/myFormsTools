<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQTagIt.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\PckmyFields;


use Gimi\myFormsTools\myCSS;
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
    
    
    /** @ignore */
    final function set_common_defaults(array $defaults){
     	$this->add_common_code(myCSS::get_css_jscode(" .ui-draggable-dragging {
													            z-index: 1000;
													            background-color: #f0f0f0;
													            opacity: 0.8;
													            border: 1px dashed #333;
													        }
													.tagit-choice:hover {  cursor: move; }",true));
    }
    /**
     * @ignore
     */
    public function get_html(){
    	if(!isset($this->afterTagAdded))	$this->afterTagAdded=" function(event, ui) {
											        	
											        		makeTagDraggable($(ui.tag));
											        	
											       }";
    								 else  $this->afterTagAdded=" function(event, ui) {
														({$this->afterTagAdded})(event, ui);
											        	 makeTagDraggable($(ui.tag));
											       }";
    					 
    	$this->add_code("$('.tagit').droppable(
									{
						            accept: '.tagit-choice',
						            tolerance: \"pointer\",
						            drop: function(event, ui) {
										 
						                var droppedTag = ui.draggable;
						                var tagText = droppedTag.data('tag-text');
						                var originalListId = droppedTag.data('original-list');
						                var targetListId = $(this).closest('.tagit').parent().find('.tagit-hidden-field').attr('id');		 
						               
										 if (originalListId === targetListId)    return; 
										
						                // Aggiungi il tag al nuovo contenitore (triggera afterTagAdded, che lo rende draggable)
						                $('#' + targetListId).tagit('createTag', tagText);
						                
						                // Rimuovi il tag dal contenitore originale
						                setTimeout(function() { 
										   		$('#' + originalListId).parent().find('.tagit-label').each(function() {
												if ($(this).text() === tagText) {
													                            $('#' + originalListId).tagit('removeTagByLabel', tagText);
													                            return false; 
													                        }
						                    					});
						                	}, 50);
						            }
						        });
						    ");
    	return parent::get_html()."<script>
										 function makeTagDraggable(tagElement) {
													
										            tagElement.draggable({
										                cursor: 'move',
										                revert: 'invalid',
										                helper: 'clone',
										                opacity: 0.7,
										                start: function(event, ui) {
														    $(this).data('tag-text', $(this).find('.tagit-label').text());
										                    $(this).data('original-list', $(this).closest('.tagit').parent().find('.tagit-hidden-field').attr('id'));
														 
										                }
										            });
										        }
										</script>";
    }

     public function set_istance_defaults(){
        $this->allowDuplicates=false;
        $this->add_common_code(self::get_add_style(".tagit{overflow:hidden!important} .tagit-new{overflow:hidden!important}",true));
        parent::set_istance_defaults();
    }
}