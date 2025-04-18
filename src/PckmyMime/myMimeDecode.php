<?php
namespace Gimi\myFormsTools\PckmyMime;

use Gimi\myFormsTools\PckmyUtils\PHP8;



class myMimeDecode{
	
	    /**
	     * The raw email to decode
	     * @protected    string
	     */
	    protected $_input;
	
	    /**
	     * The header part of the input
	     * @protected    string
	     */
	    protected $_header;
	
	    /**
	     * The body part of the input
	     * @protected    string
	     */
	    protected $_body;
	
	    /**
	     * If an error occurs, this is used to store the message
	     * @protected    string
	     */
	    protected $_error;
	
	    /**
	     * Flag to determine whether to include bodies in the
	     * returned object.
	     * @protected    boolean
	     */
	    protected $_include_bodies;
	
	    /**
	     * Flag to determine whether to decode bodies
	     * @protected    boolean
	     */
	    protected $_decode_bodies;
	
	    /**
	     * Flag to determine whether to decode headers
	     * @protected    boolean
	     */
	    protected $_decode_headers;
	
	    /**
	    * If invoked from a class, $this will be set. This has problematic
	    * connotations for calling decode() statically. Hence this variable
	    * is used to determine if we are indeed being called statically or
	    * via an object.
	    */
	    protected $mailMimeDecode;
	
	    /**
	     * Constructor.
	     *
	     * Sets up the object, initialise the variables, and splits and
	     * stores the header and body of the input.
	     *
	     * @param string The input to decode
	     * 
	     */
	     public function __construct($input)
	    {
	        list($header, $body)   = $this->_split_body_header($input);
	
	        $this->_input          = $input;
	        $this->_header         = $header;
	        $this->_body           = $body;
	        $this->_decode_bodies  = true;
	        $this->_include_bodies = true;
	        
	        $this->mailMimeDecode  = true;
	    }
	    
	    
	    
	    private function walk($object) {
	    	if($object->parts) {
	    		foreach ($object->parts as &$part)
	    		{if($part->ctype_primary=='message' && $part->ctype_secondary='rfc822') return $part;
	    		if(isset($part->parts))
	    		{ $found=$this->walk($part);
	    		if($found) return $found;
	    		}
	    		}
	    	}
	    }
	    
	    
	    public function cerca_body(&$parte) {
	    	$out=array();
	    	if(    (!isset($parte->disposition) || !$parte->disposition) &&
	    			isset($parte->ctype_primary) && $parte->ctype_primary=='text') $out[$parte->ctype_secondary]=$parte;
	    			if(!$out && isset($parte->parts) && $parte->parts)  foreach ($parte->parts as &$part)  $out= array_merge($out,$this->cerca_body($part));
	    			return $out;
	    }
	    
	    
	    
	    function &get_postacert_parts($parts=array(),$recursive=false){
	    	if(!$parts) $parts=array('include_bodies'=>false,'decode_bodies'=>true,'decode_headers'=>true);
	    	$components=$this->decode($parts,$recursive);
	    	$parti=$this->walk($components);
	    	
	    	$out=array('eml'=>&$parti,
	    			'body_cert'=>$this->cerca_body($components),
	    			'header_cert'=>$components->headers,
	    	);
	    	if($components && isset($components->parts) && $components->parts)
	    		foreach ( $components->parts as &$parte)
	    			if(isset($parte->disposition) && $parte->disposition)
	    				$out['attach_cert'][]=$parte;
	    				
	    				
	    				
	    				$this->__construct($parti->body);
	    				$files=$this->decode($parts,$recursive);
	    				
	    				if($files) {
	    					$out['header']=&$files->headers;
	    					$out['body']=$this->cerca_body($files);
	    					if($files && $files->parts)
	    						foreach ( $files->parts as &$parte)   if(isset($parte->disposition)) $out['attach'][]=$parte;
	    				}
	    				return $out;
	    }
	
	    /**
	     * Begins the decoding process. If called statically
	     * it will create an object and call the decode() method
	     * of it.
	     *
	     * @param array An array of various parameters that determine
	     *              various things:
	     *              include_bodies - Whether to include the body in the returned
	     *                               object.
	     *              decode_bodies  - Whether to decode the bodies
	     *                               of the parts. (Transfer encoding)
	     *              decode_headers - Whether to decode headers
	     *              input          - If called statically, this will be treated
	     *                               as the input
	     * @return object Decoded results
	     * 
	     */
	     public function decode($params = null,$recursive=true)
	    {
	        if($params===null) $params=array('include_bodies'=>$this->_include_bodies,
	                                         'decode_bodies' =>$this->_decode_bodies,
	                                         'decode_headers'=>$this->_decode_headers
	                                       );
	        
	
	            $this->_include_bodies = isset($params['include_bodies'])  ? $params['include_bodies']  : false;
	            $this->_decode_bodies  = isset($params['decode_bodies'])   ? $params['decode_bodies']   : false;
	            $this->_decode_headers = isset($params['decode_headers'])  ? $params['decode_headers']  : false;
	
	            $structure = $this->_decode($this->_header, $this->_body,'text/plain',$recursive);
	            if ($structure === false) $structure =$this->_error;
	                                 
	       
	        return $structure;
	    }
	    
	    
	    
	   /**
	    * 
	    * @param function() $rule � una funzione che riceve in ingresso un nodo del risultato della decode e restituisce true se soddisfa requisito di ricerca
	    * @return array
	    */
	    function &find($rule,&$parts=array()) {
	       if(!$parts) $parts=$this->decode(array('include_bodies'=>true,'decode_bodies'=>true,'decode_headers'=>true),false);
	       if($rule($parts)) return $parts;
	       if(!isset($parts->parts) && $parts->body && 
	           ($parts->ctype_primary=='message' || $parts->ctype_primary=='multipart' ))
                                               {$mime=new myMimeDecode($parts->body);
                    	                        $parts=$mime->decode(array('include_bodies'=>true,'decode_bodies'=>true,'decode_headers'=>true),false);
                    	                        unset($parts->body);
                    	                      }
                 	                      
           if(isset($parts->parts) && $parts->parts)
                    	                      	foreach ($parts->parts as &$part)
	                                   {if($rule($part)) return $part;
	                                    $found=$this->find($rule,$part);
	                                    if($found) return $found;
	                                   }
	                        
	    }
	
	    /**
	     * Performs the decoding. Decodes the body string passed to it
	     * If it finds certain content-types it will call itself in a
	     * recursive fashion
	     *
	     * @param string Header section
	     * @param string Body section
	     * @return object Results of decoding process
	     * 
	     */
	     protected function _decode($headers, $body, $default_ctype = 'text/plain',$recursive=true)
	    {
	        $return = new \stdClass;
	        $headers = $this->_parseHeaders($headers);
	
	        foreach ($headers as $value) {
	            if (isset($return->headers[strtolower($value['name'])]) AND !is_array($return->headers[strtolower($value['name'])])) {
	                $return->headers[strtolower($value['name'])]   = array($return->headers[strtolower($value['name'])]);
	                $return->headers[strtolower($value['name'])][] = $value['value'];
	
	            } elseif (isset($return->headers[strtolower($value['name'])])) {
	                $return->headers[strtolower($value['name'])][] = $value['value'];
	
	            } else {
	                $return->headers[strtolower($value['name'])] = $value['value'];
	            }
	        }
	
	        foreach (array_keys($headers) as $key) {
	            $headers[$key]['name'] = strtolower($headers[$key]['name']);
	            switch ($headers[$key]['name']) {
	
	                case 'content-type':
	                    $content_type = $this->_parseHeaderValue($headers[$key]['value']);
	                    $regs=array();
	                    if (preg_match('/([0-9a-z+.-]+)\/([0-9a-z+.-]+)/i', $content_type['value'], $regs)) {
	                        $return->ctype_primary   = $regs[1];
	                        $return->ctype_secondary = $regs[2];
	                    }
	
	                     if (isset($content_type['other'])) 
	                        foreach($content_type['other'] as $p_name=>$p_value ) {
	                            $return->ctype_parameters[$p_name] = $p_value;
	                        }
	                    
	                    break;
	
	                case 'content-disposition';
	                    $content_disposition = $this->_parseHeaderValue($headers[$key]['value']);
	                    $return->disposition   = $content_disposition['value'];
	                     
	                      if (isset($content_disposition['other'])) 
	                            foreach($content_disposition['other'] as $p_name=>$p_value ) {
	                                $return->d_parameters[$p_name] = $p_value;
	                            }
	                   
	                    break;
	
	                case 'content-transfer-encoding':
	                    $content_transfer_encoding = $this->_parseHeaderValue($headers[$key]['value']);
	                    break;
	            }
	        }
	
	        if (isset($content_type)) {
	            switch (trim(strtolower($content_type['value']))) {
	                case 'text/plain':
	                    $encoding = isset($content_transfer_encoding) ? $content_transfer_encoding['value'] : '7bit';
	                    !isset($return->disposition) || $this->_include_bodies===true ||  $this->_include_bodies== isset($return->disposition) ? $return->body = ($this->_decode_bodies ? $this->_decodeBody($body, $encoding) : $body) : null;
	                    break;
	                case 'text/html':
	                    $encoding = isset($content_transfer_encoding) ? $content_transfer_encoding['value'] : '7bit';
	                    !isset($return->disposition) || $this->_include_bodies===true ||  $this->_include_bodies== isset($return->disposition)  ? $return->body = ($this->_decode_bodies ? $this->_decodeBody($body, $encoding) : $body) : null;
					    break;
	
	                case 'multipart/parallel':
	                case 'multipart/report': // RFC1892
	                case 'multipart/signed': // PGP
	                case 'multipart/digest':
	                case 'multipart/alternative':
	                case 'multipart/related':
	                case 'multipart/mixed':
	                    if(!isset($content_type['other']['boundary'])){
	                        $this->_error = 'No boundary found for ' . $content_type['value'] . ' part';
	                        return false;
	                    }
	
	                    $default_ctype = (strtolower($content_type['value']) === 'multipart/digest') ? 'message/rfc822' : 'text/plain';
						$parts = $this->_boundarySplit($body, $content_type['other']['boundary']);
						for ($i = 0; $i < count($parts); $i++) {
            	                        list($part_header, $part_body) = $this->_split_body_header($parts[$i]);
            	                        $part = $this->_decode($part_header, $part_body, $default_ctype,$recursive);
            	                        if($part === false)  $part = $this->raiseError($this->_error);
            	                        $return->parts[] = $part;
            	                    }
	                    break;
	
	                case 'message/rfc822':
	                    if($recursive) {
            	                    $obj = new myMimeDecode($body,true);
            	                    $return->parts[] = $obj->decode(array('include_bodies'=>$this->_include_bodies,
                                                	                        'decode_bodies' =>$this->_decode_bodies,
                                                	                        'decode_headers'=>$this->_decode_headers
                                                	                    ),true);
            	                    unset($obj);
	                               }
	                          else {
	                                $encoding = isset($content_transfer_encoding) ? $content_transfer_encoding['value'] : '7bit';
	                                $return->body =$this->_decodeBody($body, $encoding);
	                              }
	                            
	                    break;
	
	                default:
	                    if(!isset($content_transfer_encoding['value']))
	                        $content_transfer_encoding['value'] = '7bit';
	                        !isset($return->disposition) || $this->_include_bodies===true ||  $this->_include_bodies==isset( $return->disposition)  ? $return->body = ($this->_decode_bodies ? $this->_decodeBody($body, $content_transfer_encoding['value']) : $body) : null;
	                    break;
	            }
	
	        } else {
	            $ctype = explode('/', $default_ctype);
	            $return->ctype_primary   = $ctype[0];
	            $return->ctype_secondary = $ctype[1];
	            $this->_include_bodies===true ||  $this->_include_bodies== $return->disposition  ?  $return->body = ($this->_decode_bodies ? $this->_decodeBody($body) : $body) : null;
	        }
	
	        return $return;
	    }
	
	    /**
	     * Given the output of the above function, this will return an
	     * array of references to the parts, indexed by mime number.
	     *
	     * @param  object $structure   The structure to go through
	     * @param  string $mime_number Internal use only.
	     * @return array               Mime numbers
	     */
	    function &getMimeNumbers(&$structure, $no_refs = false, $mime_number = '', $prepend = '')
	    {
	        $return = array();
	        if (!empty($structure->parts)) {
	            if ($mime_number != '') {
	                $structure->mime_id = $prepend . $mime_number;
	                $return[$prepend . $mime_number] = &$structure;
	            }
	            for ($i = 0; $i < count($structure->parts); $i++) {
	
	            
	                if (!empty($structure->headers['content-type']) AND substr(strtolower($structure->headers['content-type']), 0, 8) == 'message/') {
	                    $prepend      = $prepend . $mime_number . '.';
	                    $_mime_number = '';
	                } else {
	                    $_mime_number = ($mime_number == '' ? $i + 1 : sprintf('%s.%s', $mime_number, $i + 1));
	                }
	
	                $arr = &$this->getMimeNumbers($structure->parts[$i], $no_refs, $_mime_number, $prepend);
	                if(is_array($arr))
	                    foreach (array_keys($arr) as $key ) {
	                    $no_refs ? $return[$key] = '' : $return[$key] = &$arr[$key];
	                }
	            }
	        } else {
	            if ($mime_number == '') {
	                $mime_number = '1';
	            }
	            $structure->mime_id = $prepend . $mime_number;
	            $no_refs ? $return[$prepend . $mime_number] = '' : $return[$prepend . $mime_number] = &$structure;
	        }
	        
	        return $return;
	    }
	
	    /**
	     * Given a string containing a header and body
	     * section, this function will split them (at the first
	     * blank line) and return them.
	     *
	     * @param string Input to split apart
	     * @return array Contains header and body section
	     * 
	     */
	     protected function _split_body_header($input)
	    {$match=array();
	        if (preg_match("/^(.*?)\r?\n\r?\n(.*)/s", $input, $match)) {
	            return array($match[1], $match[2]);
	        }
	        $this->_error = 'Could not split header and body';
	        return false;
	    }
	
	    /**
	     * Parse headers given in $input and return
	     * as assoc array.
	     *
	     * @param string Headers to parse
	     * @return array Contains parsed headers
	     * 
	     */
	     protected function _parseHeaders($input)
	    {
	
	        if ($input !== '') {
	            // Unfold the input
	            $input   = preg_replace("/\r?\n/", "\r\n", $input);
	            $input   = preg_replace("/\r\n(\t| )+/", ' ', $input);
	            $headers = explode("\r\n", trim((string) $input));
	
	            foreach ($headers as $value) {
	                $hdr_name = substr($value, 0, $pos = strpos($value, ':'));
	                $hdr_value = substr($value, $pos+1);
	                if($hdr_value[0] == ' ')
	                    $hdr_value = substr($hdr_value, 1);
	
	                $return[] = array(
	                                  'name'  => $hdr_name,
	                                  'value' => $this->_decode_headers ? $this->_decodeHeader($hdr_value) : $hdr_value
	                                 );
	            }
	        } else {
	            $return = array();
	        }
	
	        return $return;
	    }
	
	    /**
	     * Function to parse a header value,
	     * extract first part, and any secondary
	     * parts (after ;) This function is not as
	     * robust as it could be. Eg. header comments
	     * in the wrong place will probably break it.
	     *
	     * @param string Header value to parse
	     * @return array Contains parsed result
	     * 
	     */
	     protected function _parseHeaderValue($input)
	    {
	
	        if (($pos = strpos($input, ';')) !== false) {
	
	            $return['value'] = trim(substr($input, 0, $pos));
	            $input = trim(substr($input, $pos+1));
	
	            if (strlen($input) > 0) {
	
	                // This splits on a semi-colon, if there's no preceeding backslash
	                // Can't handle if it's in double quotes however. (Of course anyone
	                // sending that needs a good slap).
	                $parameters = preg_split('/\s*(?<!\\\\);\s*/i', $input);
	
	                for ($i = 0; $i < count($parameters); $i++) {
	                    $param_name  = substr($parameters[$i], 0, $pos = strpos($parameters[$i], '='));
	                    $param_value = substr($parameters[$i], $pos + 1);
	                    if ($param_value[0] == '"') {
	                        $param_value = substr($param_value, 1, -1);
	                    }
	                    $return['other'][$param_name] = $param_value;
	                    $return['other'][strtolower($param_name)] = $param_value;
	                }
	            }
	        } else {
	            $return['value'] = trim((string) $input);
	        }
	
	        return $return;
	    }
	
	    /**
	     * This function splits the input based
	     * on the given boundary
	     *
	     * @param string Input to parse
	     * @return array Contains array of resulting mime parts
	     * 
	     */
	     protected function _boundarySplit($input, $boundary)
	    {
			$boundary=trim((string) $boundary);
			if(substr($boundary,-1)=='"')
		     	$boundary=substr_replace($boundary,"" ,-1 );
			if(substr($boundary,0,1)=='"')
		     	$boundary=substr_replace($boundary,"" ,0,1);
			$boundary=trim((string) $boundary);
		    $tmp = explode('--'.$boundary,$input);//boundary
	        for ($i=1; $i<count($tmp)-1; $i++) {
	            $parts[] = $tmp[$i];
	        }
	
	        return $parts;
	    }
	
	    /**
	     * Given a header, this function will decode it
	     * according to RFC2047. Probably not *exactly*
	     * conformant, but it does pass all the given
	     * examples (in RFC2047).
	     *
	     * @param string Input header value to decode
	     * @return string Decoded header value
	     * 
	     */
	     protected function _decodeHeader($input)
	    {
	        // Remove white space between encoded-words
	        $input = preg_replace('/(=\?[^?]+\?(q|b)\?[^?]*\?=)(\s)+=\?/i', '\1=?', $input);
	        $matches=array();
	        // For each encoded-word...
	        while (preg_match('/(=\?([^?]+)\?(q|b)\?([^?]*)\?=)/i', $input, $matches)) {
	
	            $encoded  = $matches[1];
	      #      $charset  = $matches[2];
	            $encoding = $matches[3];
	            $text     = $matches[4];
	
	            switch (strtolower($encoding)) {
	                case 'b':
	                    $text = base64_decode($text);
	                    break;
	
	                case 'q':
	                    $text = str_replace('_', ' ', $text);
	                    preg_match_all('/=([a-f0-9]{2})/i', $text, $matches);
	                    foreach($matches[1] as $value)
	                        $text = str_replace('='.$value, chr(hexdec($value)), $text);
	                    break;
	            }
	
	            $input = str_replace($encoded, $text, $input);
	        }
	
	        return $input;
	    }
	
	    /**
	     * Given a body string and an encoding type,
	     * this function will decode and return it.
	     *
	     * @param  string Input body to decode
	     * @param  string Encoding type to use.
	     * @return string Decoded body
	     * 
	     */
	     protected function _decodeBody($input, $encoding = '7bit')
	    {
	        switch ($encoding) {
	            case '7bit':
	                return $input;
	                break;
	
	            case 'quoted-printable':
	                return $this->_quotedPrintableDecode($input);
	                break;
	
	            case 'base64':
	                return base64_decode($input);
	                break;
	
	            default:
	                return $input;
	        }
	    }
	
	    /**
	     * Given a quoted-printable string, this
	     * function will decode and return it.
	     *
	     * @param  string Input body to decode
	     * @return string Decoded body
	     * 
	     */
	     protected function _quotedPrintableDecode($input)
	    {
	        // Remove soft line breaks
	        $input = preg_replace("/=\r?\n/", '', $input);
	
	        // Replace encoded characters
	        $input = preg_replace_callback('/=([a-f0-9]{2})/i',
                            	            function ($m) {return chr(hexdec($m[1]));}
                            	            , $input);
	
	        return $input;
	    }
	
	    /**
	     * Checks the input for uuencoded files and returns
	     * an array of them. Can be called statically, eg:
	     *
	     * $files =& $this->uudecode($some_text);
	     *
	     * It will check for the begin 666 ... end syntax
	     * however and won't just blindly decode whatever you
	     * pass it.
	     *
	     * @param  string Input body to look for attahcments in
	     * @return array  Decoded bodies, filenames and permissions
	     * 
	     * @author Unknown
	     */
	    function &uudecode($input)
	    {$matches=array();
	        // Find all uuencoded sections
	        preg_match_all("/begin ([0-7]{3}) (.+)\r?\n(.+)\r?\nend/Us", $input, $matches);
	
	        for ($j = 0; $j < count($matches[3]); $j++) {
	
	            $str      = $matches[3][$j];
	            $filename = $matches[2][$j];
	            $fileperm = $matches[1][$j];
	
	            $file = '';
	            $str = preg_split("/\r?\n/", trim((string) $str));
	            $strlen = count($str);
	
	            for ($i = 0; $i < $strlen; $i++) {
	                $pos = 1;
	                $d = 0;
	                $len=(int)(((ord(substr($str[$i],0,1)) -32) - ' ') & 077);
	
	                while (($d + 3 <= $len) AND ($pos + 4 <= strlen($str[$i]))) {
	                    $c0 = (ord(substr($str[$i],$pos,1)) ^ 0x20);
	                    $c1 = (ord(substr($str[$i],$pos+1,1)) ^ 0x20);
	                    $c2 = (ord(substr($str[$i],$pos+2,1)) ^ 0x20);
	                    $c3 = (ord(substr($str[$i],$pos+3,1)) ^ 0x20);
	                    $file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));
	
	                    $file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));
	
	                    $file .= chr(((($c2 - ' ') & 077) << 6) |  (($c3 - ' ') & 077));
	
	                    $pos += 4;
	                    $d += 3;
	                }
	
	                if (($d + 2 <= $len) && ($pos + 3 <= strlen($str[$i]))) {
	                    $c0 = (ord(substr($str[$i],$pos,1)) ^ 0x20);
	                    $c1 = (ord(substr($str[$i],$pos+1,1)) ^ 0x20);
	                    $c2 = (ord(substr($str[$i],$pos+2,1)) ^ 0x20);
	                    $file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));
	
	                    $file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));
	
	                    $pos += 3;
	                    $d += 2;
	                }
	
	                if (($d + 1 <= $len) && ($pos + 2 <= strlen($str[$i]))) {
	                    $c0 = (ord(substr($str[$i],$pos,1)) ^ 0x20);
	                    $c1 = (ord(substr($str[$i],$pos+1,1)) ^ 0x20);
	                    $file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));
	
	                }
	            }
	            $files[] = array('filename' => $filename, 'fileperm' => $fileperm, 'filedata' => $file);
	        }
	
	        return $files;
	    }
	
	    /**
	     * getSendArray() returns the arguments required for Mail::send()
	     * used to build the arguments for a mail::send() call 
	     *
	     * Usage:
	     * $mailtext = Full email (for example generated by a template)
	     * $decoder = new MIMEDECODE($mailtext);
	     * $parts =  $decoder->getSendArray();
	     * if (!PEAR::isError($parts) {
	     *     list($recipents,$headers,$body) = $parts;
	     *     $mail = Mail::factory('smtp');
	     *     $mail->send($recipents,$headers,$body);
	     * } else {
	     *     echo $parts->message;
	     * }
	     * @return mixed   array of recipeint, headers,body or Pear_Error
	     * 
	     * @author Alan Knowles <alan@akbkhome.com>
	     */
	     public function getSendArray()
	    {
	        // prevent warning if this is not set
	        $this->_decode_headers = FALSE;
	        $headerlist =$this->_parseHeaders($this->_header);
	        $to = "";
	        if (!$headerlist) {
	            return $this->raiseError("Message did not contain headers");
	        }
	        foreach($headerlist as $item) {
	            $header[$item['name']] = $item['value'];
	            switch (strtolower($item['name'])) {
	                case "to":
	                case "cc":
	                case "bcc":
	                    $to = ",".$item['value'];
	                default:
	                   break;
	            }
	        }
	        if ($to == "") {
	            return $this->raiseError("Message did not contain any recipents");
	        }
	        $to = substr($to,1);
	        return array($to,$header,$this->_body);
	    } 
	
	
	
	
	
	
	
	
	
	    /**
	     * Returns a xml copy of the output of
	     * $this->decode. Pass the output in as the
	     * argument. This function can be called statically. Eg:
	     *
	     * $output = $obj->decode();
	     * $xml    = $this->getXML($output);
	     *
	     * The DTD used for this should have been in the package. Or
	     * alternatively you can get it from cvs, or here:
	     * http://www.phpguru.org/xmail/xmail.dtd.
	     *
	     * @param  object Input to convert to xml. This should be the
	     *                output of the $this->decode function
	     * @return string XML version of input
	     * 
	     */
	     public function getXML($input)
	    {
	        $crlf    =  "\r\n";
	        $output  = '<?xml version=\'1.0\'?>' . $crlf .
	                   '<!DOCTYPE email SYSTEM "http://www.phpguru.org/xmail/xmail.dtd">' . $crlf .
	                   '<email>' . $crlf .
	                   $this->_getXML($input) .
	                   '</email>';
	
	        return $output;
	    }
	
	    /**
	     * Function that does the actual conversion to xml. Does a single
	     * mimepart at a time.
	     *
	     * @param  object  Input to convert to xml. This is a mimepart object.
	     *                 It may or may not contain subparts.
	     * @param  integer Number of tabs to indent
	     * @return string  XML version of input
	     * 
	     */
	     protected function _getXML($input, $indent = 1)
	    {
	        $htab    =  "\t";
	        $crlf    =  "\r\n";
	        $output  =  '';
	        $headers = @(array)$input->headers;
	
	        foreach ($headers as $hdr_name => $hdr_value) {
	
	            // Multiple headers with this name
	            if (is_array($headers[$hdr_name])) {
	                for ($i = 0; $i < count($hdr_value); $i++) {
	                    $output .= $this->_getXML_helper($hdr_name, $hdr_value[$i], $indent);
	                }
	
	            // Only one header of this sort
	            } else {
	                $output .= $this->_getXML_helper($hdr_name, $hdr_value, $indent);
	            }
	        }
	
	        if (!empty($input->parts)) {
	            for ($i = 0; $i < count($input->parts); $i++) {
	                $output .= $crlf . str_repeat($htab, $indent) . '<mimepart>' . $crlf .
	                           $this->_getXML($input->parts[$i], $indent+1) .
	                           str_repeat($htab, $indent) . '</mimepart>' . $crlf;
	            }
	        } elseif (isset($input->body)) {
	            $output .= $crlf . str_repeat($htab, $indent) . '<body><![CDATA[' .
	                       $input->body . ']]></body>' . $crlf;
	        }
	
	        return $output;
	    }
	
	    /**
	     * Helper function to _getXML(). Returns xml of a header.
	     *
	     * @param  string  Name of header
	     * @param  string  Value of header
	     * @param  integer Number of tabs to indent
	     * @return string  XML version of input
	     * 
	     */
	     protected function _getXML_helper($hdr_name, $hdr_value, $indent)
	    {
	        $htab   = "\t";
	        $crlf   = "\r\n";
	        $return = '';
	
	        $new_hdr_value = ($hdr_name != 'received') ? $this->_parseHeaderValue($hdr_value) : array('value' => $hdr_value);
	        $new_hdr_name  = str_replace(' ', '-', ucwords(str_replace('-', ' ', $hdr_name)));
	
	        // Sort out any parameters
	        if (!empty($new_hdr_value['other'])) {
	            foreach ($new_hdr_value['other'] as $paramname => $paramvalue) {
	                $params[] = str_repeat($htab, $indent) . $htab . '<parameter>' . $crlf .
	           			        str_repeat($htab, $indent) . $htab . $htab . '<paramname>' .PHP8::htmlspecialchars($paramname) . '</paramname>' . $crlf .
	                            str_repeat($htab, $indent) . $htab . $htab . '<paramvalue>' .PHP8::htmlspecialchars($paramvalue) . '</paramvalue>' . $crlf .
	                            str_repeat($htab, $indent) . $htab . '</parameter>' . $crlf;
	            }
	
	            $params = implode('', $params);
	        } else {
	            $params = '';
	        }
	
	        $return = str_repeat($htab, $indent) . '<header>' . $crlf .
	                  str_repeat($htab, $indent) . $htab . '<headername>' .PHP8::htmlspecialchars($new_hdr_name) . '</headername>' . $crlf .
	                  str_repeat($htab, $indent) . $htab . '<headervalue>' .PHP8::htmlspecialchars($new_hdr_value['value']) . '</headervalue>' . $crlf .
	                  $params .
	                  str_repeat($htab, $indent) . '</header>' . $crlf;
	
	        return $return;
	    }

	
	
	} // End of class
?>