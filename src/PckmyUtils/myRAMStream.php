<?php
/**
 * Contains Gimi\myFormsTools\PckmyUtils\myRAMStream.
 */

namespace Gimi\myFormsTools\PckmyUtils;



/*
 myramStream::register('disk1');
 file_put_contents("disk1://myvar/b.txt", 'BBBBBBBBBBBBBBBBBBB');
 
 file_put_contents("disk1://myvar/a.txt", 'asdfsdgfsdfg');
 
 echo '<hr>';
 copy("disk1://myvar/a.txt", "disk1://myvar/c.txt");
 echo '<hr>';
 
 $fp = fopen("disk1://myvar/c.txt", "r");
 fseek($fp,filesize("disk1://myvar/c.txt")/2);
 echo '<pre>';
 fwrite($fp, "line1\n");
 fwrite($fp, "line2\n");
 fwrite($fp, "line3\n");
 
 rewind($fp);
 while (!feof($fp)) {
 echo fgets($fp);
 }
 fclose($fp);
 unlink("disk1://myvar/b.txt");
 
 print_r(scandir('disk1://myvar'));
 */
class myRAMStream {
	private static $streamprefix='var';
	private $position = 0;
	private $dir_pointer = -1;
	private $dir_list=array();
	private $eof = false;
	private $mode = null;
	private $path;
	protected static $vars=array(),$prefs=array(); 
	private $info = null;
	public $context;

	
	
	public static function &getStorage($prefix=''){
		if(!$prefix) return static::$vars;
			elseif(isset(static::$vars[$prefix])) return static::$vars[$prefix];
		$null=null;
		return $null;	
	}
	
	public static function formatStorage($prefix=''){
		if(!$prefix) static::$vars=array();
			    else static::$vars[$prefix]=array();
	}
	
	 public function __call($f,$a) { }

	public static function register($prefix='myram'){
		//(re)register the wrapper
		static::$streamprefix=$prefix;
		static::$prefs[$prefix]=$prefix;
		if(in_array(static::$streamprefix,stream_get_wrappers())) return true;
		return stream_wrapper_register(static::$streamprefix,get_called_class());
	}
	
	public static function unregister($prefix='myram'){
		if(in_array($prefix,stream_get_wrappers()))
					{self::formatStorage($prefix);
					 unset(static::$prefs[$prefix]);
					 stream_wrapper_unregister($prefix);
					 static::$streamprefix=null;
					}
		 
	}

	public function __construct(){}
	
	


	protected function reset(){
		$this->position = 0;
		$this->dir_pointer = 0;
		if(!isset(static::$vars[static::$streamprefix][$this->path]))
			static::$vars[static::$streamprefix][$this->path]=array( 'mode'		=>	$this->mode			//file size in bytes
                                                        			,'ctime'	=>	time()				//created timestamp
                                                        			,'mtime'	=>	time()				//last modified timestamp
                                                        			,'atime'	=>	time()				//last accessed timestamp
                                                        			,'blksize'	=>	4096				//block size of filesystem
                                                        			,'data'     =>'',
                                                        		);
		$this->info=static::$vars[static::$streamprefix][$this->path];
	}

	//getters
	public static function getPrefix($suffix=true){
		return static::$streamprefix.($suffix ? '://' : null);
	}

	public function getSize(){
	    if($this->info===null) return false;
	    return strlen($this->info['data']);
	}

	public function getCtime(){
	    if($this->info===null) return false;
	    return round($this->info['ctime']);
	}

	public function getMtime(){
	    if($this->info===null) return false;
	    return round($this->info['mtime']);
	}

	public function getAtime(){
	    if($this->info===null) return false;
	    return round($this->info['atime']);
	}

	public function getMode(){
	    if($this->info===null) return false;
	    return $this->mode;
	}

	//general utility functions
	public static function parsePath($path){
		//strip prefix
		$path=str_replace('\\','/',$path);
		foreach (static::$prefs as $pref)
			if(strpos($path, $pref)===0) 
						{$path = substr($path,strlen($pref)+1);
						 static::$streamprefix=$pref;
						 break;
						}
		//break off the query string if we can
		if(strpos($path,'?') !== false){
											$query = substr($path,strpos($path,'?'));
											$path = substr($path,0,(strlen($path)-strlen($query)));
											//remove the questionmark
											$query = str_replace('?','',$query);
										}
		//setup opts
		if(!$path || $path[0]!='/') $path='/'.$path;
		$opts=array('pref'=>$pref);
		$opts['path'] = $path;
		$opts['params'] = array();
		if(isset($query)) parse_str($query,$opts['params']);
		return $opts;
	}

	
	public function stream_stat(){
	    if($this->info===null) return false;
	    $stat = array();
		// 0	dev		device number
		$stat[0]	=	$stat['dev']		= 0;
		// 1	ino		inode number *
		$stat[1]	=	$stat['ino']		= 0;
		// 2	mode	inode protection mode
		$stat[2]	=	$stat['mode']		= 33206;
		// 3	nlink	number of links
		$stat[3]	=	$stat['nlink']		= 1;
		// 4	uid		userid of owner *
		$stat[4]	=	$stat['uid']		= 0;
		// 5	gid		groupid of owner *
		$stat[5]	=	$stat['gid']		= 0;
		// 6	rdev	device type, if inode device
		$stat[6]	=	$stat['rdev']		= 0;
		// 7	size	size in bytes
		$stat[7]	=	$stat['size']		= $this->getSize();
		// 8	atime	time of last access (Unix timestamp)
		$stat[8]	=	$stat['atime']		= $this->getAtime();
		// 9	mtime	time of last modification (Unix timestamp)
		$stat[9]	=	$stat['mtime']		= $this->getMtime();
		//10	ctime	time of last inode change (Unix timestamp)
		$stat[10]	=	$stat['ctime']		= $this->getCtime();
		//11	blksize	blocksize of filesystem IO **
		$stat[11]	=	$stat['blksize']	= max($this->getSize(),4096);
		//12	blocks	number of 512-byte blocks allocated **
		$stat[12]	=	$stat['blocks']		= 1;
		return $stat;
	}
	
	
	
	public  function url_stat($path,$flags){
	  	$opts = static::parsePath($path);
	    if(!$opts['path']) return false;
	    if(!isset(static::$vars[static::$streamprefix][$opts['path']])) return false;
		$f=fopen($path,'r');
		if(!$f)  return false;
			else return fstat($f);
	}

	// StreamWrapper functions from here on out
	public function stream_set_option($option,$arg1,$arg2){
	    if($this->info===null) return false;
	    switch($option){
			case STREAM_OPTION_BLOCKING: //The method was called in response to stream_set_blocking()
			case STREAM_OPTION_READ_TIMEOUT: //The method was called in response to stream_set_timeout()
			default:
				return false; //unsupported
				break;
			case STREAM_OPTION_WRITE_BUFFER:
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
	    $this->mode = $mode;
		//parse path and get options
		$opts = static::parsePath($path);
		if(!$opts['path']) return false;
        $this->path=$opts['path'];
        //startup handlers
		$this->reset();

		if($mode[0]=='a') $this->position=strlen($this->info['data']);
	    	elseif($mode[0]=='w') $this->info['data']='';	
	#	stream_set_read_buffer($stream, 99999);
	#	stream_set_write_buffer($stream, 99999);
		
		return true;
	}

	public function stream_close(){
	    if($this->info===null) return false;
	    static::$vars[static::$streamprefix][$this->path]=$this->info;
	    $this->info=null;
		return true;
	}


	public function stream_flush(){
	    if($this->info===null) return false;
		return true;
	}

	
     public function stream_read($count)
       {
        if($this->info===null) return false;
        $this->info['atime']=time();
        $pos = $this->position;
        $this->position = min($this->position+$count,strlen($this->info['data']));
        if($pos>= strlen($this->info['data'])) return false;
        	elseif($pos==0 && strlen($this->info['data'])<=$count) 
        			  return $this->info['data'];
       			 else return substr($this->info['data'], $pos, $count);
       }

     public function stream_write($data)
    	{
    	if($this->info===null) return false;
        $this->info['mtime']=$this->info['atime']=time();
        $length=strlen($data);
        if($this->position==0)  {$this->info['data'] = &$data;	
        						 $this->position= $length;
        						}
	          	else { 
	          		if(!function_exists('substr_replace'))
	          				 	  for($i=0;$i<$length;$i++)$this->info['data'][$this->position++]=$data[$i];
	          				else{ $this->info['data'] = substr_replace($this->info['data'], $data ,$this->position, strlen($data));
		          				  $this->position += $length;
	          					}
				    		
					    		
					
																	    	
	   				}
        clearstatcache();
        return strlen($data);
    	}

    	
     public function stream_tell()
   	 	{
   	 	    if($this->info===null) return false;
   	 	    return $this->position;
    	}

     public function stream_eof()
    	{
    	    if($this->info===null) return false;
    	    return $this->position >= strlen($this->info['data']);  
    	}
    	
    public function stream_truncate($size) {
       if($this->info===null) return false;
       if(strlen($this->info['data'])<$size)  $this->info['data'].=str_repeat(' ', $size-strlen($this->info['data']));
                                       else   $this->info['data']=substr($this->info['data'], 0,$size);
       return true;
      }	

     public function stream_seek($offset, $whence)
    {if($this->info===null) return false;
        switch ($whence) {
            case SEEK_SET:
                if ($offset <= strlen($this->info['data']) && $offset >= 0) {
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
        }
    }
	
	public function stream_lock($operation){
		return false;
	}
	
	//-----------------------------------------------------
	//Directory Listing and Modification
	//-----------------------------------------------------
	public function dir_closedir(){
		$this->dir_list=array();
		$this->dir_pointer=-1;
	}

	public function dir_readdir(){
		$this->dir_pointer++;
		if(!isset($this->dir_list[$this->dir_pointer])) return false;
		return $this->dir_list[$this->dir_pointer];
	}

	public function dir_rewinddir(){
		$this->dir_pointer = -1;
		return true;
	}

	public function mkdir($path,$mode,$options){
	    return true;
	}

	public function rename($path_from,$path_to){
		$from=static::parsePath($path_from);
		$from=$from['path'];
		$to=static::parsePath($path_to);
		$to=$to['path'];
		if(isset(static::$vars[static::$streamprefix][$to]) || 
		  !isset(static::$vars[static::$streamprefix][$from])) return false;
		static::$vars[static::$streamprefix][$to]=&static::$vars[static::$streamprefix][$from];
		unset(static::$vars[static::$streamprefix][$from]);
		return true;
	}

	public function rmdir($path,$options){
		$from=static::parsePath($path);
		if(!$from['path']) return false;
		$from=$from['path'];
		if($from[strlen($from)-1]!='/') $from.='/';
		foreach (static::$vars[static::$streamprefix] as $k=>&$v) {
			$K=static::parsePath($k);
			$K=$K['path'];
			if($K[strlen($K)-1]!='/') $K.='/';
			if(strpos($K,$from)===0) unset(static::$vars[static::$streamprefix][$k]);
			$v;
		}
		return true;
	}



	public function unlink($path){
		$path=static::parsePath($path);
		if(!isset($path['path'])) return false;
		unset(static::$vars[static::$streamprefix][$path['path']]);
		return true;
	}
	


	public function dir_opendir($opts,$options){
		$this->dir_list=array();
		$this->dir_pointer=-1;
		$from=static::parsePath($opts);
		$from=$from['path'];
		if(!$from || $from[strlen($from)-1]!='/') $from.='/';
		if(is_array(static::$vars[static::$streamprefix]))
		  foreach (static::$vars[static::$streamprefix] as $k=>&$v) {
			$K=static::parsePath($k);
		    $K=$K['path'];
			if($K[strlen($K)-1]!='/') $K.='/';
			if(strpos($K,$from)===0) $this->dir_list[]=$this->getPrefix().$k;
			$v;
		}
		return true;
	}



}