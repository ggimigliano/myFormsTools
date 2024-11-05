<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\myJQueryMyTable.
 */

namespace Gimi\myFormsTools\PckmyJQuery;


use Gimi\myFormsTools\PckmyTables\myTable;

 


/**
 * Classe di utilità per produrre codice facilmente utilizzabile con il metodo {@link myTable::add_myJQuery()}
 */
	
abstract class myJQueryMyTable extends myJQuery{
/**
 * @ignore
 */protected $myTable;


/**
 * @ignore
 */	final public function __construct() {
		parent::__construct('');
	}


	
/**
 * @ignore
 */final function application(myTable &$myTable) {
		$this->myTable=&$myTable;
		$class=get_class($this);
		$class::init();
		$this->set_istance_defaults();
	}



/**
 * @ignore
 */function get_id(){
		return '#'.$this->myTable->get_id();
	}



	/**
	 * Impostare (tramite {@link self::add_code()}) qui commandi specifici della singola istanza
	 */
	 public abstract  function set_istance_defaults();



   /**
    * Imposta le chiamate statiche es. caricamento degli script .js
    * @param string $widget e' il nome del widget jQuery implementato
    */
      static protected function init(){}

 //   abstract function ricalcola_TAG($riga,$colonna,$tag,$attuale);
 //   abstract function ricalcola_TAG_intestazione($colonna,$tag,&$attuale);

}