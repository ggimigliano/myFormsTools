<?php
/**
 * Contains Gimi\myFormsTools\PckmySessions\myAutoSessions.
 */

namespace Gimi\myFormsTools\PckmySessions;




/**
     * Ottiene un lock se true lock ottenuto, false non ottenuto
     *
     * @param string $nome_lock codice mnemonico lock
     * @param int $attesa_max    attesa massima in microsecondi , 0 non ha attesa
     * @param int $timeout_naturale numero di secondi dopo di che il lock si rilascia ugualmente, se 0 scade dopo 30 giorni (durata max per memcache), attenzione!
     * @return boolean
     */
	
abstract class myAutoSessions {
	 public static function get_Istanza($procedura,
										   $options=array(
														  'myZendSessions'=>array(),
										   				  'myAPCUSessions'=>array(),
										                  'myAPCSessions'=>array(),
										   				  'myWincacheSessions'=>array(),
										   				  'myMemcacheSessions'=>array('127.0.0.1:11211'),
														  'myFileSessions'=>array()
														 )
										 ){
			$test_fun=array(
						    'myAPCUSessions'=>array('apcu_cache_info'),
	               		    'myAPCSessions'=>array('apc_cache_info'),
    			    		'myZendSessions'=>array('zend_shm_cache_store'),
							'myWincacheSessions'=>array('wincache_ucache_info'),
							'myMemcacheSessions'=>array('memcache','memcached'),
							'myFileSessions'=>array('fopen')
							);
		   	foreach ($options as $class=>&$pars) {
		   			if(isset($test_fun[$class])) 
		   					{$ok=false;
		   				  	 foreach ($test_fun[$class] as $nome) if(is_callable($nome) || class_exists($nome,false)) {$ok=true;break;}
		   					 if(!$ok) continue;	
		   					}
		   					
					$rc = new \ReflectionClass("Gimi\\myFormsTools\\PckmySessions\\$class");
		   			$obj=$rc->newInstanceArgs(array_merge(array($procedura) , (array) $pars));
		   			if($obj->is_available()) return $obj;
 					unset($obj);unset($rc);
		   			}
		   	return false;
		   }
}