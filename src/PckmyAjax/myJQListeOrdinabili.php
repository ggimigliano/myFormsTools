<?php
/**
 * Contains Gimi\myFormsTools\PckmyAjax\myJQListeOrdinabili.
 */

namespace Gimi\myFormsTools\PckmyAjax;

use Gimi\myFormsTools\PckmyFields\myField;
use Gimi\myFormsTools\PckmyJQuery\myJQuery;



/**
 * Classe per gestire liste ordinabili tramite jquery
 *
 * Questa classe permette di costruire delle liste ordinabili tramite trascinamento degli elementi.
 * <code>
 * .. includo le myAjax
 *
 * $miaListaOrdinabile=new myListeOrdinabili('Nome',array('Primo','Secondo','Terzo','Quarto','Quinto'));
 *
 * echo $miaListaOrdinabile->get_html();
 * </code>
 *
 * E' possibile salvare la posizione attuale delle liste passando alla variabile  $scriptUpdate uno script esterno
 * Esempio:
 * <code>
 * <?
 * $miaListaOrdinabile=new myListeOrdinabili('Nome',array('Primo','Secondo','Terzo','Quarto','Quinto'),"process-sortable.php");
 * ?>
 *
 * Lo script richiamato puo' leggere le liste ordinate:
 * <?
 * foreach ($_GET[$nome] as $position => $item) {
 *
 * //puoi salvare la posizione della chiave in una $_SESSION o direttamente sulla tabella di un d.b.
 *
 * }
 * ?>
 * </code>
 *
 * Si puo' utilizzare un css diverso da quello di default, ma si deve avere l'accortenza che le classi che compongono il css
 * abbiano lo stesso nome del oggetto istanziato
 *
 * Esempio:
 *  $miaListaOrdinabile=new myListeOrdinabili('Nome',array('Primo','Secondo','Terzo','Quarto','Quinto'),'','mio.css');
 *
 * File mio.css
 * 	#Nome {
 *		list-style: none;
 *	}
 *
 *	#Nome li {
 *		display: block;
 *		padding: 10px 5px; margin-bottom: 3px;
 *		background-color: #efefef;
 *	}
 *
 *	#Nome li img.handle {
 *		margin-right: 10px;
 *		cursor: move;
 *	}
 *
 */
	
class myJQListeOrdinabili  {
	private $myLista;
	private $scriptSalvataggio;
	private $stile;
	private $immagine;
	private $percorso;
	private $id,$div;
	private $css;
	
	public $ajaxCall=array('type'=>"POST",'url'=>'');
	
	
	/**
	 * @param array $valori (array contenenti le voci da ordinare nel formato chiave=>testo
	 * @param string $div id del div in cui e' contenuta la lista
	 * @param string $scriptUpdate (l'eventuale script da utilizzare per salvare le modifiche)
	 */
	public function __construct($valori,$div,$scriptUpdate=''){
		if (is_array($valori)) $this->myLista=$valori;
		if ($scriptUpdate) $this->scriptSalvataggio=$scriptUpdate;
		$this->id="ul_$div";
		$this->div=$div;
		$this->percorso="/".myField::get_MyFormsPath();
		$this->immagine=$this->percorso."icone/imm_list.png";
		//if ($style)	$this->stile=$style;else
	}
	
	
	
	
	/**
	 * Metodo che ritorna l'html delle liste ordinabili
	 *
	 */

	
	public function get_html(){
	    $out.="<ul id='{$this->id}'>";
	    foreach ($this->myLista as $chiave => $valore) {
	        $out.="<li >
            	        <div class='handle' onmousedown='document.getElementById(\"{$this->div}\").innerHTML=\"\"' style='background:url({$this->immagine}) no-repeat top left;padding-left:15px;min-height:18px'>$valore
            	                <span style='display:none' class='chiave'>$chiave</span>
            	        </div>
	        </li>";
	    }
	    $out.="</ul>";
	return $out.$this->jquery().$this->css();
	}
	
	
	/**
	* Metodo per settare l'immagine da visualizzare a fianco delle liste
	 *
	 * @param string $percorso
	 */
	 public function set_immagine($percorso){
		$this->immagine=$percorso;
		}


		
		/**
	 * Metodo privato che importa jquery ed inizializza la funzione
	  * @ignore
	  * @return string
	  */
	  protected function jquery(){
	/*  $js.='<script type="text/javascript" src="'.$this->percorso.'ajax/jquery-1.3.2.js"></script>
	  <script type="text/javascript" src="'.$this->percorso.'ajax/jquery-ui-1.7.1.custom.min.js"></script>';*/
	  //if ($this->stile) $js.="<link rel='stylesheet' href='$this->stile' type='text/css' media='all' />";
	  //else
	  myJQuery::add_src("jquery/ui/jquery-ui.min.js");
	   
	  
	  $jq=new myJQuery("#{$this->id}");	   
	  $sortable=array('handle'=>'.handle');
	  
	  if ($this->scriptSalvataggio){
	  	$div=new myJQuery("#{$this->div}");
	  	$this->ajaxCall['url']=$this->scriptSalvataggio;
	  	$sortable['update']="function() {
	  	                            var order={};var i=1;
	  	                            {$div->JQvar()}('#{$this->id} .handle .chiave').each(
	  	                                                                            function(){
	  	                                                                                       if({$div->JQvar()}(this).text()) order[i++]={$div->JQvar()}(this).text();
	                                                                                           }
	  	                                                                            );
	  	                            var call=".myJQuery::encode_array($this->ajaxCall).";
	  	                            call['data']= {'listItem':order};                                                
	  							    {$div->JQvar()}.ajax(call);
	  							} ";

	 }
	 	 
	  $jq->add_code($jq->sortable($sortable));
	  return $jq;
	  }

	  
	  

	  
	  
	  /**
	   * ignore
	   */
	  protected function css(){
	  	return "<script type='text/javascript' >
	  	var css=document.styleSheets[0];
	  	if ( css.insertRule) {
				css.insertRule(\"#{$this->id} {list-style: none;}\",0);
				css.insertRule(\"#{$this->id} li {list-style: none;display: block;padding: 5px 10px; margin-bottom: 3px;	background-color: #efefef;}\",0);
				css.insertRule(\"#{$this->id} li div.handle {margin-right: 10px;	cursor: move;}\",0);
	 			 }
			else{
			css.addRule(\"#{$this->id}\", \"list-style: none;\", 0);
			css.addRule(\"#{$this->id} li\", \"list-style: none;display: block;padding: 5px 10px; margin-bottom: 3px;	background-color: #efefef;\", 0);
			css.addRule(\"#{$this->id} li div.handle\", \"margin-right: 10px;	cursor: move;\", 0);
			}
			</script>";


	  }



}