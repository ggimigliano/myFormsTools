<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQTimePicker.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\PckmyFields;


use Gimi\myFormsTools\PckmyFields\myDateTime;
use Gimi\myFormsTools\PckmyFields\myDate;
use Gimi\myFormsTools\PckmyFields\myField;

use Gimi\myFormsTools\myCSS;
use Gimi\myFormsTools\PckmyJQuery\myJQuery;
use Gimi\myFormsTools\PckmyJQuery\myJQueryMyField;
                                                


/**
 * 
 * @see http://trentrichardson.com/examples/timepicker/
 *
 */
	
class myJQTimePicker extends myJQueryMyField {
    /**
     * @ignore
     */
	protected $lingua="it",$addSlider;


	/**
	 * @ignore
	 */
	static protected function init(&$widget) {
		$widget='timepicker';
	}


	/**
	 * @ignore
	 */
	 public function set_istance_defaults() {
	    parent::set_istance_defaults(); 
		self::add_src(self::$percorsoMyForm."jquery/timepicker/jquery-ui-timepicker-addon.js");
		self::add_css(self::$percorsoMyForm."jquery/timepicker/jquery-ui-timepicker-addon.css");
    }


    /**
     * @ignore
     */
	 public function get_common_defaults() {
		return array();

	}

	/**
	 * Permette di localizzare il timestamp in base alla sigla della lingua
	 * @param string $lingua (sigla)
	 * Parametri accettati:
	 * af => Afrikaans
	 * bg => Bulgaro
	 * ca => Castellano
	 * cs => Ceco
	 * de => Tedesco
	 * el => Greco
	 * es => Spagnolo
	 * et=>estone
	 * eu => Basco
	 * fi => Finnico
	 * fr => Francese
	 * gl => Galiziano
	 * he => Ebraico
	 * hu => Ungherese
	 * id => Indonesiano
	 * it => Italiano
	 * ja => Giapponese
	 * ko => Coreano
	 * li => Lituano
	 * nl => Olandese
	 * no => Norvegese
	 * pl => Polacco
	 * pt => Portoghese
	 * pt-BR => Portoghese-Brasiliano
	 * ro => Rumeno
	 * ru => Russo
	 * sk => Slovacco
	 * sv => Svedese
	 * th => Thailandese
	 * tr => Turco
	 * uk => Ucraino
	 * vi => Vietnamese
	 * zh-CN => Cinese semplificato
	 * zh-TW => Cinese
	 *
	 */
	public function set_localization($lingua){
		if (strpos($lingua, "-")>0){
			$nuova=explode("-", $lingua);
			$this->lingua=strtolower($nuova[0])."-".strtoupper($nuova[1]);
		}
		else $this->lingua=strtolower($lingua);
	}

	
	
	 public function get_common_code($usa_autoavvio=true){
	    $d=new MyDate('');
	    $dp=$d->add_myJQuery(new myJQDatepicker());
	    $dp->prepara_codice();
	    return parent::get_common_code().$dp->get_common_code();
	}

	
	public function usa_slider($sliderAccessArgs=array('touchonly'=>false,'where'=>'after') ){
		    self::add_src(self::$percorsoMyForm."jquery/timepicker/jquery-ui-sliderAccess.js",'all','slider');
		//    $this->addSlider=true;
		  //  $this->add_common_code(myCSS::get_css_jscode('.ui-button-icon-only{height:5px!important}',true));
		    $this->addSliderAccess=true;
		    $this->sliderAccessArgs=$sliderAccessArgs;
		    return $this;
	}
	
	/**
	 * @ignore
	 */
	private function get_componenti($v){
	    $v=explode(' ',$v);
	    if(count($v)==2) {
	                       if(strpos($v[1],':')!=false)
	                                   return array('ora'=>explode(':',$v[1]),'data'=>new MyDate('',$v[0]));
	                              else return array('ora'=>explode(':',$v[0]),'data'=>new MyDate('',$v[1])); 
	                      }
	    if(count($v)==1) return array('ora'=>explode(':',$v[0]));
	    return null;
	}

	/**
	 * @ignore
	 */
	 public function prepara_codice(){
	 if($this->myField)  {
	      
	        $this->set_istance_defaults();
	        if(method_exists($this->myField,'get_frazioni_ora')) $this->stepMinute=$this->myField->get_frazioni_ora();
	        $v=$this->get_componenti($this->myField->get_value());
	        if(isset($v['ora'])) {
	                       if(isset($v['ora'][0])) {$this->hour=$v['ora'][0];
	                                                $this->timeFormat='HH:mm';
	                                               }
	                       if(isset($v['ora'][1])) $this->minute=$v['ora'][1];
	                       if(isset($v['ora'][2])) {$this->second=$v['ora'][2];
	                                                $this->timeFormat='HH:mm:ss';
	                                               }
                           }
                           
           if($this->myField instanceof myDateTime){
                              $m=array();
	                          preg_match('/^([dmy]{1,})([^dmy]{1,})([dmy]{1,})([^dmy]{1,})([dmy]{1,})$/', 'ymd',$m);
	                          }
	                          
            if($this->myField->get_max(true) && 
               $v=$this->get_componenti($this->myField->get_max(true))) {
                   if(isset($v['data']) && isset($v['ora'])) $this->maxDateTime="new Date({$v['data']->get_parte(2)},".($v['data']->get_parte(1)-1).",{$v['data']->get_parte(0)},{$v['ora'][0]},{$v['ora'][1]},{$v['ora'][2]})";
                                   elseif(isset($v['ora']))  $this->maxTime=implode(':', $v['ora']);
                   
              }
             
            if($this->myField->get_min(true) && 
               $v=$this->get_componenti($this->myField->get_min(true))) {
                   if(isset($v['data']) && isset($v['ora'])) $this->minDateTime="new Date({$v['data']->get_parte(2)},".($v['data']->get_parte(1)-1).",{$v['data']->get_parte(0)},{$v['ora'][0]},{$v['ora'][1]},{$v['ora'][2]})";
                                   elseif(isset($v['ora']))  $this->minTime=implode(':', $v['ora']);
                   
              }                       
	    }
	    return parent::prepara_codice();
	}
	
	
	/**
	 * @ignore
	 */
	protected  function construct(){
		$this->costruito=true;
		$opts=$this->build_options();
		if($opts===false) return;
		if($this->myField && $this->myField instanceof MyDateTime) $widget='datetimepicker';
		                                                      else $widget=$this->widget;
		if(!$opts)	$this->add_code("{$this->myJQVarName()}={$this->JQid()}.{$widget}()",null);
		       else $this->add_code("{$this->myJQVarName()}={$this->JQid()}.{$widget}($opts)",null);

		if($this->css_font_class) {
		                 $css='';
			             foreach ($this->css_font_class as $class) $css.=$class.self::$css_fonts[get_called_class()]." ";
			             $codice=myJQuery::get_add_style($css,true);
			             $pred=self::get_common_codes('commonJQUI');
			             if(strpos($pred,$codice)===false)  $pred.=";".$codice.";";
			             self::add_common_codes($pred,'commonJQUI');
		              }
		$this->add_common_code(mycss::get_css_jscode(".ui-datepicker-trigger{position:relative;top:3px;left:-18px}",true));
		return $this;
	}
	
	
	
	
	/**
	 * @ignore
	 */
	 public function get_html(){
	    if(!$this->costruito) $this->prepara_codice();
	    $v=trim((string) $this->get_common_code())."\n".trim((string) $this->get_code());
	    $out= self::get_src().  self::get_add_action($this->get_new_html($v));
	    $f=new myField();
	    if($f->get_dizionario()) $this->set_localization($f->get_dizionario()->get_al());
	    static $linguaFatta;
	    if(!$linguaFatta) { 
	           $linguaFatta=true;
	           self::add_src(self::$percorsoMyForm."jquery/timepicker/i18n/jquery-ui-timepicker-{$this->lingua}.js");
	           $out.=self::get_src();
	           }
	     return $out;      
	}
	
}