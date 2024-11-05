<?

/**
 * @ignore
 */
 function &tokenizer($file) {
    $tokens=token_get_all(file_get_contents($file));
    foreach ($tokens as &$v) {
        if(!is_array($v)) $v=array(-1,$v,'?');
        $v[2]=token_name($v[0]);
    }
    return $tokens;
}



/**
 * @ignore
 */
 function tokenizer_to_classi(&$tokens){
    
    //  ini_set('memory_limit',-1);
    $Class=0;
    $bdy='';
    $out=array('classi'=>array(),'resto'=>array());
    if(!defined('T_TRAIT')) define('T_TRAIT',null);
    foreach ($tokens as $k=>&$v) {
        if($Class==0 && 
            is_array($v) && 
            in_array($v[0], array(T_ABSTRACT,T_CLASS,T_TRAIT,T_INTERFACE,T_FUNCTION,T_FINAL)))
        {$Class=1;
         $bdy='';
         $parentesi=null;
         $parts=array();
         
         for($i=$k-1;$i>0;$i--) 
             if($tokens[$i][0]==T_WHITESPACE ||
                 $tokens[$i][0]==T_DOC_COMMENT 
                 ){
                     $parts[]= $tokens[$i][1];
                     if($tokens[$i][0]==T_DOC_COMMENT) break;
                    }
         $parts=trim(preg_replace('@/\*[\*\s+]+\*/@iS','',preg_replace('/\*\s+@package\s+[a-zA-Z0-9]+\s+/iS','',implode('',array_reverse($parts)))));
         if($parts) $bdy=$parts."\n";
        }
        if($Class==0) $out['resto'][]=$v;
        else {
            $bdy.=$v[1];
            if($Class==1 && $v[0]==T_STRING) {$classe=$v[1]; $Class=2;}
            elseif($Class==2)
                  { if(trim((string) $v[1]=='{')) $parentesi+=1;
            elseif($v[0]==-1 && trim((string) $v[1]=='}')) $parentesi-=1;
                     if($parentesi===0)
                            {$out['classi'][($classe)]=$bdy;
                             $Class=0;
                             $bdy='';
                            }
                        }
                 }
      
    }
    return $out;
}





foreach (glob(__DIR__."/*.php") as $file)  
    if(stripos($file,'myglobalizer')===false){
            $files[strstr(basename($file),'.',true)]=realpath($file);
            }
    
foreach (glob(__DIR__."/Pck*") as $path ) 
               foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST) as $file => $object)
                   if(strpos($file,'.php')!==false ) $files[strstr(basename($file),'.',true)]=realpath($file);
                                    
$object;
 /*                  
foreach (glob('../../myForms/my*.php') as $file)
    if(stripos($file,'loader')===false &&
        stripos($file,'mymenudock')===false) 
                      {
                       $file=str_replace('.php','',basename($file));
                       $tokens=tokenizer("../../myForms/$file.php");
                       $classi=tokenizer_to_classi($tokens,$file);
                       if(!$classi['classi']) throw new Exception('ERRORE');
                       foreach ($classi['classi'] as $classe=>$txt)
                                 if(!$files[$classe]) throw new Exception("ERRORE $classe");
                                               else  {
                                                      $txt=str_replace(array('new DOMDocument','new stdclass'),
                                                                       array('new \DOMDocument','new \stdclass'),$txt);
                                                      file_put_contents($files[$classe], preg_split('/(?:\babstract\s+class|\bfinal\s+class|\bclass|\binterface)\s+/USi',file_get_contents($files[$classe]))[0].$txt)   ;
                                                    }
                                    
                   }
*/
$dir='myExtras';
foreach (glob("../../myForms/$dir/my*.php") as $file)
    if(stripos($file,'mymenudock')===false)
     {
        $file=str_replace('.php','',basename($file));
        
        $tokens=tokenizer("../../myForms/$dir/$file.php");
        $classi=tokenizer_to_classi($tokens,$file);
        if(!$classi['classi']) throw new Exception('ERRORE');
        foreach ($classi['classi'] as $classe=>$txt)
            if(is_file($files[$classe])) 
                    {
                        $commenti=array();
                   preg_match_all('@(\/\*\*.+\*\/)\s*(?:\babstract\s+class|\bfinal\s+class|\bclass|\binterface)\s+([\w°§]+\b)(?:[°§a-z0-9_ \n\r\t\,]*\{)@isS',
                                $txt,$commenti);
                   print_r(array_combine($commenti[2],$commenti[1]));
                   foreach (array_combine($commenti[2],$commenti[1]) as $classe=>$commento) 
                                       {
                                       $parti=preg_split('/(?:\babstract\s+class|\bfinal\s+class|\bclass|\binterface)\s+/sSi',file_get_contents($files[$classe]),2);
                                       if(count($parti)!=2) throw new Exception($files[$classe].'=>'.count($parti).' TROPPI '.print_r($parti,1));
                                       $new=str_replace($parti[0],$parti[0]."\n\n".trim((string) $commento)."\r\n\t\r\n",file_get_contents($files[$classe]));
                                       file_put_contents($files[$classe],$new)   ;
                                       }
                }
            
    }


                   