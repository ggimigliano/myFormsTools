<?php
/**
 * Contains Gimi\myFormsTools\myJQSpinner.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\Pckgeneric;


use Gimi\myFormsTools\PckmyFields\myField;
use Gimi\myFormsTools\PckmyFields\myFloat;
use Gimi\myFormsTools\PckmyJQuery\myJQueryMyField;
                                                


/**
 *  @author Luigi
 * Opzioni:
 * min: valore minimo
 * max: valore massimo
 * step: passo di in/decremento
 * start: valore iniziale e/o attuale
 */
	
class myJQSpinner extends myJQueryMyField {
	/** @ignore */
	protected $items,$inizio=0,$fine, $step=1,$start;
	
	/** @ignore */
	 public function __set($var,$val){
		if (strtolower($var)==='min') $this->inizio=$val;
		  elseif (strtolower($var)==='max') $this->fine=$val;
		      elseif (strtolower($var)==='step') $this->step=$val;
		          elseif (strtolower($var)==='start') $this->start=$val;
		return parent::__set($var,$val);
	}

	/** @ignore */
	static protected function init( &$widget) {
		$widget='spinner';
	}
	
	/** @ignore */
	 public function application(MyField &$myField) {
	    $myField->set_style('text-align','right');
	    return parent::application($myField);
	}

	/** @ignore */
	 public function prepara_codice(){
	    if($this->myField) 
	           {$this->start=$this->myField->get_value();
	            if($this->myField instanceof MyFloat)
                    	    {$this->numberFormat='C';
                    	    //  $f=new MyField();
                    	    //  $this->culture=
                    	    }
                 $this->min=$this->myField->get_min();
                 $this->max=$this->myField->get_max();
                
                }
	    $this->add_code("{$this->JQid()}.val({$this->start});");
	    parent::prepara_codice();
	}
	
	
	public function set_value($valore){
		$this->start=$valore;
		return $this;
	}

	public function Errore(){
		try {
			if ($this->start<$this->inizio) throw new \Exception("Il valore ".$this->start. "&nbsp;&egrave;&nbsp; inferiore del valore minimo");
			if ($this->fine && $this->start>$this->fine) throw new \Exception("Il valore ".$this->start. "&nbsp;&egrave;&nbsp;maggiore del valore massimo");
			if ($this->inizio && $this->fine)	{
				//echo "qui";
				$range=range($this->inizio, $this->fine,$this->step);
				if (!in_array($this->start,$range)) throw new \Exception("Il valore ".$this->start. " non&nbsp;&egrave;&nbsp;nel range accettabile");
			}
			else{
				$range=range($this->inizio, (intval($this->start)+1),$this->step);
				if (!in_array($this->start,$range)) throw new \Exception("Il valore ".$this->start. " non&nbsp;&egrave;&nbsp;nel range accettabile");
			}
		}
		catch (\Exception $excp) {
  			return (string)$excp->getMessage();
		}
	}

	
	
}