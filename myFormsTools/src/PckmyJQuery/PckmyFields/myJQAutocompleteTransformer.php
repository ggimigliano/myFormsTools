<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQAutocompleteTransformer.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\PckmyFields;


use Gimi\myFormsTools\PckmyFields\myField;
use Gimi\myFormsTools\PckmyFields\myText;
use Gimi\myFormsTools\PckmyFields\myHidden;

use Gimi\myFormsTools\PckmyJQuery\myJQuery;
                                                


/**
 *
 * Se applicato ad un [@link mySelect} lo trasforma automaticamente in [@link myAutocomplete} (se e' attivo JS)
 * <code>
 *  $mySelettore=new mySelect('selettore');
 *	$mySelettore->set_opzioniqry($this->con,"select descrizione,targa from province order by descrizione")
 *					->set_domanda('** QUALUNQUE **')
 *					->add_myjQuery(new myJQAutocompleteTransformer())
 *																	->get_myText()
 *																	->set_maxlength(50);
 * </code>
 *
 */
	
class myJQAutocompleteTransformer extends myJQAutocomplete {
/** @ignore */
protected $f,$h;


		 public function get_myText(myField $mytext=null){
		      if(!$this->f || $mytext!==null)    
		                     {if($mytext!==null)   $this->f=$mytext; 
		                                      else $this->f=new myText('','');
			                  foreach ($this->myField->get_attributi() as $attr=>$val)
			                             if(!in_array($attr,array('name','value','onchange','id'))) $this->f->set_attributo($attr,$val);
		                     }
			return $this->f;
		}
		
		 public function application(&$myField){
		    if($myField->get_domanda()==='' && !$myField->get_notnull()) $myField->set_domanda(' ');
		    return parent::application($myField);
		}
		
		 public function get_myHidden(myField $myhidden=null){
		    if(!$this->h || $myhidden!==null)    
		                     {if($myhidden!==null)   $this->h=$myhidden; 
			                                    else $this->h=new myHidden('','');
		                      $this->h->set_name($this->myField->get_name());
		                     }
			return $this->h;
		}

		 public function get_html(){
		    $this->h=$this->get_myHidden();
		    $this->h->set_value($this->myField->get_value());
			if(!method_exists($this->myField, 'get_opzioni')) return;
			$this->set_opzioni($this->myField->get_opzioni(),$this->h);
			$this->add_code("{$this->JQid()}.css({display:'none'});
							 {$this->JQid()}.prop('disabled','disabled')");

			
			$this->f=$this->get_myText();
			if($this->myField->get_titolo() && $this->myField->get_Domanda()!=$this->myField->get_titolo())
			                        $this->f->set_value($this->myField->get_titolo());
			                 elseif($this->myField->get_Domanda()) 
			                             {$this->f->set_value($this->myField->get_Domanda());
			                              $jid=myJQuery::JQvar()."('#{$this->f->get_id()}')";
			                              $this->f->set_js("if($jid.val()=='".str_replace("'","\\'",$this->myField->get_Domanda())."') $jid.val('')",'onfocus');
			                             }
			
			$this->f->unset_attributo('name')
							  ->set_style('display', 'none')
							  ->set_disabled();
			$this->application($this->f);
			$this->set_event('open',"document.getElementById('{$this->h->get_id()}').value='';");
			$this->add_code("{$this->JQid()}.removeAttr('disabled');
							 {$this->JQid()}.css({display:'block'})");
			
			return $this->f.$this->h.parent::get_html();
		}

}