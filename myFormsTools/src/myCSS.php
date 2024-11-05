<?php
/**
 * Contains Gimi\myFormsTools\myCSS.
 */

namespace Gimi\myFormsTools;


use Gimi\myFormsTools\PckmyJQuery\myJQuery;



/**
 * @author Gianluca GIMIGLIANO
 */
	
class myCSS {

var $misurabili=array
			  ( 'font-family',
				'font-size',
				'line-height',
				'background-position',
				'letter-spacing',
				'text-indent',
				'vertical-align',
				'word-spacing',
				'height',
				'width',
				'margin',
				'margin-top',
				'margin-right',
				'margin-bottom',
				'margin-left',
				'padding-top',
				'padding-right',
				'padding-bottom',
				'padding-left',
			 	'border',
				'padding',
				'border-bottom',
				'border-top',
				'border-right',
				'border-left',
				'border-top-width',
				'border-right-width',
				'border-bottom-width',
				'border-left-width',
				'left',
				'top',
				'right',
				'bottom');
var $numerici=array('font-weight');
var $alfabetici=array('font-style',
				'font-variant',
				'text-transform',
				'text-decoration',
				'background-attachment',
				'background-image',
				'background-repeat',
				'text-align',
				'white-space',
				'display',
				'clear',
				'float',
				'border-top-style',
				'border-right-style',
				'border-bottom-style',
				'border-left-style',
				'list-style-position',
				'list-style-image',
				'list-style-type',
				'overflow',
				'position',
				'visibility',
				'z-index',
				'cursor',
				'filter',
				'mso-protection',
				'page-break-before',
				'page-break-after',
				'overflow-x',
				'overflow-y');

var $colori=array('color',
				'background',
				'background-color',
				'border-color',
				'border-left-style',
				'border-top-color',
				'border-right-color',
				'border-bottom-color',
				'border-left-color');
var $tutti;
var $classi=array();
var $contaClassi;
var $md5;
var $prefix='Auto';

	 public function __construct() {
	 	$this->tutti=array_flip(array_merge((array)$this->misurabili,(array)$this->alfabetici,(array)$this->colori,(array)$this->numerici));
	}


	 public function parseStyle($val,$verifica=true) {
	 $val=self::minimizza_css($val);
	 $val=explode(';',strtolower($val));
   	 foreach ($val as $v) {
   	  $v=trim((string) $v);
   	  if($v) {
   		      $v=explode(':',$v,2);
   		      if (count($v)==2 && (!$verifica || isset($this->tutti[trim((string) $v[0])]))) $new[trim((string) $v[0])]=trim((string) $v[1]);
   		     }
   	  }
   	  return $new;
	}


	 public function get_classi() {
	  return $this->classi;
	}


	 public function set_classi($classi) {
	    $this->classi=$classi;
	}

	 public function UnparseStyle($classe,$soloStyle=false) {
	 $x='';   
	 if ($this->classi[$classe])
		{
   		foreach ($this->classi[$classe] as $i=>$v)  {
   						if (isset($this->tutti[strtolower(trim((string) $i))]))
   												if (!$soloStyle) $x.="\t\t$i: $v;\r\n";
   															else $x.="$i:$v;";
   					}
   		if (!$soloStyle) $x="$classe{\r\n$x}\r\n\r\n";
		}
    return $x;
	}


	 public function Crea_nome_classe() {
		$this->contaClassi++;
		return '.'.$this->prefix.'____css_'.$this->contaClassi;
	}


	 public function UnsetClasse($nome) {
	        unset($this->classi[$nome] );
	}


     public function add_classe($valori,$nome='') {
    	if (!$nome) {
    	             $nome=$this->md5[md5(serialize($valori))];
    				 if ($nome) return substr($nome,1);
    				 $nome=$this->Crea_nome_classe();
    				}
    	if(!isset( $this->classi[$nome]))  $this->classi[$nome]=array();			
    	$this->classi[$nome]=array_merge( $this->classi[$nome],(array)$valori);
    	//echo "<pre>$nome<br>";	print_r($this->classi[$nome]);echo "<hr>";
    	if($nome[0]=='.') {
    					   $this->md5[md5(serialize($this->classi[$nome]))]=$nome;
    		    		   return substr($nome,1);
    					  }
    	return $nome;
    }



     public function get_classe($nome) {
    	if (!$this->classi[$nome]) return array();
    	return 	$this->classi[$nome];
    }


    static function &minimizza_css($testo){
        $sostituiti=false;
        $testo=str_replace(array("\n","\r","\t"), ' ', $testo);
        do {$testo=str_replace("  "," ",$testo,$sostituiti);} while ($sostituiti);
        $testo=trim((string) $testo);
        return $testo;
    }

     public function UnparseClassi() {
        if (count($this->classi)>0) foreach (array_keys($this->classi) as $classe)  $x.=$this->UnparseStyle($classe);
    	return $x;
    }

     public function salva_con_nome($nomeFile) {
    	if (count($this->classi)>0) {
    	$f=fopen($nomeFile,'w');
    	if (count($this->classi)>0) foreach (array_keys($this->classi) as $classe) fwrite($f,$this->UnparseStyle($classe));
    	fclose($f);
    	}
    }


     public function CaricaCSS($val) {
    	$f=explode('}',strtr($val,array("\r"=>'',"\t"=>'',"\n"=>'')));
    	foreach ($f as $resto) {
    		$resto=explode('{',$resto);
    		if (count($resto)==2) {
    				$nome=trim((string) $resto[0]);
    				//if ($nome[0]=='.') $nome=substr($nome,1);
    				$resto[1]=self::minimizza_css($resto[1]);
    				if (count($classe=$this->parseStyle(trim((string) $resto[1])))>0) $this->add_classe($classe,$nome);
    				}

    	}
	}

     public function caricaFileCSS($nomeFile) {
    	$this->CaricaCSS(preg_replace('#/\*.+\*/#sUS','',file_get_contents($nomeFile)));
    }



     public function NormalizzaMisure($classi=array()) {
    	$font2pt=array(1=>'7pt',2=>'9pt',3=>'10pt',4=>'12pt',5=>'16pt',6=>'20pt');
    	$misure=array('cm'=>0.4318,'mm'=>0.04318,'in'=>0.17,'pt'=>12,'pc'=>2,'px'=>16,'ex'=>2);
    	$misurabili=array_flip($this->misurabili);
    	if (!$classi) $classi=array_keys($this->classi);
    	 foreach ($classi as $classe)
    	   if ($this->classi[$classe])
    	    foreach ($this->classi[$classe] as $attr=>$val)
    	    		if (isset($misurabili[$attr]))
    	    			{$val=explode(' ',$val);
    	    			 foreach ($val as $i=>$mis) {
    	    			 	//echo $mis."<br>";
    	    			 	if(trim((string) $attr)=='font-size' && preg_match('/^[0-9]{1,3}$/',$mis)) {
    	    			 															$mis=$font2pt[min((int) $mis ,6)];
    	    			 														  }
							//echo "$attr=>$mis<br>";
    	    			 	if(preg_match('/'.implode('$|',array_keys($misure)).'$/',$mis))
    	    			 						{
    	    			 						$n=(float) (str_replace(',','.',$mis));
    	    			 						$unita=str_replace($n,'',$mis);
    	    			 						//echo implode(' ',$val);
    	    			 						if (!$misure[$unita]) unset($val[$i]);
    	    			 										 else {
    	    			 										 	  $val[$i]=str_replace(',','.',1.5*($n/$misure[$unita])).'em';
    	    			 										 	  }

    	    			 						}
    	    			 	 if(preg_match('/%$/',$mis))
    	    			 						{
    	    			 						 $n=(float) (str_replace(',','.',$mis));
    	    			 						 $unita=str_replace($n,'',$mis);
    	    			 						 $val[$i]=min($n,100).'%';
    	    			 						}
    	    			 	 if(trim((string) $attr)=='border' && count($val)==1) $val[$i].='px';
    	    			 }
    	    			 $this->classi[$classe][$attr]=implode(' ',$val);
    	    			}

    }


  static function &get_css_jscode($CSS,$ready_to_use=false){
       if(!is_array($CSS)) $CSS=array($CSS);
       $r1=$r2=$jq=$out=$m='';
       foreach($CSS as $css) { 
        $css=self::minimizza_css($css);
  	    preg_match_all('@([^\{]+)\{([^\}]+)\}@Us',str_replace(array("\n","\r","\t")," ",$css),$m);
  	    if($m[1])
  	    		{   foreach ($m[1] as $i=>$sel)
              	    	{$sel=str_replace("'","\\'",stripslashes(trim((string) $sel)));
              	    	 $rule=addslashes(trim((string) $m[2][$i]));
              	    	 $r1.="css.insertRule('$sel { $rule }',0);\n";
            			 $r2.="css.addRule('$sel', '$rule', 0);\n";
            					
            			 $rules=array();
            	         foreach (explode(';',$rule) as $rule) 
            	                               {$rule=explode(':', $rule);
            	                                $rule[0]=trim((string) $rule[0]);
            	                                if(count($rule)==2) $rule[1]=trim((string) $rule[1]);
            	                                               else $rule[]='';
            	                                if(!$rule[0] || !$rule[1]) continue;
            	                                $rules[]="'{$rule[0]}':'{$rule[1]}'";       
            	                               }
            	         if($sel && $rules)  $jq.=myJQuery::JQvar()."('$sel').css({".implode(',',$rules)."});";
              	    	}
  	             }
        }
        if($r1  || $r2) {
  	    		   $out="for (var i=0;i<document.styleSheets.length-1;i++)
  	    		   		  {try{
  	    		   				var css=document.styleSheets[i];
	  								if ( css.insertRule) { $r1 }
	  												else { $r2 }
	  						   i=document.styleSheets.length;
	  						  }catch (err) {console.log(err)}
	  						try{
  	    		   			   $jq  
  	    		   			   }catch (errJQ) {console.log(errJQ)}
	  					  }
	  					";
  	    		  if(!$ready_to_use)  $out="function(){ $out }";
  	    		}
  	    return $out;
	  }

}