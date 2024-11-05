<?php
/**
 * Contains Gimi\myFormsTools\PckmyUtils\myVirtualDir.
 */
                                                
namespace Gimi\myFormsTools\PckmyUtils;

                                                
class myVirtualDir {
protected $realHandle,$dirRes,$fileRes;
protected static $realpath=array();
protected static $streamprefix='var';



 protected static function buildpath($path){
 $path=static::parsePath($path);
 return static::$realpath[static::$streamprefix].$path['path'];
 }
	
 public static function register($prefix='mydir',$realpath){
 static::$streamprefix=$prefix;
 if(in_array(static::$streamprefix,stream_get_wrappers())) stream_wrapper_unregister(static::$streamprefix);
 stream_wrapper_register(static::$streamprefix,get_called_class());
 static::$realpath[static::$streamprefix]=$realpath;
 }

	public function __construct(){}

	
//getters
	public static function getPrefix($suffix=true){
		return static::$streamprefix.($suffix ? '://' : null);
	}

	public function getSize(){
		return strlen($this->info['data']);
	}

	public function getCtime(){
		return round($this->info['ctime']);
	}

	public function getMtime(){
		return round($this->info['mtime']);
	}

	public function getAtime(){
		return round($this->info['atime']);
	}

	public function getMode(){
		return $this->mode;
	}

	
//general utility functions
	public static function parsePath($path){
		
//strip prefix
		$path = str_replace(static::getPrefix(),'',str_replace('\\','/',$path));
		
//break off the query string if we can
		if(strpos($path,'?') !== false){
			$query = substr($path,strpos($path,'?'));
			$path = substr($path,0,(strlen($path)-strlen($query)));
			
//remove the questionmark
			$query = str_replace('?','',$query);
		}
		
//setup opts
		if(!$path || $path[0]!='/') $path='/'.$path;
		$opts['path'] = $path;
		$opts['params'] = array();
		if(isset($query)) parse_str($query,$opts['params']);
		return $opts;
	}

	
	public function stream_stat(){
	 return $this->info=@fstat($this->fileRes);
	}
	

	public static function url_stat($path,$flags){
		$f=@fopen(static::buildpath($path),'r');
		if(!$f) return false;
			else {$out=@fstat($f);@fclose($f);
			 return $out;
			 }
	}

	
// StreamWrapper functions from here on out
	public function stream_set_option($option,$arg1,$arg2){
		switch($option){
			case STREAM_OPTION_BLOCKING: 
//The method was called in response to stream_set_blocking()
			case STREAM_OPTION_READ_TIMEOUT: 
//The method was called in response to stream_set_timeout()
			default:
				return false; 
//unsupported
				break;
			case STREAM_OPTION_WRITE_BUFFER: 
//The method was called in response to stream_set_write_buffer()
				switch($arg1){
					case STREAM_BUFFER_NONE:
						
//DGAF, we buffer anyways because PHP streams use 8K buffers and this isn't 1998
					case STREAM_BUFFER_FULL:
						break;
				}
				$this->setBufferLimit($arg2);
				break;
		}
		return true;
	}

	public function stream_cast($cast_as){
		return false;
	}

	public function stream_open($path,$mode,$options,&$opened_path){
		
		
//parse path and get options
		$opts = static::parsePath($path);
		if(!$opts['path']) return false;
 $this->path=$opts['path'];
 
//startup handlers
 $this->fileRes=@fopen(static::buildpath($path), $mode);
		if(!$this->fileRes) return false;
		$this->stream_stat();
		return true;
	}

	public function stream_close(){
	 return @fclose($this->fileRes);
	}


	public function stream_flush(){
	 return @fflush($this->fileRes);
	}

	
  public function stream_read($count)
 {return @fread($this->fileRes,$count);
 
/*$this->info['atime']=time();
        $ret = substr($this->info['data'], $this->position, $count);
        $this->position += strlen($ret);
        return $ret;*/
 }

  public function stream_write($data)
 	{
 	return @fwrite($this->fileRes,$data); 
 	
/*$this->info['mtime']=$this->info['atime']=time();
        $left = substr($this->info['data'], 0, $this->position);
        $right = substr($this->info['data'], $this->position + strlen($data));
        $this->info['data'] = $left . $data . $right;
        $this->position += strlen($data);
        clearstatcache();
        return strlen($data);*/
 	}

 	
  public function stream_tell()
 	 	{
 return @ftell($this->fileRes);
 	}

  public function stream_eof()
 	{
 	 return @feof($this->fileRes); 
 
//return $this->position >= strlen($this->info['data']);
 	}

  public function stream_seek($offset, $whence)
 {
 return @fseek( $this->fileRes , $offset , $whence);
 
/*switch ($whence) {
            case SEEK_SET:
                if ($offset < strlen($this->info['data']) && $offset >= 0) {
                     $this->position = $offset;
                     return true;
                } else {
                     return false;
                }
                break;

            case SEEK_CUR:
                if ($offset >= 0) {
                     $this->position += $offset;
                     return true;
                } else {
                     return false;
                }
                break;

            case SEEK_END:
                if (strlen($this->info['data']) + $offset >= 0) {
                     $this->position = strlen($this->info['data']) + $offset;
                     return true;
                } else {
                     return false;
                }
                break;

            default:
                return false;
        }*/
 }
	
	public function stream_lock($operation){
		return @flock($this->fileRes,$operation);
	}
	
	
//-----------------------------------------------------
	
//Directory Listing and Modification
	
//-----------------------------------------------------
	public function dir_closedir(){
	 return closedir( $this->dirRes);
	}

	public function dir_readdir(){
	 return @readdir( $this->dirRes);
	}

	public function dir_rewinddir(){
	 return @rewinddir( $this->dirRes);
	}

	public function mkdir($path,$mode,$options){
	 return @mkdir(static::buildpath($path),$mode,$options);
	}

	public function rename($oldname,$newname){
	 return @rename(static::buildpath($oldname), static::buildpath($newname));
	}

	public function rmdir($path,$options){
	 return @rmdir(static::buildpath($path));
	}



	public function unlink($path){
	 return @unlink(static::buildpath($path));
	}
	


	public function dir_opendir($path,$options){
	 $this->dirRes=@opendir(static::buildpath($path));
	 return $this->dirRes!=false;
	}



}