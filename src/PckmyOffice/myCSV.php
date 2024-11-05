<?php
/**
 * Contains Gimi\myFormsTools\PckmyOffice\myCSV.
 */

namespace Gimi\myFormsTools\PckmyOffice;


use Gimi\myFormsTools\PckmyTables\myTable;
use Gimi\myFormsTools\PckmyUtils\myRAMStream;



/**
* 
* Questa classe permette di creare dei file CSV
* 
* Ecco alcune modalità consigliate
* <code>
*  $t=new myTableQRY($conn,"select * from dati", true); //il parametro true non fa salvare i dati della tabella in sessione risparmiando ram e tempo
*  $csv=new myCSV();
*  $csv->importa_myTable($t); //Utile se la query produce caratteri o tag da convertire
*  $csv->send('dati.csv');
* </code>
*
* Piu' performante
* <code>
*  $t=new myTableQRY($conn,"select * from dati", true); //il parametro true non fa salvare i dati della tabella in sessione risparmiando ram e tempo
*  $csv=new myCSV();
*  $csv->set_matrix($t->get_matrix(),
*  					true,				//fa si' che la matrice si carichi direttamente per indirizzo
*  					$t->get_intestazioni()); //la matrice passata e' numerica e quindi l'intesazione va specificata 
*  $csv->send('dati.csv');
* </code>
*  
* 
* Ancora piu' performante
* <code>
*  $t=new myTableQRY($conn,"select * from dati", true); //il parametro true non fa salvare i dati della tabella in sessione risparmiando ram e tempo
*  $csv=new myCSV();
*  $csv->set_matrix($t->get_matrix(),
*  					true,				//fa si' che la matrice si carichi direttamente per indirizzo
*  					$t->get_intestazioni()); //la matrice passata e' numerica e quindi l'intesazione va specificata 
*  $csv->set_output_stream()   //viene impostato uno stream di uscita direttamente durante la produzoine del csv di default lo standard output
*  	   ->send('dati.csv');
* </code>
*/
	
class myCSV extends myTable {
protected $separatori,$intestazioni=array(),$stream,$built=false;

	 public function __construct($separatore=';',$quotatura='"',$accapo="\n",$stream=null) {
		$this->set_separatori($separatore,$quotatura,$accapo);
		if($stream) $this->stream=$stream;
	}

    /**
	 * @ignore
	 */
	 public function __call($m,$v){
	   if(strtolower($m)==strtolower(__CLASS__)) {
	        $pars=array();
	        for ($i=0;$i<count($v);$i++) $pars[]="\$v[{$i}]";
	        eval("return $m::__construct(".implode(',',$pars).");");
	        return;
	       }
	}
	
	
	 public function __destruct(){
	    if($this->built) @ftruncate($this->stream, 0);
	    return parent::__destruct();
	}
	
	/**
	 * Scrittura effettiva dei dati sullo stream usato dall'istanza
	 */
	protected function put_stream(array $dati) {
		if(!$this->stream) {myramStream::register('myRamStreamCSV');
							$this->stream=fopen("myRamStreamCSV:///".spl_object_hash($this).".csv", "w+");
							$this->built=true;
		                   }
		return @fputcsv($this->stream, $dati,$this->separatori['sep'],$this->separatori['quot']);
	} 
	
	
	 public function load_from_file($file,$con_intestazione=true,$sep=";"){
		if (!$sep) $sep=$this->sep;
		if (!($max=@filesize($file))) return;
		$f=fopen($file,'r');
		if ($con_intestazione) $this->intestazioni=(array) @fgetcsv($f,$max,$sep);
		while ($riga=@fgetcsv($f,$max,$sep)) $this->valori[]=$riga;
		@fclose($f);
		return true;
	}

			
	/**
	 * Importa un myTable da html
	 * Ecco come importare la seconda tabella contenuta in questo html e poi aggiungerci una riga
	 * <code>
	 * $html="
	 *	<table>
	 *		<tr><th>A</th><th>B</th></tr>
	 *		<tr><td>1</td><td>2</td></tr>
	 *		<tr><td>11</td><td>22</td></tr>
	 *	  </table>
	 *
	 *		<table style='background:blue' cellspacing='2'>
	 *		<tr style='color:blue'>
	 *			<th style='color:white'>A</th>
	 *			<th style='color:white'>B</th>
	 *			<th style='color:white'>C</th>
	 *		</tr>
	 *		<tr style='color:blue'>
	 *			<td style='background:white'>1</td>
	 *			<td style='background:white'>2</td>
	 *			<td style='background:white'>3</td>
	 *		</tr>
	 *		<tr style='color:blue'>
	 *			<td style='background:white'>11</td>
	 *			<td style='background:white'>22</td>
	 *			<td style='background:white'>33</td>
	 *		</tr>
	 *		</table>";
	 *
	 * $t=new myTable();
	 * $t->importa_html($html,2)
	 *   ->ins_row(array(111,222,333));
	 * echo $t;
	 *</code>
	 *
	 *
	 * @param string $html      html da lavorare
	 * @param int $quale		numero d'ordine della tabella da importare
	 * @param boolean $accoda	se true accoda i valori a quelli correnti (ma solo per le prime n colonne dove n e' il numero di colonne già  in memoria)
	 */
	 public function importa_html(&$html,$quale=1,$accoda=false,$solodati = false) {
		$t=strip_tags($html,'<table><td><tr><th>');
		$table=new myTable();
		if($accoda) $table->set_matrix($this->get_matrix());
		$this->Importa_MyTable($table->importa_html($t,$quale,true,true));
		return $this;
	}



	 public function set_separatori($separatore=';',$quotatura='"',$accapo="\n"){
		$this->separatori['sep']=$separatore;
		$this->separatori['quot']=$quotatura;
		$this->separatori['acc']=$accapo;
		return $this;
	}


	/**
	 * Importa i dati da una myTable o sua estensione
	 *
	 * @param myTable $MyTable
	 * @param array $colonne  array con i numeri di colonna da importare, se omesso tutte
	 * @param array $righe	  array con i numeri di riga da importare, se omesso tutte
	 * @param boolean $intestazione se true importa anche le intestazioni
	 * @param boolean $accoda se true accoda i valori a quelli correnti (ma solo per le prime n colonne dove n e' il numero di colonne già  in memoria)
	 * @param boolean $usa_ricalcola_cella se false usa i valori originali altrimenti eventuali ridefinizioni di classe
	 */
	 public function Importa_MyTable($MyTable,$colonne='',$righe='',$intestazione=true,$accoda=false,$usa_ricalcola_cella=false) {
		if ($intestazione) $Intestazioni=$MyTable->get_intestazioni();
		if(!$usa_ricalcola_cella || !$MyTable->isOverridden('ricalcola_cella'))
								  $matrice=$MyTable->get_matrix();
							else {$matrice=array();
								  for ($j=0;$j<$MyTable->get_N_rows();$j++)
								     if (!$righe || isset($righe[$j]))
								     	for($i=0;$i<$MyTable->get_N_cols();$i++)
								     		 $matrice[$j][$i]=$MyTable->ricalcola_cella($j, $i);
								 }
								 
		foreach ($matrice as &$riga)
				foreach ($riga as &$val)
						$val=trim(html_entity_decode($val,ENT_COMPAT,'ISO-8859-1'));
							 
								 
		if (!$this->get_n_rows() &&
			(!$righe && !$colonne && !$accoda))
				return $this->set_matrix($matrice,true,$Intestazioni);

		if ($righe) $righe=array_flip($righe);

		if (!$colonne) $colonne=array_keys($MyTable->get_row(0));
		if (!($colonne_interne=array_keys((array) $this->get_row(0))))
						$colonne_interne=array_keys($colonne);
		//se csv non inizializzato colonne_interne=colonne => colonne_comuni=colonne
		//

		foreach ($matrice as $j=>&$riga)
		   if (!$righe || isset($righe[$j]))
		   		{ $r=array();
		   		  foreach ($colonne_interne as &$i){$r[]=$riga[$colonne[$i]];}
				  $this->ins_row($r);
				}
		if($Intestazioni) {
					$intestazioni=array();
				 	foreach ($colonne_interne as &$i) $intestazioni[]=$Intestazioni[$colonne[$i]];
				 	$this->set_intestazioni($intestazioni);
					}
		return $this;

	}

	/** @ignore */
	protected function &quota(&$x){
		$x=html_entity_decode($x);
		if(strpos($x,$this->separatori['sep'])!==false)
			 return $this->separatori['quot'].str_replace($this->separatori['quot'],$this->separatori['quot'].$this->separatori['quot'],$x).$this->separatori['quot'];
		else return $x;
	}


	
	/**
	 * @ignore
	 */
	protected function genera(){
	    $tot=0;
    	$intestazioni=$this->get_intestazioni();
		if($intestazioni)	$tot+=$this->put_stream($intestazioni);
		if($this->valori) {
            		$n=count($this->valori);
            		
            		$usa_ricalcola_cella=$this->isOverridden('ricalcola_cella');
            		for ($i=0;$i<$n;$i++)
            					{
            					  if(!$usa_ricalcola_cella)  $tot+=$this->put_stream($this->valori[$i]);
            					  		else	{$v=array();
            					  				 for($j=0;$j<count($this->valori[$i]);$j++) $v[]=$this->ricalcola_cella($i,$j);
            					  				 $tot+=$this->put_stream($v);
            					  		    	}	
            					 
            					}
            		@fflush($this->stream);
		          }
		return $tot;          
	}
	
	
	
	/**
	* Imposta uno stram di uscita per i dati
	* <code>
	*  $conn->setfetchmode(ADODB_FETCH_NUM);
	*  $dati=$DbCon->getarray("select * from dati");
	*
	*  $csv=new myCSV();
	*  $csv->set_matrix($dati,true,array('col1','col2','col3'))
	*  	   ->set_output_stream()   //viene impostato uno stream di uscita direttamente durante la produzoine del csv di default lo standard output
	*  	   ->send('dati.csv');
	* </code>
	* 
	*
	* Salva ed invia
	*  <code>
	*  $conn->setfetchmode(ADODB_FETCH_NUM);
	*  $dati=$DbCon->getarray("select * from dati");
	*
	*  $csv=new myCSV();
	*  $csv->set_matrix($dati,true,array('col1','col2','col3'))
	*  	   ->set_output_stream($file=fopen('dati.csv','w+'))   //viene impostato uno stream di uscita come file su cui verrà scritto
	*  	   ->send('dati.csv'); //qui il file appena scritto viene riavvolto e mandato al client
	* </code>
	* 
	* In alternativa si puo' fare l'overriding del metodo put_stream in cui oltre a scrivere sullo stream impostato si invia anche l'ouput allo stream standard
	* 	* 
	*/
	 public function set_output_stream($stream=''){
		if($stream) $this->stream=$stream;
			   else $this->stream=fopen('php://output', 'w');
		return $this;
	}

	function &get_output($colonne = '', $righe = ''){
	    $this->genera();
	    if(!@rewind($this->stream)) $out= false;
	                           else $out=stream_get_contents($this->stream);
	    return $out;                       
	}
	

	 public function salva($file=null) {
		$tot=$this->genera();
		if(!@rewind($this->stream)) return false;
		if($file)  {
            		$f=@fopen($file,'w');
            		if(!$f) return false;
	               	@stream_copy_to_stream($this->stream, $f);
		            @fclose($f);
		            ftruncate($this->stream, 0);
		           }
		           
		return $tot;
	}
	
	
	 public function send($nomeFile='file.csv',$forza_download=true,$esci=true) {
		
		header("Pragma:");
		header("Expires: " . gmdate("D, d M Y H:i:s",time()+30) . " GMT");
	    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

    	header("Cache-Control: private, must-revalidate, post-check=0, pre-check=0");
    	header("Content-Description: File Transfer");
		header("Content-Transfer-Encoding: binary");
		header("Content-Type: application/csv");
		header('Content-Type: application/vnd.ms-excel');
    	if($forza_download)  header("Content-Disposition: attachment; filename=\"".basename($nomeFile)."\"");
				       else  header("Content-Disposition: inline; filename=\"".basename($nomeFile)."\"");
			     
		$this->genera();
		if (@rewind($this->stream) && !headers_sent()) 
				{
				 $size=fstat($this->stream);
				 $size=$size['size'];

/*				 header("Content-Encoding: gzip");
				 header("Original-Size: ".$size);
			 
				 echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
				 	
				 $gz=fopen('php://output', 'wb');
				 stream_filter_append( $gz, "zlib.deflate", STREAM_FILTER_WRITE, -1);
				 stream_copy_to_stream($this->stream,$gz);
				 
				 while(!feof($this->stream)) $tutto.=@fread($this->stream,$size);
				 if($size>1024 && strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') )
     						{
				          	header("Content-Encoding: gzip");
				          	header("Original-Size: ".$size);
							$tutto= "\x1f\x8b\x08\x00\x00\x00\x00\x00". substr(gzcompress($tutto, 6), 0, - 4). pack('V', crc32($tutto)). pack('V', $size);
							$size=strlen($tutto);	
     						}
     						*/
	  			header("Content-Length: ".$size);
				while(!feof($this->stream)) echo @fread($this->stream,4096);
				}
		ftruncate($this->stream, 0);
		if($esci) exit;
	}
}