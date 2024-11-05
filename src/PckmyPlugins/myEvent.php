<?php
/**
 * Contains Gimi\myFormsTools\PckmyPlugins\myEvent.
 */

namespace Gimi\myFormsTools\PckmyPlugins;




/**
 * Classe di gestione di un evento da scatenare in myEventManager
 * @see myEventManager
 */
	
interface myEvent{
	/**
	 * 
	 * Restituisce true se l'azione di questo evento deve essere scatenata nella classe {@link myEventManager}
	 * @param myEventManager $eventManager    Istanza a cui  e'  aggiunto
	 * @return boolean
	 */
	function isTrue(myEventManager $eventManager=null);
	
	/**
	 *  E' l'azione che deve essere scatenata al varificarsi di isTrue()
	 * @param myEventManager $eventManager    Istanza a cui  e'  aggiunto
	 *
	 */
	function action(myEventManager $eventManager=null);
	
}