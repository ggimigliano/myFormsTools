<?php
    set_time_limit(0);
	
	class myParser {
			 public function parse($file,$cartella,$clean=true) {
					require_once(dirname(__FILE__).'../myImap/mime_parser.php');
					$mime=new mime_parser_class;
					/*
	 				 * Set to 0 for parsing a single message file
	 				 * Set to 1 for parsing multiple messages in a single file in the mbox format
	 				 */
					$mime->mbox = 0;
					/*
	 				 * Set to 0 for not decoding the message bodies
	 				 */
					$mime->decode_bodies = 1;

				    /*
	 				 * Set to 0 to make syntax errors make the decoding fail
	 			  	 */
					$mime->ignore_syntax_errors = 1;
					$decoded=array();
					$esito=$mime->Decode(array('File'=>$file,"SkipBody"=>0,'SaveBody'=>$cartella),$decoded);
					unset($mime);
					if (!$esito) return $esito;
					$parti=$this->rinominaAttach($decoded,$cartella,$clean);
					$parti['Headers']=$decoded[0]['Headers'];
					return $parti;
			}
			
			
			private function rinominaAttach($parti,$cartella,$clean=true) {
				$out=array();
				if (!count($parti)) $out;
				foreach ($parti as $parte) {
											$out+=$this->rinominaAttach($parte['Parts'],$cartella,$clean);
											if ($parte['BodyLength']>0){
												if ($parte['FileName'] && $parte['BodyFile']) @rename($parte['BodyFile'],"$cartella/$parte[FileName]");
																						else $out['Contents'][]=@file_get_contents($parte['BodyFile']);
											}
											if ($clean) @unlink($parte['BodyFile']);
											if ($parte['FileName']) $out['Files'][]=$parte['FileName'];
										   }
				return $out;										   
			}
		
	}
	
	
?>
