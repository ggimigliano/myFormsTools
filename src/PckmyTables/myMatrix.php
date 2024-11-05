<?php
/**
 * Contains Gimi\myFormsTools\PckmyTables\myMatrix.
 */

namespace Gimi\myFormsTools\PckmyTables;

use Gimi\myFormsTools\PckmyFields\myField;
use Gimi\myFormsTools\PckmyUtils\myCharset;



/**
*  Definizione di matrice, da non usare direttamente
**/
	
Class myMatrix implements \ArrayAccess {
/** @ignore */
protected  $valori=array(), $intestazioni,$aData, $aSortkeys,$codifica='ISO-8859-1',$codifica_default='ISO-8859-1';



/**
 * @ignore
 */public function offsetSet(mixed $offset, mixed $value): void {}

/**
 * @ignore
 */public function offsetExists(mixed $offset):bool {}

/**
 * @ignore
 */public function offsetUnset(mixed $offset):void {}
    
 /**
  * @ignore
  */public function offsetGet(mixed $offset): mixed {
        if($this->codifica_default==$this->codifica) return $this->valori[$offset];
                else {  $v=array();
                        for ($i=0;$i<$this->get_n_cols();$i++) $v[]=$this->get_cell($offset,$i);
                        return $v;
                     } 
    }

/**
 * @ignore
 */
     public function isOverridden($metodo,$rispetto_alla_classe='') {

    	try {
    	if(!$rispetto_alla_classe) $rispetto_alla_classe=__CLASS__;
        $thisClass = new \ReflectionClass(get_class($this));

    	if(!$thisClass->getMethod($metodo)) return false;

		//do {
		$declaringClass=$thisClass->getMethod($metodo)->getDeclaringClass();
		/*
    	if($declaringClass->name==strtolower(get_class($this)))
    			{
    			 return false;
    			}
		*/
		$foundParent=false;
		if($declaringClass->getParentClass())
    		foreach ($declaringClass->getParentClass() as $classParentName) {
							  $classParent=new \ReflectionClass($classParentName);
    						  if($classParent->getMethod($metodo)) {$foundParent=true;
    						  										break;
    						  										}
							}
    	if(!$foundParent) return false;
    	if(strtolower($rispetto_alla_classe)==strtolower($classParentName) ||
    	   $classParent->isSubclassOf($rispetto_alla_classe)) return $declaringClass->name;
    	return false;
    	} catch (\Exception $e) {return false;}
    }


 /** @ignore */
  public static function AbsPath(){
 	myField::Abspath();
  }

/**
	* Imposta i percorsi da far usare al package, ove non riesca a reperirli in automatico
 *
    * 
     * @param    string $PathSito E' la path assoluta del sito es "c:\wwwwpublic\htdocs"
     * @param    string $MyFormsPath la path assoluta delle myforms rispetto alla root del sito esl "/librerie/myform"
     **/
   public static function setAbsPath($PathSito,$MyFormsPath) {
  	myField::setAbsPath($PathSito,$MyFormsPath);
  }


   public static function get_PathSito() {
  	return myField::get_PathSito();
  }

   public static  function get_MyFormsPath() {
   	return myField::get_MyFormsPath();
  }

    
    /**
	 * @ignore
	 */
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
    * @param    array $tabella  Opzionale e' un array di righe, le righe vengono reindicizzate da zero a prescindere dalle chiavi
    * @param    boolean $fetch_intestazioni Se true usa le chiavi della prima riga come intestazione
    **/
	 public function __construct(&$tabella,$fetch_intestazioni=true) {
		if (!is_array($tabella)) $this->valori=array();
		 	else foreach ($tabella as &$riga) {
					if ($fetch_intestazioni && !$this->intestazioni && $riga && is_array($riga)) $this->intestazioni=array_keys($riga);
					$this->valori[]=array_values($riga);
					if($fetch_intestazioni && $this->intestazioni && count($riga)>count($this->intestazioni)) 
									{   $this->intestazioni=array();
										$fetch_intestazioni=false;
									}
			    	}

	}




     /** @ignore */
	protected static function ins_array(&$array,$dove,$cosa) {
		if ($cosa===null) return;
		if (!isset($array[$dove]))
							{$array[$dove]=$cosa;
							 ksort($array);
							}
			else {
				 $i=count($array);
   	 		     while ($i>$dove) {$array[$i]=$array[$i-1];
   	 		     				   --$i;
   	 		     				  }
   	 			 $array[$dove]=$cosa;
   	 			}
	}



 	/** @ignore */
    public function riformatta($val) {
       $regs=array();
   	 if (!preg_match('#[/.-]#',$val)) return $val;
   	 if (preg_match('#^(([0-9]{4})[/.-]([0-9]{1,2})[/.-]([0-9]{1,2}))$#', $val)) return $val;
   	 if (preg_match('#^(([0-9]{1,2})[/.-]([0-9]{1,2})[/.-]([0-9]{4}))$#', $val,$regs)) return "$regs[3].$regs[2].$regs[1]";
   	 if (preg_match('#^(([0-9]{4})[/.-]([0-9]{1,2})[/.-]([0-9]{1,2}))#', $val))
   	 		{$x=explode(' ',$val);
   	 		 if (count($x)==2 && preg_match('#([0-9]{4})[/.-]([0-9]{1,2})[/.-]([0-9]{1,2})#', $x[0])) return $val;
   	 		}
     return $val;
   }



	 /** @ignore */
    public function _sortcmp($a, $b) {
   	   foreach ($this->aSortkeys['dati'] as $col=>$verso)
   	   	  {
   	   	   if ($this->aSortkeys['case']=='s') $r = strnatcmp($this->riformatta($a[$col]),  $this->riformatta($b[$col]));
       	   								 else $r = strnatcmp(strtolower($this->riformatta($a[$col])), strtolower($this->riformatta($b[$col])));
       	   if ($verso == "-") $r = -$r;
       	   if ($r!=0) break;
   	   	  }
       return $r;
   }


    public function set_charset($codifica){
       $this->codifica=strtoupper($codifica);
       return $this;
   }



    /**
     * Riordina la tabella Case sensitive
     * @param  array $ordine  E' un array associativo che ha come chiave il numero di colonna da ordinare e come valore i simboli +- a seconda se l'ordinamento e' crescente o decrescente
    **/
    public function Sort($ordine) {
     if(count($ordine)) {
		//ksort($ordine);
		$this->aSortkeys['dati']=$ordine;
		$this->aSortkeys['case']='s';
		usort($this->valori,array($this,"_sortcmp"));
       }
       return $this;
   }


     /**
     * Riordina la tabella Case Unsensitive
     * @param  array $ordine  E' un array associativo che ha come chiave il numero di colonna da ordinare e come valore i simboli +- a seconda se l'ordinamento e' crescente o decrescente
    **/
    public function USort($ordine) {
     if(count($ordine)) {
		//ksort($ordine);
		$this->aSortkeys['dati']=$ordine;
		$this->aSortkeys['case']='u';
		usort($this->valori,array($this,"_sortcmp"));
       }
      return $this;
   }


   /**
     * Inserisce la 'riga' nella posizione pos
     * @param    array $riga
     * @param    int $pos Opzionale, se non c'e' si accoda
     **/
    public function ins_row ($riga,$pos='') {
     if(!is_array($riga)) $riga=array();
   	            else $riga=array_values($riga);
   	 //if (!$this->intestazioni) $this->intestazioni=array_keys($riga);
   	 if ($pos==='') $pos=$this->get_N_rows();
   	 self::ins_array($this->valori,$pos,$riga);
   	 return $this;
   	}



  	/**
     * Inserisce la 'colonna' nella posizione pos
     * @param    array $colonna
     * @param    int $pos Opzionale, se non c'e' si accoda
     **/
    public function ins_col ($colonna,$pos='') {
   	 $i=0;
   	 if ($pos==='') $pos=$this->get_N_cols();
   	 for ($i=0;$i<$this->get_N_rows();$i++) self::ins_array($this->valori[$i],$pos,$colonna[$i]);
   	 return $this;
    }



	/**
 	*   Restituisce una colonna come array singolo a chiave numerica
 	*
 	* @param int $ncolonna
 	* @return array
 	**/
    public function get_col ($ncolonna) {
     if(is_callable('array_column')) return array_column($this->valori,$ncolonna);  
   	 for ($i=0;$i<$this->get_n_rows();$i++) $out[$i]=$this->valori[$i][$ncolonna];
   	 return $out;
    }


    /**
 	*   Restituisce una riga come array singolo a chiave numerica
 	*
 	* @param int $nriga
 	* @return array
 	**/
    public function get_row ($nriga) {
   	 return $this->valori[$nriga];
    }


   /**
     * Restituisce il numero di colonne
      * @return   int
     **/
    public function get_N_cols () {
   	  if (is_array($this->valori)) $max=array_reduce($this->valori,function($max, $riga){ return max($max,max(array_keys($riga))); });
   	  return $max+1;
    }

  	/**
     * Cancella la colonna pos
     * @param    int $pos
     **/
    public function del_col ($pos) {
   	 $i=0;
   	 unset($this->intestazioni[$pos]);
   	 if (is_array($this->intestazioni)) $this->intestazioni=array_values($this->intestazioni);
   	 for ($i=0;$i<$this->get_N_rows();$i++) {unset($this->valori[$i][$pos]);
   	 										 if (is_array($this->valori[$i])) $this->valori[$i]=array_values($this->valori[$i]);
   	 										 }
	return $this;
    }


  	/**
     * Cancella la riga pos
     * @param    int $pos
     **/
    public function del_row ($pos) {
   	  unset($this->valori[$pos]);
   	  return $this;
    }



/**
 * Restituisce la matrice o ina sottomatrice sottoforma di array[][]
 *
 * @param int $colStart   colonna di partenza per il prelievo della matrice (se omessa parte da 0)
 * @param int $rowStart	  riga di partenza per il prelievo della matrice (se omessa parte da 0)
 * @param int $colStop	  colonna di arrivo per il prelievo della matrice (se omessa prende tutte le colonne)
 * @param int $rowStop	  riga di arrivo per il prelievo della matrice (se imessa prende tutte le righe)
 * @param boolean $trasposta se true traspone la matrice prelevata, altrimenti la lascia com'e'
 * @return array
 **/
   function &get_matrix ($colStart=0,$rowStart=0,$colStop='',$rowStop='',$trasposta=false) {

   	 if (!$trasposta && $colStart==0 && $rowStop=='' && $colStop=='') return $this->valori;

   	 if ($rowStop==='') $rowStop=$this->get_N_rows();
   	 if ($colStop==='') $colStop=$this->get_N_cols();
   	 if ($rowStart>$rowStop || $colStart>$colStop) return array();
   	 $nuova=array();
   	 $k=0;
   	 for ($i=$colStart;$i<min($colStop+1,$this->get_N_cols());$i++)
   	 	{
   	 	$v=0;
   	 	for ($j=$rowStart;$j<min($rowStop+1,$this->get_N_rows());$j++)
   	 			{
   	  			 if ($trasposta) $nuova[$k][$v]=$this->valori[$j][$i];
   	  					   else  $nuova[$v][$k]=$this->valori[$j][$i];
   	  			 $v++;
   	 			}
   	 	$k++;
   	 	}
    return $nuova;
     }





    /**
     * Setta i valori interni
     * @param    array $valori e' un array [][] con chiavi numeriche da 0
     **/
    public function set_matrix (&$valori,$giaNumerica=false,$intestazioni=array()) {
   	 $this->valori=array();
   	 $this->intestazioni=$intestazioni;
   	 if (!$giaNumerica) self::__construct($valori);
   	 			   else $this->valori=&$valori;
	 return $this;
    }



   /**
     * Restituisce il numero di righe
     * @return   int
     **/
    public function get_N_rows () {
   	  return count($this->valori);
    }


   /**
     * Restituisce il contenuto di una cella
     * @param    int $riga
     * @param    int $colonna
     * @return   mixed
     **/
   function &get_cell ($riga,$colonna) {
       if($this->codifica_default!=$this->codifica) { 
                     if($this->codifica=='UTF-8') return myCharset::utf8_encode( $this->valori[$riga][$colonna]);
                                           else  return iconv("ISO-8859-1", "{$this->codifica}//TRANSLIT", $this->valori[$riga][$colonna]);
                    }
                else return $this->valori[$riga][$colonna];
    }





}