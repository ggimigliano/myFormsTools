<?php
use Gimi\myFormsTools\PckmyJQuery\myJQuery;
use Gimi\myFormsTools\PckmyFields\mySelect;
use Gimi\myFormsTools\PckmyFields\myText;
use Gimi\myFormsTools\PckmyFields\myField;


/**
 *
 * Imposta vari caratteristiche come zebratura ({@link myJQTableStyleEffects::set_zebra()}),
 * evidenziazione della riga attiva ({@link myJQTableStyleEffects::set_evidenziatore()})
 * ecc. vedere metodi
 * @package myJQuery
 * @deprecated Usare myJQDataTable
 */
class myJQTableStyleEffects extends myJQuery{
/**
 * @ignore 
 */	protected $JQ,$Operations;
	
 /**
 * @ignore 
 */function prepara_codice(){
		if($this->Operations) foreach ($this->Operations as $f) $f['func']($this,$this->JQ,$f['extra']);
	}

	/**
	 * @ignore
	 */
	 public function __construct($id) {
		parent::__construct($id);
		$this->JQ=self::$identificatore;
	}



/**
 * Imposta le classi di stile della tabella a righe  alternate
 * @param string $dispari nome classe per righe dispari
 * @param string $pari   nome classe per righe pari
 */
	 public function set_zebra($dispari=".even",$pari=".odd"){
		 	$this->Operations[]=array(
                'extra'=>array('dispari'=>$dispari,'pari'=>$pari),
                'func'=>function ($obj,$pref,$extra) use($dispari,$pari){
	  	                    if($extra) extract($extra);
							if($pari[0]=='.') $pari=substr($pari,1);
							if($dispari[0]=='.') $dispari=substr($dispari,1);
							$obj->add_code("{$pref}('{$obj->get_id()} tbody tr:even').addClass('$dispari');
											{$pref}('{$obj->get_id()} tbody tr:odd').addClass('$pari');");
							$obj->add_code($obj->bind("sortEnd","function() {
											var ZebraIndex=0;
											{$pref}('{$obj->get_id()} tbody tr').removeClass('$pari');
											{$pref}('{$obj->get_id()} tbody tr').removeClass('$dispari');
											{$pref}('{$obj->get_id()} tbody tr').filter(function (index) {
                  																		   if(".myJQuery::JQVar()."(this).css('display')== 'none') return false;
                  																		   return ( ++ZebraIndex %2!=0 );
                  																		 }).addClass('$dispari');
               								ZebraIndex=0;
											{$pref}('{$obj->get_id()} tbody tr').filter(function (index) {
                  																		  if(".myJQuery::JQVar()."(this).css('display')== 'none') return false;
                  																		   return ( ++ZebraIndex %2==0);
               																			   }).addClass('$pari');

											}")
										);
							});
	return $this;
	}



	/**
	*
	* Evidenzia la riga passandoci sopra con il mouse
	* @param string $classe (classe di css)
	*
	*/
	  public function set_evidenziatore($classe){
	 	 	$this->Operations[]=array(
	  	                'extra'=>array('classe'=>$classe),
	  	                'func'=>
	  	                function ($obj,$pref,$extra)use($classe){
	  	                    if($extra) extract($extra);
					 	
							$obj->add_code("{$pref}('{$obj->get_id()} tr').hover(
												function() { {$pref}(this).addClass('$classe');},
												function() { {$pref}(this).removeClass('$classe');}
												);");
					 		}
	 	 	);
		return $this;
	}


	/**
	*
	* Nasconde le colonne
	* @param array $colonne
	*
	*/
	 public function set_hidden_cols($colonne){
		$this->Operations[]=
		function ($obj,$pref) use ($colonne){
	 		foreach ($colonne as  $value)
	 		$obj->add_code("
						 {$pref}('.sHeaderInner table colgroup').find('col').eq($value).remove();
						 {$pref}('.sData table colgroup').find('col').eq($value).remove();
						 {$pref}('{$obj->get_id()} tr').find('th').eq($value).hide()
						 {$pref}('{$obj->get_id()} tr').each(function(i){
						 							{$pref}(this).find('td').eq($value).hide()
						 						});");
		};
	return $this;
	}




	 /**
	 *
	 * Permette di selezionare la riga cliccando con il mouse
	 * @param array $arrayOpzioni
	 */
	  public function seleziona_riga($arrayOpzioni){
	 	 $this->Operations[]=array(
	 	        'extra'=>array('arrayOpzioni'=>$arrayOpzioni),
	 	        'func'=>
	 	     function ($obj,$pref,$extra) use($arrayOpzioni){
	 	         if($extra) extract($extra);
				 $Codice.="
				 {$pref}('{$obj->get_id()} tr').mouseover(function() {
											 {$pref}(this).css({ 'cursor': 'hand', 'cursor': 'pointer'});
											 	{$pref}(this).addClass('{$arrayOpzioni['seleziona_riga']['classi']['evidenzia']}');
											 });


				 {$pref}('{$obj->get_id()} tr').mouseout(function() {
											 {$pref}(this).removeClass('{$arrayOpzioni['seleziona_riga']['classi']['evidenzia']}');
											 });

				 {$pref}('{$obj->get_id()} tr').click(function() {

										 {$pref}('{$obj->get_id()} tr').removeClass('{$arrayOpzioni['seleziona_riga']['classi']['selezione']}');

										 {$pref}(this).toggleClass('{$arrayOpzioni['seleziona_riga']['classi']['selezione']}');

				 ";

				 if (is_array($arrayOpzioni['operazione']))
				 			{
							 foreach ($arrayOpzioni['operazione'] as $chiave => $valore)
							 				{
											 $url=$valore['link'];

											 $Codice.="	{$pref}('#$chiave').removeAttr('onclick');

											 var miafrase_$chiave = new String('$url');

											";
											 if ($valore['finestra']){
											 	if ($valore['finestra']['titolo']) $Codice.="var titolo='{$valore['finestra']['titolo']}';";
												 if ($valore['finestra']['opzioni']) $Codice.="var opzioni='{$valore['finestra']['opzioni']}';";
											 }
											 if ($valore['valori'])
											 			 {
														 if ($valore['valori']['accoda']) $Codice.="miafrase_$chiave = miafrase_$chiave + ?tt=".time();
														 foreach ($valore['valori'] as $key => $value)
														 					{
																			 $Codice.="var valore = ({$pref}(this).find('td').eq($value).text());";

																			 if ($valore['accoda']==false) $Codice.="miafrase_$chiave = miafrase_$chiave.replace('$key=','$key=' + valore); \n";
																								 	 else $Codice.="miafrase_$chiave = miafrase_$chiave + '&' + '$key=' + valore; \n ";
																			 }
														 }

											 if ($valore['valori']['disabilita']==true) $Codice.=" {$pref}('#$chiave').prop('disabled', 'disabled');";
											 $Codice.="
											 miafrase_$chiave = miafrase_$chiave.replace(/[\"]/g,'\'');


											 if ({$pref}('#$chiave').prop('disabled')=='disabled') {$pref}('#$chiave').removeAttr('disabled');
											 {$pref}('#$chiave').click(function(){

																					 if ({$pref}('#$chiave').prop('type')=='submit') {$pref}('form').submit(function(){ return false; });
											 							"
																	 .($valore['finestra']?"eval (window.open(miafrase_$chiave,titolo,opzioni));":"window.location.href = miafrase_$chiave")
																	 ."});  ";


											 }

							 }
				 $Codice.=" })";

	 		$obj->add_code($Codice);
	 	});
	 return $this;
	 }


	  /**
	  * Permette di creare intestazioni fisse
	  * 
	  * @param string $percorsoCCS 
	  * @param string $classeCSS  può essere "sDefault", "sSky", "sOrange", "sDark" se si mette stringa vuota in $percorsoCCS      
	  * @param number $nrigheHeader Altezza dell'header
	  * @return myJQTableStyleEffects
	  */
	    public function set_header_fisso($percorsoCCS='',$classeCSS='sSky',$nrigheHeader=1){

	  	self::add_src(self::$percorsoMyForm."myDeprecated/js/supertable/superTables.js");
 	  	if($percorsoCCS) self::add_css($percorsoCCS);
 	  				else self::add_css(self::$percorsoMyForm."myDeprecated/js/supertable/css/superTables.css");
        
 	  				
	  	$this->Operations[]=array(
	  	    'extra'=>array('classeCSS'=>$classeCSS,'nrigheHeader'=>$nrigheHeader),
	  	    'func'=>
	  	    function ($obj,$pref,$extra) use($classeCSS,$nrigheHeader){
	  	    if($extra) extract($extra);
	  	    $JQVar=myJQuery::JQvar();
         	$codice="
	  		 new(function ()
	  		               {//per inquadrere la classe della tabella corrente esegue sempre $JQVar('.sData {$obj->get_id()}').parent()
	  	                    this.reArrange=function()
	  		                                       {
	  		                                         $JQVar('.sBase {$obj->get_id()}').find('colgroup').remove();
                            	  		             $JQVar('.sBase {$obj->get_id()}').parent().parent().parent().prepend($JQVar('.sHeaderInner {$obj->get_id()}'));
                            	  		             $JQVar('.sBase {$obj->get_id()}').parent().parent().parent().parent().find('table').append($JQVar('.sData {$obj->get_id()} tbody'));
                            	  		             $JQVar('.sData {$obj->get_id()}').parent().remove();
                            	  		             $JQVar('.sBase {$obj->get_id()}').parent().remove();
                            	  		             $JQVar('{$obj->get_id()}').removeClass('$classeCSS').removeClass('$classeCSS-Headers');  
	  		                                         new superTable({$pref}('{$obj->get_id()}').prop('id'),{cssSkin:'$classeCSS', headerRows:$nrigheHeader});
	  		                                        }
	  		               new superTable({$pref}('{$obj->get_id()}').prop('id'),{cssSkin:'$classeCSS', headerRows:$nrigheHeader});
	  		               $JQVar(window).bind('resize',this.reArrange);
                    	   });
                    	   
	  		
	  		var cssPari={$pref}('.sData {$obj->get_id()} tbody tr:eq(0)').prop('class');
	  		var cssDispari={$pref}('.sData {$obj->get_id()} tbody tr:eq(1)').prop('class');
	  		
	  		";

	  		$codice.="{$pref}('.sHeaderInner table thead tr:first th').click(function(){
	  				var col = {$pref}(this).parent().children().index({$pref}(this));
	  					{$pref}('{$obj->get_id()} thead tr:first th').each(
							function(a){
								var index ={$pref}(this).index();
								var sortUp={$pref}(this).is('.headerSortUp');
								var sortDown={$pref}(this).is('.headerSortDown');
								if ((sortDown==true || sortUp==true) && index==col){
									if (sortDown==true)	ordine=1;
									if (sortUp==true) ordine=0;
								}
								if (index==col){
									var sortUp={$pref}(this).is('.headerSortUp');
									var sortDown={$pref}(this).is('.headerSortDown');
									if(sortDown==true)	ordine=1;
									else ordine=0;
								}

							}
						);
						var sorting = [[col,ordine]];
						{$pref}('.sData {$obj->get_id()}').trigger('sorton',[sorting])
														.bind('sortEnd',function() {
															if (ordine==1) {$pref}('.sHeaderInner table thead tr:first th').eq(col).addClass('headerSortUp').removeClass('headerSortDown');
															else {$pref}('.sHeaderInner table thead tr:first th').eq(col).addClass('headerSortDown').removeClass('headerSortUp');
    													});
    			});
//			{$obj->JQId()}.css({'white-space':'nowrap'}); 
			";
			$obj->add_code($codice);
	  	});
	  	return $this;

	  }


}



/**
 * Classe che permette di aggiungere dei campi per filtrare la tabella
 * @author Luigi
 * @package myJQuery
 * @deprecated Usare myJQDataTable
 */

class myJQTableFilters extends myJQuery{
/**
 * @ignore 
 */
	protected $campiFiltro,$campipubblici, $evento,$eventiCampi,$JQ,$Operations,
	$FiltroJQBase="':containsNC(\"' + filtro + '\")'",
	 $FiltroJSBase="FiltroJSBase",
	 $FiltroJQPersonalizzato=array(),
	 $FiltroJSPersonalizzato=array(),
	 $ordineSelect=array(),
	 $codiceFiltro,
	 $usa_cookie=false;

/**
 * @ignore 
 */	function prepara_codice(){
        if ($this->filtro) self::add_src(self::$percorsoMyForm."myDeprecated/js/tablefilter/picnet.table.filter.min.js");
		if($this->Operations) foreach ($this->Operations as $f) $f($this,$this->JQ);
		//Aggiungo i campi filtro all'header
		$codice.="
		var colonnaEsclusa='';
		var TipoDatiFiltro;
		var valoreAttuale;

		if ({$this->JQ}('.sData {$this->get_id()}  tbody tr').length>0){
			var SELETTORE='.sData {$this->get_id()} ';
			var SELETTORE_HEAD='.sHeader {$this->get_id()}';
		}
		else {
			SELETTORE='{$this->get_id()}';
			SELETTORE_HEAD=SELETTORE;
		}

		var stringaFiltri='';
		var colonnaMov='';


		";
		$codice.=$this->crea_header();
		$codice.=$this->set_eventi();
		$codice.=$this->crea_option();
		$codice.="var cssPari={$this->JQ}(SELETTORE + ' tbody tr:visible').filter('tr:eq(1)').prop('class');
		var cssDispari={$this->JQ}(SELETTORE + ' tbody tr:visible').filter('tr:eq(2)').prop('class');

		";
		
		$this->add_code(preg_replace('@<script [^>]+>\s*</script>@SUi','', $codice));
	}


	/**
 * @ignore 
 */function __construct($id) {
		parent::__construct($id);
		$this->JQ=self::$identificatore;

	}

	protected final function get_MyFormsPath(){
		return self::$percorsoMyForm;
	}



	/**
	 * Metodo per settare le colonne su cui applicare i filtri
	 *
	 * 
	 * @param array $colonne (passare un array nel formato numero_colonna=>tipo_filtro (es:0=>'text',1=>'select') dove il default del tipo e' Text
	 * @param array $filtroJQ (eventuale filtro personalizzato Jquery nel formato numero colonna=>script filtro personalizzato)
	 * n.b. di default vengono filtrate le colonne che contengono i caratteri digitati, ma esiste anche
	 * 1) containsBegin : colonne che iniziano con i caratteri digitati
	 * 2) containsAll: colonne che contengono esattamente tutta la stringa digitata
	 * @param array $filtroJS (eventuale filtro Javascript che serve per personalizzare testo e valore nei filtri di tipo select)
	 * @param $ordineSelect (eventuale ordine personalizzato nei filtri select, di default e' alfabetico). Si puo' passare uno degli ordinamenti predefiniti:
	 * (text,numeric,dateIt) oppure una funzione javascript nel formato colonna=>ordinamento
	 * @param $evento (evento che innesca l'operazione di filtraggio default:'keyup change') se si vuole attivare il filtro dopo aver riempito il campo settare 'blur'
	 * <code>
	 * $x=new myJQTableFilters("#".$tabella->get_id());
	 * $x->set_filtro(array(0=>'text',1=>'select',2=>'text',3=>'select'),array(0=>'containsBegin'),
	 * 				array(1=>'function FiltroJSAnno(a){
	 *				testoSelect=\"Anno \" + a.substring(6);
	 *					return a.substring(6);
	 *				});'),
	 *				array(1=>'dateIt'));
	 *
	 * </code>
	 */
	public function set_filtro($colonne=array(),$filtroJQ=array(),$filtroJS=array(),$ordineSelect=array(),$evento='keyup mouseup'){
		$this->evento=$evento;

		if ($filtroJQ && is_array($filtroJQ)){
			foreach ($filtroJQ as $key => $value) {
				if (!is_array($value)) $this->FiltroJQPersonalizzato[$key]=($value);
				else{
					foreach ($value as $chiave => $valore) {
						$this->FiltroJQPersonalizzato[$key."_".$chiave]=($valore);
					}
				}
			}
		}

		if ($filtroJS && is_array($filtroJS)){
			foreach ($filtroJS as $key => $value) {
				if (!is_array($value)) $this->FiltroJSPersonalizzato[$key]=$value;
				else{
					foreach ($value as $chiave => $valore) {
						$this->FiltroJSPersonalizzato[$key."_".$chiave]=($valore);
					}
				}
			}

		}
		if ($ordineSelect && is_array($ordineSelect) ){
			foreach ($ordineSelect as $key => $value) {
				if (!is_array($value)) $this->ordineSelect[$key]=$value;
				else {
					foreach ($value as $chiave => $valore) {
						$this->ordineSelect[$key."_".$chiave]=($valore);
					}
				}
			}
		}


		$this->add_common_code($this->set_funzioni_comuni(), 'tablefilter', 'Funzioni comuni');
		//Istanzio gli oggetti campo che mi servono per costruire i filtri
		//Se non è stato indicato un array, costruisco un campo filtro su tutte le colonne di tipo myText
		if (!is_array($colonne)) $this->set_campiFiltroAllText();
		else $this->set_campiFiltro($colonne);


	}

	/**
	* @param array $colonne
	 * @ignore
	 */
	protected function set_campiFiltro($colonne){

		foreach ($colonne as $key => $value) {
			if (!is_array($value)) $this->campiFiltro[$key]=$this->get_campi_html($value,$key);
			else{
				foreach ($value as $chiave => $valore) 	{
					$this->campiFiltro[$key][$chiave]=$this->get_campi_html($valore,$key."_".$chiave);
					}
			}
		}

	}
	protected function set_campiFiltroAllText(){
		for ($x=0;$x<$this->myTable->get_n_cols();$x++)	$this->campiFiltro[$x]=$this->get_campi_html('text',$x);
	}

/**
 * Metodo per istanziare degli oggetti MyField che verrano utilizzati per i filtri
 * 
 * @ignore
 * @param string/object $tipo
 * @param integer $colonna
 * @return object

 */
	protected function get_campi_html($tipo,$colonna){
		if (is_object($tipo)) {
			$cf=&$tipo;

		}
		else{
			if (strtolower($tipo)=='text')	$cf=new myText("filtro[{$colonna}]",'','filter');
			if (strtolower($tipo)=='select') $cf=new mySelect("filtro[{$colonna}]",'','filter');
			$cf->set_attributo('title',"filter_{$colonna}");
		}
		$cf->set_attributo('id',"filter_{$colonna}");
		$cf->set_attributo('class',"filter");

		$cf->set_style('width', '90%');

		$this->campipubblici[$colonna]=&$cf;
		return $cf;
	}

/**
 * Metodo che ritorna i puntatori agli oggetti istanziati
 * 
 *
 * @return array di object
 */
	public function get_campiFiltro(){
		return  $this->campipubblici;
	}

	/**
	 * @ignore
	 */
	protected function set_funzioni_comuni(){

			$codiceComune="
			var filtro='';

			var FiltroJQBase=':containsNC(\"' + filtro + '\")';
			var FiltroJQPersonalizzato='';
			var testoSelect='';

			{$this->JQ}.extend({$this->JQ}.expr[\":\"], {
			\"containsNC\": function(elem, i, match, array) {
			return (elem.textContent || elem.innerText || \"\").toLowerCase().indexOf((match[3] || \"\").toLowerCase()) >= 0;
			}
			});

			{$this->JQ}.extend({$this->JQ}.expr[\":\"], {
			\"containsAll\": function(obj, index, meta, stack) {

			return (obj.textContent || obj.innerText || {$this->JQ}(obj).text() || \"\").toLowerCase() == meta[3].toLowerCase();
			}
			});

			{$this->JQ}.extend({$this->JQ}.expr[\":\"], {
			\"containsBegin\": function(elem, i, match, array) {

			return (elem.textContent || elem.innerText || \"\").toLowerCase().indexOf((match[3] || \"\").toLowerCase()) === 0;
			}
			});
			function strip_tags(testo)  {
        		return testo.replace(/(<([^>]+)>)/ig,'');
    		};
			";

			return $codiceComune;
			}


			/**
			 * crea la riga di intestazione contenente i campi filtro
			 * 
			 * @ignore
			 * @param array $colonne
			 * @return string
			 *
			 */
			protected function crea_header(){
            $mf=new myField();
            $jscommon=$mf->get_js_common();
			$header.="var FiltriJQPersonalizzati=[];
			var FiltriJSPersonalizzati=[];
			var ordineSelect=[];
			FiltriJQPersonalizzati[{$this->JQ}('{$this->get_id()}').prop('id')]=".self::encode_array($this->FiltroJQPersonalizzato,'{}').";
			FiltriJSPersonalizzati[{$this->JQ}('{$this->get_id()}').prop('id')]=".self::encode_array($this->FiltroJSPersonalizzato,'{}').";
			ordineSelect[{$this->JQ}('{$this->get_id()}').prop('id')]=".self::encode_array($this->ordineSelect,'{}').";";

			//$header.="{$this->JQ}(SELETTORE + '  > tbody tr:first').before({$this->JQ}(SELETTORE + '  > tbody tr:first').clone());";


  			$header.="{$this->JQ}(SELETTORE_HEAD + '  > thead').append({$this->JQ}(SELETTORE_HEAD + ' thead tr').clone());";

  			$header.="{$this->JQ}(SELETTORE_HEAD + ' tr:eq(1) th').removeClass('header').removeClass('headerSortDown').removeClass('headerSortUp').html('').css('padding','0.5px');";
 			$header.=
 			"if ({$this->JQ}('.sData {$this->get_id()}  tbody tr').length>0){
				$('.sData thead').append($('.sData thead tr').clone());

  			}
 			";

			foreach ($this->campiFiltro as $key => $value) {
			    $html='';
				if (!is_array($value)) $value=array($value);
                foreach ($value as $valore) 	$html.=str_replace($jscommon,'', $valore->get_html())."<br /><br />";
                $header.="{$this->JQ}(SELETTORE_HEAD + ' thead tr:last th:eq({$key})').html('{$html}');";
				}
            return $header;
			}

			/**
			 * Setta gli eventi sui campi filtro
			 * @ignore
			 */
			protected function set_eventi(){
			$eventi="
				var selettore='';
				var colonnaMov='';
				var idAttuale";

			$eventi="
			var semaphore=null;
			function myFiltro(current){

				if(current!=semaphore) return;

				{$this->JQ}(SELETTORE + ' tbody tr').show();
				var valoriCookie=new Array();
				valoriCookie['{$this->get_id()}'] = new Array();

				{$this->JQ}(SELETTORE_HEAD + ' .filter').filter('[value!=\"\"]').each(function(){


					var stringa=this.id;
					var pezzi=this.id.split('_');
					idAttuale=stringa;
					colonna= (pezzi[1]);
					colonnaEsclusa=colonna;
					var tipo=this.type;
					if (tipo!='text') var filtro={$this->JQ}(SELETTORE_HEAD + ' #' + stringa + ' option:selected').val();
					else var filtro = this.value;

					if (filtro!=' '){

						valoriCookie['{$this->get_id()}'][colonna]=filtro;
						var FiltroJQBase=':containsNC(\"' + filtro + '\")';
						if (typeof(FiltriJQPersonalizzati)!='undefined'){
							var FiltroJQ=FiltriJQPersonalizzati[{$this->JQ}('{$this->get_id()}').prop('id')][colonna]?FiltriJQPersonalizzati[{$this->JQ}('{$this->get_id()}').prop('id')][colonna]:FiltroJQBase;
							if (FiltroJQ=='containsBegin') FiltroJQ=':containsBegin(\"' + filtro + '\")';
							if (FiltroJQ=='containsAll') FiltroJQ=':containsAll(\"' + filtro + '\")';
						}
						else var FiltroJQ=FiltroJQBase;
						if(current!=semaphore) return;
						{$this->JQ}(SELETTORE +' tbody tr:visible').find('td:eq(' + colonna +')').not(FiltroJQ).parent().hide();



				";

				$eventi.=$this->crea_option();
				$eventi.="

					}

				});


var cookies=".self::$identificatore.".toJSON(valoriCookie['{$this->get_id()}']);

				{$this->JQ}.cookie('{$this->get_id()}', cookies);
				{$this->JQ}(SELETTORE +' tbody tr:visible').removeClass(cssDispari).removeClass(cssPari);
						{$this->JQ}(SELETTORE +' tbody tr:visible').filter('tr:odd').addClass(cssPari);
						{$this->JQ}(SELETTORE + ' tbody tr:visible').filter('tr:even').addClass(cssDispari);

			};


			";


			foreach ($this->campiFiltro as $key => $value) {
			if (!is_array($value)){
				if (strtolower($value->get_MyType())=='mytext')	$eventi.="{$this->JQ}(SELETTORE_HEAD + ' #filter_{$key}').keyup".$this->corpo_eventi();
				else $eventi.="{$this->JQ}(SELETTORE_HEAD + ' #filter_{$key}').change".$this->corpo_eventi();
				}
				else{
				foreach ($value as $chiave => $valore) {
				if (strtolower($valore->get_MyType())=='mytext')	$eventi.="{$this->JQ}(SELETTORE_HEAD + ' #filter_{$key}_{$chiave}').keyup".$this->corpo_eventi();
				else $eventi.="{$this->JQ}(SELETTORE_HEAD + ' #filter_{$key}_{$chiave}').change".$this->corpo_eventi();
				}
				}
				}



				return $eventi;
			}

				/**
				 * @ignore
				 */
			protected function corpo_eventi(){
			return
			"(function(event){
			if (event.keyCode!=13){
			if (event.type=='blur') myFiltro(semaphore);
			else{
			semaphore=Math.random();

			var pezzi=this.id.split('_');
			colonnaMov=(pezzi[1]);
			var tempo=Math.floor({$this->JQ}(SELETTORE +' tbody tr').length/1000)*1000;
			if (tempo>3000) tempo=3000;
			window.setTimeout(function(){
			myFiltro(semaphore)},tempo);
			}
			}
			});
			";
			}
			/**
			*  Metodo per creare le option in una select
			*  se si passa la colonna, sarà  esclusa dalla creazione
			* @param integer $colonna
				*/
			protected function crea_option(){
			$option="

			var arrayTemp=new Array();
			testoSelect='';
			";

			$option.=
			"


			{$this->JQ}(SELETTORE_HEAD + ' .filter').filter('select').each(function(){

			var pezzi=this.id.split('_');
			idAttuale=this.id;

			var colonnaAttuale= (pezzi[1]);
			if (colonnaAttuale!=colonnaEsclusa){

            valoreAttuale={$this->JQ}(SELETTORE_HEAD +' #'+ idAttuale + ' option:selected').val();

			{$this->JQ}(SELETTORE_HEAD +' #'+ idAttuale).find('option').remove();
			{$this->JQ}(SELETTORE_HEAD + ' #' + idAttuale).append({$this->JQ}('<option></option>').val(' ').html('----------'));
			var arraySelect =new Array();
			{$this->JQ}(SELETTORE + ' tr:visible').find('td:eq('+ colonnaAttuale +')').each(function(){

			if (FiltriJSPersonalizzati[{$this->JQ}('{$this->get_id()}').prop('id')][colonnaAttuale]) {
				var f1=FiltriJSPersonalizzati[{$this->JQ}('{$this->get_id()}').prop('id')][colonnaAttuale];
				var valore=f1(strip_tags(this.innerHTML));
			}
			else var valore=FiltroJSBase(strip_tags(this.innerHTML));

			if (testoSelect!=''?testoOption=testoSelect:testoOption=valore);

			if ({$this->JQ}.inArray(valore,arrayTemp)<0){
				arraySelect.push({\"chiave\":valore,\"valore\":testoOption});
				arrayTemp.push(valore);


			}
			});
			var ordinato=arrayTemp.sort();


			{$this->JQ}.each(arraySelect,function(key, value) {
			var defaultSelected = false;
			var nowSelected     = false;

			if(typeof(valoreAttuale) != 'undefined' && valoreAttuale==value.chiave) nowSelected=true;
			if (nowSelected) {$this->JQ}(SELETTORE_HEAD + ' #' + idAttuale).append({$this->JQ}('<option selected=\"selected\"></option>').val(value.chiave).html(value.valore));
			else {$this->JQ}(SELETTORE_HEAD + ' #' + idAttuale).append({$this->JQ}('<option></option>').val(value.chiave).html(value.valore));

			});


			";

			$option.=$this->ordina_select();

			$option.="
			}
			});


			";


			//$option.=$this->ordina_select();
			return $option;

			}

			/**
			 * Metodo che ordina le voci delle select
			 *
			 * @ignore
			 */
			protected function ordina_select(){

			$ordine.="

			if (ordineSelect[{$this->JQ}('{$this->get_id()}').prop('id')][colonnaAttuale]) var ordine=eval(ordineSelect[{$this->JQ}('{$this->get_id()}').prop('id')][colonnaAttuale]);
			else var ordine=text;

			{$this->JQ}(SELETTORE_HEAD + ' #' + idAttuale).html({$this->JQ}(SELETTORE_HEAD + ' #' + idAttuale + ' option').sort(ordine));

			if (!valoreAttuale) {$this->JQ}(SELETTORE_HEAD + ' #' + idAttuale).val('');
			else {$this->JQ}(SELETTORE_HEAD + ' #' + idAttuale).val(valoreAttuale);
			{$this->JQ}(SELETTORE_HEAD +' #' + idAttuale).prop('style','width:95%');
			";
			return $ordine;
			}

	/**
	 * @see myJQuery::set_order_memory()
	 * @return myJQuery
	 * @return myJQuery
	 * Permette di utilizzare i cookie per ricordare gli eventuali filtri inseriti dall'utente
	 */
	 public function set_order_memory() {
		parent::set_order_memory();
		$this->usa_cookie=true;

	}




}




?>