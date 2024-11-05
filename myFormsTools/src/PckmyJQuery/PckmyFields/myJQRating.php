<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQRating.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\PckmyFields;



use Gimi\myFormsTools\PckmyJQuery\myJQuery;
use Gimi\myFormsTools\PckmyJQuery\myJQueryMyField;
use Gimi\myFormsTools\PckmyFields\myMultiCheck;
                                                


/**
 * classe che permette di visualizzare html opzionale al posto dei myRadio, myMultiCheck e mySelect
 * <code>
 *
 * $radio=new myRadio('RADIO',3,array('opz1'=>1, 'opz2'=>2, 'opz3'=>3));
 * $arrayImmagini=array(
 *			     array('<img src="img/cookie-off.png">', //mostra di default
 *				 	 '<img src="img/cookie-half.png">' //mostra su click
 *				 					),
 *		 	     array('<img src="img/0.png">',
 *				 	 '<img src="img/5.png">'),
 *		 	     array('<img src="img/star-off.png">',
 *				 	 '<img src="img/star-on.png">')
 *		);
 *
 *$radio->add_myJQuery(new myJQRating())->set_immagini($arrayImmagini);
 *
 *
 * </code>
 *
 */
	
class myJQRating extends myJQueryMyField {
    /** @ignore **/
    protected  $arrayHtml, $data,$accapo=null,$riempiSX=false,
    $effetti=array('attivo'=>"function(x){x.css({'opacity':1});} ",
        'non_attivo'=>"function(x){x.css({'opacity':0.5});}",
        'riempiSX'=>false,
        'su_accensione'=>"function(x){}",
        'su_spegnimento'=>"function(x){}",
    );
    
    /**
     *
     * @param array $arrayHtml
     * @return myJQRating
     */
     public function set_immagini($arrayHtml) {
        $this->arrayHtml=$arrayHtml;
        return $this;
    }
    
    
    
    
    /**
     * @ignore
     */
    static protected function init( &$widget) {
        $widget='star';
        self::add_src(self::$percorsoMyForm."jquery/rating/jquery.starRating.js");
    }
    
    
    
    /**
     * @ignore
     */
    protected  function construct(){
        static $k;
        $k++;
        $this->costruito=true;
        $this->add_code("{$this->jqvar()}('body').star({$this->crea_dati("{$this->myField->get_id()}_RATING_$k")},'{$this->myField->get_id()}_RATING_$k')");
        if($this->css_font_class) {
            foreach ($this->css_font_class as $class) $css.=$class.self::$css_fonts[get_called_class()]." ";
            $codice=myJQuery::get_add_style($css,true);
            $pred=self::get_common_codes('commonJQUI');
            if(strpos($pred,$codice)===false)  $pred.=";".$codice.";";
            self::add_common_codes($pred,'commonJQUI');
        }
        
        return $this;
    }
    
    
    
    /** Setta ogni quante righe/colonne deve  andare accapo la visualizzazione,
     * se inseriti entrambi comanda le $colonne
     * se non si inseriscono parametri eredita accapo dal myField
     * se non si usa questo metodo vanno tutte su un unica riga
     * @param   int $colonne
     * @param	 int $righe
     */
     public function set_accapo($colonne=0,$righe=0) {
        if ($colonne) $this->accapo=$colonne;
        elseif ($righe) $this->accapo=-$righe;
        else $this->accapo=0;
        return $this;
    }
    
    
     public function set_effetti($attivo='',$non_attivo='',$su_accensione='',$su_spegnimento=''){
        if($attivo) $this->effetti['attivo']=$attivo;
        if($non_attivo) $this->effetti['non_attivo']=$non_attivo;
        if($su_accensione) $this->effetti['su_accensione']=$su_accensione;
        if($su_spegnimento) $this->effetti['su_spegnimento']=$su_spegnimento;
    }
    
    
    
    /**
     * Se true e si applica ad un myRadio o mySelect si colorano anche le stelline a sinistra di quella scelta
     * @param string $stato
     * @return myJQRating
     */
     public function set_riempi_sx($stato=true){
        $this->effetti['riempiSX']=($this->myField->isMultiple()?false:$stato);
        return $this;
    }
    
    /**
     * @ignore
     */
    private function crea_dati($k){
        $contatore=0;
        $azioni=array();
        if($this->arrayHtml) {
            $this->effetti['notNull']=(boolean)$this->myField->get_notnull();
        //    $JQ=$this->JQvar();
            if($this->myField instanceof MyMultiCheck)
                foreach ($this->myField->get_attributi_filtrati(function($x){return strpos($x,'on')===0;}) as $campo)
                    foreach ($campo['attributi'] as $tipo=>$azione) {
                        $tipo=substr($tipo,2);
                        $azioni[$campo['titolo']][]=".$tipo(function(){ $azione });";
                    }
                else {
                    foreach (array_keys($this->myField->get_opzioni()) as $titolo)
                        foreach ($this->myField->get_attributi_filtrati(function($x){return strpos($x,'on')===0;}) as
                        $tipo=>$azione) {
                            $tipo=substr($tipo,2);
                            $azioni[$titolo][]=".$tipo(function(){ $azione });";
                        }
                }
                
                foreach (array_keys($this->myField->get_opzioni()) as $key ) {
                    $arrayHtml=array_shift($this->arrayHtml);
                    $htmlBase=        $arrayHtml[0];
                    $htmlSelezionata= $arrayHtml[1];
                    if($this->accapo===null && $this->myField->get_accapo()>1) $this->accapo=$this->myField->get_accapo();
                    if($this->accapo===null) $accapo=0;
                      elseif($this->accapo===0) $accapo=(int) $this->myField->get_accapo();
                         elseif($this->accapo<0) $accapo=round(count($this->myField->get_opzioni())/-$this->accapo);
                             else $accapo=$this->accapo;
                    $contatore++;
                    $oggetti[]=array('id'=>'#'.$this->myField->get_id().(method_exists($this->myField,'get_campi_interni')?"_$contatore":''),
                        'htmlBase'=>$htmlBase,
                        'htmlSelezionata'=>$htmlSelezionata,
                        'nascondiField'=>!$arrayHtml[2],
                        'nascondiLabel'=>!$arrayHtml[3],
                        'azioni'=>(array) $azioni[$key],
                        'accapo'=>(int) $accapo);
                }
                $oggetti[]=$this->effetti;
        }
        
        return myJQuery::encode_array(array($k=>$oggetti),'{}',true);
        
    }
    
}