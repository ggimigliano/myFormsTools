<?
/**
 * Contains Gimi\myFormsTools\myGlobalizer.
 */

namespace Gimi\myFormsTools;
 
class myGlobalizer {
static $working=false;
protected static $dir,$ns;
 
 public function __construct($ns,$dir=null){
   
    if(!self::$working) {spl_autoload_register(__NAMESPACE__.'\\myGlobalizer::finder');
                         if (is_callable('__autoload')) spl_autoload_register('__autoload');
                         self::$ns=$ns;
                         if($dir) {self::$dir=str_replace('//','/',str_replace('\\','/',$dir.'/_myGlobalizer/'.self::$ns.'/'));
                                   if(!is_dir(self::$dir)) @mkdir(self::$dir,0777,true);
                                   if(!is_dir(self::$dir)) self::$dir=null;
                                  }
                        }
     self::$working=true;
    }
    
protected static function get_completa_class_name($path,$classe){
    if(strtolower($classe)!==strtolower(strstr(basename($path),'.',true))) return null;
    return array(str_replace('\\','/',$path),
                 '\\Gimi\\myFormsTools\\'.str_replace('/','\\', strstr(explode('/src/',str_replace('\\','/',$path),2)[1],'.',true)));
}

protected static function match_class_name($carry,$file){
    static $class;
    if(is_array($carry)) return $carry;
        elseif($carry) $class=$carry;
    return self::get_completa_class_name($file,$class);
}



 public static function finder($class){
     $path_percorsi='';
    if(self::$dir) { $filename=self::$dir.str_replace('\\','/',$class).'.php';
                     if(is_file($filename)) {include_once($filename);  return;}
                     $path_percorsi=self::$dir.'X'.md5(self::$dir).'.php';
                    }
    if(!$path_percorsi || !is_file($path_percorsi))
            { 
              foreach (glob(__DIR__."/*.php") as $file)  $percorsi[]=$file;
              foreach (glob(__DIR__."/Pck*") as $path )
                  foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST) as $name => $object)
                         if(strpos($name,'.php')!==false)  $percorsi[]=$name;
              $object;
              if($path_percorsi) @file_put_contents($path_percorsi, "<?php return ".var_export($percorsi,1).'; ?>',LOCK_EX);
            }
   if($path_percorsi && is_file($path_percorsi))  $percorsi=include($path_percorsi);
   $filename=$final=$abstract='';
    foreach ($percorsi as &$file)    
        if($found=self::get_completa_class_name($file,$class)) 
              {
                 class_exists($found[1],true);
                 $refl = new ReflectionClass($found[1]);
                 if($refl->isAbstract()) $abstract="abstract";
                 if($refl->isFinal())    $final="final";
                 if(!$filename)  eval ("$final $abstract class $class extends {$found[1]}{};");
                            else {
                                  $filename=str_replace('\\','/',$filename);
                                  $dir=dirname($filename);
                                  if(!is_dir($dir)) @mkdir($dir,0777,true);
                                  if(!is_file($filename)) @file_put_contents($filename, "<?php $final $abstract class $class extends {$found[1]}{};",LOCK_EX);
                                  include_once($filename);  
                                 }
                 break;
               }
    
    }
}



