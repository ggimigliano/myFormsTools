<?php
/**
 * Contains Gimi\myFormsTools\PckmyArrayObject\myArrayObject.
 */

namespace Gimi\myFormsTools\PckmyArrayObject;





/**
 * Mia estensione della classe SPL arrayObject, si tratta di una classe 
 * che presenta alternativamente le modalità di accesso di un array
 * <code>
 *$x=new myArrayObject();
 *$x->a=1;
 *$x->b=2;
 *$x['c']=3;
 *$x['valori']='5';
 *foreach ($x as $i=>$j) echo $i.'=>'.$j.'<br>';
 *echo $x->valori;
 *</code>
 * @link http://it2.php.net/manual/en/class.arrayobject.php Vedi manuale online della classe madre
 */
	
class myArrayObject extends \ArrayObject   {

	/** @ignore */
	 public function __toString() {return var_export($this->getArrayCopy(),true);}
	/** @ignore */
	 public function __set($par,$val) { $this[$par]=$val;}
	/** @ignore */
	 public function __get($par)	  {return (isset($this[$par])?$this[$par]:null);}
	/** @ignore */
	 public function __isset($par) 	  {return isset($this[$par]);}
	/** @ignore */
	 public function __unset($par) 	  {if(isset($this[$par])) unset($this[$par]);}
	/** @ignore */
	 public static function __set_state($values)    { return new myArrayObject($values);}
	
}