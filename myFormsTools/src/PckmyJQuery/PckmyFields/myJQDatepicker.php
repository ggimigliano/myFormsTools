<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQDatepicker.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\PckmyFields;


use Gimi\myFormsTools\myCSS;
use Gimi\myFormsTools\PckmyJQuery\myJQueryMyField;
use Gimi\myFormsTools\PckmyFields\myField;
                                                


/**
 *
 * Classe che produce l'effetto datepicker, di default e' autocaricata con {@link myDate::set_calendar()}
 *
 */
	
class myJQDatepicker extends myJQueryMyField {
/** @ignore */
//in js inst.dpDiv.zIndex($(input).zIndex()+1); deve diventare inst.dpDiv.zIndex($(input).zIndex()+100);
protected  $css_font_default='{font-size:11px}',$css_font_class=array('#ui-datepicker-div');

    /** @ignore */
    static protected function init(&$widget) {
		$widget='datepicker';
	}

	/** @ignore */
	final function set_common_defaults(array $defaults){
		$this->add_common_code(self::$identificatore.".{$this->widget}.setDefaults(".self::encode_array($defaults).")",'defaults');
	    $this->add_common_code(mycss::get_css_jscode(".ui-datepicker-trigger{position:relative;top:3px;left:-18px}",true));
      }

	/** @ignore */
	 public function get_common_defaults(){
	    if(!$this->myField) return array();
		for($i=1;$i<=12;$i++) {$mese=$this->myField->inlettere(array('m'),"1/$i/".date('Y'));
	    					   $mesi[]=ucfirst($mese['m']);
	    					   $mesiBrevi[]=substr(ucfirst($mese['m']),0,3);
	    					   }
	    foreach (array('domenica','lunedì','martedì','mercoledì','giovedì','venerdì','sabato') as $giorno)
	    			{$giorno=ucfirst($this->myField->trasl($giorno));
	                 if(myField::get_charset()!='ISO-8859-1') $giorno=myField::charset_encode($giorno);
	    			 $giorni[]=$giorno;
	    			 $giorniBrevi[]=substr($giorno,0,3);
	    			}
	    return array('changeYear'=>true,
					 'changeMonth'=>true,
	    			 'buttonImage'=> self::$percorsoMyForm."icone/cal.gif",
	    			 'buttonImageOnly'=>true,
	    			 'showOn'=>'button',
	    			 'hideIfNoPrevNext'=>true,
	    			 'showMonthAfterYear'=>true,
					 'dateFormat'=>'dd/mm/yy',
	    			 'monthNames'=>$mesi,
	    			 'monthNamesShort'=>$mesiBrevi,
	    			 'nextText'=>ucfirst($this->myField->trasl('Mese successivo')),
	    			 'prevText'=>ucfirst($this->myField->trasl('Mese precedente')),
	    			 'dayNamesMin'=>$giorniBrevi,
	    			 'dayNames'=>$giorni
	    			);
	}

	/** @ignore */
	 public function set_istance_defaults(){
		//$opzioni['showOn']='button';
		$var="val_".spl_object_hash($this);
	    $this->set_event('beforeShow',"$var = {$this->JQid()}.val()");
	    $this->set_event('onSelect',"{$this->JQid()}.focus(); if($var!={$this->JQid()}.val()) {$this->JQid()}.trigger('change');");
		$opts=$this->get_common_defaults();
		$m=array();
		if(isset( $opts['dateFormat'])) preg_match('/^([dmy]{1,})([^dmy]{1,})([dmy]{1,})([^dmy]{1,})([dmy]{1,})$/', $opts['dateFormat'],$m);
	    if(count($m)>=5) $formato=trim(str_replace(array('d','m','y'),array('g','m','a'),$m[1][0].$m[3][0].$m[5][0]));
		if(!$this->myField) return;
		//$this->set_style('padding-right', '18px');
		$annomax=$annomin=null;
		if($this->myField->get_disabled()) $this->disabled=true;
	    if($this->myField->get_min(true)!='')  {$this->minDate=($x=$this->myField->get_formatted($formato,$m[2][0],$this->myField->get_min(true))) ;
	    									$annomin=explode($m[2][0],$x);
	    									$annomin=$annomin[2];
	    								   }
	    if($this->myField->get_max(true)!='')  {$this->maxDate=($x=$this->myField->get_formatted($formato,$m[2][0],$this->myField->get_max(true)));
	    									$annomax=explode($m[2][0],$x);
	    									$annomax=$annomax[2];
	    								   }

		if ($annomax && !$annomin) $annomin=$annomax-100;
		if(!$annomin)  $annomin=date('Y')-20;
		if(!$annomax)  $annomax=date('Y')+20;
		$this->yearRange="$annomin:$annomax";
	}

	/** @ignore */
	 public static function get_event_sign($metodo){
        switch ($metodo) {
        	case 'beforeShow': return 'function(input, inst)';
        	case 'beforeShowDay': return 'function(date)';
        	case 'onChangeMonthYear': return 'function(year, month, inst)';
        	case 'onClose': return 'function(dateText, inst)';
        	case 'onSelect': return 'function(dateText, inst)';
        	default:return 'function(event, ui)';
        }
	}


}