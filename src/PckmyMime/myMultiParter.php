<?php

namespace Gimi\myFormsTools\PckmyMime;


use Gimi\myFormsTools\myMail;



/**
 * Classe utile per produrre documenti multiparter in generale
 *
 */
	
class myMultiParter extends myMail {
/** @ignore */
	public $Id,$mime_types              = "\r\n";

	


  public function __construct($id=''){
 		if(!$id) $id= md5(uniqid(time()));
		$this->Id =$id;
		$this->init();
	}


  public function AddStringAttachment($string, $filename, $encoding = 'base64', $type = 'application/octet-stream',$cid=0,$mode='attachment') {
    // Append to $attachment array
    $this->attachment[] = array(
      0 => $string,
      1 => $filename,
      2 => basename($filename),
      3 => $encoding,
      4 => $type,
      5 => true,  // isStringAttachment
      6 => $mode,
      7 => $cid
    );
  }



     /**
     * Assembles message header.
     * @ignore
     * @return string
     */
     public function CreateHeader() {
       $result = "";
       $this->Mailer = 'mail';
       // Set the boundaries
       $uniq_id = $this->Id ;
       $this->boundary[1] = "----=_NextPart_" . $uniq_id;

       $result  = $this->HeaderLine("MIME-Version", "1.0");
       $result .= trim((string) $this->GetMailMIME());
       IF(isset($this->start)) {
      			 	if($this->attachment[$this->start][4]) $result.="; type=\"{$this->attachment[0][4]}\"";
       				if($this->attachment[$this->start][7]) $result.="; start=\"<{$this->attachment[0][7]}>\"";
       				}
       return $result.self::$LE.self::$LE;
    }


    /**
     * Attaches all fs, string, and binary attachments to the message.
     * Returns an empty string on failure.
     * @ignore
     * @return string
     */
  protected function attachAll($disposition_type='', $boundary='') {
        // Return text of body
        $mime = array();
        $this->boundary[1] = "----=_NextPart_" .  $this->Id;
        // Add all attachments
        for($i = 0; $i < count($this->attachment); $i++)
        {
            // Check for string attachment

            $string = $this->attachment[$i][0];
			if(is_file($string)) $string=file_get_contents($string);
            $filename    = $this->attachment[$i][1];
        #    $name        = $this->attachment[$i][2];
            $encoding    = $this->attachment[$i][3];
            $type        = $this->attachment[$i][4];
        #    $disposition = $this->attachment[$i][6];
            $cid         = $this->attachment[$i][7];

            $mime[] = sprintf("--%s%s", $this->boundary[1], self::$LE);
            if($cid) $mime[] = sprintf("Content-ID: <%s>%s",  $cid, self::$LE);
            	else $mime[] = sprintf("Content-Location: file:///%s%s",$filename, self::$LE);
            $mime[] = sprintf("Content-Transfer-Encoding: %s%s", $encoding, self::$LE);
         	$mime[] = sprintf("Content-Type: %s%s", $type,  self::$LE);

            // Encode as string attachment
         	$mime[] = self::$LE;
            $mime[] = $this->EncodeString($string, $encoding);
            $mime[] = self::$LE.self::$LE;


        }

        $mime[] = sprintf("--%s--%s", $this->boundary[1], self::$LE);

        return join("", $mime);
    }

    /**
     * @ignore
     */
     public function prepare(){
       $this->SetMessageType();
   }


   /**
    * @ignore
    */
   public function get_boundary(){
   	 	return str_replace('----=','',$this->boundary[1]);
   }

    
   /**
    * @ignore
    */public function SetAttachments($attachments) {
    	$this->attachment=$attachments;
   		return $this;
   }
    /**
    * @ignore
    */
    public function __toString(){
       $this->SetMessageType();
       return $this->CreateHeader().$this->CreateBody();
    }
}