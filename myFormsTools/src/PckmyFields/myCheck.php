<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myCheck.
 */

namespace Gimi\myFormsTools\PckmyFields;

class myCheck extends myField {
/** @ignore */
protected $checked,$addhidden=false;


	/**
  	  * 
	  *
	  * @param	 string $nome E' il nome del campo
	  * @param	 string $valore Valore da assegnare come default
	  * @param	 string $classe e' la classe css da utilizzare
	  */
		   public function __construct($nome,$valore='',$classe='') {
		  		myField::__construct($nome,$valore);
				$this->set_attributo('type','checkbox');
				if ($classe) $this->set_attributo('class',$classe);
				$this->set_MyType('MyCheck');
		  }

	/**
	  * setta a checked  caratteristica checked
	  */

		 public function set_checked() {
			 $this->checked='checked="checked"';
			 return $this;
		  }


	/**
	  * toglie  caratteristica checked
	  */
		  public function set_unchecked() {
					 $this->checked='';
					 return $this;
		  }


			/**
	  * restituisce il valore della caratteristica checked
		*/
		   public function get_checked() {
					 return $this->checked;
		  }


		  /**
	* Setta la autotabulazione del campo*/
		   public function set_autotab () {
					 parent::set_autotab();
					 $onclick=$this->attributi['onclick'];
					 $onclick.="MyTabulazione(this)";
					 $this->SetJS($onclick,'onclick',false);
					 return $this;
   	  }

   	   public function get_js_chk($js = ''){" return null;";}


  		  /** @ignore */
   		 protected  function _get_html_hidden($pars){
   		  	if ($this->get_checked()) return parent::_get_html_hidden($pars);
   	 	 }



		/** @ignore */
   		protected  function _get_html_show($pars, $tag = 'span', $content = "<span style='font-size:120%;font-weight:bold'> &radic; </span>"){
   		  	if (!$this->get_checked()) return '';
   		  	$out=parent::_get_html_hidden($pars);
   	 		$span=$this->_get_html_show_obj($pars,$tag,$content);
   		    return $span.$out;
   		 }


   	  	function &get_xml(){
   	  			if(!$this->abilitaXML) return;
	    	    if($this->get_checked()) return parent::get_xml();
		}

	
	/**
	 * Usando questa opzione il check aggiungerà un campo nascosto vuoto con lo stesso nome del campo in modo da averlo in post vuoto anche quando non si checka
	 * Di default non c'e' e va abilitato
	 * @param boolean $stato 
	 */
	  public function set_using_hidden($stato=true){
	 	$this->addhidden=$stato;
	 }

	  public function get_Html () {
	        $this->html5Settings();
		  	if (isset($this->Metodo_ridefinito['get_Html']['metodo']) && $get_html=$this->Metodo_ridefinito['get_Html']['metodo']) return $this->$get_html(isset($this->Metodo_ridefinito['get_Html']['parametri'])?$this->Metodo_ridefinito['get_Html']['parametri']:null);
			if ((!$this->checked && isset($this->attributi["readonly"])) || isset($this->attributi["disabled"]))
								{$this->set_disabled();
								 unset($this->attributi['name']); //non uso set_name per non modificare anche l'id
								}
           
			return $this->jsAutotab().($this->addhidden?"<input type='hidden' name='{$this->get_name()}' />":'').$this->send_html('<input '.$this->stringa_attributi().' '.$this->checked.' />');
	 }


}