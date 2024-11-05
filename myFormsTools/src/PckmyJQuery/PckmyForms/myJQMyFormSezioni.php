<?php
/**
 * Contains Gimi\myFormsTools\PckmyForms\PckmyJQuery\myJQMyFormSezioni.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\PckmyForms;



/**
 * Interfaccia per tutte le estensioni di myJQuery aggiungibili a gruppo di campi di {@link myForms}
 * 
 */
	
interface myJQMyFormSezioni {
	/**
	 * Restituisce  html con i campi rifoormattato
	 * @param string $titolo titolo del gruppo
	 * @param string $v      html del gruppo di campi da riformattare
	 * @return string
	 */
	 function get_panel($titolo,&$v);
	 /**
	  * Cambia il coneuto dell'html con uno personalizzato.
	  * @param string $html
	  */
	 function set_content($html);
	 /**
	  * 
	  * Indica titolo attivo
	  * @param string $titolo
	  */
	 function set_selected($titolo);
	 
	
}