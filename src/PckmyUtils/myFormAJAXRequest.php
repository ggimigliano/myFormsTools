<?php
/**
 * Contains Gimi\myFormsTools\PckmyUtils\myFormAJAXRequest.
 */

namespace Gimi\myFormsTools\PckmyUtils;


use Gimi\myFormsTools\myCSS;
use Gimi\myFormsTools\PckmyFields\myField;
use Gimi\myFormsTools\PckmyJQuery\myJQueryUI;
use Gimi\myFormsTools\PckmyJQuery\Pckgeneric\myJQDialog;

                       


/** @ignore*/
	
class myFormAJAXRequest extends myJQDialog {
	protected $attesa="",$azioni=array(),$JQvars=array(),$onBeforeSubmit="function(){return true} ";
    /**
     * @see http://www.chimply.com/Generator#classic-spinner
     * @param string $html
     */
     public function set_html_attesa($html){$this->attesa=$html;}
    
    /**
     * Restituisce il nome di una var javascript che se false disabilita l'uso di ajax
     * @return string
     */
     public static function get_disable_varname(){
        return 'noMyFormAjax';
    }
     public static function isAJAXCall(){
        return (key_exists('HTTP_X_REQUESTED_WITH',$_SERVER) && $_SERVER['HTTP_X_REQUESTED_WITH']=="XMLHttpRequest") ;
    }
    
     public static function send_message($message,$code,$utf8encode=null,$isLocked=false){
        if(static::isAJAXCall())
			        { header("myContent-Type: ".($isLocked?'Locked':'Unlocked'));
			          if($code) header("myContent-Code: $code");
			          if(is_array($message)) { header("Content-Type: application/json; charset=utf-8");
			                                   die(json_encode($message));
			                                 }
			        
			        if($utf8encode  ||  myField::get_charset()!='UTF-8' ) $message=myCharset::utf8_encode($message);
			        
			        //  header("Content-Length: ".strlen($message));
			        if($utf8encode) header("Content-Type: text/html; charset=utf-8");
			                   else header("Content-Type: text/html; charset=iso-8859-1");
			        die($message);
			        }
    }
    
     public static function send_locked_message($message,$utf8encode=null){
        if($utf8encode===null)  $utf8encode=myField::get_charset()!='UTF-8';
        static::send_message($message, '',$utf8encode,true);
    }
    
     public static function send_unlocked_message($message,$utf8encode=null){
        if($utf8encode===null)  $utf8encode=myField::get_charset()!='UTF-8';
        static::send_message($message, '',$utf8encode,false);
    }
    
     public function set_azioni_codice($code,$js,$chiudiDialogAttesa=true){
        $this->azioni[$code]=($chiudiDialogAttesa?"if(!data) ajax.closeDialog();":'').$js;
        return $this;
    }
    
     public function get_dialog_VarName(){
        return $this->myJQVarName();
    }
    
    
    /**
     *  @param string $options funzione JS ch riceve in input il form se ristitusce false il submit fallisce altrimenti prosegue
     */
    public function set_onBeforeSubmit($fun){
    	$this->onBeforeSubmit=$fun;
    	return $this;
    }
    
    public function prepara_codice(){
    	 
        if(!$this->attesa)
             { if(strtolower(myJQueryUI::get_tema())=='redmond') $tema='redmond';
                                                            else $tema='base';
               $this->attesa="<div style='text-align:center'><img src='/".myField::get_MyFormsPath()."icone/spinner/$tema.gif' alt='Attendere prego...' style='width:50%' /></div>";
            }
        $this->modal=true;
        $this->autoOpen=false;
        $this->resizable=false;  
        $formId=$this->get_id();
        
      
        $id='X'.spl_object_hash($this);
        $this->set_id("#$id");
       
        $this->set_html($this->attesa);
        
        $this->closeOnEscape=true;
       
        $extra='';
        foreach ($this->azioni as $k=>$v) $extra.="if(code=='$k') { $v }\n ";
                                                             
        $this->add_code("
            {$id}_start=false;
 		    {$this->JQvar()}('{$formId} input[type=submit],{$formId} input[type=image]').click(function(){  {$this->JQvar()}('#{$id}_Action').remove(); {$this->JQvar()}('{$formId}').append(\"<input type='hidden' id='{$id}_Action' name='\"+{$this->JQvar()}(this).prop('name')+\"' value='\"+{$this->JQvar()}(this).val()+\"' />\");});
            {$this->jqvar()}('{$formId}').submit(
                    function(event) {
                       try{
                          if(".self::get_disable_varname().") return;
                       } catch(ee) {}
                     try{  
                          if({$id}_start) return false; 
						  var theForm = {$this->JQvar()}(this);
						  
						  if( !({$this->onBeforeSubmit})( theForm )) return false;
                          {$id}_start=true;  
						  
                          {$this->myJQVarName()}.get(0).innerHTML=\"{$this->html}\";
                          {$this->myJQVarName()}.dialog('option','title',\"{$this->trasl('Attendere')}\");
                          {$this->JQvar()}('div[aria-describedby={$id}] .ui-dialog-titlebar button').css({'display':'none'});
                          {$this->get_js_show()};
                        
                          var ajax={
                                     type: {$this->JQvar()}(this).attr('method'),
                                     url:  {$this->JQvar()}(this).attr('action'),
                                     contentType: ({$this->jqvar()}('{$formId}').prop('enctype')?{$this->jqvar()}('{$formId}').prop('enctype'):'application/x-www-form-urlencoded'),
                                     data: null,
                                     processData: false,
                                     crossDomain:false,
                                     closeDialog:function(){  {$this->JQvar()}('div[aria-describedby=$id] .ui-dialog-titlebar button').css({'display':'inline'});
                                                              try{  {$this->myJQVarName()}.dialog('close');  } catch (eee){};
                                                           }
                                    }
                         if(ajax['contentType']=='multipart/form-data') { 
																		var finalFormData = new FormData(theForm.get(0));
  																		$('.myUploaderDD').each(function(){
																				var uploaderInstance=$(this).data('myUploader');
		                														var nomeUpload=$('.myUploaderDD').find('input[type=\"file\"]').prop('name');
		                													    var uploadedFiles = uploaderInstance.getFiles();
																				var allFiles = uploaderInstance.getFiles();
																		        var filesToSend = allFiles.filter(file => !file.isServerFile);
																	         	filesToSend.forEach(file => { finalFormData.append(nomeUpload, file); });
																		 		});
																        
												                    	 ajax['type']= 'POST';
												                    	 ajax['data']= finalFormData;
												                    	 ajax['processData']= false; // Evita che jQuery elabori il FormData
												                    	 ajax['contentType']= false; // Non impostare l'header Content-Type, lo fa il browser per FormData
												                    	 ajax['enctype']= 'multipart/form-data';
																		}
		                                                           else { ajax['data']=theForm.serialize();
		                                                                  ajax['contentType']='application/x-www-form-urlencoded;charset=UTF-8';
		                                                                 }
                         event.preventDefault();
                         {$this->JQvar()}.ajax(ajax).done(function(data,mx,jxhr){
                                                              
                                                                if(jxhr.getResponseHeader('myContent-Type')=='Unlocked') 
                                                                              {
                                                                                {$this->JQvar()}('div[aria-describedby=$id] .ui-dialog-titlebar button').css({'display':'inline'});
                                                                                {$this->JQvar()}('div[aria-describedby=$id]').click(function(){ {$this->myJQVarName()}.dialog('close'); });
                                                                              }
                                                                if(typeof data=='string') data={'js':'','html':data};
                                                                if(!data.html)  {$this->myJQVarName()}.dialog('close');
                                                                          else  {    
                                                                                {$this->myJQVarName()}.dialog('option','title',\"{$this->trasl('Attenzione')}\");
                                                                                {$this->myJQVarName()}.get(0).innerHTML=data.html;
                                                                                }

                                                                var code=jxhr.getResponseHeader('myContent-Code');
                                                                try{
                                                                     $extra;
                                                                    }catch(e1) {}                                                               
                                                               
                                                                {$id}_start=false;
                                                                })
                                                    .fail(function( jqXHR, textStatus, errorThrown ){
                                                               if(jqXHR.statusCode()==500){
                                                                                {$this->JQvar()}('div[aria-describedby=$id] .ui-dialog-titlebar button').css({'display':'inline'});
                                                                                {$this->JQvar()}('div[aria-describedby=$id]').click(function(){{$this->myJQVarName()}.dialog('close'); });
                                                                                {$this->myJQVarName()}.dialog('option','title',\"{$this->trasl('Errore')}\");
                                                                                if(ajax['contentType'])  {$this->myJQVarName()}.get(0).innerHTML='".($this->trasl('Errore interno, prego riprovare in un altro momento'))."';
                                                                                                    else {$this->myJQVarName()}.get(0).innerHTML='".($this->trasl('Errore interno, verificare che la dimensione dei file caricati non ecceda il limite massimo consentito'))."'+'<br>'+textStatus;
                                                                             }
                                                                {$id}_start=false;
                                                                });
                         return false;
                         } catch (err) { return false;}
                   }
                );
                
                 ".myCSS::get_css_jscode("div.ui-dialog {position:fixed;}",true));
        parent::prepara_codice();                                                        
    }
    
    private function trasl($x){
        $f=new myField('');
        $trasl=$f->get_dizionario()?$f->get_dizionario()->trasl($x):$x;
        if(myField::get_charset()!='UTF-8') $trasl=myCharset::utf8_encode($trasl);
        return $trasl;
    }
    
    
    
    
}