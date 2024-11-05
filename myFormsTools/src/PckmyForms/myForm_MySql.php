<?php
/**
 * Contains Gimi\myFormsTools\PckmyForms\myForm_MySql.
 */

namespace Gimi\myFormsTools\PckmyForms;


use Gimi\myFormsTools\PckmyFields\myDate;



/**
 *  Versione di myForm_DB ottimizzata per mysql
  **/
	
class myForm_MySql extends myForm_DB  {
/** @ignore */
protected  $SalvaInfo=43200;

	/**
     * Costruttore di classe
  	 * 
     *
	 * @param    mixed $con Connessione al db da usare
     * @param    array $colonne elenco delle colonne della tabella da utilizzare, se omesso si usano tutte
     * @param    string $tabella Nome della tabella da utilizzare
     * @param    array $condizioni Array associativo Colonna=>Valore per la condizione di recupero valori dalla tabella
     */
     public function __construct(&$con,$colonne=array(),$table='',$condizioni=array())
	   {//$con->debug=1;
	   	parent::__construct($con,$colonne,str_replace('`','',$table),$condizioni);
	  	$this->SalvaInfo=3600*24;
	   }
	   
	   
	   protected function tableNameForQry(){
	       return $this->schema?"{$this->schema}`.`{$this->get_tabella()}":$this->get_tabella();
	   }

	 /**
	  * 
	  * {@inheritDoc}
	  * @see myForm_DB::GeneraUpdate()
	  */
	   protected function GeneraUpdate(&$valori){
	       $qry=parent::GeneraUpdate($valori);
	       if($qry!='' && count($this->get_chiavi())==1) $qry.=" limit 1";
	       return $qry;
	   }
	   
	   /**
	    *
	    * {@inheritDoc}
	    * @see myForm_DB::GeneraDelete()
	    */
	   protected function GeneraDelete(){
	       $qry=parent::GeneraDelete();
	       if($qry!='' && count($this->get_chiavi())==1) $qry.=" limit 1";
	       return $qry;
	   }
	   
	   
	   function  MetaColumns(){
	       $savem = $this->con->SetFetchMode(ADODB_FETCH_ASSOC);
	       $meta= $this->con->getall("SELECT *
                                          FROM INFORMATION_SCHEMA.COLUMNS
                                          WHERE table_name = ?
                                          AND table_schema = ?
                                          ORDER BY ORDINAL_POSITION",array($this->get_tabella(),$this->schema));
           if (isset($savem)) $this->SetFetchMode($savem);
           $retarr = array();
	       foreach ($meta as $rs) {
	                   $fld = new \ADOFieldObject();
	                   $fld->name = $rs['COLUMN_NAME'];
	                   $fld->type = $rs['COLUMN_TYPE'];
	                   $fld->scale = $rs['NUMERIC_SCALE'];
	                   $fld->max_length=-1;
	                   if (TRIM((string) $fld->scale)!=='')  $fld->max_length = $rs['NUMERIC_PRECISION'];
	                       elseif ($fld->type=='enum' || $fld->type=='set') 
        	                       {
        	                       $opz=strstr($rs['COLUMN_TYPE'],'(',false);
        	                       eval("\$fld->enums=array$opz;");
        	                       } 
	                     
	                   IF(TRIM((string) $rs['CHARACTER_MAXIMUM_LENGTH'])!=='') $fld->max_length=$rs['CHARACTER_MAXIMUM_LENGTH'];
	                   $fld->not_null = $rs['IS_NULLABLE'] == 'NO';
	                   $fld->primary_key =  $rs['COLUMN_KEY'] == 'PRI';
	                   $fld->auto_increment = (strpos($rs['EXTRA'], 'auto_increment') !== false);
	                   $fld->binary = (strpos($rs['COLUMN_TYPE'],'blob') !== false);
	                   $fld->unsigned = (strpos($rs['COLUMN_TYPE'],'unsigned') !== false);
	                   $fld->zerofill = (strpos($rs['COLUMN_TYPE'],'zerofill') !== false);
	                   
	                   if (!isset($rs['COMMON_DEFAULT']) || trim( $rs['COMMON_DEFAULT'])==='')  $fld->has_default = false;
                                	                       else {
                                	                           $fld->has_default = true;
                                	                           $fld->default_value =  $rs['COMMON_DEFAULT'];
                                	                           }
	                   
	                  $retarr[strtoupper($fld->name)] = $fld;
	               }
	               
	              return $retarr;
	            }
	   
	   
	   /**
	    * @ignore
	    */
	   protected function &rebuild_where($where,$pars){
	        $m=array();
	        $out=array('pars'=>array(),
	                   'where'=>$where);
	        foreach ($pars as $k=>&$val)
	                       {
	                        preg_match("@{$k}(.+){$this->con->quote($val)}\s@UiSs",$out['where']." ",$m);
	                        if($m) {
	                                $out['where']=str_replace(trim((string) $m[0]), "{$k}{$m[1]}? ", $out['where']);
	                                $out['pars'][]=&$val;
	                                continue;
        	                       }
        	              
        	                preg_match("@{$k}(.+)$val\s@UiSs",$out['where']." ",$m);
        	                if($m) {
        	                       $out['where']=str_replace(trim((string) $m[0]), "{$k}{$m[1]}? ", $out['where']);
        	                       $out['pars'][]=&$val;
        	                       
        	                       continue;
        	                       }
	                       }
	                     
	        return $out;
	   }
	   
	   /**
	    * @ignore
	    */
	   protected function quota_colonne($array,$tutte=false) {
	   	$where=array();
	   	if ($array)
	   	foreach ($array as $col=>&$val) {
	   		$val=trim((string) $val);
	   		$col=strtoupper($col);
	   		if ($tutte && $val==='') return null;
	   
	   		if (($val=trim((string) $val))!=='' && ($this->metaDatiTabella[$col]->primary_key || ($this->chiavi && array_search($col,$this->chiavi)!==false)))
	   		{$type=$this->get_meta_type($this->metaDatiTabella[$col]);
	   		if ($type=='D') {
	   			$val=explode(' ',$val,2);
	   			$d=new myDate('',$val[0]);
	   			$val[0]=$d->get_formatted();
	   			$where[]="$col=".$this->con->quote(implode(' ',$val));
	   		}
	   		elseif ($type=='T') {
	   			$val=explode(' ',$val,2);
	   			if(count($val)==1) $where[]="$col=".$this->con->quote($val[0]);
	   			else {$m=array();
	   				if(preg_match('@^([0-9]+)[^0-9]+([0-9]+)[^0-9]+([0-9]+)$@', trim((string) $val[0]),$m))
	   				{
	   					if(strlen($m[1])==4) $val[0]="{$m[1]}-{$m[2]}-{$m[3]}";
	   					else   $val[0]="{$m[3]}-{$m[2]}-{$m[1]}";
	   				}
	   				$where[]="$col=".$this->con->quote(implode(' ',$val));
	   			}
	   			}
	   			elseif (in_array($type,array('N','I','R')) && @preg_match('|^[0-9]*[\.]{0,1}[0-9]*$|',$val)) $where[]="$col=$val";
	   					else $where[]="$col=".$this->con->quote($val);
	   		}
	   		}
	   		return $where;
	   		}
	   

/** @ignore */
     protected function get_meta_type($fld){
     	if (strtoupper($fld->type)=='YEAR') return 'I';
     	return parent::get_meta_type($fld);
	 }


	 public function MetaForeignKeys()
	 {  $prec=$this->con->SetFetchMode(ADODB_FETCH_NUM);
		 	$rs=$this->con->execute("SHOW CREATE TABLE {$this->tabella}");
	 	$this->con->SetFetchMode($prec);

	 	$create=explode('FOREIGN KEY',$rs->fields[1]);
	 	unset($create[0]);
		$f_k=array();
 
		if (is_array($create))
			foreach ($create as $str)
				{
				 $campi=preg_split('~\\)|\\(~s',str_replace('`',"'",str_replace('REFERENCES ','',$str)));

				 if (count($campi)>=5) {$tab='';$k_ext=$k_locali=array();
				 						eval("\$k_locali=array($campi[1]);");
				 						eval("\$tab=$campi[2];");
				 						eval("\$k_ext=array($campi[3]);");
				 						foreach ($k_locali as $i=>$val) $f_k[$tab][]="$val=$k_ext[$i]";
				 						}
				}
		return $f_k;
  	 }





 /** @ignore */
	 public function SpecificheTecnologiche() {
	   IF ($this->ParametriAutomatici)
    	 foreach ($this->ParametriAutomatici as $id=>$val)
	        {
	          if (strpos($val['TIPO'],'enum')!==false) {
	        	  //print_r($val);
	        	  if (count(explode("','",$val['TIPO']))>4)  $this->ParametriAutomatici[$id]['MYTYPE']='MySelect';
	        	                                       else  $this->ParametriAutomatici[$id]['MYTYPE']='MyRadio';
	          }
	          if (strpos($val['TIPO'],'set')!==false) {
	        	  						 	$this->ParametriAutomatici[$id]['MYTYPE']='MyMultiCheck';
	        	                   	 	    }
	          if (preg_match('/blob$|text$/i',$val['TIPO']))
	          									{$this->ParametriAutomatici[$id]['MYTYPE']='MyTextArea';
	          									 if (stripos($val['TIPO'],'tiny')!==false) $this->ParametriAutomatici[$id]['MAXSIZE']=255;
	          									   	elseif (stripos($val['TIPO'],'long')!==false) $this->ParametriAutomatici[$id]['MAXSIZE']=4294967295;
	          									   	 	elseif (stripos($val['TIPO'],'medium')!==false) $this->ParametriAutomatici[$id]['MAXSIZE']=16777215;
	          									  	   		else $this->ParametriAutomatici[$id]['MAXSIZE']=65535;
	         									 }
	  	      if ($val['TIPO']=='time') {$this->ParametriAutomatici[$id]['MYTYPE']='MyTime';
	  	      							 unset($this->ParametriAutomatici[$id]['MAXSIZE']);
	        	 						 unset($this->ParametriAutomatici[$id]['DECIMALI']);
	          							 }
	  	      if ($val['TIPO']=='year')   {$this->ParametriAutomatici[$id]['MYTYPE']='MyIntPos';
	  	      							   $this->ParametriAutomatici[$id]['ADODB']='I';
	  	        						   unset($this->ParametriAutomatici[$id]['MAXSIZE']);
	        	 						   unset($this->ParametriAutomatici[$id]['DECIMALI']);
	  	      							  }
			  if (preg_match('/multi|radio|select|check/i',$this->ParametriAutomatici[$id]['MYTYPE']))
			   		{ unset($this->ParametriAutomatici[$id]['MAXSIZE']);
	        	 	  unset($this->ParametriAutomatici[$id]['DECIMALI']);
			  		 }
	        }

	}



	 public function IstanziaCampi($carica_FK=false) {
	  
	  parent::IstanziaCampi($carica_FK);
	 
	  $colonne=array_flip($this->colonne);
	  if($this->ParametriAutomatici)
	  foreach ($this->ParametriAutomatici as $id=>&$val) {
	  	if(isset($this->campi[$id]) && is_object($this->campi[$id])) $val['MYTYPE']=$this->campi[$id]->get_MyType();
	 	if (!$colonne || isset($colonne[$id]))
	 		{if(!isset($val['MYTYPE'])) $val['MYTYPE']='';
	 		 if(!isset($val['TIPO'])) $val['TIPO']='';
	 		                      else {$TIPO=array();
	 		                            preg_match('@^[a-z]+@', $val['TIPO'], $TIPO);
	 		                            $TIPO=$TIPO[0];
	 		                           }
	 	     if ($val['MYTYPE']=='MyIntPos'){
                                	         if ($TIPO=='tinyint') $this->campi[$id]->set_max(255);
                                	  	       	elseif ($TIPO=='smallint') $this->campi[$id]->set_max(65535);
                                	  	        		elseif ($TIPO=='mediumint') $this->campi[$id]->set_max(16777215);
                                	  	          		  elseif ($TIPO=='int')  $this->campi[$id]->set_max(4294967295);
                                			  if ($val['MAXSIZE']) 	$this->campi[$id]->set_maxlength($val['MAXSIZE']);
                               	    	  }


	    	  if ($val['MYTYPE']=='MyInt'){
	    	     if ($TIPO=='tinyint') {$this->campi[$id]->set_max(127);
	    	      								$this->campi[$id]->set_min(-128);
	    	      								}
	  	          	elseif ($TIPO=='smallint') {$this->campi[$id]->set_max(32767);
	  	          									   $this->campi[$id]->set_min(-32768);
	  	          									  }
						elseif ($TIPO=='mediumint') {$this->campi[$id]->set_max(8388607);
	  	          									  		$this->campi[$id]->set_min(-8388608);
	  	          									  		}
	  	          			elseif ($TIPO=='int') {
	  	          									  		 $this->campi[$id]->set_max(2147483647);
	  	          											 $this->campi[$id]->set_min(-2147483648);
	  	          									  		}

	  	  	  if ($val['MAXSIZE']) 	$this->campi[$id]->set_maxlength($val['MAXSIZE']);
	  	      }


		      if ($val['MYTYPE']=='MyOrario' || $val['MYTYPE']=='MyOra')
		      								  { $v=explode(':',$this->campi[$id]->get_value());
		      								  	unset($v[2]);
		      								    $this->campi[$id]->set_value(implode(':',$v));
	  	      								  }

	  	      if (isset($this->campi[$id]) && $this->campi[$id] && ($val['MYTYPE']=='MySelect' || $val['MYTYPE']=='MyRadio'  || $val['MYTYPE']=='MyMultiCheck') && (isset($val['TIPO']) && preg_match('/enum|set/',$val['TIPO'])))
	  	                                    {  $opzioni=array();
	  	                                       if(!$this->campi[$id]->get_notnull() && $val['MYTYPE']=='MyRadio') $opzioni=array();
	  	                                       $v=preg_split("~\\('|','|'\\)~",str_replace("''","'",$val['TIPO']));
	  	                                       if ($v){
        	  	                                    	 unset($v[count($v)-1]);
        	  	         								 unset($v[0]);
        	  	                                    	 $opzioni=array_merge($opzioni,array_values($v));
        	  	                                    	}
	  	                                    	$opz=array();
	  	                                    	foreach ($opzioni as $o) $opz[$this->trasl($o)]=$o;
	  	                                    	$this->campi[$id]->set_Opzioni($opz);
	  	      								    if(!$this->campi[$id]->get_notnull() && !isset($opz['']) && $val['MYTYPE']=='MySelect') $this->campi[$id]->set_domanda(' ');
	  	      								    if(strlen(serialize(array_keys($opz)))<=80 && ($val['MYTYPE']=='MyRadio'  || $val['MYTYPE']=='MyMultiCheck')) $this->campi[$id]->SET_accapo(0,1);	
	  	                                    }
	  	                                    
	  	      if (isset($val['TIPO']) && $val['MYTYPE']=='MyTextArea' && preg_match('/medium|long/',$val['TIPO'])) $this->campi[$id]->set_rows('30');
	  	     
	  	      if (isset($val['TIPO']) && $val['TIPO']=='year')  
	  	                                  {$this->campi[$id]->set_max(2100,4);
	  	      							   $this->campi[$id]->set_min(1890,4);
	  	      							  }
		      }
	 		 }

	 		 return $this;
		}

	/**
	 * @ignore
	 */
	protected function get_meta_comments(){
	    $tab=explode('.',$this->tabella);
	    if(count($tab)==2) {$db=$this->con->quote($tab[0]);
	                        $tabella=$tab[1];
	                       }
	                 else {$db='database()';
	                       $tabella=$this->tabella;
	                       }
	   return  $this->con->getassoc("select COLUMN_NAME,COLUMN_COMMENT from information_schema.COLUMNS where TABLE_SCHEMA=$db and TABLE_NAME=?",array($tabella),false,false);
	}


}