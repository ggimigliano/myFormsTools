<?php
/**
 * Contains Gimi\myFormsTools\PckmyArrayObject\myGroupFields.
 */

namespace Gimi\myFormsTools\PckmyArrayObject;




/**
 * Permette di raggruppare i campi in gruppi in modo da personalizzarne il layout
 * attraverso la riscrittura del metodo get_html
 */
	
class myGroupFields extends myArrayObject{
/** @ignore */
protected static $campi;
protected $label,$form;	public $Prevede_label=false;

/**
 * @param array $campi elenco dei nomi dei campi (riferiti alla form)
 * @param string $label del gruppo
 */
	 public function __construct($campi,$label='') {
		$this->label=$label;
		foreach ($campi as &$v) {$this[$v]=$v;
								self::$campi[strtoupper($v)]=$this;
								}
	}
	
	
	
	/** @ignore */
	 public function in_form(&$form){
		$this->form=&$form;
		foreach ($this as $k=>&$field) 
						{$this[$k]=$form->campo($field);
						 $form->salta_get_html($k);
						 if (method_exists($this,'get_xml')) $form->salta_get_xml($k);
						 
						}
	}
	
	/**
	 * Restituisce la label del gruppo di campi
	 *
	 * @return string
	 */
	 public function get_label(){
		return $this->label;
	}
	
	/** @ignore */
	 public function __get($par)	  {return $this[strtoupper($par)];}
	/** @ignore */
	 public function __set($par,$val) { $this[strtoupper($par)]=strtoupper($val);}
	
	/**
	 * Restituisce l'html per la label del campo $k, 
	 * se il campo non e' nella form restituisce null
	 *
	 * @param string $k
	 * @return string
	 */
	 public function html_label($k) {
		if(!$this->form->campo($k)) return null;
		return $this->form->htmlLabelCampo($k,false,true);
	}

	/**
	 * Restituisce l'html del campo $k, 
	 * se il campo non e' nella form restituisce null
	 *
	 * @param string $k
	 * @return string
	 */
	function &html_campo($k) {
		if(!$this->form->campo($k)) return null;
		return $this->form->Get_html_campo($k);
	}

	
	/**
	 * Restituisce il valore del campo $k,
	 * se il campo non e' nella form restituisce null
	 *
	 * @param string $k
	 * @return mixed
	 */
	function &value_campo($k) {
		if(!$this->form->campo($k)) return null;
		return $this->form->campo($k)->get_value();
	}

	
	
	/**
	 * Restituisce il valore del della label campo $k,
	 * se il campo non e' nella form restituisce null
	 *
	 * @param string $k
	 * @return string
	 */
	function &value_label($k) {
		if(!$this->form->campo($k)) return null;
		return $this->form->get_label($k);
	}

	/**
	 * Restituisce true se il campo $k è nascosto o e' stato rimosso,
	 * se il campo non e' nella form restituisce null
	 *
	 * @param string $k
	 * @return boolean
	 */
	 public function is_hidden($k){
		if(!$this->form->campo($k)) return null;
		return  $this->form->campo($k)->estende('myHidden',true) ||
			    $this->form->campo($k)->is_hidden();
		       
	}
	
	
	/**
	  * Restituisce l'xml della form 
	  *
	  * @param string $campo nome del campo di cui si vuole l'xml
	  * @param string $case se omesso si rispetta il case originale dei campi (o del DB) , U= maiuscolo L=minuscolo
	  * @return string
	  */
	function &xml_campo($campo,$case='') {
	 	return $this->form->get_xml('',$case,false,$campo,null,true);
	}

	
	function &get_html() {
		if($this->label) $out="<tr><th colspan='2'>{$this->label}</th></tr>";
		foreach (get_object_vars($this) as $k) $out.="<tr><td>{$this->html_label($k)}</td><td>{$this->html_campo($k)}</td></tr>";
		return $out;
	}
	
	
	/** @ignore */
	 public function __call($func,$pars){
		switch ($func) {
			case 'get_id':
			case 'get_name': return md5(spl_object_hash($this));
				break;
		}
	}
	
	/** @ignore */
	 public function __toString(){
		return $this->get_html();
	}	
}