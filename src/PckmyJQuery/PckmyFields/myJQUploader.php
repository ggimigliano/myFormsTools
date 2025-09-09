<?php
/**
 * Contains Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQTooltip.
 */
                                                
namespace Gimi\myFormsTools\PckmyJQuery\PckmyFields;



 
use Gimi\myFormsTools\PckmyJQuery\myJQueryMyField; 
use Gimi\myFormsTools\PckmyFields\myField;  
       


/**
 *
 * Imposta drag-drop per un elemento
 *
 */
	
class myJQUploader extends myJQueryMyField {
    /**
     * @ignore
     */
	protected 	$delFun='function(file){return null;}',$addFun="function(file){return null;}",$charset,$messages = [
			'it' => [
					'UPLOAD_TITLE' => "Carica i tuoi file",
					'ADD_FILES_BTN' => "Aggiungi File...",
					'START_UPLOAD_BTN' => "Avvia Caricamento",
					'CANCEL_UPLOAD_BTN' => "Annulla",
					'DROP_ZONE_TEXT' => "Clicca qui o trascina i file per aggiungerli",
					'DUPLICATE_FILE' => "Il file '{fileName}' � gi� stato selezionato.",
					'TOTAL_SIZE_EXCEEDED' => "La dimensione totale dei file supera il limite di {maxSize}.",
					'MIN_FILES_REQUIRED' => "Devi caricare almeno {minFiles} file.",
					'NULL_UPLOAD_FORBIDDEN' => "Non � possibile avviare il caricamento senza file.",
					'FILE_TYPE_NOT_ALLOWED' => "Il file '{fileName}' non ha un'estensione valida.",
					'FILE_SIZE_EXCEEDED' => "Il file '{fileName}' supera la dimensione massima di {maxSize}.",
					'FILE_SIZE_MIN_EXCEEDED' => "Il file '{fileName}' � inferiore alla dimensione minima di {minSize}.",
					'FILE_COUNT_EXCEEDED' => "� stato raggiunto il numero massimo di file ({maxCount}).",
					'ERROR_TITLE' => "Errore di caricamento",
					'SUCCESS_TITLE' => "Caricamento riuscito",
					'CLOSE_MODAL_BTN' => "Chiudi",
					'UPLOAD_SUCCESS' => "Caricamento avviato! (Logica di caricamento simulata)",
					'INVALID_METHOD' => "Metodo '{method}' non valido per myUploader.",
					'INVALID_OPTION' => "Opzione '{option}' non valida.",
					'CONSTRAINT_SUMMARY_TITLE' => "Riepilogo dei vincoli di caricamento:",
					'CONSTRAINTS_MODAL_TITLE' => "Riepilogo Vincoli",
					'MAX_FILES_CONSTRAINT' => "Numero massimo di file: {maxFiles}",
					'MIN_FILES_CONSTRAINT' => "Numero minimo di file: {minFiles}",
					'MAX_FILE_SIZE_CONSTRAINT' => "Dimensione massima per singolo file: {maxSize}",
					'MIN_FILE_SIZE_CONSTRAINT' => "Dimensione minima per singolo file: {minSize}",
					'TOTAL_SIZE_CONSTRAINT' => "Dimensione totale massima: {maxTotalSize}",
					'ALLOWED_FILE_TYPES_CONSTRAINT' => "Tipi di file consentiti: {fileTypes}",
					'SPECIFIC_CONSTRAINTS_TITLE' => "Vincoli specifici per tipo di file:",
					'FILE_TYPE_CONSTRAINT' => "  - {fileType}: massimo {maxCount} file, dimensione max {maxSize}, dimensione min {minSize}.",
					'SKIPPED_FILES_TITLE' => "I seguenti file non sono stati aggiunti:",
					'FILE_REASON' => "<strong>{fileName}</strong>: {reason}",
					'REMOVE_BTN_ARIA' => "Rimuovi file",
					'REMOVE_BTN_ALT' => "Rimuovi",
					'FILE_ICON_ALT' => "Icona file"
			],
			'en' => [
					'UPLOAD_TITLE' => "Upload your files",
					'ADD_FILES_BTN' => "Add Files...",
					'START_UPLOAD_BTN' => "Start Upload",
					'CANCEL_UPLOAD_BTN' => "Cancel",
					'DROP_ZONE_TEXT' => "Click here or drag and drop your files",
					'DUPLICATE_FILE' => "The file '{fileName}' has already been selected.",
					'TOTAL_SIZE_EXCEEDED' => "The total file size exceeds the limit of {maxSize}",
					'MIN_FILES_REQUIRED' => "You must upload at least {minFiles} files.",
					'NULL_UPLOAD_FORBIDDEN' => "Cannot start upload with no files.",
					'FILE_TYPE_NOT_ALLOWED' => "The file '{fileName}' does not have a valid extension.",
					'FILE_SIZE_EXCEEDED' => "The file '{fileName}' exceeds the maximum size of {maxSize}",
					'FILE_SIZE_MIN_EXCEEDED' => "The file '{fileName}' is below the minimum size of {minSize}",
					'FILE_COUNT_EXCEEDED' => "The maximum number of files ({maxCount}) has been reached.",
					'ERROR_TITLE' => "Upload Error",
					'SUCCESS_TITLE' => "Upload Successful",
					'CLOSE_MODAL_BTN' => "Close",
					'UPLOAD_SUCCESS' => "Upload started! (Simulated upload logic)",
					'INVALID_METHOD' => "Invalid method '{method}' for myUploader.",
					'INVALID_OPTION' => "Invalid option '{option}'.",
					'CONSTRAINTS_MODAL_TITLE' => "Constraints Summary",
					'SKIPPED_FILES_TITLE' => "The following files were skipped:",
					'FILE_REASON' => "<strong>{fileName}</strong>: {reason}",
					'REMOVE_BTN_ARIA' => "Remove file",
					'REMOVE_BTN_ALT' => "Remove",
					'FILE_ICON_ALT' => "File icon"
			],
			'es' => [
					'UPLOAD_TITLE' => "Sube tus archivos",
					'ADD_FILES_BTN' => "A�adir archivos...",
					'START_UPLOAD_BTN' => "Iniciar la carga",
					'CANCEL_UPLOAD_BTN' => "Cancelar",
					'DROP_ZONE_TEXT' => "Haz clic aqu� o arrastra y suelta tus archivos",
					'DUPLICATE_FILE' => "El archivo '{fileName}' ya ha sido seleccionado.",
					'TOTAL_SIZE_EXCEEDED' => "El tama�o total de los archivos excede el l�mite de {maxSize}.",
					'MIN_FILES_REQUIRED' => "Debe subir al menos {minFiles} archivos.",
					'NULL_UPLOAD_FORBIDDEN' => "No se puede iniciar la carga sin archivos.",
					'FILE_TYPE_NOT_ALLOWED' => "El archivo '{fileName}' no tiene una extensi�n v�lida.",
					'FILE_SIZE_EXCEEDED' => "El archivo '{fileName}' excede el tama�o m�ximo de {maxSize}.",
					'FILE_SIZE_MIN_EXCEEDED' => "El archivo '{fileName}' est� por debajo del tama�o m�nimo de {minSize}.",
					'FILE_COUNT_EXCEEDED' => "Se ha alcanzado el n�mero m�ximo de archivos ({maxCount}).",
					'ERROR_TITLE' => "Error de carga",
					'SUCCESS_TITLE' => "Carga exitosa",
					'CLOSE_MODAL_BTN' => "Cerrar",
					'UPLOAD_SUCCESS' => "�Carga iniciada! (L�gica de carga simulada)",
					'INVALID_METHOD' => "M�todo '{method}' no v�lido para myUploader.",
					'INVALID_OPTION' => "Opci�n '{option}' no v�lida.",
					'CONSTRAINTS_MODAL_TITLE' => "Resumen de Restricciones",
					'SKIPPED_FILES_TITLE' => "Los siguientes archivos fueron omitidos:",
					'FILE_REASON' => "<strong>{fileName}</strong>: {reason}",
					'REMOVE_BTN_ARIA' => "Eliminar archivo",
					'REMOVE_BTN_ALT' => "Eliminar",
					'FILE_ICON_ALT' => "Icono de archivo"
			],
			'fr' => [
					'UPLOAD_TITLE' => "T�l�chargez vos fichiers",
					'ADD_FILES_BTN' => "Ajouter des fichiers...",
					'START_UPLOAD_BTN' => "D�marrer le t�l�chargement",
					'CANCEL_UPLOAD_BTN' => "Annuler",
					'DROP_ZONE_TEXT' => "Cliquez ici ou glissez et d�posez vos fichiers",
					'DUPLICATE_FILE' => "Le fichier '{fileName}' a d�j� �t� s�lectionn�.",
					'TOTAL_SIZE_EXCEEDED' => "La taille totale des fichiers d�passe la limite de {maxSize}.",
					'MIN_FILES_REQUIRED' => "Vous devez t�l�charger au moins {minFiles} fichiers.",
					'NULL_UPLOAD_FORBIDDEN' => "Impossible de d�marrer le t�l�chargement sans fichiers.",
					'FILE_TYPE_NOT_ALLOWED' => "Le fichier '{fileName}' n'a pas d'extension valide.",
					'FILE_SIZE_EXCEEDED' => "Le fichier '{fileName}' d�passe la taille maximale de {maxSize}.",
					'FILE_SIZE_MIN_EXCEEDED' => "Le fichier '{fileName}' est en dessous de la taille minimale de {minSize}.",
					'FILE_COUNT_EXCEEDED' => "Le nombre maximum de fichiers ({maxCount}) a �t� atteint.",
					'ERROR_TITLE' => "Erreur de t�l�chargement",
					'SUCCESS_TITLE' => "T�l�chargement r�ussi",
					'CLOSE_MODAL_BTN' => "Fermer",
					'UPLOAD_SUCCESS' => "T�l�chargement d�marr� ! (Logique de t�l�chargement simul�e)",
					'INVALID_METHOD' => "M�thode '{method}' non valide pour myUploader.",
					'INVALID_OPTION' => "Option '{option}' non valide.",
					'CONSTRAINTS_MODAL_TITLE' => "R�sum� des Contraintes",
					'SKIPPED_FILES_TITLE' => "Les fichiers suivants ont �t� ignor�s:",
					'FILE_REASON' => "<strong>{fileName}</strong>: {reason}",
					'REMOVE_BTN_ARIA' => "Supprimer le fichier",
					'REMOVE_BTN_ALT' => "Supprimer",
					'FILE_ICON_ALT' => "Ic�ne de fichier"
			],
			'de' => [
					'UPLOAD_TITLE' => "Dateien hochladen",
					'ADD_FILES_BTN' => "Dateien hinzuf�gen...",
					'START_UPLOAD_BTN' => "Hochladen starten",
					'CANCEL_UPLOAD_BTN' => "Abbrechen",
					'DROP_ZONE_TEXT' => "Hier klicken oder Dateien hierher ziehen",
					'DUPLICATE_FILE' => "Die Datei '{fileName}' wurde bereits ausgew�hlt.",
					'TOTAL_SIZE_EXCEEDED' => "Die Gesamtgr��e der Dateien �berschreitet das Limit von {maxSize}.",
					'MIN_FILES_REQUIRED' => "Sie m�ssen mindestens {minFiles} Dateien hochladen.",
					'NULL_UPLOAD_FORBIDDEN' => "Hochladen ohne Dateien nicht m�glich.",
					'FILE_TYPE_NOT_ALLOWED' => "Die Datei '{fileName}' hat keine g�ltige Erweiterung.",
					'FILE_SIZE_EXCEEDED' => "Die Datei '{fileName}' �berschreitet die maximale Gr��e von {maxSize}.",
					'FILE_SIZE_MIN_EXCEEDED' => "Die Datei '{fileName}' ist unter der Mindestgr��e von {minSize}.",
					'FILE_COUNT_EXCEEDED' => "Die maximale Anzahl von Dateien ({maxCount}) wurde erreicht.",
					'ERROR_TITLE' => "Fehler beim Hochladen",
					'SUCCESS_TITLE' => "Hochladen erfolgreich",
					'CLOSE_MODAL_BTN' => "Schlie�en",
					'UPLOAD_SUCCESS' => "Hochladen gestartet! (Simulierte Hochlade-Logik)",
					'INVALID_METHOD' => "Ung�ltige Methode '{method}' f�r myUploader.",
					'INVALID_OPTION' => "Ung�ltige Option '{option}'.",
					'CONSTRAINTS_MODAL_TITLE' => "Zusammenfassung der Einschr�nkungen",
					'SKIPPED_FILES_TITLE' => "Die folgenden Dateien wurden �bersprungen:",
					'FILE_REASON' => "<strong>{fileName}</strong>: {reason}",
					'REMOVE_BTN_ARIA' => "Datei entfernen",
					'REMOVE_BTN_ALT' => "Entfernen",
					'FILE_ICON_ALT' => "Dateisymbol"
			]
	];
	
	public function set_charset($charset){
		$this->charset=$charset;
		return $this;
	}
	
	/**
	 *
	 * @param string $fun function(file) {return null/string} in funzione del return l'azione fallisce o mostra string di errore
	 * @return \Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQUploader
	 */
	public function set_onAddFile($fun){
		$this->addFun=$fun;
		return $this;
	}
    
	/**
	 * 
	 * @param string $fun function(file) {return null/string} in funzione del return l'azione fallisce o mostra string di errore
	 * @return \Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQUploader
	 */
	public function set_onDelFile($fun){
		$this->delFun=$fun;
		return $this;
	}
    
    static protected function init( &$widget) {
    	$widget='uploader';
    	self::add_src(self::$percorsoMyForm.'jquery/myupload/myJQUploader.js');
    	self::add_css(self::$percorsoMyForm.'jquery/myupload/myJQUploader.css?t='.date('d'));
    }
    
    
    private function convertiDimensioneInByte($dimensione) {
    	$dimensione = trim($dimensione);
    	$last = strtolower($dimensione[strlen($dimensione)-1]);
    	$dimensione = (int)$dimensione;
    	switch($last) {
    		case 'g':
    			$dimensione *= 1024;
    		case 'm':
    			$dimensione *= 1024;
    		case 'k':
    			$dimensione *= 1024;
    	}
    	return $dimensione;
    }
   
    private function convert($text) {
    	// Array delle entit� da riconvertire
    	$entities = array('&lt;', '&gt;', '&quot;', '&#039;');
    	// Array dei caratteri corrispondenti
    	$characters = array('<', '>', '"', "'");
    	return str_replace($entities, $characters, htmlentities($text,ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,$this->charset?$this->charset:myField::get_charset()));
    }
    
    
     public function get_code($ns='') {
     	$minNFiles=1;
     	$maxNFiles=1;
     	$id=strstr($this->myField->get_id().'[','[',true);
     	if(method_exists($this->myField, 'get_molteplicita') && $this->myField->is_multiplo()) 
     					{
     						$maxNFiles=$this->myField->get_molteplicita()['max'];
     					}
     	$maxNFiles=min($maxNFiles,ini_get('max_file_uploads'));
     	
     	$iniziali=array();
     	foreach ($this->myField->get_info_uploaded() as $k=>$file)
     		if($file['size']>0 && !$file['error'])
     			$iniziali[]=array('name'=>$file['name'],'size'=>$file['size'],'hash'=>$k,'url'=>$this->myField->get_download_url($file));
     	
     	$out=array();
     	foreach ($this->messages[$this->myField->get_lingua()] as $var=>&$v) $out[$var]=$this->convert($v);
     	 
     	$opzioni=array(
     				'defaultLang'=>$this->myField->get_lingua(), // Imposta la lingua di default in inglese
		     		'uploaderId'=>$id, // Imposta un ID personalizzato
     				'fileInputName'=> strstr($this->myField->get_name().'[','[',true).'[]', // Imposta un nome per l'input dei file
     			    'fileExt'=> $this->myField->get_ext_ammesse()?'.'.implode(',.',$this->myField->get_ext_ammesse()):'',
     			    'fileConstraints'=>[],
     				'path'=>'/'.myField::get_MyFormsPath(),
     				'minNumberOfFiles'=>$minNFiles,
     			    'maxNumberOfFiles'=>$maxNFiles, 
     			    'acceptFileTypes'=>$this->myField->get_ext_ammesse()?'^('.implode('|',$this->myField->get_ext_ammesse()).')$':'.+',
     			    'minFileSize'=>$this->myField->get_min()?$this->myField->get_min():10,
     			    'maxFileSize'=>$this->myField->get_max()?$this->myField->get_max():min($this->myField->convertiDimensioneInByte(ini_get('upload_max_filesize')),$this->myFieldhis->convertiDimensioneInByte(ini_get('post_max_size'))),
     				'notNull'=>$this->myField->get_notnull()?true:false,
    				'initialFiles'=>$iniziali,
     				'messages'=>array($this->myField->get_lingua()=>$out),
     			    'onAddFile'=>'',
     				'onDelFile'=>'',
     				);
     	$opzioni=self::quote($opzioni);
     	$opzioni=str_replace(array("onAddFile:''","onDelFile:''"), 
     						 array("onAddFile:{$this->addFun}","onDelFile:{$this->delFun}"), $opzioni);
     	
     	$this->add_code(" $('#uploader-{$id}').uploader(".self::quote($opzioni).")");
        return parent::get_code($ns);
    }

	
}