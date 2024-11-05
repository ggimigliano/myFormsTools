<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQAutocompleteUtilsStatic.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\PckmyFields;



use Gimi\myFormsTools\PckmyAjax\myRicercaAjax;
use Gimi\myFormsTools\PckmyJQuery\myJQuery;
                                                

/**
 * @ignore
 */

/**
 * @ignore
 */
	
abstract class myJQAutocompleteUtilsStatic extends myJQuery {
	 public static function estrai_ids($array,$chiamata=''){
		if(!$array || !is_array($array)) return array();
		if(!$chiamata) $chiamata=function($id){return $id;};
		foreach ($array as $nome=>&$campo) 
			if(!is_object($campo)) $campo=call_user_func($chiamata,$campo);
				else{
				if($campo->isMultiple())
					{$subfields=$campo->get_campi_interni();
					unset($array[$nome]);
					if($subfields) {
						$j=0;
						foreach ($subfields as $fld)
								{
									$array["{$nome}[{$j}]"]=call_user_func($chiamata,$fld->get_id());
									$j++;
								}
						}
					}
					elseif(method_exists($campo,'get_id')) $campo=call_user_func($chiamata,$campo->get_id());
					elseif ($campo instanceof MyRicercaAjax) $campo=call_user_func($chiamata,$campo->get_id());
			}	
		return  $array;	
	}

		
	 public static function altezza_scroll_code($px){
		if(self::isMSIE() && self::isMSIE()<=6) $max_height='height';
										else 	$max_height='max-height';
		if($px)
			{
			$open=self::get_add_style("
						.ui-autocomplete { $max_height:{$px}px; overflow-y: auto; overflow-x: hidden;padding-right: 20px}
						html .ui-autocomplete { $max_height:  {$px}px;}
						",true);
			}
			else {$open=self::get_add_style(" 
						.ui-autocomplete { $max_height: auto;overflow-y: auto;overflow-x: hidden;padding-right: 5px;}
						html .ui-autocomplete {	$max_height:  auto;}
						",true);
			}
	 	return $open;
	 	}



	  public static function indicatore_attesa_code($url=''){
	 	if($url===false)  $open=self::get_add_html('head',"<style>.ui-autocomplete-loading{background:none}</style>");
			 elseif($url=='') $open=self::get_add_html('head',"<style>.ui-autocomplete-loading{background:white url('".self::$percorsoMyForm."jquery/ui/images/ui-rotella_16x16.gif') right center no-repeat}</style>");
				else  $open=self::get_add_html('head',"<style>.ui-autocomplete-loading{background:white url('$url') right center no-repeat}</style>");
		 return $open;
	}
	
	
}