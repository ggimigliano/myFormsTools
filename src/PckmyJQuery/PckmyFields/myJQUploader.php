<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQTooltip.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\PckmyFields;



 
use Gimi\myFormsTools\PckmyJQuery\myJQueryMyField; 
use Gimi\myFormsTools\PckmyFields\myField; 
       


/**
 *
 * Imposta drag-drop per un elemento
 *
 */
	
class myJQUploader extends myJQueryMyField {
    /**
     * @ignore
     */
    static protected function init( &$widget) {$widget='uploader';}
    
     public function set_istance_defaults(){
     	parent::add_src('jquery/myJQUtils/myJQUploader.js');
     	parent::add_css('jquery/../css/myJQUploader.css?t='.date('d'));
        parent::set_istance_defaults();
    }
    
    
    private function convertiDimensioneInByte($dimensione) {
    	$dimensione = trim($dimensione);
    	$last = strtolower($dimensione[strlen($dimensione)-1]);
    	$dimensione = (int)$dimensione;
    	switch($last) {
    		case 'g':
    			$dimensione *= 1024;
    		case 'm':
    			$dimensione *= 1024;
    		case 'k':
    			$dimensione *= 1024;
    	}
    	return $dimensione;
    }
   
    
    
     public function get_code($ns='') {
     	$minNFiles=1;
     	$maxNFiles=1;
     	$id=strstr($this->myField->get_id().'[','[',true);
     	if(method_exists($this->myField, 'get_molteplicita') && $this->myField->is_multiplo()) 
     					{
     						$maxNFiles=$this->myField->get_molteplicita()['max'];
     					}
     	$maxNFiles=min($maxNFiles,ini_get('max_file_uploads'));
     	
     	$iniziali=array();
     	foreach ($this->myField->get_info_uploaded() as $k=>$file)
     		if($file['size']>0 && !$file['error'])
     			$iniziali[]=array('name'=>$file['name'],'size'=>$file['size'],'hash'=>$k,'url'=>$this->myField->get_download_url($file));
     	
     	$opzioni=array(
     				'defaultLang'=>$this->myField->get_lingua(), // Imposta la lingua di default in inglese
		     		'uploaderId'=>$id, // Imposta un ID personalizzato
     				'fileInputName'=> strstr($this->myField->get_name().'[','[',true).'[]', // Imposta un nome per l'input dei file
     			    'fileExt'=> $this->myField->get_ext_ammesse()?'.'.implode(',.',$this->myField->get_ext_ammesse()):'',
     			    'fileConstraints'=>[],
     				'path'=>'/'.myField::get_MyFormsPath(),
     				'minNumberOfFiles'=>$minNFiles,
     			    'maxNumberOfFiles'=>$maxNFiles, 
     			    'acceptFileTypes'=>$this->myField->get_ext_ammesse()?'^('.implode('|',$this->myField->get_ext_ammesse()).')$':'.+',
     			    'minFileSize'=>$this->myField->get_min()?$this->myField->get_min():10,
     			    'maxFileSize'=>$this->myField->get_max()?$this->myField->get_max():min($this->myField->convertiDimensioneInByte(ini_get('upload_max_filesize')),$this->myFieldhis->convertiDimensioneInByte(ini_get('post_max_size'))),
     				'notNull'=>$this->myField->get_notnull()?true:false,
    				'initialFiles'=>$iniziali
     				);
    	$this->add_code(" $('#uploader-{$id}').uploader(".self::quote($opzioni).")");
        return parent::get_code($ns);
    }

	
}