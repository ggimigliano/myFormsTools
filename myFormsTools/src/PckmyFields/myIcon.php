<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myIcon.
 */

namespace Gimi\myFormsTools\PckmyFields;
 



class myIcon extends myImage {
/** @ignore */
protected $Link;


    /**
	  *
  	  * 
	  * @param	 string $src E' il percorso dell'immagine da usare
	  * @param	 string $tooltip E' l'eventuale tooltip
	  */
  public function __construct($src,$tooltip=""){
    myField::__construct('');
    $this->unset_attributo('name');
    if ($tooltip)  {$this->set_attributo('alt',$tooltip);
                    $this->set_attributo('title',$tooltip);
                    }
	$this->set_attributo('src',$src);
	$this->set_style('border','0em');
	$this->set_MyType('MyIcon');
 }


 /*** setta un link per l'icona
 *
 * @param	 myLink $Link E' il link da applicare all'icona
 */
   public function set_link($Link) {
  	 $this->Link=$Link->clonami();
  	 return $this;
  }


 /*** restituisce il link applicato all'icona
  *
  * @return	 myLink
  */
   public function get_link() {
	 return $this->Link;
  }


  public function get_Html () {
  $get_html=isset($this->Metodo_ridefinito['get_Html']['metodo'])?$this->Metodo_ridefinito['get_Html']['metodo']:null; if ($get_html!='') return $this->$get_html(isset($this->Metodo_ridefinito['get_Html']['parametri'])?$this->Metodo_ridefinito['get_Html']['parametri']:null);
  $stringa=$this->stringa_attributi();

  if ($this->Link)
			  {$Alt=$this->attributi['alt'];
			   if(!$this->attributi['title']) $this->set_attributo('title',$this->Link->get_attributo('title'));
			   $x=$this->Link->get_html("<img $stringa />");
			   if($Alt) $this->set_attributo('alt',$Alt);
			   $this->unset_attributo('name');
			   $this->unset_attributo('id');
			   myField::__construct('');
			  }
		  else $x="<img $stringa />";

  return $this->jsAutotab().$this->send_html($x);
 }




}