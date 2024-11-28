<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myEditor.
 */

namespace Gimi\myFormsTools\PckmyFields;

use Gimi\myFormsTools\myCSS;
use Gimi\myFormsTools\PckmyJQuery\myJQuery;
use Gimi\myFormsTools\PckmyJQuery\myJQueryUI;
use Gimi\myFormsTools\PckmyPlugins\myAjaxFileManagerPlugin;
 
class myEditor  extends myText {
/** @ignore */
    protected $tinymcepar=array(),$EstendoMyText=true, $width,$height,$css,$stili,$ajaxFileManager,$toolbar,$plugins=array(),$embedded_img=false,
          $skin='base',$rimuovi_spazi_doppi=false,
		   $vietati=',link,submit,button,input,textarea,select,object,param,body,script,applet,frame,iframe,html,head,title,meta,form,!doctype,!--,link,var', //DEVE INIZIARE CON VIRGOLA
		   $disabilitati,$modalita,$cleanup=array('cleanup'=>'true','cleanup_on_startup'=>'false','verify_html'=>'true');
            





	/**
	  * E' prevista una toolbar con questi elementi:
	  *
	  *	 bold, italic,underline,strikethrough,alignleft,aligncenter,alignright,alignjustify,
	  *  bullist,numlist,outdent,indent,cut,copy,paste,undo,redo,link,unlink,image,
	  *  cleanup,hr,removeformat,subscript,superscript,forecolor,backcolor,charmap,visualaid,pastetext,
	  *  pasteword,searchreplace,anchor,insertdate,inserttime,forecolor,fontselect,fontsizeselect,fullscreen,print,tablecontrols
	  *
	  *
  	  * 
	  * @param	 string $nome E' il nome del campo
	  * @param	 string $valore Valore da assegnare come default
	  */
		   public function __construct($nome,$valore='') {
            	myField::__construct($nome,$valore);
				$this->set_MyType('MyEditor');
				$this->set_attributo('id',$this->attributi['id'].'_editor');
				$this->toolbar[0]="bold,italic,underline,strikethrough,separator,alignleft,aligncenter,alignright,alignfull,separator,styleselect,formatselect,fontselect,fontsizeselect,cleanup";
				$this->toolbar[1]="selectall,cut,copy,pastetext,pasteword,paste,separator,searchreplace,separator,bullist,numlist,separator,outdent,indent,separator,undo,redo,separator,link,unlink,anchor,image,separator,forecolor,separator,fullscreen"; //code;
				$this->toolbar[2]="table,hr,removeformat,visualaid,separator,subscript,superscript,separator,charmap,separator,insertdate,inserttime";
				$this->disabilitati=array();
				$this->modalita='advanced';
				$this->plugins=explode(',',"advlist,autolink,autosave,link,myimage,lists,charmap,print,preview,hr,anchor,pagebreak,spellchecker,searchreplace,wordcount,visualblocks,visualchars,code,fullscreen,insertdatetime,media,nonbreaking,table,contextmenu,directionality,emoticons,template,textcolor,paste,fullpage,textcolor,colorpicker,textpattern");
		  }
		  
		  
		  /**
		   * @ignore
		   */
		   public function set_value($valore) {   
		      $valore=$this->rimuovi_spazi_doppi($valore);
		      $this->attributi['value']=$this->striptags(stripslashes(trim((string) $valore)));
		      return $this;
		  }
		  

			/**
			 * @ignore
			 */
		  protected function clean_accessibilita($valore){
		  		$valore=preg_replace('@(<.+ style[\s]*=[\s]*".*)font-size[\s]*:[\s]*[0-9]+[\s]*(pt|px)(.*\">)@sUi','\1\3',$valore);
				$valore=preg_replace('@(<.+ style[\s]*=[\s]*".*font.*:[\s]*)([0-9]+[\s]*(pt|px))(.*\">)@sUi','\1\4',$valore);
				$valore=preg_replace('@(<.+ style[\s]*=[\s]*".*)background.*:[ ^;^"]+(.*\">)@sUi','\1\4',$valore);
				$valore=preg_replace('@(<.+ )style[\s]*=[\s]*\"[ ;]*\"(>)@sU','\1\2',$valore);
				return $valore;
		  }


		  /**
	        * Setta la larghezza e l'altezza in % o pt
			* @param	string $width
			* @param	string $height
	       */
		   public function set_size($width,$height='') {
					  $this->width=$width;
					  $this->height=$height;
					  return $this;
		  }



		 /**
		   * setta le eventuali toolbar max su 3 righe
		   * riceve un array di elementi
		   *
		   * Es. per avere una toolbar di 2 righe
		   * <code>
		   *  $toolbar[0]="bold,italic,underline,strikethrough,separator,alignleft,aligncenter,alignright,alignfull,separator,styleselect,formatselect,fontselect,fontsizeselect";
		   *  $toolbar[1]="selectall,cut,copy,pastetext,pasteword,separator,searchreplace,spellchecker,separator,bullist,numlist,separator,outdent,indent,separator,undo,redo,separator,link,unlink,anchor,image,cleanup,separator,insertdate,inserttime,separator,forecolor,separator,fullscreen"; //code;
		   *
		   *  $oggetto=new myEditor('campo');
		   *  $oggetto->set_toolbar($toolbar);
		   * </code>
		   *
		   * @param array $toolbar
		   */
		   public function set_toolbar($toolbar) {
			  $this->toolbar=$toolbar;
			  $this->toolbar[]='';$this->toolbar[]='';$this->toolbar[]='';
			  return $this;
		  }


		  /**
	        * Rende invisibile un elemento della toolbar
			* @param	string $nomeElemento	 es 'paste'
	  		*/
		   public function unset_toolbar_element($nomeElemento) {
			$this->disabilitati[]=$nomeElemento;
		  }


		  /**
	* Setta il percorso della cartella Immagini
			* @param	string $percorso  E' il percorso assoluto seguito da / es: /utenti/img/
	  		*/
		   public function set_cartella_img($percorso,$anteprime='') {
		  	$this->ajaxFileManager=new myAjaxFileManagerPlugin($percorso,$anteprime);
		  	$this->ajaxFileManager->ext_img = array( 'jpg', 'jpeg', 'png', 'gif' );
		  	$this->ajaxFileManager->ext_file=array();
		  	$this->ajaxFileManager->ext_video=array();
		  	$this->ajaxFileManager->ext_music=array();
		  	$this->ajaxFileManager->ext_misc=array();
		  	return $this;
		  }


		  /**
		   * Deve essere usato dopo @myEditor::set_cartella_img() e imposta parametri specifici del file manager @see http://www.phpletter.com/
		   * @param array $pars array associativo con i valori delle costanti di configurazione NON PEECEDURE DAL PREFISSO "CONFIG_"
		   */
		   public function set_filemanager_pars(array $pars) {
		  	if($this->ajaxFileManager)
		  			foreach ($pars as $k=>$v)
		  				$this->ajaxFileManager->$k=$v;
		  	return $this;
		  }


		    /**
		   * Imposta parametri specifici del file manager @see http://www.tinymce.com/wiki.php/Configuration
		   * @param array $pars array associativo con i valori selle costanti di configurazione NON PEECEDURE DAL PREFISSO "CONFIG_"
		   */
		   public function set_tinymce_pars($pars) {
		     foreach ($pars as $k=>$v)
		  	        $this->tinymcepar[$k]=$v;
		  	return $this;
		  }


		  /**
	        * Restituisce il nome del tipo del campo
	  		*
			* @return string
	  		*/
			 public function get_MyType() {return $this->MyType; }


		  /** @ignore*/
			 public function set_MyType($nome) {  $nome[0]=strtolower($nome[0]);$this->MyType=$nome; return $this;}


		  /**
	       * Non attiva in questa classe */
	 		 public function set_readonly($var = true){return $this;}

		  /**
	       * Non attiva in questa classe */
		     public function set_autotab () {return $this;}


		  /**
	       * Non attiva in questa classe */
	 		 public function set_disabled($var = true){return $this;}


		  /**
	       * Setta gli stili CSS consentiti
			* @param	string $fileCSS E' il percorso assoluto al file css le classi ammesse
			* @param	array $stili E' un array con l'elenco delle classi ammesse
	  		*/
		  	 public function set_stili($fileCSS,$stili) {
					 $this->css=$fileCSS;
					 if (is_array($stili)) $this->stili=$stili;
					 return $this;
		  }


		  /**
	       * Setta la modalità della toolbar
			* @param	'myadvanced'|'advanced'|'simple' $modalita
			 */
		  	 public function set_modalita($quale='advanced') {
					 if (preg_match('/advanced|simple/',$quale)) $this->modalita=strtolower($quale);
					 return $this;
		  }

		   public function get_plugins(){
		      return implode(',',$this->plugins);
		  }
		  
		   public function add_plugin($plugin){
		      $this->plugins[]=$plugin;
		      return $this;
		  }

		  /**
			* Setta i tag non ammessi e/o eventuali loro parametri
			*
			* @param string $stringa
			*
			* es. 'iframe,table[bgcolor|background]' esclude il tag iframe ed i parametri bgcolor e background del tag table
			* In ogni caso vengono eliminati 'submit,button,input,textarea,select,object,param,body,script,applet,frame,iframe,html,head
			*/
		   public function set_TagVietati($stringa){ $this->vietati.=','.$stringa; return $this;}


		  //L'eliminazione dei tag avviene tramite js
		  function &striptags($testo){
		  	$txtArea=new myTextArea('');
		  	$out= $txtArea->striptags($testo,$this->vietati);
		  	return $out;
		  }


		  /**
		   * @deprecated
		   */
		   public function set_puliziahtml($Abilitata,$AncheInCaricamento=true) {
		  			 if ($Abilitata) {$this->cleanup=array('verify_html'=>'true','cleanup'=>'true');
															$AncheInCaricamento=false;
									 }
								else $this->cleanup=array('verify_html'=>'false','cleanup'=>'false');

					 $this->cleanup['cleanup_on_startup']=($AncheInCaricamento?'true':'false');
					 return $this;
		  //		  $this->cleanup['add_form_submit_trigger']=($SuInvio?'true':'false');
		  }

        
		  
		   public function js() {
					 $v=$this->disabilitati;
					 $stili=$toolbars =null;
				     if ($this->modalita=='simple') $v=array_merge($v,array('bullist','numlist'));
						 elseif ($this->modalita=='advanced')
								 foreach ($this->toolbar as $i=>$scelta)
							 			{
							 			 $scelta=preg_replace('@justify([a-z]+)@','align\1',$scelta);
							 			 $scelta=str_ireplace(array('tablecontrols,','alignfull,','search','sub,','sup,'), array('table,','alignjustify,','searchreplace,','subscript,','superscript,'), $scelta);
							 			 $v=array_merge($v,explode(',',$scelta));
										 $i++;
										 
										 $scelta=str_ireplace(array('separator',','), array(' | ',' '), $scelta);
									 	 $toolbars.="toolbar$i:\"$scelta\",\n";
							 		 	}
					 $v=array_flip($v);
					 
					 if (!isset($v['table'])) $this->vietati=',table,thead,td,tfoot,th,tr'.$this->vietati;
					 if (!isset($v['numlist'])) $this->vietati=',ol,li'.$this->vietati;
					 if (!isset($v['bullist'])) $this->vietati=',ul,li'.$this->vietati;
					 if (!isset($v['link'])) $this->vietati=',a'.$this->vietati;
					 if (!isset($v['formatselect'])) $this->vietati=',h1,h2,h3,h4,h5,h6'.$this->vietati;
					 if (!isset($v['fontselect'])) $this->vietati=',font[face]'.$this->vietati;
					 if (!isset($v['fontsizeselect'])) $this->vietati=',font[size]'.$this->vietati;


					 if ($this->ajaxFileManager)
					 				{ /*
					 						$imgScript=' dir_image: "'.$this->imgDir.'",
													 root_image: "'.str_replace('//','/',$this->imgDir).'",
											 	   	external_image_list_url : "/'.self::get_MyFormsPath().'js/tiny_mce/plugins/myadvimage/listaImg.php?dir='.$this->imgDir.'&realdir='.$this->imgRealDir.'&tt='.self::unid().'",';
										*/
					 				 $this->tinymcepar['external_plugins']["filemanager"]=$this->ajaxFileManager->get_url_plugin();
					 				 $this->tinymcepar['filemanager_title']=$this->trasl('File Manager');
					 				 $this->tinymcepar['filemanager_access_key']='&'.$this->ajaxFileManager->build_params();
					 				 $this->tinymcepar['external_filemanager_path']=dirname($this->ajaxFileManager->get_url_plugin()).'/';
								/*      $this->tinymcepar['file_browser_callback']='function (field_name, url, type, win) {
													
													 var view = "detail";
													 switch (type) {
																 case "image": view = "thumbnail"; break;
																 case "media": case "flash": case "file":break;
																 default: return false;
																 }
                                                	 tinyMCE.activeEditor.windowManager.open
												 		(
										 					{
															 url:  "'.$this->ajaxFileManager->get_url().'&field=" + field_name + "&type=" + type+"&view=" + view,
															 width: 782,
															 height: 540, 
															 inline : "yes",
															 close_previous : "yes"
															 },
														   {
															 window : win,
															 input : field_name
														   }
														);
												
									 	}';*/
									 }
							else{if(!$this->embedded_img) 
							         {$this->disabilitati[]='image';
									  $this->vietati=',img'.$this->vietati;
									 }
							     $toolbars=str_replace('image','',$toolbars );
							    }

					 if ($this->width)  $dimensioni='width : "'.$this->width.'",';
					 if ($this->height) $dimensioni.='height : "'.$this->height.'",';
					 if ($this->css)	$stili='content_css : "'.$this->css.'",';
					 if ($this->stili) {$styles=array();
										foreach ($this->stili as $s) $styles[]="$s=$s";
										$stili.='theme_advanced_styles : "'.implode(';',$styles).'", // Theme specific setting CSS classes';
										 }
										 
					$standard='      script_url:"/'.self::get_MyFormsPath().'/js/tinymce/mytinymce.gzip.php?pathCache='.base64_encode(__MYFORM_DATACACHE__).'",
					 				 language:"it",	
					 		         theme: "modern",
					 			 	 skin: "'.$this->skin.'",
					 			 	 element_format: "xhtml",
					 			 	 schema: "html4",
					 			 	 fontsize_formats: "9pt 10pt 11pt 12pt 13pt 14pt",
					 		
								 	 plugins:   [ "'.self::get_plugins().'"],
					 			 	 menubar: false,
        						 	 toolbar_items_size: "small",
					 			
                                     selector: "textarea",
									 browser_spellcheck: true ,
                                     contextmenu: false,

									 debug: false,
									 mode: "exact",
									 elements : "'.$this->attributi['id'].'",
									 event_elements : "",
									 myformpath: "'.self::get_MyFormsPath().'",
									 accessibility_warnings: true,
									 object_resizing: "img",
									 visual:true,    
									 auto_resize: false,
									 add_unload_trigger: false,

									 fix_list_elements:true,
									 fix_content_duplication: true,
									 trim_span_elements: false,
									 theme_advanced_resizing: true,
									 
									 table_inline_editing: false,
                                     table_advtab: false,
                                     table_cell_advtab: false,
									 table_row_advtab: false,    
									    									    
									 keep_styles: false,
									 verify_html: true,
																		
									 convert_fonts_to_spans: true,
									 convert_newlines_to_brs: false,
									    
									 paste_auto_cleanup_on_paste: true,
									 paste_convert_headers_to_strong: true,
                                     
									 paste_word_valid_elements: "a[href|target|name|title],div,p/div,br,ul,ol,li,h1,h2,h3,h4,h5,h6,table[border|cellpadding|cellspacing|summary|width],tbody,tfoot,tr,td[width|rowspan|colspan|scope],th[width|rowspan|colspan|scope],big,small,bdo,blockquote,address,strong/b,em/i,u,sup,sub",
									 paste_strip_class_attributes: "mso",

									 paste_retain_style_properties: "none",
 									 paste_webkit_styles: "none",
									 paste_remove_styles_if_webkit: true,   
									    									
									 paste_data_images: '.($this->embedded_img?'true':'false').',
									 paste_merge_formats: true,
													
									 force_p_newlines: true,
									 force_br_newlines: false,
									 forced_root_block: "div",
									 force_hex_style_colors: true,

                                     invalid_styles: "background background-color background-image",
									 inline_styles: true,
									    
									 link_title: true,
								
                                     cleanup_on_startup:true,
									 image_description: true,
									 image_advtab: true,
											
									 autosave_ask_before_unload: false,		
									 relative_urls: false,
        						  
                                     
									 protect: [
                                                    /\<\/?(if|endif)\>/g, 
                                                    /\<xsl\:[^>]+\>/g, 
                                                    /<\?php.*?\?'.'>/g,
									                /<\?script[^>]*>[^>]+<\/script>/g
                                                ],
									';


					 if ($this->modalita=='simple')
						 $defaults='
					 			var mce_defaults={
						         invalid_styles: "background",
								 invalid_elements: "submit,button,input,textarea,select,object,param,body,script,applet,frame,iframe,table,tr,td,th,thead,tfoot,a'.$this->vietati.'",
								 '."$standard\n$dimensioni\n".'
								 theme: "simple"
								 }';

					 else $defaults='
								 var mce_defaults={
								 theme_advanced_disable: "'.implode(',',$this->disabilitati).'",
								 theme_advanced_blockformats: "p,h1,h2,h3,h4,h5,h6",
								 '.$toolbars.'
								 		
								 theme_advanced_toolbar_location: "top",
								 theme_advanced_toolbar_align: "left",
							
								 invalid_elements: "body,script,applet,frame,iframe,table[bgcolor|background],tr[bgcolor|background],td[bgcolor|background],th[bgcolor|background]'.$this->vietati.'",
								 theme_advanced_statusbar_location: "bottom",
								 // extended_valid_elements: "hr[class|width|size|noshade],span[class|align|style]",
								 '."$standard\n$stili\n$dimensioni\n".'
								 theme_advanced_resize_horizontal: true,
								 theme_advanced_resizing: true
								 }';

		  			 
		 foreach ($this->tinymcepar as $k=>$v) 
		              {
		                if(is_string($v) && preg_match('@^(true|false|[0-9]+|([0-9]+\.[0.9]{1,2}))$@Ui', $v)) 
		                           $defaults.="\n;mce_defaults[\"{$k}\"]=$v;";
		                     else  $defaults.="\n;mce_defaults[\"{$k}\"]=".myJQuery::quote($v).";";
		              }
		 return $defaults;
		  }

		  
		  /**
		   * 
		   * @param boolean $stato
		   * @return myEditor
		   */
		   public function set_img_embedded($stato=true){
		      $this->embedded_img=$stato;
		      return $this;
		  }


		  /**
	* Restituisce il campo in html pronto per la visualizzazione
		  *  @return string
		  */
		   public function get_Html () {
		  	         $get_html=isset($this->Metodo_ridefinito['get_Html']['metodo'])?$this->Metodo_ridefinito['get_Html']['metodo']:null; if ($get_html!='') return $this->$get_html(isset($this->Metodo_ridefinito['get_Html']['parametri'])?$this->Metodo_ridefinito['get_Html']['parametri']:null);
					 if ($this->width)	 $dimensioni=" width:".$this->width. (preg_match('@px$@', $this->width)?";":"px;");
					 if ($this->height)  $dimensioni.=" height:".$this->height.(preg_match('@px$@', $this->height)?";":"px;");
					 $this->unset_attributo('size');
					 $tema=myJQueryUI::get_tema();
					 if($tema=='redmond') $this->skin='redmond';
					 
                     $jq=new myJQuery("#{$this->get_id()}");
					 if (!isset($this->myFields['static']['js_src'][get_class($this)])) {
					 			  $js=$jq.'
					 	                <script type="text/javascript" src="/'.self::get_MyFormsPath().'js/tinymce/jquery.tinymce.min.js"></script>
					 			        <script  type="text/javascript">'.$this->js().'</script>';
								  $this->myFields['static']['js_src'][get_class($this)]=1;
								}
					$jq->add_code("{$jq->JQid()}.tinymce(mce_defaults);
					                document.getElementById('{$this->get_id()}').style.display='block';
					              ".myCSS::get_css_jscode('.mce-window-head{height:20px}
					                                       .mce-ico{font-family: tinymce !important}',true));
					return $this->jsAutotab()."
				 						<textarea {$this->stringa_attributi(array('value','opzioni','maxlength','size'))} style='$dimensioni'>".$this->get_value()."</textarea>
				 						<script type='text/javascript'>
				 						     document.getElementById('{$this->get_id()}').style.display='none';
				 						</script>".$js.$jq;

	 }
	 
	 
	 function &get_value_DB(){
	     return $this->attributi['value'];
	 }
	 
	 function &get_value(){
	     return $this->attributi['value'];
	 }
}