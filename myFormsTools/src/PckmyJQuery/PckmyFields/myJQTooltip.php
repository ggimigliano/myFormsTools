<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQTooltip.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\PckmyFields;



use Gimi\myFormsTools\myCSS;
use Gimi\myFormsTools\PckmyJQuery\myJQueryMyField;
                                                


/**
 *
 * Imposta tooltip per un elemento
 *
 */
	
class myJQTooltip extends myJQueryMyField {
    /**
     * @ignore
     */
    static protected function init( &$widget) {$widget='tooltip';}
     public function set_istance_defaults(){
        $this->add_common_code(self::get_add_style(".ui-tooltip{z-index:".(static::$max_z_index+1)." !important;}",true));
        parent::set_istance_defaults();
    }
    
    
   
    
    
     public function get_code($ns='') {
        $open='';
    /*    if(!$this->content) $content=self::JQvar()."( this ).prop('title')";
                    else  { if(strpos($this->content,'function')===0)  $content="( {$this->content} )();";
                                                                  else $content="'".str_replace("'","\\\'",$content)."'";   
                          }
                        
        $this->content=" function() {
					                     fn=$content;
					                     if ( !/ipad|iphone|ipod/i.test(navigator.userAgent.toLowerCase())) fn += \"<div align=right'><a href='#' onclick=\\\"javascript:".self::JQvar()."('.tooltip-trigger').tooltip('close');\\\" class='ipadTooltipHack'>X</a></div>\";
					                     return fn;
					                  }";
					                  */
        if($this->open) $open="({$this->open})(evt,ui);";
        $this->open="function(evt,ui){   $open;
                                         if (/ipad|iphone|ipod/i.test(navigator.userAgent.toLowerCase())) $(evt.target).trigger( 'click' );
                                    }";
        return parent::get_code($ns);
    }

	/**
	 * Imposta l'html del tooltip se non si vuole usare l'attributo title
	 * @param string $html
	 */
	 public function set_html($html){
		$this->set_option('content',str_replace(array("\r","\n","\t")," ",$html));
		return $this;
	}

	 public function set_fumetto($colore='',$sfondo=''){
	    static $colore,$sfondo;
	    if((!$colore || !$sfondo) )
    				   {$m=$pars=array();
    				    preg_match('@http://jqueryui.com/themeroller/(.+)@S',@file_get_contents(__DIR__.'/../jquery/ui/themes/'.self::$tema.'/jquery-ui.min.css'),$m);
    				    if($m) {parse_str($m[1],$pars);
				                if(isset($pars['borderColorDefault'])) $colore='#'.$pars['borderColorDefault'];
				                if(isset($pars['bgColorContent'])) $sfondo='#'.$pars['bgColorContent'];
            				    }
				     }
				     
				     
				     
		  $txt=".ui-tooltip{
                        box-shadow: 0 0 7px $colore !important;
                        -moz-shadow: 0 0 7px $colore !important;
                        -webkit-shadow: 0 0 7px $colore !important;
                     	}
                ";
    	   $old='.arrow {
    					background: url('.self::$percorsoMyForm.'jquery/myJQUtils/toolTipArrow.php?l=33&b=3&c='.str_replace('#','',(string) $colore).'&s='.str_replace('#','',(string) $sfondo).') no-repeat center top;
    					width: 40px;
    					height: 16px;
    			        left:50%;
    					margin-left:-20px;
    					overflow: hidden;
    			        position: absolute;
    			        bottom: -16px;
    					z-index:'.(static::$max_z_index+1).';
    				 }
    			 .arrow.top{ top: -16px; bottom: auto;width: 40px;margin-left:-20px;
    						   background: url('.self::$percorsoMyForm.'jquery/myJQUtils/toolTipArrow.php?l=33&b=3&c='.str_replace('#','',(string) $colore).'&s='.str_replace('#','',(string) $sfondo).') no-repeat center -16px;
    			    }
    			';
    	   
    	    $new=".arrow:after {
    				         background:$sfondo;
    				         border:3px solid $colore;
    						 ".(static::$max_z_index+1).";
    				    	}
    		     .arrow { width: 70px; height: 16px; overflow: hidden;  position: absolute; left: 50%;margin-left: -35px;  bottom: -16px; }
    			 .arrow.top {top: -16px; bottom: auto;}
    			 .arrow.top:after { bottom: -20px;  top: auto;}
    			 .arrow.left {  left: 20%;}
    			 .arrow:after {
    				content: '';
    				position: absolute;
    				left: 20px; top: -20px;
    				width: 25px;  height: 25px;
    		        box-shadow: 6px 5px 9px -9px $colore;
    				-webkit-transform: rotate(45deg);
    		        -moz-transform: rotate(45deg);
    		        -ms-transform: rotate(45deg);
    		        -o-transform: rotate(45deg);
    		        transform: rotate(45deg);
    			  }
    		  ";
                    
      $this->add_code("
          if(  (".self::isFirefoxJQ().">0 && ".self::isFirefoxJQ()."<=6) ||  
               (".self::isMSIEJQ().">0 && ".self::isMSIEJQ()."<=8) ) 
                  {".self::get_add_style(myCSS::minimizza_css($txt.$old),true)."}
            else  {".self::get_add_style(myCSS::minimizza_css($txt.$new),true)."}
          ",$this->widget);
          
      $this->position=array(
                		'my'=> "center bottom-20",
		                'at'=> "center top",
		                'using'=> "function( position, feedback ) {
										{$this->JQvar()}( this ).css( position );
					                    {$this->JQvar()}( '<div>' )
					                        .addClass( 'arrow' )
					                        .addClass( feedback.vertical )
					                        .addClass( feedback.horizontal )
					                        .appendTo( this );
					                }");
      

	}
}