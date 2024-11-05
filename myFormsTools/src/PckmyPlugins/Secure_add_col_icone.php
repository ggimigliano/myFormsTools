<?php
/**
 * Contains Gimi\myFormsTools\PckmyPlugins\PckmySecurizer.
 */

namespace Gimi\myFormsTools\PckmyPlugins;


              


/**
 * 
 * Estensione utile per codificare {@link myTable::add_col_icone()}
 * <code>
 *  $valori=array( array(1,'pippo'),
 *  			   array(2,'topolino'),
 *  			   array(3,'pluto')
 *  			);		
 * 	$tabella=new myTable($valori);
 * 	$i=new MyIcon('/icone/edit.png');    //creo un'icona
 *  $i->set_link(new MyLink('gestione.php','Modifica dati'));  //le assegno un link ad un script che per cancellare chiede le chiavi in _GET
 *	$tabella->add_col_icone($i,array(
 *									 'codice'=>array(1, Secure_add_col_icone::getInstance()  )
 *									 )
 *							); 
 * </code>
 *
 */
	
class Secure_add_col_icone extends mySecurizer implements myTable_Plugin_for_add_col_icone{

	 public function add_col_val($colonna,$valore){
		 return $this->encode($colonna,$valore);
	}
}