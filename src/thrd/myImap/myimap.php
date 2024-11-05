<?php
use Gimi\myFormsTools\PckmyUtils\MIMEDecode;

if(!class_exists('IMAPMAIL',false)) include_once(dirname(__FILE__)."/imap.inc.php");
	
class myImap extends IMAPMAIL {
		public $port=143;
		public $state="DISCONNECTED";

		 public function __construct($host='',$port='') {
			if($host) $this->host=$host;
			if($port) $this->port=$port;
			$this->must_update=false;
			$this->tag=uniqid("HKC");
			
		}	
		
		 
		 public function connect($user='',$password='',$mailBox='INBOX'){
			$errore=error_reporting(0);
			if(!$user) $user=$this->user;;
			if(!$password) $password=$this->password;
			if($this->open($this->host,$this->port) &&
					$this->login($user,$password)) $esito=true;
			if($mailBox!==false) $esito= $this->open_mailbox($mailBox);
			error_reporting($errore);
			return $esito;	
		}
		
		
		function  fetch_mail($msg_set,$msg_data_name)
		{
			if($this->state!="SELECTED")
			{
				$this->error= "Error : No mail box is selected.!<br>";
				return false;
			}
			$msg_set =trim((string) $msg_set);
			$msg_data_name =trim((string) $msg_data_name);
			if($this->put_line($this->tag." FETCH $msg_set ($msg_data_name)"))
			{
				$response=$this->get_server_responce();
				if(substr($response,strpos($response,"$this->tag ")+strlen($this->tag)+1,2)!="OK")
				{
					$this->error= "Error : $response !<br>";
					return false;
				}
			}
			else
			{
				$this->error= "Error : Could not send User request. <br>";
				return false;
			}
			return $response;
		}
		
		
		
		
		 public function get_message_header($msg='',$uid='',$structured=false){
			if(!$structured) return parent::get_message_header($msg,$uid);
		
			$header=explode("\r\n",parent::get_message_header($msg,$uid));
			foreach ($header as $row) {
					$parti=explode(': ',$row,2);
					if(count($parti)==2) 
							{
							 $k=trim((string) $parti[0]);
							 $nheader[$k]=$parti[1];	
							}
						else $nheader[$k].=	"\r\n".$parti[0];
					}
			return $nheader;
			
		}
		
		
		 public function get_server_responce()
		{
			while(1)
			{
				//$response.="\r\n".$this->get_line();
				$ln=$this->get_line();
				$response.="\r\n".$ln;
		
				//if(substr($response,strpos($response,$this->tag),strlen($this->tag))==$this->tag)
				if(substr($ln,strpos($ln,$this->tag),strlen($this->tag))==$this->tag)
					break;
			}
			return $response;
		}
		
		/*
		function get_mail($msg,$uid=null,$params=array()){
			$params['include_bodies'] = true;
			$params['decode_bodies']  = true;
			$params['decode_headers'] = true;
			$mp=new Mail_mimeDecode(trim(parent::get_message_header($msg,$uid))."\r\n\r\n".
									trim(parent::get_message_body($msg,$uid)));
			return $mp->decode($params);
		}
		*/

		 public function get_mail($msg,$uid=null,$params=array('include_bodies'=>true,
				'decode_bodies'=>true,
				'decode_headers'=>true)){
				$mp=new MIMEDecode($this->get_message($msg,$uid),"\r\n");
				return $mp->decode($params);
		}
		
		
		
		
		 public function save_message($msg_set,$file)
		{ 
			if($this->state!="SELECTED")
				{
				$this->error= "Error : No mail box is selected.!<br>";
				return false;
				}
			
			$msg_set =trim((string) $msg_set);
			$msg_data_name ='BODY[]';
			if(!$this->put_line($this->tag." FETCH $msg_set ($msg_data_name)"))		
				{
				$this->error= "Error : Could not send User request. <br>";
				return false;
				}
			elseif ($this->get_line() && $f=fopen($file,'w')) 
				{
		   	  	while(true)
					{$response=$this->get_line();
				 	if(substr($response,strpos($response,$this->tag),strlen($this->tag))==$this->tag) 
				 		{
						if(substr($response,strpos($response,"$this->tag ")+strlen($this->tag)+1,2)=="OK")
											 break;
										else {
											  $this->error= "Error : $response !<br>";
											  @fclose($f);
											  @unlink($file);
											  return false;
											 }
						}						 	
				 	   if (fwrite($f,$response)===FALSE || fwrite($f,"\r\n")===FALSE) 
				 							 {
											  $this->error= "Error : impossibile salvare il file $file!<br>";
											  @fclose($f);
											  @unlink($file);
											  return false;
											 }
					}
				fclose($f);
				return true;
				}
			 $this->error= "Error : impossibile creare il file $file, o impossibile aprire mail $msg_set!<br>";	
		}
	}
?>
