<?php
/**
 * Contains Gimi\myFormsTools\PckmyTables\myJQDataTable.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\PckmyTables;
 

use Gimi\myFormsTools\myCSS;
use Gimi\myFormsTools\myDizionario;
use Gimi\myFormsTools\PckmyFields\myField;
use Gimi\myFormsTools\PckmyFields\myDate;
use Gimi\myFormsTools\PckmyJQuery\myJQuery;
use Gimi\myFormsTools\PckmyJQuery\myJQueryUI;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQDatepicker;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQInputMask;
use Gimi\myFormsTools\PckmyTables\myTable;
use Gimi\myFormsTools\PckmyUtils\myCharset;
                                                
class myJQDataTable extends myJQuery implements myJQmyTable{
    /**
     * @ignore
     */
    protected static $serverSidevars=array();
	/**
	 * @ignore
	 */
	protected $JQ,$myTable,
	$opzioni,$nFiltri=0,$copyLanguage='',
	 $dichiarazione,$on=array(),
	 $scriptRange,$Labels=array(),$hiddenBeforeLoading=false,
	 $testiLabel=array('legenda'=>'Filtri','cerca'=>'Cerca','da'=>'Da','a'=>'A'),
	 $funzioni,$language,$dizionario,$rowReordering,$temaJQUI=false;
	
	
	/**
	 *
	 * @param $id string e' il selettore JQuery
	 * @param string $sDom indica come posizionare gli oggetti seguenti:
	 *
    		l - Length changing
    		f - Filtering input
    		t - The table!
    		i - Information
    		p - Pagination
    		r - pRocessing
    		R - colReorder
    		T - toolbar
    		C - colVis
    		< and > - div elements
    		<"class" and > - div with a class
    		Examples: <"wrapper"flipt>, <lf<t>ip>

	 *
	 */
	public function __construct($id='',$sDom='<"top"i>rt<"bottom"flp><"clear">') {
		parent::__construct ( $id );
		self::add_src(self::$percorsoMyForm.'jquery/dataTables/js/jquery.dataTables.min.js');
		self::add_src(self::$percorsoMyForm.'jquery/myJQUtils/myJQDataTable.js');
	

		/*
		 * Imposto i parametri di default
		 */
		$this->paging=false;
		$this->filter=false;
		$this->info= false;
		$this->ordering=false;
		
		
		
		if ($sDom) $this->set_dom($sDom);
		$this->set_lingua('IT');
		$this->JQ=myJQuery::JQvar();
   }
	
	
	/**
	 * Restituisce il namspace della classe
	 * @return string
	 */
	 public function get_widget(){
		return 'myJQDataTable';
	}
	
	/**
	 * @ignore
	 */
	 public function application(myTable &$myTable) {
		$this->myTable=&$myTable;
	}
	
	static function &get_ServerSidePars($idEvento){
	    $serverSidePars=array();
	    if(!isset($_POST['myJQDatatable'][$idEvento]['draw'])) return $serverSidePars; 
         
	    $serverSidePars=$_POST['myJQDatatable'][$idEvento];
	    
	    $serverSidePars['ordine']=array();
        if(isset($serverSidePars['order'])) 
                    for($i=0;$i<count($serverSidePars['order']);$i++) 
                            $serverSidePars['ordine'][$serverSidePars['order'][$i]['column']]=($serverSidePars['order'][$i]['dir']=='asc'?'+':'-');
                           
                            
        $stdsearch=array();
        if(isset($serverSidePars['search']['value']) && $serverSidePars['search']['value'])
              $stdsearch=array('type'=>$serverSidePars['search']['regex']=='true'?'regex':'normal',
                               'val'=>$serverSidePars['search']['value']);
          
          
        $serverSidePars['ricerca']=array();
        while(isset($serverSidePars['columns'][$i])) {
                  if($serverSidePars['columns'][$i]['searchable']=='true')
                       if($stdsearch)      $serverSidePars['ricerca'][$i]=$stdsearch;
                                      elseif($serverSidePars['columns'][$i]['search']['value'])
                                           $serverSidePars['ricerca'][$i]=array('type'=>$serverSidePars['columns'][$i]['search']['regex']=='true'?'regex':'normal',
                                                                                 'val'=>$serverSidePars['columns'][$i]['search']['value']);
                  $i++;
                  }
       return $serverSidePars;           
	}
	
	
	
	
	 	
	 public function get_id(){
		if(!$this->myTable) return parent::get_id();
		return '#'.$this->myTable->get_id();
	}
	
	/**
	 * Restituisce il nome della variabile JS che rappresenta la DataTable
	 * @return string
	 */
	function  myJQVarName(){
		return "myDatatable_".md5($this->get_id());
	}
	
	/**
	 * Metodo che prepara il codice da visualizzare
	 *
	 */
	 public function prepara_codice(){
        //print_r($this->funzioni); exit;
        /*parent::add_code("{$this->JQ}('{$this->get_id()}').dataTable().rowReordering()");
        return;
        */
	  
    	if($this->rowReordering!==null && !$this->ordering) 
		                          {$this->ordering=true;
		                           parent::add_code("var cols=[];
		                                            {$this->JQvar()}('{$this->get_id()} th').each(function(){cols[cols.length]=cols.length});
		                                            ");
		                            $this->opzioni['columnDefs'][]='{orderable:false, targets:cols}';
		                           }
		                           
		if(is_object($this->myTable) && $this->myTable->get_n_rows()<=1 && !$this->serverSide) {		                           
		                                $this->ordering=false;
                                        unset( $this->funzioni['set_filtri']);
		                          }
        
        if($this->funzioni)  foreach ($this->funzioni as $fun=>$f)
		                                  foreach ($f as $pars)	{
		                                      call_user_func_array(array($this,"_{$fun}"), $pars);
		                                  }
		IF($this->language) $this->opzioni['language']=$this->language;
		if($this->copyLanguage) $this->opzioni['language']=substr($this->opzioni['language'],0,-1).','.
		                                                       substr(self::encode_array($this->copyLanguage),1) ;
		$codice=" {$this->myJQVarName()}= {$this->JQ}('{$this->get_id()}').dataTable(".self::quote($this->opzioni).")";
		if(isset(	$this->funzioni['set_filtri']) && 	$this->funzioni['set_filtri']) $codice.="\n;{$this->myJQVarName()}_filters.setDatatable({$this->myJQVarName()});";
		if($this->rowReordering!==null)    $codice.=".rowReordering(".($this->rowReordering?self::quote($this->rowReordering,'{}'):'').")";
		foreach ($this->on as $evt=>$code) $codice.=".on('$evt',$code)";
		parent::add_code($codice,-1);
		
	}
	
	
	/**
	 * @ignore
	 * @param array $pars
	 * @param array $data
	 */
	 public static function build_json($idEvento,$data) {
	    $pars=self::get_ServerSidePars($idEvento);
	    if(!isset($data[0])) $data=array_values($data);
	    return json_encode(array(
                	        "draw"=> intval($pars['draw']),
                            "recordsTotal"=>    intval(self::$serverSidevars[$idEvento]['unfiltered']),
                            "recordsFiltered"=> intval(self::$serverSidevars[$idEvento]['filtered']),
                             (is_array($data)?"data":"error")=>$data
                	        ));       
	}
	
	/**
	 * @ignore
	 * @param string $par
	 * @param mixed $val
	 */
	 public static function set_ServerSidePars($idEvento,$par,$val){
	   self::$serverSidevars[$idEvento][$par]=$val;
	}
	
	
	
	/**
	 * @ignore
	 */
	 public function __set($var,$val){
		$this->opzioni[$var]=$val;
		}
	
	/**
	 * @ignore
	 */
	 public function __get($var){
	    if(isset($this->opzioni[$var])) return $this->opzioni[$var];
	}


	/**
	 * Imposta l'ordine iniziale della tabella
	 * @param array $colonne array associativo n.Colonna=>+|-
	 *
	 */
	public function set_ordine_iniziale($colonne=array()){
		foreach ($colonne as $key => $value)
			if($value=='-' || $value=='desc') $ordine[]=array($key,'desc');
										else  $ordine[]=array($key,'asc');
		$this->opzioni['sorting']=$ordine;
		return $this;
	}
	
	/**
	 * Restituisce l'elenco di tutte le traduzioni di default, al netto di eventuale dizionario
	 * 
	 * @see http://datatables.net/reference/option/#Internationalisation
	 * @param string $lingua lingua da usare
	 */
	 public function get_traduzioni($lingua='IT'){
	    if(!$this->language) $this->set_lingua($lingua);
	    return json_decode($this->language,true);
	}
	
	/**
	 * Modifica le traduzioni passate (Sovrascrive solo quelle passate lasciando le precedenti intatte)
	 * es: $dt->set_traduzioni('paginate'=>array('first'=>'Inizio'));
	 *  
	 * @param array $traduzioni
	 * @param string $lingua
	 */
	 public function set_traduzioni(array $traduzioni,$lingua='IT') {
	    $termini=$this->get_traduzioni($lingua);
	 
	    foreach ($traduzioni as $k1=>&$l1) 
	        if(!is_array($l1)) $termini[$k1]=$l1;
	               else foreach ($l1 as $k2=>&$l2)
	                           if(!is_array($l2)) $termini[$k1][$k2]=$l2;
	                               else foreach ($l2 as $k3=>&$l3) $termini[$k1][$k2][$k3]=$l3;
	    $this->language=self::encode_array($termini);
	    return $this;
	}
	
	
	
	/**
	 * Nasconde le colonne indicate
	 * @param array $colonne
	 *
	 */
	public function set_col_hidden($colonne){
		$this->opzioni['columnDefs'][]=array('visible'=>false, 'targets'=>$colonne);
		return $this;
	}
	
	
	public function set_dom($dom){
	    $this->dom=str_replace('T','B',$dom);
	    return $this;
	}
	
	
	
	/**
	 * Imposta su quali colonne non effettuare la ricerca con il campo Search
	 * @param array $colonne
	 *
	 */
	public function set_col_unsearchable($colonne){
		$this->searching=true;
		$this->opzioni['columnDefs'][]=array('searchable'=>false, 'targets'=>$colonne);
		return $this;
	}
	
	
	
	/**
	 * Imposta una classe css per evidenziare la riga su cui si passa
	 * @param string $classe
	 *
	 */
	public function set_classe_evidenziatore($classe='highlighted'){
		$this->sortClasses= false;
		$this->add_code("{$this->JQ}('{$this->get_id()}  tr').hover( function(){ {$this->JQ}(this).find('td').addClass('{$classe}' );	},
																	 function(){ {$this->JQ}(this).find('td.{$classe}').removeClass('{$classe}' );	}
																	);"
		);
		return $this;
	}
	
	
	/**
	 * Imposta su quali colonne non si applicherà il sort
	 * @param array $colonne
	 */
	public function set_col_unsortable($colonne=array()){
		$this->ordering=true;
		$this->opzioni['columnDefs'][]=array('sortable'=>false, 'targets'=>$colonne);
		return $this;
	}
	

	/**
	 * 
	 * @param string $cosa
	 * @param string $con
	 */
	public function set_traduzione($cosa,$con){
	    $this->language=json_decode($this->language,true);
	    $this->language[$cosa]=$con;
	    $this->language=json_encode($this->language);
	    return $this;
	}
	
	/**
	 * Imposta la traduzione leggendo i parametri da un file
	 * @param string $lingua (passare la targa internazionale del paese
	 * @param myDizionario $dizionario se inserito vengono tradotte anche le label
	 *
	 */
	public function set_lingua($lingua='IT',$dizionario=''){
	    static $languages;
		$lingua=strtoupper($lingua);
		$m=array();
		if(!isset($languages[$lingua])){
		                   $language=myCharset::utf8_decode(str_replace(array("\n","\r","\t"),'',file_get_contents(__DIR__."/../../jquery/dataTables/plugins/i18n/{$lingua}.txt")));
		                   preg_match('@({.+})@', $language,$m);
		                   $languages[$lingua]=$m[1];
		                  
		                  }
		$this->language=$languages[$lingua];
		if(!$dizionario) 
		              {$dizionario=new myDizionario($lingua);
		               $dizionario->log_errori(false);
		              }
		$this->dizionario=$dizionario;
		$this->set_label_filtri();
		return $this;
	}
	
	/**
	 * Include il .js relativo ad un'estensione dataTable
	 * 
	 * @see https://datatables.net/extensions/index
	 * @param string $extName nome case sensitive del plugin
	 */	
	  public function add_extension_src($extName,$cssFile='',$jsFile='') {
	    $extNameFile=strtolower($extName[0]).substr($extName,1);
	    
	    if($jsFile)  self::add_src("jquery/dataTables/extensions/$extName/js/$jsFile");
	           else  self::add_src("jquery/dataTables/extensions/$extName/js/dataTables.$extNameFile.min.js");
	    if($cssFile) self::add_css("jquery/dataTables/extensions/$extName/css/$cssFile");
	           else  {if(!$this->temaJQUI)  self::add_css("jquery/dataTables/extensions/$extName/css/$extNameFile.dataTables.min.css");
	                                   else self::add_css('jquery/dataTables/plugins/integration/jqueryui/dataTables.jqueryui.css');
	                 }

	}
	
	/**
	 * Imposta i tipi di dato usati nel sort delle colonne
	 * @param array $colonne array associativo n_col=>date-euro|ip-address|natural|num-html
	 *                                   oppure n_col=>array('name'=>stringa con nome mnemonico del criterio di ordinamento
	 *                                                       'pre'=>'function(a) {return a}', //restituisce il valore di a eventualmente manipolato
	 *                                                       'asc'=>'function(a,b){ return -1|0|1}',  //effettua il confronto tra a e b durante ordiamento ascendente (facoltativa) 
	 *                                                       'desc'=>'function(a,b){ return -1|0|1}',  //effettua il confronto tra a e b durante ordiamento discendente (facoltativa)   
	 *                                     
	 * @see http://www.datatables.net/plug-ins/sorting/
	 */
	public function set_tipi_sort($colonne){
		$this->ordering=true;
		$stringaSort=array();
		foreach ($colonne as $key => $value){
		    if(is_array($value))
		                  { if(!isset($value['name']) || !$value['name']) $value['name']=$this->id.'_'.$key;
		                    if(!isset($value['pre'])  || !$value['pre'])  $value['pre']='function(a) {return a}';
		                    if(!isset($value['asc'])  || !$value['asc'])  $value['asc']="function ( a, b ) {return ((a < b) ? -1 : ((a > b) ? 1 : 0));}";
		                    if(!isset($value['desc']) || !$value['desc']) $value['desc']="function ( a, b ) {return ((a < b) ? 1 : ((a > b) ? -1 : 0));}";
		                    $f="{$this->JQ}.extend( {$this->JQ}.fn.dataTableExt.oSort, {'{$value['name']}-pre':{$value['pre']},'{$value['name']}-asc': {$value['asc']},'{$value['name']}-desc': {$value['desc']}})";
		                    $value=$value['name'];
		                    $stringaSort[$value]=true;
		                    $this->add_common_code($f);
		                  }
             $this->opzioni['columnDefs'][]= array("type"=>$value ,"targets"=>array($key));
            if(!isset($stringaSort[$value]) && is_file(myField::get_PathSito()."/jquery/dataTables/plugins/sorting/{$value}.js")) $stringaSort[$value]=self::add_src("jquery/dataTables/plugins/sorting/{$value}.js");
	       }
		return $this;	
	}
	
	
	/**
	 * Aggiunge funzionalita alla tabella nel sDom indicare B
	 * @see http://datatables.net/reference/button/
	 * @param array $pulsanti es. ('copy','print','csv','excel','pdf')
	 * @param array $extra parametri extra per i pulsanti    array("pdf"=>array('text'=>'Esporta PDF','extend'=>'pdfHtml5') )
	 */
	public function set_tool(array $pulsanti=array('csv','pdf'),array $extra=array()){
	    $this->add_extension_src('Buttons');
	    if(!$this->buttons) $this->buttons=array();
	    if(strpos(preg_replace('@"[^"]+"@','',$this->dom),'B')===false) $this->dom="B<\"clear\">{$this->dom}";
	    foreach ($pulsanti as $k=>&$v)  
	                   if(!is_array($v)) {$pulsanti[strtolower($v)]=array('extend'=>strtolower($v));
	                                      unset($pulsanti[$k]);
	                                     }
	    
	    foreach ($extra as $k=>$e)  {  if(!isset($pulsanti[strtolower($k)]) || !$pulsanti[strtolower($k)]) $pulsanti[strtolower($k)]=array();
	                                   if(!is_array($e)) $e=array();    
	                                   $pulsanti[strtolower($k)]=array_merge($pulsanti[strtolower($k)],$e);
	                                 }
	    foreach ($pulsanti as $k=>&$v) {	            
	        
	            switch ($k) {
	                case 'copy':
	                case 'copyHtml5':
	                    
	                    if(!isset($v['text'])) $v['text']=($this->dizionario?$this->dizionario->trasl('Copia'):'COPIA');
	                    $v['text']=($this->dizionario?$this->dizionario->trasl('Copia'):'COPIA');
	                    $this->copyLanguage=array('buttons'=>array('copySuccess'=>array(
                                                                     1=> ($this->dizionario?$this->dizionario->trasl('Una riga copiata negli appunti'):"Una riga copiata negli appunti"),
                                                                   '_'=> ($this->dizionario?$this->dizionario->trasl('%d righe copiate negli appunti'):"%d righe copiate negli appunti")
                                        	                    ),
	                                              'copyTitle'=> ($this->dizionario?$this->dizionario->trasl('Copia negli appunti'):'Copia negli appunti')
	                                             ));
	                    self::add_src("jquery/dataTables/extensions/Buttons/js/buttons.html5.min.js");
	                    self::add_src("jquery/dataTables/extensions/Buttons/js/buttons.flash.min.js");
	                    break;
	                case 'print':
	                    if(!isset($v['text'])) $v['text']=($this->dizionario?$this->dizionario->trasl('Stampa'):'STAMPA');
	                    self::add_src("jquery/dataTables/extensions/Buttons/js/buttons.print.min.js");
	                break;
	                
	                
	                case 'csv':
	                 //   $v['extend']="{$k}Html5";
	                    if($k=='csv' && !isset($v['fieldSeparator'])) $v['fieldSeparator']=';';
	                    if(!isset($v['text'])) $v['text']=strtoupper( $k);
	                    self::add_src("jquery/dataTables/3parti/jszip.min.js");
	                    self::add_src("jquery/dataTables/extensions/Buttons/js/buttons.html5.min.js");
	                    self::add_src("jquery/dataTables/extensions/Buttons/js/buttons.flash.min.js");
	                break;
	                
	                case 'excel':
	                case 'pdf':
	                    /*self::add_src("jquery/dataTables/3parti/pdfmake.min.js");
	                    self::add_src("jquery/dataTables/3parti/vfs_fonts.js");
	                    
	                    self::add_src("jquery/dataTables/extensions/Buttons/js/buttons.html5.min.js");
	                    */
	                    $v['extend']="{$k}Flash";
	                    if(!isset($v['text'])) $v['text']=strtoupper( $k);
	                    self::add_src("jquery/dataTables/extensions/Buttons/js/buttons.flash.min.js");
	                 break;   
	                
	            }
	            if(!isset($v['title'])) $v['title']="'doc'+{$this->JQvar()}('title').html()";
	    }
	                 
	    $this->buttons= array_merge($this->buttons,array_values($pulsanti));
	    return $this;
	}
	
	

	/**
	 * Aggiunge funzionalità alla tabella nel sDom indicare t e poi s
	 * @param string $altezzaMax
	 */
	public function set_scrollerY($altezzaMax='200px'){
	 
		$this->scrollY= $altezzaMax;
		$this->paging=false;
		return $this;
	}
	
	/**
	 * Alias di set_scrollerY
	 * @ignore
	 * @deprecated
	 */
	public function set_scroller($altezzaMax='200px'){
	   return $this->set_scrollerY($altezzaMax);        
	}
	
	/**
	 * Aggiunge funzionalità alla tabella nel sDom indicare t e poi s
	 * @param string $larghezzaMax
	 */
	public function set_scrollerX(){
	  
	    $this->scrollX= true;
	    $this->sScrollXInner='100%';//$larghezzaMax;
	    return $this;
	}
	


	/**
	 * Permette di impostare l'integrazione grafica con tema jqueryUI 
	 * 
	 * @see https://datatables.net/examples/styling/jqueryUI.html
	 * @param bool $status se true attiva
	 * @return myJQDataTable
	 */
	 public function set_integration_jqueryui($status=true){
	    $this->temaJQUI=$status;
	    $this->jQueryUI=(boolean) $status;
	    return $this;
	}
	
	
	
	
	/**
	 * Permette di impostare l'header fisso 
	 * 
	 * @see https://datatables.net/extensions/fixedheader/options
	 * @param array $options opzioni come da documentazione
	 * @return myJQDataTable
	 */
	public function set_fixed_header($options=array('top'=>true)){
	    $this->add_extension_src('FixedHeader');
	    $this->add_code("new ".myJQuery::JQvar().".fn.dataTable.FixedHeader({$this->myJQVarName()},".self::quote($options,'{}').");");
	    return $this;
	}
	


	/**
	 * Permette di riordinare le colonne
	 * nel sDom indicare R
	 *
	 */
	public function set_col_reorder(){
	    $this->add_extension_src('colReorder');
		if(strpos(preg_replace('@"[^"]+"@','',$this->dom),'R')===false) $this->dom="R{$this->dom}";
		$this->add_code("{$this->JQvar()}('{$this->get_id()} th').css('cursor','e-resize')");
		return $this;
	}
	
	/**
	 * Permette di riordinare le righe
	 * @see http://jquery-datatables-row-reordering.googlecode.com/svn/trunk/index.html
	 * @param array $ServerSide per le opzioni vedere  https://code.google.com/p/jquery-datatables-row-reordering/wiki/ServerSideIntegration
	 * @param int $colonna_indice indica una colonna con un indice numerico a partire da 1, se omessa viene aggiunta una colonna nascosta 
	 */
	
	public function set_row_reorder(array $ServerSide=array(),$colonna_chiave=0){
	    self::add_src("jquery/dataTables/plugins/rowreordering/jquery.dataTables.rowReordering.js");
	    $this->rowReordering=$ServerSide;
	    $this->iIndexColumn=$colonna_chiave;
	    if($colonna_chiave!==0) $this->add_code("{$this->JQvar()}('{$this->get_id()} tr').css('cursor','n-resize');",-1);
	                       else {
                            	    $this->add_code("var nRows=0;",-1);
                            	    $this->add_code("{$this->JQvar()}('{$this->get_id()} tr').css('cursor','n-resize').each(
                            	                                                                                           function(){   var id={$this->JQvar()}(this).prop('id');
                            	                                                                                                         if(!id)  {$this->JQvar()}(this).prop('id','row_'+nRows);
                            	                                                                                                         if(nRows==0) {$this->JQvar()}(this).children().first().before('<th style=\"display:none\"></th>');
                            	                                                                                                                 else {$this->JQvar()}(this).children().first().before('<td style=\"display:none\">'+nRows+'</td>');
                            	                                                                                                         nRows++; 
                            	                                                                                                      }
                            	                                                                                           )",-1);
                            	}
                     	
        $this->add_code("{$this->JQvar()}('{$this->get_id()} thead .sorting').css('background-image','none');",1);
        
	    return $this;
	    
	  /* Aggiunto questo alla riga 231 di jquery/dataTables/plugins/rowreordering/jquery.dataTables.rowReordering.js
                        sostituito 
                        
                         for(var i=0;i<matrix.length;i++) {
                        	if(startRow==null && matrix[i][properties.iIndexColumn]==oState.iCurrentPosition) startRow=matrix[i];
                        	if(endRow==null   && matrix[i][properties.iIndexColumn]==oState.iNewPosition)     endRow=matrix[i];
                        	if(startRow!=null && endRow!=null) break;
                        	}
                        	
                        con	
                        	
                        for(var i=0;i<$dataTable.fnGetData().length;i++) {
                        	if(startRow==null && $dataTable.fnGetData(i)[properties.iIndexColumn]==oState.iCurrentPosition) startRow=$dataTable.fnGetData(i);
                        	if(endRow==null   && $dataTable.fnGetData(i)[properties.iIndexColumn]==oState.iNewPosition)     endRow=$dataTable.fnGetData(i);
                        	if(startRow!=null && endRow!=null) break;
                        	}
                        
 */    
	    
	    
	}
	
	
	
	/**
	 * Permette di nascondere/visualizzare le colonne nel sDom del costruttore bisogna inserire C
	 * @deprecated
	 *
	 */
	public function set_col_vis($colonne_escluse=array(),$testo='Mostra/nascondi colonne'){
		/*if($testo) $aggiunta['buttonText']=$testo;
		if ($colonne_escluse) $aggiunta['aiExclude']=$colonne_escluse;
		if ($aggiunta) {$this->oColVis=$aggiunta;
                        $this->add_extension_src('ColVis');
						if(strpos(preg_replace('@"[^"]+"@','',$this->dom),'C')===false) $this->dom="C{$this->dom}";
						}
		return $this;
	*/
	}
	
	
	
	
	
	/**
	 * Imposta i filtri
	 *
	 * @param array  $colonne (colonne su cui impostare i filtri es:colonna=>tipo)
	 * Tipi:
	 * text (campo di testo)
	 * select (campo con selezione a discesa con tutti i possibili valori presenti all'interno della colonna selezionata)
	 * selectdateit (campo con selezione a discesa con tutte le date presenti all'interno della colonna selezionata)
	 * rangedateit (due campi data con i quali si possono scegliere la data minima e massima dei valori da cercare all'interno della colonna)
	 * range (due campi testo)
	 * rangefloat (due campi testo per intervalli di numeri float)
	 * rangeint (due campi per intervalli di numeri interi)
	 * oppure un array('tipo'=>select|text,
	 *                 'nome'=>stringa Nome Da inserire nel filtro in sostituzione di quello ricavato dall'header 
	 *                 'ricalcolaselect'=>funzione js per ricalcolare i dati che verranno usati se è un filtro di tipo select - function(a):string
	 *                 'filtroselect'=>funzione js per filtrare i dati che verranno usati se è un filtro di tipo select - function(a):bool
	 *                 'ordineselect' =>funzione js per ordinare le opzioni se è un filtro di tipo select - function(a,b):bool
	 *                 'descrizioneselect'=>funzione js per cambiare la descrizione che deve comparire a fronte del valore nel filtro di tipo select - function(v):string
	 *                 ) 
	 * @param array $RegEx      imposta il filtro tramite le espressioni regolari nei campi indicati ma deve essere scritto come risultato di una funzione es per dire che la seconda colonna "inizia per" scrivere 1=>'function(a){return '^'+a;}  
	 * 
	 */
	
	public function set_filtri($colonne=array(),$RegEx=array()){
		$this->funzioni['set_filtri'][]=func_get_args();
		return $this;
	}



	/**
	 * Imposta il testo da visualizzare Davanti ai filtri
	 * @param array $testiLabel  di default 'legenda'=>'Filtri','cerca'=>'Cerca','da'=>'Da','a'=>'A'
	 */
	public function set_label_filtri($testiLabel=array('legenda'=>'Filtri','cerca'=>'Cerca','da'=>'Da','a'=>'A')){
	   	if (count($testiLabel)>0) $this->testiLabel=array_merge($this->testiLabel,$testiLabel);
	   	if($this->dizionario && $this->dizionario->get_al()!='IT') foreach ($this->testiLabel as &$testo) $testo=$this->dizionario->trasl($testo);
	   	return $this;
	}
	
	
	 public function set_on($evt,$action) {
	    $this->on[$evt]=$action;
	    return $this;
	}
	

	/**
	 * @ignore
	 */
	 private function _set_filtri($colonne=array(),$RegEx=array()){
	 	if (!$RegEx) $RegEx=array();
	 	$funzioniFiltroR="";
		$nFiltro=++$this->nFiltri;
		$search=false;
		$this->filter=true;
		if($this->stateSave) {
		                if($this->fnStateLoadParams) $predLoad="({$this->fnStateLoadParams})(oSettings, oData);";
                		$this->fnStateLoadParams="function (oSettings, oData) {//salva in settings i valori di filtri caricati 
                		                                    var esito=true;  
                		                                    try{ oSettings.filtersStatus={}; 
                		                                         if(oData.filtersStatus==undefined) oData.filtersStatus={};
                		                                         esito={$this->myJQVarName()}_filters.presetFilters(oData.filtersStatus,'DataTables_'+oSettings.sInstance+'_'+location.pathname);
                		                                         $predLoad 
                		                                       } catch(err) {return false;}
                		                                      return esito;
                		                                   }  ";
                		if($this->fnStateSaveParams) $predSave="({$this->fnStateSaveParams})(oSettings, oData);";
                		$this->fnStateSaveParams="function (oSettings, oData) {//salva in filtersStatus i valori di filtri  
                                                            try{ var OData=oData;
                                                                 oData.filtersStatus={};  
                		                                         {$this->JQ}('#filtro_{$nFiltro} .search_init').add('#filtro_{$nFiltro} .filtri_range').each( function(index){OData.filtersStatus[{$this->JQ}(this).prop('id')]={'class':{$this->JQ}(this).prop('class'),'val':{$this->JQ}(this).val(),'type':{$this->JQ}(this)[0].selectedIndex,'type':{$this->myJQVarName()}_filters.getType({$this->JQ}(this)[0])}});
                		                                         $predSave
                		                                       } catch(err) {}
                		                                      
                		                                   }  ";
		              }
		//$this->creaObject=true;
		if(count($colonne)>0) $this->dom=str_replace('f','',$this->dom);
	    foreach ($colonne as $i=>$filtro) {
	        if(!is_array($filtro)) {if(isset($filtro[$i]) && $filtro[$i]=='selectdata') $filtro[$i]='selectdatait';
		                                   $filtro=array('tipo'=>$filtro);
		                                 }
		          
		           $filtro['tipo']=strtolower(trim((string) $filtro['tipo']));
		           switch ($filtro['tipo']) {
		               case 'selectdata'   :$filtro['tipo']='selectdateit';break;
		               case 'selectdatait' :$filtro['tipo']='selectdateit';break;
		               case 'selectdate'   :$filtro['tipo']='selectdateit';break;
		               case 'rangedatait'  :$filtro['tipo']='rangedateit';break;
		              }
		           if(!$filtro['tipo']) {unset($colonne[$i]);continue;}
		           $colonne[$i]=$filtro;
	               if(stripos($filtro['tipo'], 'range')===0) $funzioniFiltroR.=$this->add_funzioni_filtro("filtro_{$nFiltro}_{$i}", $i, $filtro['tipo']);
	               }
	         
	              
	             

		$funzioniFiltro="
	var {$this->myJQVarName()}_filters={
	arraycolonne:{},
	arrayRegEx:{}, 
	dataTable:null,
	getType:function(ele){return ({}).toString.call(ele).match(/\s([a-zA-Z]+)/)[1].toLowerCase();},
    presetFilters:function(filtersStatus,idData){
                    try{ localStorage.removeItem(idData);}catch(e){}
                    var self=this;
                    var ok=true;
                    for (var id in filtersStatus) {
                               if(filtersStatus[id].val)
                                  {
                                   var filtro={$this->JQ}('#'+id);
                                   if(!filtro || 
                                      filtro.prop('class')!=filtersStatus[id].class ||
                                      this.getType(filtro[0])!=filtersStatus[id].type ||
                                      filtro.val(filtersStatus[id].val).val===null
                                      ) ok=false;
                                   if(!ok) break;
                                  }
                    }
                    if(!ok) for (id in filtersStatus) 
                                    {
	                                 {$this->JQ}('#filtro_{$nFiltro} .search_init').add('#filtro_{$nFiltro} .filtri_range').each( function(index){{$this->JQ}(this).val('');});     
	                                 return false;
	                               }
                    return true;            
	               },

	setDatatable:function(dt){
	     this.dataTable=dt;
	     var self=this;
	 	{$this->JQ}('#filtro_{$nFiltro} .search_init').bind('keyup change', function () { 
		        var idx=parseInt({$this->JQ}(this).prop('class').split(' ')[1].replace(/^idx_/,''));
		       
             	if (self.arrayRegEx[idx]) self.dataTable.fnFilter(self.arrayRegEx[idx](this.value), idx,true );
				                     else self.dataTable.fnFilter(this.value,idx,false );
    		  } );
	   ".($this->stateSave?" {$this->JQ}( window ).unload(self.dataTable.fnDraw());":'')." 
	 },    
	 
	init:function ()
	  { var self=this;
	    $funzioniFiltroR;
		
		this.arraycolonne=".self::encode_array($colonne,'{}').";
		this.arrayRegEx=".self::encode_array($RegEx,'{}').";
		".(!$search? "{$this->JQ}('{$this->get_id()}_filter').css('display','none');" :'')."

		{$this->JQid()}.before('<fieldset style=\"clear:both\" id=\"".str_replace('#','',$this->get_id())."Fieldset\"><legend>{$this->testiLabel['legenda']}</legend><div id=\"filtro_$nFiltro\"></div></fieldset>');
		{$this->JQid()}.find('tbody tr:first td').each
					(function(i)
							{
							if(self.arraycolonne!={})
										{
										var campo={$this->JQid()}.find('thead tr:first th:eq('+ i +')').text();
									    var stringa='';
										if (self.arraycolonne[i]==undefined) return;
										 						   else var stringa=self.arraycolonne[i];
										if(({}).toString.call(stringa).match(/\s([a-zA-Z]+)/)[1].toLowerCase()=='object')
										     {
										      if(stringa['nome'])              campo=stringa['nome'];
										      if(stringa['filtroselect'])      myJQDataTable.FunzioneFiltroSelect=stringa['filtroselect'];
										      if(stringa['ordineselect'])      myJQDataTable.FunzioneOrdineSelect=stringa['ordineselect'];
										      if(stringa['descrizioneselect']) myJQDataTable.FunzioneDescrizioneSelect=stringa['descrizioneselect'];
										      if(stringa['ricalcolaselect'])   myJQDataTable.FunzioneRicalcolaSelect=stringa['ricalcolaselect'];
                                              stringa=stringa['tipo'];
										      } 
										campo='<span style=\"font-weight:bold\" class=\"filtro_{$nFiltro}_campo\">'+campo+'</span>';   
										if (stringa=='text' )	{
										 	{$this->JQ}('#filtro_$nFiltro').append('<div  style=\"float:left;margin-right:1em\"><label for=\"filtro_{$nFiltro}_'+i+'\">{$this->testiLabel['cerca']}: ' + campo + '</label><br /><input type=\"text\" id=\"filtro_{$nFiltro}_'+i+'\" class=\"search_init idx_'+i+'\" ></div>');
											}
										else if(stringa.indexOf('select')==0) {
                                		    myJQDataTable.DataIt=(stringa=='selectdateit');
                                		    if(!self.arrayRegEx[i]) self.arrayRegEx[i]=function(v){return '^\s*'+v.trim()+'\s*';}
                                		    var htmlSelect=myJQDataTable.CreateSelect( {$this->JQid()},i,'filtro_{$nFiltro}_'+i, 'search_init idx_'+i,{$this->JQ});
                                		    if(htmlSelect) {
                											{$this->JQ}('#filtro_$nFiltro').append('<div  style=\"float:left;margin-right:1em\"><label for=\"filtro_{$nFiltro}_'+i+'\">{$this->testiLabel['cerca']}: ' + campo + '</label><br />'+htmlSelect+'</div>');
                											}
											}
										else if(stringa.indexOf('range')==0)
											{ 
								 			if (stringa=='rangedateit')  {$this->JQ}('#filtro_$nFiltro').append('<div style=\"float:left;margin-right:1em\">{$this->testiLabel['cerca']}: ' + campo+'<br><label for=\"MIN_filtro_{$nFiltro}_'+i+'\">{$this->testiLabel['da']}:</label>&nbsp;<input class=\"filtri_MINPICKER filtri_range\" type=\"text\" id=\"MIN_filtro_{$nFiltro}_'+i+'\" size=\"10\" maxlength=\"10\">&nbsp;&nbsp;<label for=\"MAX_filtro_{$nFiltro}_'+i+'\">{$this->testiLabel['a']}:</label>&nbsp;<input  class=\"filtri_MAXPICKER filtri_range\" type=\"text\" id=\"MAX_filtro_{$nFiltro}_'+ i + '\" size=\"10\" maxlength=\"10\"></div>');
										 		                    else {$this->JQ}('#filtro_$nFiltro').append('<div style=\"float:left;margin-right:1em\">{$this->testiLabel['cerca']}: ' + campo+'<br><label for=\"MIN_filtro_{$nFiltro}_'+i+'\">{$this->testiLabel['da']}:</label>&nbsp;<input type=\"text\" class=\"filtri_range\" id=\"MIN_filtro_{$nFiltro}_'+ i + '\">&nbsp;&nbsp;<label for=\"MAX_filtro_{$nFiltro}_'+i+'\">{$this->testiLabel['a']}:</label>&nbsp;<input type=\"text\" class=\"filtri_range\" id=\"MAX_filtro_{$nFiltro}_'+ i + '\"></div>');
                                            }
											
						
		  							}

						  }
					);
			if(!{$this->JQvar()}('{$this->get_id()}Fieldset').find('select, input').length) {$this->JQvar()}('{$this->get_id()}Fieldset').hide();
		";
		
		if($funzioniFiltroR)
		    foreach ($colonne as $key => $value)  
		        if(stripos($value['tipo'], 'range')===0) { 
		    		//if($value=='select' || $value=='selectdateit') $funzioniFiltro.="{$this->JQ}('#MIN_filtro_{$nFiltro}_{$key},#MAX_filtro_{$nFiltro}_{$key}').bind('change',function() {self.dataTable.fnDraw();});";
		            $funzioniFiltro.="{$this->JQ}('#MIN_filtro_{$nFiltro}_{$key},#MAX_filtro_{$nFiltro}_{$key}').bind('keyup',function() {self.dataTable.fnDraw();});";
		            if($value['tipo']=='rangedateit')
                                                  {  
                                                      static $sent;
                                                      if(!$sent) {$sent=true;  
                                                		        $t=new mydate('');  $t->set_calendar();
                                                		        foreach ($t->get_myJQueries() as $jq) if($jq instanceof myJQDatepicker) break;
                                                		        $jq->prepara_codice();
                                                		        $v=$jq->get_common_defaults();
                                                		        $v['onSelect']="function(){{$this->JQ}(this).trigger('keyup')}";
                                                    		    $funzioniFiltro.="  {$jq->get_common_code()}
                                                    		                        {$this->JQ}('.filtri_MINPICKER,.filtri_MAXPICKER').datepicker(".myJQuery::quote($v).");
                                                    		                        {$this->JQ}('.filtri_MINPICKER,.filtri_MAXPICKER').inputmask({placeholder:'__/__/____',oncomplete:function(){},alias:'date'});";
                                                            	  }
                                                  }
		        }
	$funzioniFiltro.="
	    return this;
	    }
     }";
	
	parent::add_code("$funzioniFiltro; {$this->myJQVarName()}_filters.init();",-1);
	
	
}



/**
 * @ignore
 */
	protected function add_funzioni_filtro($id,$key,$value){
		$baseValori="var iMin = document.getElementById('MIN_$id').value.trim();
				 	 var iMax = document.getElementById('MAX_$id').value.trim();
					 var iVersion = aData[$key].trim();
		";
		
		$controlloBase="if (iMin==0 && iMax==0) return true;
					else if (iMax==0 && iMin<=iVersion) return true;
					else if ( iMin==0 && iMax>=iVersion) return true;
					else if (iMin<=iVersion && iMax>=iVersion) return true;
					return false;";
		
		if ($value=='rangedateit') {
			$funzione="
			{$this->JQ}.fn.dataTableExt.afnFiltering.push(
				function(oSettings, aData, iDataIndex) {
					$baseValori
					iMin=iMin.replace('_','');
					if (iMin.length<10) iMin='00/00/0000';
					iMax=iMax.replace('_','');
					if (iMax.length<10) iMax='99/99/9999';
					return myJQDataTable.confrontaD(iVersion,iMin)>=0 && myJQDataTable.confrontaD(iVersion,iMax)<=0;
				}
			);";
		}
		elseif ($value=='range'){
			$funzione="
			{$this->JQ}.fn.dataTableExt.afnFiltering.push(
				function(oSettings, aData, iDataIndex) {
					$baseValori
					var iMin=iMin.toLowerCase(); 
					var iMax=iMax.toLowerCase();
					while(iMax.length<iMin.length) iMax+='z';
					while(iMin.length<iMax.length) iMin+='a';
					var iVersion=iVersion.toLowerCase().substring(0,iMax.length);
					return iVersion.localeCompare(iMin)>=0 && iVersion.localeCompare(iMax)<=0;
				}
			);";
		}
		elseif ($value=='rangefloat'){
			$funzione="
			{$this->JQ}.fn.dataTableExt.afnFiltering.push(
				function(oSettings, aData, iDataIndex) {
					$baseValori
					var iMin=parseFloat(iMin* 1);
					var iMax=parseFloat(iMax * 1);
					var iVersion=parseFloat(iVersion);
					$controlloBase
				}
			);	";
		}
		elseif ($value=='rangeint'){
			$funzione="
		       {$this->JQ}.fn.dataTableExt.afnFiltering.push(
					function(oSettings, aData, iDataIndex) {
					$baseValori
					var iMin=parseInt(iMin* 1);
					var iMax=parseInt(iMax * 1);
					var iVersion=parseInt(iVersion);
					$controlloBase
				}
			);
		
		
		";
		}
     return $funzione;
	}
	
	 public function set_responsive(){
	    $this->add_extension_src('Responsive');
	    $this->responsive=array( 'details'=>true);
	}

	 public function set_hidden_beforeLoading($status=true){
	    $this->hiddenBeforeLoading=$status;
	    return $this;
	}
	
	 public function get_html(){
	  	 $rotella='';
	      if($this->hiddenBeforeLoading) { 
	                                    if(strtolower(myJQueryUI::get_tema())=='redmond') $tema='redmond';
		                                                                             else $tema='base';
		                                                                             
                                		 $rotella="{$this->JQid()}.before(\"<div id='".str_replace('#','',$this->get_id())."_whait' style='text-align:center'><img src='/".myField::get_MyFormsPath()."icone/spinner/$tema.gif' alt='Attendere...' /></div>\");        
                                		           {$this->JQid()}.css({'display':'none'});
                                		          (".myCSS::get_css_jscode("{$this->get_id()}_wrapper{display:none} {$this->get_id()}_length{display:none} {$this->get_id()}Fieldset{display:none}".true).")();" ;       
	                                   }
	
        if(!$this->temaJQUI){
	                           self::add_css(self::$percorsoMyForm."jquery/dataTables/css/jquery.dataTables.min.css");
	                       //    $this->add_extension_src('ColVis',"dataTables.colVis.min.css","dataTables.colVis.min.js");
	                         }
	                   else {
	                         
	                         $this->add_src(self::$percorsoMyForm.'jquery/dataTables/plugins/integration/jqueryui/dataTables.jqueryui.min.js');
	                         $this->add_css(self::$percorsoMyForm.'jquery/dataTables/plugins/integration/jqueryui/dataTables.jqueryui.css');
	                         if($this->rowReordering)  $this->add_code("{$this->JQvar()}('{$this->get_id()} .DataTables_sort_icon').css('display','none');",1);
	                     //    $this->add_extension_src('ColVis',"dataTables.colvis.jqueryui.css","dataTables.colVis.min.js");
	                        }
	                    
	   if($this->hiddenBeforeLoading) {
                                      if( $this->drawCallback!='') $pred=explode('{',$this->drawCallback,2);
	                                                          else $pred=array('','}');  
	                                   $this->drawCallback="function(settings){
	                                              {$this->JQvar()}('{$this->get_id()}_whait').css({'display':'none'});   
	                                              {$this->JQvar()}('{$this->get_id()}_wrapper,{$this->get_id()}_length,{$this->get_id()}Fieldset').css({'display':'block'});
	                                              {$this->JQid()}.css({'display':'table'});

	                                              {$pred[1]}
	                                                ";
	                                   }
	   if($this->scrollX)
	           {                                
        	   self::add_common_code('function myJQDataTableRefreshCols(id,obj,settings,last){
        	                               if(last)  obj.api().columns.adjust();
        	                                          else {
                        	                               for(var i=0;i<settings.aoDrawCallback.length;i++)
                        	                                         if(settings.aoDrawCallback[i]["sName"]=="scrolling")
                        	                                            {
                        	                                             setTimeout(function(){ myJQDataTableRefreshCols(id, obj,settings,true); }, 50);
        	                                                             return;  
                        	                                                /*   if(id.width()+20<id.parent().first(".dataTables_scrollHeadInner").width())
                        	                                                                   {
                        	                                                                   id.parent().parent().find(".dataTables_scrollHeadInner:first").hide();
                        	                                                                   id.find("thead tr").removeAttr("style").prop("style","");
                        	                                                                   id.find("thead div").removeAttr("style").prop("style","");
                        	                                                                   id.find("thead th").removeAttr("style").prop("style","");
                        	                                                                   id.css("width","100%");
                        	                                                                   }*/
                        	                                            }
        	                                               setTimeout(function(){ myJQDataTableRefreshCols(id, obj,settings,false); }, 350);
        	                                               }
        	                           }');
        	   $callRefresh="var self=this; setTimeout(function(){ myJQDataTableRefreshCols({$this->JQid()}, self,settings,false)}, 350);";
        	   
        	   if(!$this->drawCallback)  $this->drawCallback="function( settings ) { $callRefresh }";
        	                       else {$this->drawCallback=explode('{',$this->drawCallback,2);
        	                             $this->drawCallback="function( settings ) { $callRefresh ".$this->drawCallback[1];
        	                             }
        	                            
	           }
	          
	   $html=parent::get_html();
	   $m=array();
	   preg_match_all('@<script.+</script>@SsUi', $html,$m);
	   return (new myJQDatepicker('')).(new myJQInputMask('')).str_replace($m[0],'',$html).
	                               "<script type='text/javascript'>
	                                               //<!--
	                                               $rotella; 
	                                          //     ".self::JQvar()."(window).resize(function(){{$this->myJQVarName()}.fnDraw()});
	                                               //-->


                    try{
                          jQuery.fn.dataTableExt.oApi.fnStandingRedraw = function(oSettings) {
                                                                                                if(oSettings.oFeatures.bServerSide === false)
                                                                                                        {
                                                                                                        var before = oSettings._iDisplayStart;
                                                                                                        oSettings.oApi._fnReDraw(oSettings);
                                                                                                        oSettings._iDisplayStart = before;
                                                                                                        oSettings.oApi._fnCalculateEnd(oSettings);
                                                                                                        }
                                                                                                  oSettings.oApi._fnDraw(oSettings);
                                                                                                };
                        }catch (e) {}
	                                </script>".implode("\n",$m[0]);                     
	}

}