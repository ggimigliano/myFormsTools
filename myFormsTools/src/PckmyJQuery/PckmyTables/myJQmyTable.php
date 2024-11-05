<?php
/**
 * Contains Gimi\myFormsTools\PckmyTables\myJQmyTable.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\PckmyTables;



use Gimi\myFormsTools\PckmyTables\myTable;
                                                
 

/**
 * Interfaccia per tutte le estensioni di myJQuery aggiungibili a {@link myTable}
 * 
 */
	
interface myJQmyTable {
	 /**
	  * Invocato implicitamente dalla myTanle con il metodo {@link myTable::add_myJQuery}
	  * @param myTable $myTable MyTable a cui si aggiungew l'istanza
	  */
	 function application(myTable &$myTable);
	 
}