<?php

/**
 * Contains Gimi\myFormsTools\PckmyUtils\myMimeParser.
 */

namespace Gimi\myFormsTools\PckmyUtils;



/**
	 *  Questa classe permette decomposizione di una string contenente una mail multipart nei suoi elementi originari
	 *  
	 */
	
class myMimeParser {
		
		 public function parseString($string,$cartella,$clean=true) {
			return $this->myparse('',$string,$cartella,$clean);
		}
				
		 public function parseFile($file,$cartella,$clean=true) {
			return $this->myparse($file,'',$cartella,$clean);
		}
			
		
		
		
		/**
		 * @ignore
		 */
		private function myparse($file,$string,$cartella,$clean=true){
				$mime=new \mime_parser_class;
				/*
				 * Set to 0 for parsing a single message file
				* Set to 1 for parsing multiple messages in a single file in the mbox format
				*/
				$mime->mbox = 1;
				/*
				 * Set to 0 for not decoding the message bodies
				*/
				$mime->decode_bodies = 1;
				
				/*
				 * Set to 0 to make syntax errors make the decoding fail
				*/
				$decoded=null;
				$mime->ignore_syntax_errors = 1;
				$array=array("SkipBody"=>1,'SaveBody'=>$cartella);
				if($file) $array['File']=$file;
					elseif($string) $array['Data']=$string;
				$esito=$mime->Decode($array,$decoded);
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