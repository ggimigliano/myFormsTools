<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\Pckgeneric\myJQTableSorter.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\Pckgeneric;



use Gimi\myFormsTools\PckmyJQuery\myJQuery;
use Gimi\myFormsTools\PckmyJQuery\PckmyTables\myJQMyTableSorter;

                                                
class myJQTableSorter extends myJQuery{

/**
 * @ignore 
 */
protected static $tema=null, $opzioniOrdine,$Opzioni=array(),$Init=array(), $f=array();

/**
 * @ignore 
 */protected static $css_fonts=array(),$tsPager;


/**
 * @ignore 
 */function __construct($id) {
		parent::__construct($id);
		$this->add_code("var opzioni={sortMultiSortKey:'ctrlKey',dateFormat:'uk',sortList:[],headers:{}}");
		self::add_src('jquery/tablesorter/jquery.tablesorter.min.js');
		
//self::add_src('jquery/ui/jquery.effects.js');
 }

 
/**
    * Imposta un'azione su un evento specifico
    * @param 'initStart'|'initEnd'|'sortStart'|'sortEnd' $evento
    * @param string $codice codice javascript o Jquery "crudo"
    *
    * es $x->set_event('sortStart','  alert(1); alert(2); '
    *
    */
  public function set_event($evento,$codice) {
 	 if($evento=='initStart') $this->Init['start'].=$codice;
 	 if($evento=='initEnd') $this->Init['end'].=$codice;
 	 if($evento=='sortStart') $this->Init['sortStart'].=$codice;
 	 if($evento=='sortEnd') $this->Init['sortEnd'].=$codice;
 	 return $this;
 }

 
/**
 * @ignore 
 */function prepara_codice(){
 
//	if ($this->filtro) self::add_src('jquery/tablefilter/picnet.table.filter.min.js');
 
//	$this->add_code($this->tablesorter($this->Opzioni)); RIMOSSO PER AGGANCIO JS
 	if($this->f) 
 	 foreach ($this->f as $f=>$pars)
 				call_user_func_array(array($this,"_{$f}"), $pars);
 	
 if($this->Init['sortStart']) $this->add_code("{$this->JQid()}.data('myStatus').occupato++;{$this->jqid()}.bind('sortStart',function(){ {$this->Init['sortStart']} })",'sortStart');
 	if($this->Init['sortEnd']) $this->add_code("{$this->JQid()}.data('myStatus').occupato--;{$this->jqid()}.bind('sortEnd',function(){ {$this->Init['sortEnd']} })",'sortEnd');
 	$this->add_code("{$this->JQid()}.data('myStatus',{occupato:0});
    					 {$this->JQid()}.data('myStatus').occupato++;
    					 {$this->Init['start']};
    					 {$this->JQid()}.tablesorter(opzioni)".$this->tsPager);
 	$this->add_code("{$this->JQid()}.addClass('tablesorter');
      					 {$this->Init['end']};
      					 {$this->JQid()}.data('myStatus').occupato--;
      					 ");
 }


 
/**
 * @ignore 
 */ protected final function get_MyFormsPath(){
 	return self::$percorsoMyForm;
 }

 
/**
 * @ignore 
 */ final function add_common_code($code,$k=''){
 	return self::add_common_codes($code,'tablesorter', $k);
	}


 

 
/**
     * setta il tema grafico
     * @param string $tema 'blue','green' o percorso completo del css
     */
 final static function set_tema($tema='base') {
 	if($tema[0]!='.' && $tema[0]!='/') $tema=self::$percorsoMyForm."jquery/tablesorter/themes/$tema/style.css";
 	self::$tema=$tema;
 }


 
/**
       *
       * Aggiunge un tipo di dato nella tabella
       * @param string $nome nome del tipo (da usare nel {@link myJQMyTableSorter::set_ordine()})
       * @param 'numeric'|'text' $tipo   tipo di logica da usare nel confronto >=<
       * @param string $codice codice javascript di produzione di un valore da usare nel confronto (il valore da lavorare e' la variabile 's'
       * @final
       * Es.
       * <code>
       * $t=new mytable($array);
	   * $t->add_myjquery(new myJQMyTableSorter())
	   *    ->add_tipo('lunghezza','numeric', 'return s.length') //definisce un tipo lunghezza che restituisce la lunghezza della stringa
	   *    ->set_ordine(array(),true,array(),array(1=>'lunghezza')); //permette di ordinare la seconda colonna rispetto alla lunghezza delle stringhe contenute
	   *
       * </code>
       * @return myJQMyTableSorter
       */
 final function add_tipo($nome ,$tipo,$codice){
 	 $codice=preg_replace('/^return /is','',trim((string) $codice));
 	 $this->add_common_code(self::$identificatore.".tablesorter.addParser({id:'$nome',is: function(s) {return false} ,format: function(s) {return $codice},  type: '$tipo' }); ") ;
 	 return $this;
 }


 
/**
       *
       * @param array $ordinamento array enumerativo con indici di colonna (da 0)
       * @param boolean $inclusi se true si applica alle colonne indicate in $ordinamento altrimenti non sia applica a quelle colonne ma alle altre
       * @param array $defaults array associativo con ordinamenti di default es (1=>'+',2=>'-') seconda colonna ascendente e terza discendente
       * @param array $tipi array associativo con tipi non standard aggiunti tramite @add_tipo sono disponibili i seguenti tipi aggiuntivi:
       *                    numeroFormattato : per numeri formattati con migliaia e/o decimali es: 1.000.000,23
       *
       * @return array
       */
  public function set_ordine(array $ordinamento=array(),$inclusi=true,array $defaults=array(),$tipi=array()){
		 $this->f['set_ordine']=func_get_args();
		 return $this;
 }
 
 
 
/**
       * chiamata in fase di prepara_codice per evitare conflitti nell'assegnazione dell'ID
       * @ignore
       */
 protected function _set_ordine(array $ordinamento=array(),$inclusi=true,array $defaults=array(),$tipi=array()){
 	 if (in_array('numeroFormattato',$tipi)) $this->add_tipo('numeroFormattato','numeric'," s.replace('.','').replace(',','.')");
 	 if (in_array('soloCaratteri',$tipi)) $this->add_tipo('soloCaratteri','text'," s.replace(/[^a-zA-Z0-9]+/,'').toLowerCase()");

 	 $this->add_code("var allTH = {$this->JQid()}.find('th');
		 				  var ordinamento=".self::quote($ordinamento).";
		 				  var inclusi=".self::quote($inclusi).";
		 				  var defaults=".self::quote($defaults,'{}').";
		 				  var tipi=".self::quote($tipi,'{}').";
						  var cookies=".self::$identificatore.".parseJSON('".stripslashes(($_COOKIE['jqCookieJar_tablesorter']?$_COOKIE['jqCookieJar_tablesorter']:'{}'))."');
						  	
						  for (i=0;i<allTH.length;i++)
		 				  	if((inclusi && ordinamento.length==0) ||
		 				  	   ((".self::$identificatore.".inArray(i,ordinamento)!=-1)==inclusi)
		 				  	   )
		 				  	   	{
		 				  	   	 if(!cookies[{$this->JQid()}.prop('id')]) 
		 				  	   	 	{
      	 	      	       		  	if(defaults[i]=='+') opzioni['sortList'][opzioni['sortList'].length]=[i,0];
      	 	      	       		  	if(defaults[i]=='-') opzioni['sortList'][opzioni['sortList'].length]=[i,1];
      	 	      	       		  	}
      	 	      	       		  if(tipi[i]!=undefined) opzioni['headers'][i]={sorter:tipi[i]};
      	 						}
      	 					else opzioni['headers'][i]={sorter:false};
      	 			       ");

	 
 }


 
/**
       *
       * Ricorda tramite cookie l'ultimo ordinamento impostato
       *
       */
 public function set_order_memory(){


//       		self::add_src('jquery/tablesorter/jquery.cookie.js');

//       		self::add_src('jquery/tablesorter/jquery.json.js');
			parent::set_order_memory();
 		self::add_src('jquery/tablesorter/jquery.cookiejar.js');
 		self::add_src('jquery/tablesorter/jquery.tablesorter.cookie.js');
 		$this->add_code("opzioni['widgets']=['cookie']");
 		return $this;
 }


/**
 * @ignore
 */
  public function get_html(){
 		if(!self::$tema) self::set_tema();
 		self::add_css(self::$tema,'tablesorter');
 		return parent::get_html();
 }



}