<?php
/**
 * Contains Gimi\myFormsTools\PckmyTables\myJQMyTableSorter.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\PckmyTables;


use Gimi\myFormsTools\PckmyJQuery\Pckgeneric\myJQTableSorter;
use Gimi\myFormsTools\PckmyTables\myTable;

                                                 
class myJQMyTableSorter extends myJQTableSorter implements myJQmyTable{
/**
 * @ignore 
 */
protected $myTable;

/**
 * @ignore 
 */	final function __construct() {
		parent::__construct('');
	}


/**
 * @ignore 
 */function application(myTable &$myTable) {
		$this->myTable=&$myTable;
 	}

     public function get_html(){
		return "<script type='text/javascript'>{$this->Init['start']}</script>".
		          parent::get_html();
	}

/**
 * @ignore 
 */function get_id(){
		return '#'.$this->myTable->get_id();
	}



      /**
       *
       * Ricorda tramite cookie l'ultimo ordinamento impostato
       * @param string $id_tabella e' un id obbligatorio da dare a quella tabella, ATTENZIONE CAMBIA l'ID DELLA TABELLA USARE POSSIBILMENTE SEMPRE COME PRIMO METODO NON PUO' ESSERE RANDOM ALTRIMENTI TORNANDO SULLA TABELLA NON SI RIPRISTINA
       *
       */
      public function set_order_memory($id_cookie=''){
      		$this->myTable->set_id($id_cookie);
      		$this->set_id("#$id_cookie");
      		parent::set_order_memory();
      		return $this;
      }
}