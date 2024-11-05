<?php
class myMIMEDECODE extends MIMEDECODE 
	{
	    
	    
	    private function walk($object) {
	        if($object->parts) {
	            foreach ($object->parts as &$part)  
	                                       {if($part->ctype_primary=='message' && $part->ctype_secondary='rfc822') return $part;
	                                        if($part->parts) { $found=$this->walk($part);
	                                                           if($found) return $found;
	                                                         }
	                                       }          
	        }
	    }
	    
	    
	      public function cerca_body(&$parte) {
	        $out=array();
	        if(!$parte->disposition && $parte->ctype_primary && $parte->ctype_primary=='text') $out[$parte->ctype_secondary]=$parte;
	        if(!$out && $parte->parts)  foreach ($parte->parts as &$part)  $out= array_merge($out,$this->cerca_body($part));
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
	        if($components && $components->parts) foreach ( $components->parts as &$parte)   if($parte->disposition) $out['attach_cert'][]=$parte;
	        
	        
	        parent::__construct($parti->body);
	        $files=$this->decode($parts,$recursive); 
	      
	        if($files) {
            	        $out['header']=&$files->headers;
            	        $out['body']=$this->cerca_body($files);
            	        if($files && $files->parts) foreach ( $files->parts as &$parte)   if($parte->disposition) $out['attach'][]=$parte;
            	        }
            return $out;         
	   }
	    
}	    
?>