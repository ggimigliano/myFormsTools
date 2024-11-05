<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myPulsante.
 */

namespace Gimi\myFormsTools\PckmyFields;

use Gimi\myFormsTools\PckmyJQuery\myJQuery;
use Gimi\myFormsTools\PckmyJQuery\Pckgeneric\myJQConfirm;


class myPulsante extends myField {
/** @ignore */
protected $set_lockform_onsend=false;

    /**
      * 
	  *
	  * @param	 string $nome E' il nome del campo
	  * @param	 string $valore Valore da assegnare come default
	  * @param	 string $classe e' la classe css da utilizzare
	  */

		   public function __construct($nome,$valore="",$classe=''){
				myField::__construct($nome,$valore);
				if ($classe) $this->set_attributo('class',$classe);
				$this->set_attributo('type','submit');
				$this->set_MyType('MyPulsante');
				if ($this->get_value()) $this->set_Tooltip($this->get_value());
		  }

            /**
	        * Associa al pulsante uno javascript che chiede la conferma della pressione
	        *
			* @param	 string $domanda Testo della domanda che lo javascript rivolge
			*/
		   public function set_Domanda($domanda,$usaJQuery=true) {
		             if(!$usaJQuery) { 
                					 $this->set_attributo('onclick','if (confirm(\''.addslashes($domanda).'\')) return true; else {if(window.event) window.event.returnValue=false;return false;}');
                					 $this->set_attributo('onkeypress','if (confirm(\''.addslashes($domanda).'\')) return true; else {if(window.event) window.event.returnValue=false;return false;}');
                					 return $this;
                		             }
                		      else {
                		             $jqc=new myJQConfirm();
                		             $jqc->set_html($domanda)->set_icona('help');
                		             $jq=new myJQuery("#{$this->get_id()}");
                		             $jq->add_code("{$jq->JQid()}.click(function(e){
                		                                              //    console.log('>{$jqc->myJQVarName()}_confirmed');
                		                                                  try{if({$jqc->myJQVarName()}_confirmed==true) {  // console.log('>{$jqc->myJQVarName()}_confirmed TRUE');
                		                                                                                                   {$jqc->myJQVarName()}_confirmed=false;
                                                                                                                            return true;
                                                                                                                            }
                		                                                      } catch (err){}
                                                                       
                		                                                  e.preventDefault();
                                                                          {$jqc->myJQVarName()}_click = {$jq->JQid()};
                                                                          {$jqc->get_js_show()}
                                                                          return false;
                                                                         });"); 
                                     $jq->add_code("{$jq->JQid()}.keypress(function(e){
                                                                           try{if({$jqc->myJQVarName()}_confirmed==true) { {$jqc->myJQVarName()}_confirmed=false;
                                                                                                                            return true;
                                                                                                                            }
                		                                                      } catch (err){}
                    		                                               e.preventDefault();
                    		                                               {$jqc->myJQVarName()}_press = {$jq->JQid()};
                                                                           {$jqc->get_js_show()}
                                                                            return false;
                                                                         });");
                		             
                		             
                		         //    $this->set_attributo('onclick',   "alert();if(window.event) window.event.returnValue=false;return false");
                		         //    $this->set_attributo('onkeypress',"function(){ {$jq->get_js_show()} };if(window.event) window.event.returnValue=false;return false");
                                     $this->add_myJQuery($jqc);
                		             $this->add_myJQuery($jq);  
                		            }       
					 return $this;
		  }


		   public function set_lockform_onsend(){
		  			$this->set_lockform_onsend=true;
		  			return $this;
		  }


		   public function get_html() {
		  	if ($this->set_lockform_onsend && !$this->myFields['static']['js_src'][get_class($this)])
											{$this->myFields['static']['js_src'][get_class($this)]=$js="<script type=\"text/javascript\">
											     	function MyDisableMess(){return false;}
											 		function MyDisableAll() {
											 								for (f=0;f<document.forms.length ;f++)
											 									for (i=0;i<document.forms[f].elements.length;i++) {
											 										document.forms[f].elements[i].onclick= MyDisableMess;
											 										document.forms[f].elements[i].onchange= MyDisableMess;
											 										document.forms[f].elements[i].onkeyup= MyDisableMess;
											 									}
											 								}
											 	  </script>";
											}
			$jsCommon=$this->get_js_common();//if(!$this->myFields['static']['common']) {$jsCommon=$this->get_js_common();$this->myFields['static']['common']=true;}
		  	$out= (!$this->con_js?'':$jsCommon).parent::get_html().($this->con_js && $this->set_lockform_onsend?"$js<script  type=\"text/javascript\">('".$this->get_id()."').form.onsubmit=MyDisableAll;</script>":'');
		  	$this->attributi['id'].='_';
		  	return $out;
		  }


    function &get_xml_value(){$out='';return $out;}

}