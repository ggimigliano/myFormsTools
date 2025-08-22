<?php
/**
 * Contains Gimi\myFormsTools\PckmyPlugins\myEventManager.
 */

namespace Gimi\myFormsTools\PckmyPlugins;


use Gimi\myFormsTools\PckmyForms\myForm;



/**
 * 
 * Classe con metodi (azioni) scatenabili su eventi personalizzabili e/o standard
 */
	
abstract class myEventManager  {
/* @ignore */
private $Register=array(),$Rewind=false,$Estop=-1,$EMsession,$Events=array(),$Epriorities=array();
/* @ignore */
private static $myEventManagerStatus=array(),$metodiEstratti=array();

		
		private function onStartsFor($where,$start){
			 $ord=$par=null;
			 if(strpos($where, $start)===0)
						{
						 $p=explode('§', substr($where,strlen($start)).'§');
						 if(count($p)>1 && is_numeric($p[count($p)-2]))
							 	{   
							 		$ord=$p[count($p)-2];
							 		array_pop($p);
							 	}
						  array_pop($p);
					 	  if(isset($p[0]) && $p[0]!='')
						  		  {
								 	$par=implode('§',$p);
								  }
								 
						 return array('ord'=>$ord,'par'=>$par);
						}
			return null;
		}
		
		
		private function addEvent($p,$startsFor,$metodo, $val){
			if(!$p['par']) 
						$this->addEventFunction(function()   {return  true;},
											$metodo->name,
											array(),
											max(0,!isset($p['ord'])?0:intval($p['ord']))
											);
				  elseif($val !==null && $metodo->name=="{$startsFor}{$p['par']}") 
				  			{
				  			 $this->addEventFunction(function()  {return true;},
											$metodo->name,
											array(&$val) ,
											max(1,!isset($p['ord'])?0:intval($p['ord']))
											);
				  		  }
					
		}
	
	   public function __construct(){ 
	    $classe= get_class($this);
	 	if(!isset(self::$myEventManagerStatus[spl_object_hash($this)])) self::$myEventManagerStatus[spl_object_hash($this)]=new \ArrayObject(array('class'=>$classe),\ArrayObject::ARRAY_AS_PROPS);
	 	if(!isset(self::$metodiEstratti[get_class($this)])) {
                                                    	 	  $reflection = new \ReflectionClass($classe);
                                                    	 	  self::$metodiEstratti[$classe] = $reflection->getMethods();
                                                    	 	}
		
		$p=array();
		 
		foreach (self::$metodiEstratti[$classe] as &$metodo) 
			if(strpos($metodo->name, 'on')===0)
				{
				$p=$this->onStartsFor($metodo->name,'onGET');
				if($p && isset($_GET) && count($_GET)>0 ) $this->addEvent($p, 'onGET',$metodo, ($p['par'] && isset($_GET[$p['par']]))? $_GET[$p['par']]:null);
				
				$p=$this->onStartsFor($metodo->name,'onPOST');
				if($p && isset($_POST)  && count($_POST)>0 ) $this->addEvent($p, 'onPOST',$metodo,$p['par'] &&  isset($_POST[$p['par']])?$_POST[$p['par']]:null);
				
				$p=$this->onStartsFor($metodo->name, 'onSESSION');
				if($p && isset($_SESSION)  && count($_SESSION)>0) $this->addEvent($p, 'onSESSION', $metodo,$p['par'] &&  isset($_SESSION[$p['par']])?$_SESSION[$p['par']]:null);
				
				$p=$this->onStartsFor($metodo->name,'onNotGET');
				if($p && (!isset($_GET) || !count($_GET) || ($p['par'] && !isset($_GET[$p['par']])))) $this->addEvent($p,'onNotGET', $metodo,true);
				
				$p=$this->onStartsFor($metodo->name,'onNotPOST');
				if($p && (!isset($_POST) || !count($_POST) || ($p['par'] && !isset($_POST[$p['par']])))) $this->addEvent($p, 'onNotPOST',$metodo,null);
				
				$p=$this->onStartsFor($metodo->name, 'onNotSESSION');
				if($p && (!isset($_SESSION) || !count($_SESSION) || ($p['par'] && !isset($_SESSION[$p['par']])))) $this->addEvent($p,'onNotSESSION', $metodo,null);
				
				}			 
						
	}
	
	
	/***
	 * Aggiunge un evento da gestire
	 * @throws \Exception
	 * @param myEvent $event    Istanza da aggiungere
	 * @param int     $prority  priorità da assegnare all'evento pi u'  e grande e prima si scatena rispetto ad altri eventi
	 */
	  public function addMyEvent($event,$prority=1) {
		if(is_object($event) && in_array('myEvent',class_implements($event))) 
								{
								  $this->Epriorities[count($this->Events)]=intval($prority);
								  $this->Events[]=$event;
								 }
						 else throw new \Exception("Impossibile aggiungere un evento che non implementi myEvent in ".get_class($this));
		return $this;
	}
	
	
	/***
	 * Aggiunge un evento da gestire
	*
	* @param function() $event    Istanza da aggiungere
	* @param string  $action   Azione(metodo) della classe {@link myEventManager} da lanciare se si verifica, di default  e'  il risultato del metodo @see myEvent::action()
	* @param array   $parameters Eventuali parametri da passare all'azione (metodo) della classe {@link myEventManager}, di default  e'  il risultato del metodo @see myEvent::parameters()
	* @param int     $prority  priorità da assegnare all'evento pi u'  e grande e prima si scatena rispetto ad altri eventi
	*/
	private function addEventFunction($event,$action='',array $parameters=array(),$prority=1) {
		if(!is_callable($event)) throw new \Exception("Impossibile aggiungere un evento per $action");
			
		$obj=new \stdClass();
		$obj->action=&$action;
		$obj->isTrue=&$event;
		$obj->param=&$parameters;
		
		$this->Epriorities[count($this->Events)]=intval($prority);
		$this->Events[]=$obj;
		return $this;
	}
	
	/**
	 * Se invocata in un metodo(azione) eventuali eventi successivi non vengono saltati
	 * @param int $jumps se 0 li salta tutti se >0 salta $jumps eventi
	 */
	final  function stopEvents($jumps=0) {
		$this->Estop=intval($jumps);	
		return $this;		 
	}


	/**
	 * Rilancia l'elaborazione degli eventi
	 */
	final  function rewindEvents() {
		$this->Rewind=true;
		return $this;
	}
	
		
	/**
	 * @ignore
	 * @return myForm puntatore all'istanza 
	 */
	final function dispatchEvents() {
		asort($this->Epriorities);	
		$this->onStart();
		do{ $this->Rewind=false; 
			$this->Register=array();
			foreach (array_keys($this->Epriorities) as $i)
				   {if($this->Estop>0) 
				   				{
				   				if(--$this->Estop===0) $this->Estop=-1;
				   				continue; 		 	
				   				}
				    $event=&$this->Events[$i];
				    if (method_exists($event,'isTrue')) 
				    				{
				    				 if($event->isTrue()) {$event->action($this);
				    				 					   $this->Register[]=get_class($event).'::action';
				    				 					  }
				    				}
				    		      elseif(($isTrue=$event->isTrue)!==null && $isTrue()) 
				    		      							{
				    								   	     call_user_func_array(array($this,$event->action),$event->param);
				    										 $this->Register[]=$event->action;
				    										}
				 	if($this->Estop===0) break;
					}
		 } while ($this->Rewind);		
		$this->onClose();	
		return $this;					 
	}
	
	final function getLastAction(){
		return array_pop(array_reverse($this->Register));
	} 
	
	final function wasExecuted($action){
		return in_array($action,$this->Register);
	}
	
	
	final function getActions(){
		return $this->Register;
	} 
	
	 public function __call($m,$v){
	    if(!class_exists($m,false)) return;
        $m=strtolower($m);
        $classInfo=new \ReflectionClass(get_class($this));
        while($classInfo) {
            $class=$classInfo->getName();
            if(strtolower($class)==$m)  { $pars=array();
                                        for ($i=0;$i<count($v);$i++) $pars[]="\$v[{$i}]";
                                        eval("return $class::__construct(".implode(',',$pars).");");
                                        }
            $classInfo=$classInfo->getParentClass();
        }
	}
	
	/**
	 * Viene invocata subito dopo {@link myEventManager::dispatchEvents()}
	 */
    abstract protected function onStart();
    
    /**
	 * Viene invocata dopo tutti gli eventi
	 */
	abstract protected function onClose();
	
	
}