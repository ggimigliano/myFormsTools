<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQInputMask.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\PckmyFields;



use Gimi\myFormsTools\PckmyJQuery\myJQueryMyField;
                                                


/**
 *
 * Permette di applicare una maschera di caratteri ammissibili sui campi test
 * @see https://github.com/RobinHerbots/Inputmask
 *
 * <code>
 * $x=new myJQInputMask("#".$tx->get_id());
 * $x->set_mask("~*.99 #aaa 999", //maschera
 * 					'',  //carattere che compare come segnaposto
 * 					array('~'=>"+-",  //~ puo' essere '+' o '-'
 * 						  '#'=>'*%')  //# puo' essere solo '*' o '%'
 * 					'function (){alert("digitato: "+self.val())}'
 * 					);
 *
 * </code>
 */
	
class myJQInputMask extends myJQueryMyField {
    /**
     * @ignore
     */	protected $maschera,$pars=array(),$done_html=array();

    
/**
     * @ignore
     */static protected function init( &$widget) {
            $widget='inputmask';
    }

     public function set_istance_defaults() {
        parent::set_istance_defaults();
        self::add_src(self::$percorsoMyForm."jquery/inputmask/inputmask.min.js");
        self::add_src(self::$percorsoMyForm."jquery/inputmask/jquery.inputmask.min.js");
        self::add_src(self::$percorsoMyForm."jquery/inputmask/inputmask.extensions.min.js");
        self::add_src(self::$percorsoMyForm."jquery/inputmask/inputmask.date.extensions.min.js");
        self::add_src(self::$percorsoMyForm."jquery/inputmask/inputmask.numeric.extensions.min.js");
        self::add_src(self::$percorsoMyForm."jquery/inputmask/inputmask.phone.extensions.min.js");
        self::add_src(self::$percorsoMyForm."jquery/inputmask/inputmask.regex.extensions.min.js");
    }
        


    /**
     * Metodo che permette di impostare la maschera di input utilizzando i seguenti caratteri:
     *     a - Rappresenta un carattere alfabetico (A-Z,a-z)
     * 	   9 - Rappresenta un carattere numerico (0-9)
     *     * - Rappresenta un carattere alfanumerico (A-Z,a-z,0-9)
     *
     *
     * @param string $maschera stringa formattata utilizzano i caratteri sopra indicati
     * @param string $placeholder carattere da utilizzare per visualizzare la posizione) default: "_"
     * @param array $definizione permette di definire mascheramenti posizionali ulteriori ad esempio se vogliamo che in una determinata posizione si possa digitare solamente uno o piu' tipi di caratteri nel formato array(carattere speciale=>carattere/i accettati
     * @param string $oncomplete funzione js anonima da attivare sull'evento di completamento della maschera la variabile self corrisponde al nodo jquery del campo "mascherato"
     * <code>
     * $x=new myJQInputMask("#".$tx->get_id());
     * $x->set_mask("~*.99 #aaa 999", //maschera
     * 					'_',  //carattere che compare come segnaposto
     * 					array('~'=>"+-",  //~ puo' essere '+' o '-'
     * 						  '#'=>'*%')  //# puo' essere solo '*' o '%'
     * 					'function (){alert("digitato: "+self.val())}'
     * 					);
     *
     * </code>
     *
     */
    public function set_mask($maschera,
                            $placeholder="",
                            $definizione=array(),
                            $oncomplete=null){
        
         $def=array();
         foreach ($definizione as $k=>&$v) 
                        {  if(!is_array($v)) $v=array('validator'=>"'[$v]'");
                           $def["'$k'"]=$v;
                        }
         
         if($def) $this->pars['definitions']=$def;
         $this->maschera=$maschera;
         if($placeholder) $this->pars['placeholder']=$placeholder;
         $this->set_oncomplete($oncomplete);
    }

    /**
     * @ignore
     */
     public function __set($a,$b){
        $this->pars[$a]=$b;
    }
   
    /**
     * 
     * @ignore
     */
    function &__get($attr){
        return $this->pars[$attr];
    }
    

    
    /**
     * @ignore
     */
    protected function build_options() {
        if(!$this->maschera) return false;
        $this->pars['mask']=$this->maschera;
        return $this->quote($this->pars);
    }
    

    /**
     * Imposta il codice js da avviare al completament
     * @param string $oncomplete
     * @param boolean $aggiungi se true si accoda al codice precedente, altrimenti lo sostituisce
     */
    public function set_oncomplete($oncomplete,$aggiungi=false){
        if(!$aggiungi || !$this->pars['completed']) 
                                     $this->pars['oncomplete']=($oncomplete?"function(){var self=this;($oncomplete)()}":'function(){}');
                elseif($oncomplete)  $this->pars['oncomplete']="function(){var self=this;({$this->pars['completed']})(); ($oncomplete)(); }";
        return $this;
    }
}