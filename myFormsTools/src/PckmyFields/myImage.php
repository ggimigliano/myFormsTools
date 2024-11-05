<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myImage.
 */

namespace Gimi\myFormsTools\PckmyFields;
 



class myImage extends myPulsante {
/**
  * 
	  *
			* @param	 string $nome E' il nome del campo
			 * @param	 string $src E' il percorso dell'immagine da usare
	  * @param	 string $tooltip E' l'eventuale tooltip
	  */

		   public function __construct($nome,$src,$tooltip=""){
				myField::__construct($nome);
				$this->set_attributo('type','image');
				$this->set_attributo('src',$src);
				if ($tooltip) $this->set_Tooltip($tooltip);
				$this->set_style('border','0em');
				$this->set_MyType('MyImage');
		  }


		  /**
	* Setta la larghezza e l'altezza in % o pt
			* @param	integer $width
			* @param	integer $height
	  */
		   public function set_size($width,$height) {
				 $this->set_attributo('width',$width);
				 $this->set_attributo('height',$height);
				 return $this;
		  }


		  /**
	* Setta il Tooltip del campo
			* @param	string $testo e' il testo
			*/
		   public function set_Tooltip ($testo) {
				 $this->set_attributo("alt",$testo);
				 $this->set_attributo("title",$testo);
				 return $this;
	 }

	  /** @ignore*/
			function &stringa_attributi ($v = array(), $Esclusi = true, $novalue = false) {
				$nogood=array('border','height','width');
				foreach ($nogood as $nome) 
				    if (isset($this->attributi[$nome])&& trim((string) $this->attributi[$nome])!=='') $this->set_style($nome,$this->attributi[$nome]);
				$stringa=parent::stringa_attributi(array_merge($nogood,$v),true,true);
//				if ($style) $stringa.=" style='$style' ";
				return $stringa;
		  }
}