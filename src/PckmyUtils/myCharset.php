<?php
/**
 * Contains Gimi\myFormsTools\PckmyUtils\myFormAJAXRequest.
 */

namespace Gimi\myFormsTools\PckmyUtils;


abstract class myCharset
{private static $charset='ISO-8859-1'; 

    public static function set_charset_against_utf8($charset='ISO-8859-1'){
        static::$charset=$charset;
    }
    
    public static function &onlyReadableASCII($string,$interpunzione=true) {
        $out='';
        for ($i=0;$i<strlen($string);$i++)
        	if( ($interpunzione && in_array($string[$i], [9,10,13])) ||
            	(ord($string[$i])>=32 && ord($string[$i])<=126 )) $out.=$string[$i];
        return $out;
    }
    
    public static function  utf8_encode($string){
    	if($string===null || $string==='' || self::is_utf8($string)) return $string;
    	
        if(class_exists('UConverter',false))   return self::utf8_encode_intl($string);
        if(is_callable('mb_convert_encoding')) return self::utf8_encode_mb($string);
        if(is_callable('iconv'))               return self::utf8_encode_iconv($string);
        return self::utf8_encode_std($string);
    }
    
    public static function  utf8_decode($string){
    	if($string===null || $string==='' || !self::is_utf8($string)) return $string;
        if(class_exists('UConverter',false))   return self::utf8_decode_intl($string);
        if(is_callable('mb_convert_encoding')) return self::utf8_decode_mb($string);
        if(is_callable('iconv'))               return self::utf8_decode_iconv($string);
        return self::utf8_decode_std($string); 
    }

    public static function  &utf8_rencode( $item,$anche_chiavi=false){
       if($item===null) return $item;
    	elseif(is_string($item)) $item=static::utf8_encode($item);
           elseif(is_array($item)) foreach (array_keys($item) as $k)
                                         if(!$anche_chiavi)  $item[$k]=static::utf8_encode($item[$k],false);
                                                                else {$K=static::utf8_encode($k);
                                                                        $item[$K]=static::utf8_rencode($item[$k],true);
                                                                        unset($item[$k]);
                                                                        }
                                
                                elseif(is_object($item))  foreach ( (new \ReflectionObject($item))->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop)
                                                                 if(!$anche_chiavi)  $item->$k=static::utf8_encode($item->$k,false);
                                                                else{   $k= $prop->getName();
                                                                        $K=static::utf8_encode($k);
                                                                        $item->$K=static::utf8_rencode($item->$k,true);
                                                                        unset($item->$k);
                                                                        }
     return $item;
    }
    
    
    public static function  &utf8_rdecode( $item,$anche_chiavi=false){
     if($item===null) return $item;	
        elseif(is_string($item)) $item=static::utf8_decode($item);
        	elseif(is_array($item))  {if(!$anche_chiavi) foreach (array_keys($item) as $k)  $item[$k]=static::utf8_decode($item[$k],false);
        									       else {$new=array();
		        									     foreach (array_keys($item)  as $k){
		        									       		 		  $new[static::utf8_decode($k)]=static::utf8_decode($item[$k],false);
		                                                           		  unset($item[$k]);
		                                                           		  }
		                                                  $item=&$new;
        												}
        							 }
                    elseif(is_object($item))  foreach ( (new \ReflectionObject($item))->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop)
                                                            if(!$anche_chiavi)  $item->$k=static::utf8_decode($item->$k,false);
                                                                            else{   $k= $prop->getName();
                                                                                    $K=static::utf8_decode($k);
                                                                                    @$item->$K=static::utf8_rdecode($item->$k,true);
                                                                                    if(strcmp($k, $K)!=0) unset($item->$k);
                                                                                }
    return $item;
    }
    
    private static function  utf8_encode_mb($string){
        return mb_convert_encoding($string, 'UTF-8',self::$charset);
    }
    
    private static function utf8_decode_mb($string){
        return mb_convert_encoding($string, self::$charset,'UTF-8');
    }
    
    
    private static  function utf8_decode_std( $string)  {
        $s = (string) $string;
        $len = \strlen($s);
        
        for ($i = 0, $j = 0; $i < $len; ++$i, ++$j) {
            switch ($s[$i] & "\xF0") {
                case "\xC0":
                case "\xD0":
                    $c = (\ord($s[$i] & "\x1F") << 6) | \ord($s[++$i] & "\x3F");
                    $s[$j] = $c < 256 ? \chr($c) : '?';
                    break;
                    
                case "\xF0":
                    ++$i;
                    // no break
                    
                case "\xE0":
                    $s[$j] = '?';
                    $i += 2;
                    break;
                    
                default:
                    $s[$j] = $s[$i];
            }
        }
        
        return substr($s, 0, $j);
    }
    
   private static   function utf8_encode_std( $s) {
        $s .= $s;
        $len = strlen($s);
        
        for ($i = $len >> 1, $j = 0; $i < $len; ++$i, ++$j) {
            switch (true) {
                case $s[$i] < "\x80": $s[$j] = $s[$i]; break;
                case $s[$i] < "\xC0": $s[$j] = "\xC2"; $s[++$j] = $s[$i]; break;
                default: $s[$j] = "\xC3"; $s[++$j] = \chr(\ord($s[$i]) - 64); break;
            }
        }
        
        return substr($s, 0, $j);
    }
    
    
    private static  function utf8_decode_intl($string){
        return \UConverter::transcode($string, self::$charset, 'UTF8');
    }
    
    
    private static  function utf8_encode_intl($string){
        return \UConverter::transcode($string, 'UTF8',self::$charset);
    }
    
    
    private static  function utf8_decode_iconv($string){
        return   iconv('UTF-8',self::$charset, $string);
    }
    
    
    private static  function utf8_encode_iconv($string){
        return   iconv(self::$charset, 'UTF-8', $string);
    }
    
    
        
    
    /**
     * Metodo con mb_check_encoding (accurato e veloce)
     */
    private static function utf8_check_mb(string $str): bool
    {
    	return mb_check_encoding($str, 'UTF-8');
    }
    
    /**
     * Metodo con iconv (secondo per affidabilità)
     */
    private static function utf8_check_iconv(string $str): bool
    {
    	return $str === @iconv('UTF-8', 'UTF-8//IGNORE', $str);
    }
    
    /**
     * Metodo con IntlChar (richiede intl, ma non mbstring!)
     */
    private static function utf8_check_intl(string $str): bool
    {
    	if (!class_exists(\IntlChar::class, false)) return false;
    	
    	$len = strlen($str);  // byte-length
    	$i = 0;
    	while ($i < $len) {
    		$charLen = 1;
    		$ord = ord($str[$i]);
    		if ($ord >= 0xF0) $charLen = 4;
    		elseif ($ord >= 0xE0) $charLen = 3;
    		elseif ($ord >= 0xC0) $charLen = 2;
    		
    		$char = substr($str, $i, $charLen);
    		if (!\IntlChar::isvalidcodepoint(\IntlChar::ord($char))) {
    			return false;
    		}
    		
    		$i += $charLen;
    	}
    	return true;
    }
    
    /**
     * Metodo standard migliorato con preg_match e json_encode
     */
    private static function utf8_check_std(string $str): bool
    {
    	return @preg_match('//u', $str) === 1 && json_encode([$str]) !== false;
    }
    
    /**
     * Entry point: restituisce true se la stringa è UTF-8 valida
     */
    public static function is_utf8(string $str): bool
    {
    	if ($str === '' || $str === null) return true;
    	
    	if (function_exists('mb_check_encoding')) {
    		return self::utf8_check_mb($str);
    	}
    	
    	if (function_exists('iconv')) {
    		return self::utf8_check_iconv($str);
    	}
    	
    	if (class_exists(\IntlChar::class, false)) {
    		return self::utf8_check_intl($str);
    	}
    	
    	return self::utf8_check_std($str);
    }
    
    
  
}

