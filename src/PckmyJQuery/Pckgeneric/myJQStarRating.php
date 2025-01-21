<?php

namespace Gimi\myFormsTools\PckmyJQuery\Pckgeneric;
use Gimi\myFormsTools\PckmyJQuery\myJQuery;

/**

 * Classe per visualizzare il gradimento in un sondaggio
 * Eventuali riferimenti: http://wbotelhos.com/raty/
 *
 */
class myJQStarRating extends myJQuery {
	protected $opzioni;
	protected $iconRange;
	protected $tooltip;
	public function __construct($id,$value='') {
		parent::__construct ( $id );
		self::add_src(self::$percorsoMyForm."/jquery/starRating/jquery.raty.min.js");
		$this->path="'".self::$percorsoMyForm.'/jquery/starRating/img'."'";
		$this->scoreName="'".substr($this->get_id(),1)."'";
		if ($value) $this->score=$value;

	}
	/**
	 * Restitiosce il namspace della classe
	 * @return string
	 */
	 public function get_widget(){
		return 'myJQStarRating';
	}
	 public function application(&$myField){
		$this->myField=$myField;
		return $this;
	}
	/*
	* @ignore
	*/
 	 public function get_new_html(&$html) {
 		//$aggiunte=$this->add_opzioni();
 		$aggiunte=$this->encode_array($this->opzioni);

 		if ($this->iconRange) $aggiunte=substr($aggiunte,0,-1).",".$this->iconRange."}";
		if ($this->tooltip) $aggiunte.=substr($aggiunte,0,-1).",".$this->tooltip."}";
 		$this->add_code(" {$this->JQid()}.raty({$aggiunte})");
 		return parent::__toString();
 	}

	/**
 	 * @ignore
 	 */
 	 public function __toString(){
 	    $html=null;
 		return "<div id='".substr($this->get_id(),1)."'>".$this->get_new_html($html)."</div>";
 	}


	 public function __set($var,$val){
		$this->opzioni[$var]="$val";

	}
	/**
	 * Setta un array di oggetti da visualizzare, dove ognuno rappresenta un'icona personalizzata.
	 *
	 * @var $iconRange= array nel formato:
	 *  array('range'=>'la posizione nella quale sarà  visualizzata l'icona',
	 *  	'on'=>'l'icona attiva'
	 *  	'off'=>'l'icona inattiva);
	 *
	 *
	 * <code>
	 * $iconRange=array(array('range'=>1,'on'=>'1.png','off'=>'0.png'),
	 *		array('range'=>2,'on'=>'2.png','off'=>'0.png'),
	 *			array('range'=>3,'on'=>'3.png','off'=>'0.png'),
	 *			array('range'=>4,'on'=>'4.png','off'=>'0.png'),
	 *			array('range'=>5,'on'=>'5.png','off'=>'0.png'));
	 * $s=new myJQStarRating('#star',3);
	 * $s->set_iconRange($iconRange);
	 * </code>
	 */
	public function set_iconRange($iconRange){
		$this->iconRange="iconRange:".$this->encode_array($iconRange);
	}

	public function set_tooltip($tooltip){
		$this->tooltip="hints:".$this->encode_array($tooltip);
	}



}

?>