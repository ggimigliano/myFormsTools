<?php
/**
 * Contains Gimi\myFormsTools\PckmyForms\myForm_XML.
 */

namespace Gimi\myFormsTools\PckmyForms;


use Gimi\myFormsTools\PckmyFields\myUUID;






/**
 * @author Gianluca GIMIGLIANO
 */
	
class myForm_XML extends myForm_MySql {
	/** @ignore */
	protected $dom, $pointer, $nomefile, $risorsefile, $xml_originale, $transazione = false;
	/** @ignore */
	protected static $files;

	
	
	 public function __construct($nomefile, $colonne=array(), $tabella='', $condizioni = array()) {
		myForm::__construct();
		if (self::$files [$nomefile])
			throw new \Exception ( "File $nomefile già  in uso in un'altra istanza" );
		self::$files [$nomefile] = $nomefile;
		$this->colonne = $colonne;
		$this->condizioni = $condizioni;
		if (! is_file ( $nomefile ))@file_put_contents ( $nomefile, LOCK_EX );
		$this->nomefile = realpath ( $nomefile );
		$this->schema = get_class ( $this );
		$this->tabella = $tabella;

		$this->id=md5($this->schema.$tabella."=>".$this->id_istance.serialize($condizioni).serialize($colonne).$_SERVER['PHP_SELF']);

		$this->ripetizione=$this->is_f5();
		if ($this->ripetizione && ($recovered=$this->recover_chiavi())) $this->condizioni=$recovered;
	}

	/** @ignore */
	protected function quota_colonne($array, $tutte = false) {
		echo 'quota colonne';
		exit ();
	}
	/** @ignore */
	 public function MetaForeignKeys() {}
	/** @ignore */
	 public function _get_conn() {
		return $this;
	}
	/** @ignore */
	 public function Begintrans() {
		$this->transazione = true;
	}
	/** @ignore */
	 public function RollbackTrans() {
		$this->transazione = false;
		$this->scarica_xml ();
	}
	/** @ignore */
	 public function CommitTrans() {
		$this->transazione = false;
		$false = false;
		$this->scarica_xml ( $false  );
	}
	/** @ignore */
	protected function carica_xml($flock = '') {
		$this->domIndex = array ();
		$lock = @flock ( $this->risorsefile = @fopen ( $this->nomefile, 'r+' ), ($flock ? $flock : LOCK_SH) );
		if (is_object ( $this->dom = \DOMDocument::LoadXML ( $this->xml_originale = @fread ( $this->risorsefile, filesize ( $this->nomefile ) ) ) ))
			$this->pointer = $this->dom->getElementsByTagName ( $this->schema )->item ( 0 );
		else {
			$this->dom = new \DOMDocument ( '1.0', 'UTF-8' );
			$this->dom->appendChild ( $this->pointer = $this->dom->createElement ( $this->schema ) );
			if ($this->risorsefile) {
				@ftruncate ( $this->risorsefile, 0 );
				@rewind ( $this->risorsefile );
			}
		}
		$this->dom->preserveWhiteSpace = false;
		if ($flock || ! $lock)
			@rewind ( $this->risorsefile );
		else {
			@flock ( $this->risorsefile, LOCK_UN );
			@fclose ( $this->risorsefile );
		}
		return $lock;
	}

	/** @ignore */
	protected function scarica_xml(&$xml = '') {
		if ($xml !== false) {
			if ($xml)
				@fwrite ( $this->risorsefile, $x = $xml );
			else
				@fwrite ( $this->risorsefile, $x = $this->xml_originale );
			@ftruncate ( $this->risorsefile, strlen ( $x ) );
			// $mancanti=filesize($this->nomefile)-strlen($x);
			// if($mancanti) @fwrite($this->risorsefile,str_repeat('
		// ',$mancanti));
		}
		@flock ( $this->risorsefile, LOCK_UN );
		@fclose ( $this->risorsefile );
	}

	/** @ignore */
	protected function &intersezione(&$a, &$b) {
		$nuovo = array ();
		for($i = 0; $i < count ( $a ); $i ++)
			for($j = 0; $j < count ( $b ); $j ++)
				if ($a [$i] === $b [$j])
					$nuovo [] = &$a [$i];
		return $nuovo;
	}

/** @ignore */
	protected function &differenza(&$a, &$b) {
		$nuovo = array ();
		for($i = 0; $i < count ( $a ); $i ++) {
			$trovato = false;
			for($j = 0; $j < count ( $b ); $j ++)
				if ($a [$i] === $b [$j])
					$trovato = TRUE;
			if (! $trovato)
				$nuovo [] = &$a [$i];
		}
		return $nuovo;
	}

	/** @ignore */
	protected function &estrai_indice($campo) {
		if ($this->domIndex [strtoupper ( $campo )])
			return $this->domIndex [strtoupper ( $campo )];
		$valori = $this->dom->getelementsbytagname ( $this->colonneOriginali [strtoupper ( $campo )] );
		if (! $valori)
			return array ();
		else
			foreach ( $valori as $elemento )
				$this->domIndex [strtoupper ( $campo )] [strtolower ( utf8_decode ( $elemento->nodeValue ) )] [] = &$elemento->parentNode;
		return $this->domIndex [strtoupper ( $campo )];
	}

	/**
	 *
	 * @ignore
	 *
	 */
	protected function &get_id_tupla(&$where, $eccezioni_entita = array()) {
	    $indici=$myWhere=array();
		if ($where)
			foreach ( $where as $campo => &$valore ) {
				$indici [strtoupper ( $campo )] = &$this->estrai_indice ( $campo );
				$myWhere [] = &$indici [strtoupper ( $campo )] [strtolower ( $valore )];
			}
		$intersezioneInclusi = $myWhere [0];
		for($i = 1; $i < count ( $myWhere ); $i ++) {
			$confronto = $myWhere [$i];
			$intersezioneInclusi = $this->intersezione ( $intersezioneInclusi, $confronto );
		}

		if ($eccezioni_entita && $intersezioneInclusi) {
			$myWhere = array ();
			foreach ( $eccezioni_entita as $campo => &$valore ) {
				foreach ( $this->estrai_indice ( $campo ) as $valore ) {
					$indici [strtoupper ( $campo )] = &$this->estrai_indice ( $campo );
					$myWhere [] = &$indici [strtoupper ( $campo )] [strtolower ( $valore )];
				}
			}
			$intersezioneEsclusi = $myWhere [0];
			for($i = 1; $i < count ( $myWhere ); $i ++) {
				$confronto = $myWhere [$i];
				$intersezioneEsclusi = $this->intersezione ( $intersezioneEsclusi, $confronto );
			}
			if ($intersezioneEsclusi)
				$intersezioneInclusi = $this->differenza ( $intersezioneInclusi, $intersezioneEsclusi );
		}
		// echo $intersezioneInclusi[0]->tagName;exit;
		return $intersezioneInclusi [0];
	}
	/*
	 * protected function &get_id_tupla(&$where,$eccezioni_entita=array()) {
	 * $xpath = new DOMXPath($this->dom); $xpath->registerPHPFunctions();
	 * $xpath->registerNamespace("php", "http://php.net/xpath");
	 * $percorso="/{$this->schema}/{$this->tabella}"; if($where) foreach ($where
	 * as $campo=>$valore) if(strlen($valore)) {
	 * $valore=(addslashes(strtolower($valore)));
	 * $campo=$this->colonneOriginali[$campo];
	 * $qry[]="php:functionString(\"strtolower\",{$campo})='$valore'"; } if
	 * ($qry) $qry='('.implode(' and ',$qry).')'; if($eccezioni_entita) foreach
	 * ($eccezioni_entita as $campo=>$valore) if(strlen($valore)) {
	 * $valore=addslashes($valore); $campo=$this->colonneOriginali[$campo];
	 * $ecz[]="php:functionString(\"strtolower\",{$campo})!='$valore'"; }
	 * if($ecz) {$ecz='('.implode(' or ',$ecz).')'; if($qry) $ecz="and $ecz"; }
	 * echo '<br>'; echo $query="{$percorso}[$qry $ecz]";
	 * $entries=$xpath->query($query); print_r($entries->length); foreach
	 * ($entries as $entry) return $entry; return false; }
	 */

	/**
	 *
	 * @ignore
	 *
	 */
	protected function estrai_record(&$where, &$id_tupla, &$tupla) {
		$id_tupla = $tupla = null;
		$id_tupla = $this->get_id_tupla ( $where );
		if ($id_tupla)
			foreach ( $id_tupla->childNodes as $oChildNode )
				$tupla [strtoupper ( $oChildNode->tagName )] = utf8_decode ( $oChildNode->nodeValue );
		return $tupla !== null;
	}
	 public function set_valori_recorddiLavoro($values, $id_tupla='') {
		if (! $this->recordset_attivo) $this->recordset_attivo = new \stdClass ();
		$this->recordset_attivo->fields = $values;
		$this->recordset_attivo->id_tupla = $id_tupla;
		return $this;
	}
	
	function &get_valori_recorddiLavoro($ricarica = false, $caseChiavi = 'U', $quali_campi = Array()) {
	    $out=null;
		if (! $this->chiavi)return $out;
		if ($ricarica) {
			$this->recordset_attivo = null;
			$where = array ();
			$condizioni = array_change_key_case ( $this->condizioni );
			foreach ( $this->chiavi as $col ) {
				$val = trim ( $condizioni [strtolower ( $col )] );
				if ($val === '') {
					$where = array ();
					break;
				}
				$where [$col] = $val;
			}
			$id_tupla=$tupla=null;
			if ($this->estrai_record( $where, $id_tupla, $tupla )) {
				$this->recordset_attivo = new \stdClass();
				$this->recordset_attivo->id_tupla = $id_tupla;
				$valori = array_change_key_case ( $tupla );
				foreach ( $this->colonne as $colonna )
					if (isset ( $valori [strtolower ( $colonna )] ))
						$this->recordset_attivo->fields [$colonna] = &$valori [strtolower ( $colonna )];
			}
		}

		if ($this->recordset_attivo && $this->recordset_attivo->fields)
			switch ($caseChiavi) {
				case 'U' :
					$out=array_change_key_case ( $this->recordset_attivo->fields, CASE_UPPER );
					break;
				case 'N' :
					$out= $this->recordset_attivo->fields;
					break;
				case 'L' :
					$out=array_change_key_case ( $this->recordset_attivo->fields, CASE_LOWER );
					break;
			}
		return $out;
	}
	 public function analizza_tabella() {
		foreach ( $this->colonne as $colonna ) {
			$COLONNA = strtoupper ( $colonna );
			$this->colonneOriginali [$COLONNA] = $colonna;
			$this->ParametriAutomatici [$COLONNA] ['MYTYPE'] = 'MyText';
		}

		$this->carica_xml ();
		IF ($this->condizioni) {
			if (! $this->chiavi)
				$this->set_chiavi ( array_keys ( $this->condizioni ) );
			$valori = $this->get_valori_recorddiLavoro ( true, 'U' );
			foreach ( $this->colonne as $colonna ) {
				$COLONNA = strtoupper ( $colonna );
				$this->ParametriAutomatici [$COLONNA] ['VALORE'] = $valori [$COLONNA];
			}
		}
		return $this;
	}

	/**
	 *
	 * @ignore
	 *
	 */
	protected function estrai_active_record($id_tupla) {
		$CHIAVI = array_change_key_case ( array_flip ( $this->chiavi ), CASE_UPPER );
		$settati=0;
		foreach ( $id_tupla->childNodes as $oChildNode )
			if (isset ( $CHIAVI [strtoupper ( $oChildNode->tagName )] )) {
				$CHIAVI [strtoupper ( $oChildNode->tagName )] = ( string ) $oChildNode->nodeValue;
				$settati ++;
			}
		if ($settati == count ( $CHIAVI ))
			return $CHIAVI;
	}
	 public function Check_Errore_Diviso($qualicampi = '', $Esclusi = true) {
		$errore = myForm::Check_Errore_Diviso ( $qualicampi, $Esclusi );
		if ($errore)
			return $errore;
		return $this->Check_Errore_Diviso_univocita ();
	}

	/** @ignore */
	protected function Check_Errore_Diviso_univocita($id_tupla = '') {
		if (is_array ( $this->indici ) && count ( $this->indici ) > 0 && ! $this->ripetizione) {
			$chiavi = $this->condizioni;
			$gia_fatto=array();
			if (! $id_tupla) $id_tupla = $this->recordset_attivo->id_tupla;
			if ($id_tupla)   $noChiave = $this->estrai_active_record ( $id_tupla );

			foreach ( $this->indici as $indice )
				if ($indice ['unique'] && ! $gia_fatto[serialize ( $indice ['columns'] )]) {
					$gia_fatto [serialize ( $indice ['columns'] )] = 1;
					$colonne = array_flip ( array_change_key_case ( array_flip ( ( array ) $indice ['columns'] ), CASE_UPPER ) );

					$campi = array ();
					$condizioni = '';
					foreach ( $colonne as $colonna )
						if ($this->campo( $colonna) &&
							strlen ($valoredb = $this->get_value_db ( $this->campo ( $colonna ) ) )>0) 
							{
							$campi [$colonna] = $valoredb;
							// echo "$colonna
							// =>".$this->campo($colonna)->get_value()."<br>";
							}
					
					if (count($campi)!= count($indice ['columns'] )) continue; //Se il numero di colonne non coincide non si può usare indice 
						
					if ($campi && count ( $campi ) == count($indice ['columns'] )) {
						if ($campi == $chiavi) continue;
						
						$condizioni = &$campi;
					}

					$idtupla = $this->get_id_tupla ( $condizioni, $noChiave );
					$fields=array();
					if ($idtupla) {
						$labels = array ();
						foreach ( $colonne as $vals )
							if ($this->get_label ( $vals )) {
								$labels [] = $this->get_label ( $vals );
								$fields [] = $this->campo ( $vals );
							}
						return array (
								'label' => $labels,
								'errore' => $this->trasl ( "I campi: '%label%' sono giè stati usati in un altro record" ),
								'campo' => $fields
						);
					}
				}
		}
	}
	
	 public function Elimina() {
		$lock = $this->carica_xml ( LOCK_EX );
		if (! $lock) {
			$this->onInternalError ( $this->trasl ( 'Impossibile eliminare' ) );
			return $this->trasl ( 'Impossibile eliminare' );
		}
		if ($this->recordset_attivo->id_tupla)
			$this->dom->removechild ( $this->recordset_attivo->id_tupla );
		$this->scarica_xml ();
	}

	 public function Salva($auto_ricarica_dati=false) {
		// echo $this->tabella.$this->con->_connectionID."
		// ".$this->con->_commit."<br>";
		// if ($this->ripetizione) return ;
		$this->lastqry = '';

		$lock = $this->carica_xml ( LOCK_EX );
		if (! $lock) {
			$this->onInternalError ( $this->trasl ( 'Impossibile salvare' ) );
			return $this->trasl ( 'Impossibile salvare' );
		}

		$this->get_valori_recorddiLavoro ( true, 'U' );

		$errore = $this->Check_Errore_Diviso_univocita ();
		if ($errore)
			$errore = strtr ( $errore ['errore'], array (
					'%label%' => implode ( ',', $errore ['label'] ),
					'%errore%' => $errore ['errore_puro']
			) );

			// $this->con->debug=1;
		if (! $errore)
			try {
				if ($this->AutoIncrementante && ! $this->campo ( $this->AutoIncrementante )->get_value ())
					$this->campo ( $this->AutoIncrementante )->set_value ( myUUID::v4 () );

				if ($this->recordset_attivo->id_tupla)
					$this->pointer->removechild ( $this->recordset_attivo->id_tupla );

					// $this->recordset_attivo->id_tupla=$this->pointer->replaceChild($this->dom->importNode(DOMDocument::loadxml($this->Get_xml($this->tabella))->getElementsByTagName($this->tabella)->item(0),true),$this->recordset_attivo->id_tupla);

				$this->recordset_attivo->id_tupla = $this->pointer->appendChild ( $this->dom->importNode ( \DOMDocument::loadxml ( $this->Get_xml ( $this->tabella ) )->getElementsByTagName ( $this->tabella )->item ( 0 ), true ) );
				if ($this->transazione) {
					fwrite ( $this->risorsefile, $x = $this->dom->saveXML () );
					@ftruncate ( $this->risorsefile, strlen ( $x ) );
				} else
					$this->scarica_xml ( $this->dom->saveXML () );
			} catch ( \Exception $e ) {
				$errore = $this->trasl ( 'Impossibile salvare' );
			}

		if (! $errore) {
			$this->set_valori_recorddiLavoro ( $this->get_values (), $this->recordset_attivo->id_tupla );
			$this->recover_chiavi ( $this->get_chiavi () );
		} else
			$this->onInternalError ( $errore );
		return $errore;
	}

}