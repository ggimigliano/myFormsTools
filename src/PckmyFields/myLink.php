<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myLink.
 */

namespace Gimi\myFormsTools\PckmyFields;




class myLink extends myTag {
/** @ignore */
	protected $html,$MyType;
 

/**
  * 
	  *
	  * @param	 string $link E' l'url da usare se e' un link
	  * @param	 string $tooltip E' l'eventuale tooltip
	  * @param	 string $html    E' l'html a cui applicare il link
	  * @param	 '_blank'|'_parent'|'_self' $target Opzionale
	  * @param   string
	  * @param	 string $classe E' l'eventuale classe css da utilizzare per il link
	  */

		   public function __construct($link,$tooltip="",$html='',$target='',$classe=''){
		  		$this->set_attributo('href',$link);
				if ($classe)	$this->set_attributo('class',$classe);
				if ($tooltip)   $this->set_attributo('title',myTag::htmlentities(myTag::nonhtmlentities($tooltip)));
				if ($target)    $this->set_attributo('target',$target);
				if ($html)      $this->set_html($html);
				$this->set_MyType('MyLink');
		  }


		  /**
	* Restituisce il nome del tipo del campo
	  		*
			* @return string
	  		*/
			  public function get_MyType() {
						return $this->MyType;
		  }


		  /**
		   * setta l'html a cui applicare il link.
		   *
		   * @param string $html
		   */
		   public function set_html($html) {
		  	$this->html=$html;
		  	return $this;
		  }


		  /**
	       * Restituisce l'url
	 		 *
			* @return string
	  		*/
			  public function get_Url() {
						return $this->attributi['href'];
		  }


			/** @ignore*/
		   public function set_MyType($nome) {
					 $this->MyType=$nome;
					 return $this;
		  }



		  /*** Restituisce il campo in html pronto per la visualizzazione
	 		 *
	  		* @param	string $html e' l'html a cui applicare il link se omesso si usa quello memorizzato nel costruttore
	  		* @return  string
	  		*/
		   public function get_html($html='') {
		  	 if (!$html) $html=$this->html;
		   	 return "<a ".$this->stringa_attributi().">$html</a>";
	 }


	  /** @ignore*/
			function &stringa_attributi ($v = array(), $Esclusi = true, $novalue = false) {
						  return parent::stringa_attributi($v,true,true);
		  }
}