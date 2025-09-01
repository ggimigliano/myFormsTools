(function($) {
    /**
     * @file jQuery.myuploader.js
     * @description Un plugin jQuery per la gestione del caricamento di file con validazione e UI localizzata.
     * @version 1.8.0
     * @author Your Name
     */

    // Dizionario dei messaggi per la localizzazione
    const MESSAGES = {
        it: {
            UPLOAD_TITLE: "Carica i tuoi file",
            ADD_FILES_BTN: "Aggiungi File...",
            START_UPLOAD_BTN: "Avvia Caricamento",
            CANCEL_UPLOAD_BTN: "Annulla",
            DROP_ZONE_TEXT: "Clicca qui o trascina i file per aggiungerli",
            DUPLICATE_FILE: "Il file '{fileName}' è già stato selezionato.",
            TOTAL_SIZE_EXCEEDED: "La dimensione totale dei file supera il limite di {maxSize}.",
            MIN_FILES_REQUIRED: "Devi caricare almeno {minFiles} file.",
            NULL_UPLOAD_FORBIDDEN: "Non è possibile avviare il caricamento senza file.",
            FILE_TYPE_NOT_ALLOWED: "Il file '{fileName}' non ha un'estensione valida.",
            FILE_SIZE_EXCEEDED: "Il file '{fileName}' supera la dimensione massima di {maxSize}.",
            FILE_SIZE_MIN_EXCEEDED: "Il file '{fileName}' è inferiore alla dimensione minima di {minSize}.",
            FILE_COUNT_EXCEEDED: "È stato raggiunto il numero massimo di file ({maxCount}).",
            ERROR_TITLE: "Errore di caricamento",
            SUCCESS_TITLE: "Caricamento riuscito",
            CLOSE_MODAL_BTN: "Chiudi",
            UPLOAD_SUCCESS: "Caricamento avviato! (Logica di caricamento simulata)",
            INVALID_METHOD: "Metodo '{method}' non valido per myUploader.",
            INVALID_OPTION: "Opzione '{option}' non valida.",
            CONSTRAINT_SUMMARY_TITLE: "Riepilogo dei vincoli di caricamento:",
            CONSTRAINTS_MODAL_TITLE: "Riepilogo Vincoli",
            MAX_FILES_CONSTRAINT: "Numero massimo di file: {maxFiles}",
            MIN_FILES_CONSTRAINT: "Numero minimo di file: {minFiles}",
            MAX_FILE_SIZE_CONSTRAINT: "Dimensione massima per singolo file: {maxSize}",
            MIN_FILE_SIZE_CONSTRAINT: "Dimensione minima per singolo file: {minSize}",
            TOTAL_SIZE_CONSTRAINT: "Dimensione totale massima: {maxTotalSize}",
            ALLOWED_FILE_TYPES_CONSTRAINT: "Tipi di file consentiti: {fileTypes}",
            SPECIFIC_CONSTRAINTS_TITLE: "Vincoli specifici per tipo di file:",
            FILE_TYPE_CONSTRAINT: "  - {fileType}: massimo {maxCount} file, dimensione max {maxSize}, dimensione min {minSize}.",
            SKIPPED_FILES_TITLE: "I seguenti file non sono stati aggiunti:",
            FILE_REASON: "<strong>{fileName}</strong>: {reason}",
            REMOVE_BTN_ARIA: "Rimuovi file",
            REMOVE_BTN_ALT: "Rimuovi",
            FILE_ICON_ALT: "Icona file"
        },
        en: {
            UPLOAD_TITLE: "Upload your files",
            ADD_FILES_BTN: "Add Files...",
            START_UPLOAD_BTN: "Start Upload",
            CANCEL_UPLOAD_BTN: "Cancel",
            DROP_ZONE_TEXT: "Click here or drag and drop your files",
            DUPLICATE_FILE: "The file '{fileName}' has already been selected.",
            TOTAL_SIZE_EXCEEDED: "The total file size exceeds the limit of {maxSize}",
            MIN_FILES_REQUIRED: "You must upload at least {minFiles} files.",
            NULL_UPLOAD_FORBIDDEN: "Cannot start upload with no files.",
            FILE_TYPE_NOT_ALLOWED: "The file '{fileName}' does not have a valid extension.",
            FILE_SIZE_EXCEEDED: "The file '{fileName}' exceeds the maximum size of {maxSize}",
            FILE_SIZE_MIN_EXCEEDED: "The file '{fileName}' is below the minimum size of {minSize}",
            FILE_COUNT_EXCEEDED: "The maximum number of files ({maxCount}) has been reached.",
            ERROR_TITLE: "Upload Error",
            SUCCESS_TITLE: "Upload Successful",
            CLOSE_MODAL_BTN: "Close",
            UPLOAD_SUCCESS: "Upload started! (Simulated upload logic)",
            INVALID_METHOD: "Invalid method '{method}' for myUploader.",
            INVALID_OPTION: "Invalid option '{option}'.",
            CONSTRAINTS_MODAL_TITLE: "Constraints Summary",
            SKIPPED_FILES_TITLE: "The following files were skipped:",
            FILE_REASON: "<strong>{fileName}</strong>: {reason}",
            REMOVE_BTN_ARIA: "Remove file",
            REMOVE_BTN_ALT: "Remove",
            FILE_ICON_ALT: "File icon"
        },
        es: {
            UPLOAD_TITLE: "Sube tus archivos",
            ADD_FILES_BTN: "Añadir archivos...",
            START_UPLOAD_BTN: "Iniciar la carga",
            CANCEL_UPLOAD_BTN: "Cancelar",
            DROP_ZONE_TEXT: "Haz clic aquí o arrastra y suelta tus archivos",
            DUPLICATE_FILE: "El archivo '{fileName}' ya ha sido seleccionado.",
            TOTAL_SIZE_EXCEEDED: "El tamaño total de los archivos excede el límite de {maxSize}.",
            MIN_FILES_REQUIRED: "Debe subir al menos {minFiles} archivos.",
            NULL_UPLOAD_FORBIDDEN: "No se puede iniciar la carga sin archivos.",
            FILE_TYPE_NOT_ALLOWED: "El archivo '{fileName}' no tiene una extensión válida.",
            FILE_SIZE_EXCEEDED: "El archivo '{fileName}' excede el tamaño máximo de {maxSize}.",
            FILE_SIZE_MIN_EXCEEDED: "El archivo '{fileName}' está por debajo del tamaño mínimo de {minSize}.",
            FILE_COUNT_EXCEEDED: "Se ha alcanzado el número máximo de archivos ({maxCount}).",
            ERROR_TITLE: "Error de carga",
            SUCCESS_TITLE: "Carga exitosa",
            CLOSE_MODAL_BTN: "Cerrar",
            UPLOAD_SUCCESS: "¡Carga iniciada! (Lógica de carga simulada)",
            INVALID_METHOD: "Método '{method}' no válido para myUploader.",
            INVALID_OPTION: "Opción '{option}' no válida.",
            CONSTRAINTS_MODAL_TITLE: "Resumen de Restricciones",
            SKIPPED_FILES_TITLE: "Los siguientes archivos fueron omitidos:",
            FILE_REASON: "<strong>{fileName}</strong>: {reason}",
            REMOVE_BTN_ARIA: "Eliminar archivo",
            REMOVE_BTN_ALT: "Eliminar",
            FILE_ICON_ALT: "Icono de archivo"
        },
        fr: {
            UPLOAD_TITLE: "Téléchargez vos fichiers",
            ADD_FILES_BTN: "Ajouter des fichiers...",
            START_UPLOAD_BTN: "Démarrer le téléchargement",
            CANCEL_UPLOAD_BTN: "Annuler",
            DROP_ZONE_TEXT: "Cliquez ici ou glissez et déposez vos fichiers",
            DUPLICATE_FILE: "Le fichier '{fileName}' a déjà été sélectionné.",
            TOTAL_SIZE_EXCEEDED: "La taille totale des fichiers dépasse la limite de {maxSize}.",
            MIN_FILES_REQUIRED: "Vous devez télécharger au moins {minFiles} fichiers.",
            NULL_UPLOAD_FORBIDDEN: "Impossible de démarrer le téléchargement sans fichiers.",
            FILE_TYPE_NOT_ALLOWED: "Le fichier '{fileName}' n'a pas d'extension valide.",
            FILE_SIZE_EXCEEDED: "Le fichier '{fileName}' dépasse la taille maximale de {maxSize}.",
            FILE_SIZE_MIN_EXCEEDED: "Le fichier '{fileName}' est en dessous de la taille minimale de {minSize}.",
            FILE_COUNT_EXCEEDED: "Le nombre maximum de fichiers ({maxCount}) a été atteint.",
            ERROR_TITLE: "Erreur de téléchargement",
            SUCCESS_TITLE: "Téléchargement réussi",
            CLOSE_MODAL_BTN: "Fermer",
            UPLOAD_SUCCESS: "Téléchargement démarré ! (Logique de téléchargement simulée)",
            INVALID_METHOD: "Méthode '{method}' non valide pour myUploader.",
            INVALID_OPTION: "Option '{option}' non valide.",
            CONSTRAINTS_MODAL_TITLE: "Résumé des Contraintes",
            SKIPPED_FILES_TITLE: "Les fichiers suivants ont été ignorés:",
            FILE_REASON: "<strong>{fileName}</strong>: {reason}",
            REMOVE_BTN_ARIA: "Supprimer le fichier",
            REMOVE_BTN_ALT: "Supprimer",
            FILE_ICON_ALT: "Icône de fichier"
        },
        de: {
            UPLOAD_TITLE: "Dateien hochladen",
            ADD_FILES_BTN: "Dateien hinzufügen...",
            START_UPLOAD_BTN: "Hochladen starten",
            CANCEL_UPLOAD_BTN: "Abbrechen",
            DROP_ZONE_TEXT: "Hier klicken oder Dateien hierher ziehen",
            DUPLICATE_FILE: "Die Datei '{fileName}' wurde bereits ausgewählt.",
            TOTAL_SIZE_EXCEEDED: "Die Gesamtgröße der Dateien überschreitet das Limit von {maxSize}.",
            MIN_FILES_REQUIRED: "Sie müssen mindestens {minFiles} Dateien hochladen.",
            NULL_UPLOAD_FORBIDDEN: "Hochladen ohne Dateien nicht möglich.",
            FILE_TYPE_NOT_ALLOWED: "Die Datei '{fileName}' hat keine gültige Erweiterung.",
            FILE_SIZE_EXCEEDED: "Die Datei '{fileName}' überschreitet die maximale Größe von {maxSize}.",
            FILE_SIZE_MIN_EXCEEDED: "Die Datei '{fileName}' ist unter der Mindestgröße von {minSize}.",
            FILE_COUNT_EXCEEDED: "Die maximale Anzahl von Dateien ({maxCount}) wurde erreicht.",
            ERROR_TITLE: "Fehler beim Hochladen",
            SUCCESS_TITLE: "Hochladen erfolgreich",
            CLOSE_MODAL_BTN: "Schließen",
            UPLOAD_SUCCESS: "Hochladen gestartet! (Simulierte Hochlade-Logik)",
            INVALID_METHOD: "Ungültige Methode '{method}' für myUploader.",
            INVALID_OPTION: "Ungültige Option '{option}'.",
            CONSTRAINTS_MODAL_TITLE: "Zusammenfassung der Einschränkungen",
            SKIPPED_FILES_TITLE: "Die folgenden Dateien wurden übersprungen:",
            FILE_REASON: "<strong>{fileName}</strong>: {reason}",
            REMOVE_BTN_ARIA: "Datei entfernen",
            REMOVE_BTN_ALT: "Entfernen",
            FILE_ICON_ALT: "Dateisymbol"
        }
    };
    
    // Definisce le opzioni di configurazione predefinite
    const defaults = {
        uploaderId: '', // ID del contenitore
		path:'/gimi/myformstools/src',
        maxNumberOfFiles: 5,
        minNumberOfFiles: 1,
        maxFileSize: 5000000, // 5 MB
        minFileSize: 1000, // 1 KB
        maxTotalSize: 10000000, // 10 MB
        acceptFileTypes: /^(gif|jpe?g|png|pdf)$/i,
        fileConstraints: {
            'pdf': { maxSize: 2000000, maxCount: 2, minSize: 50000 } // 2 MB per i PDF, 50 KB minimo
        },
        initialFiles: [], // Opzione per i file pre-caricati
        notNull: true, // se true, non si può avviare l'upload con lista file vuota
        // Funzione di validazione personalizzata
        customValidation: function(file) {
			/*
            const extension = file.name.split('.').pop().toLowerCase();
            if (file.name.toLowerCase().includes("test")) {
                return { valid: false, message: "Il nome del file non può contenere la parola 'test'." };
            }
            if (extension === 'gif' && file.size > 1000000) { // 1 MB
                return { valid: false, message: "Le immagini GIF non possono superare 1 MB." };
            }
			*/
            return { valid: true };
        }
    };


	
    // Icona GIF per il pulsante di rimozione
    const REMOVE_ICON_URL = 'https://placehold.co/16x16/dc3545/ffffff.gif?text=X';

    // Elenco delle opzioni configurabili
    const configurableOptions = Object.keys(defaults);

    // Definisce i metodi pubblici del plugin
    const methods = {
        /**
         * Imposta un'opzione di configurazione.
         * @param {string} key - Il nome dell'opzione da modificare.
         * @param {*} value - Il nuovo valore dell'opzione.
         */
        setOption: function(key, value) {
            const instance = $(this).data('myUploader');
            if (!instance) {
                console.error("myUploader non è stato inizializzato su questo elemento.");
                return;
            }
            if (!configurableOptions.includes(key)) {
                instance.showMessage(MESSAGES[instance.currentLang].INVALID_OPTION.replace('{option}', key));
                return;
            }
            instance.settings[key] = value;
        },

			formatSize:function(size){ 
															return (size>1048576/16?(size / 1048576 ).toFixed(2)+' MB':(size / 1024).toFixed(2)+' KB');
														},
			
		/**
	        * Restituisce una stringa che riassume tutti i vincoli di caricamento.
	        * @param {string} [lang] - Lingua opzionale per il riepilogo. Se non specificata, usa la lingua corrente.
	        * @returns {string} Una stringa formattata con i vincoli.
	        */
	       getConstraintsSummary: function(lang) {
			           const instance = $(this).data('myUploader');
			           if (!instance) {
			               console.error("myUploader non è stato inizializzato su questo elemento.");
			               return "";
			           }

			           const currentLang = lang || instance.currentLang;
			           const settings = instance.settings;
			           const messages = MESSAGES[currentLang];
			           let summary = messages.CONSTRAINT_SUMMARY_TITLE + "\n\n";

			           // Vincoli generali
			           if(settings.maxNumberOfFiles!==NaN) summary += messages.MAX_FILES_CONSTRAINT.replace('{maxFiles}', settings.maxNumberOfFiles) + "\n";
			           if(settings.minNumberOfFiles!==NaN) summary += messages.MIN_FILES_CONSTRAINT.replace('{minFiles}', settings.minNumberOfFiles) + "\n";
			           if(settings.maxFileSize!==NaN) summary += messages.MAX_FILE_SIZE_CONSTRAINT.replace('{maxSize}',methods.formatSize (settings.maxFileSize)) + "\n";
			           if(settings.minFileSize!==NaN) summary += messages.MIN_FILE_SIZE_CONSTRAINT.replace('{minSize}', methods.formatSize(settings.minFileSize )) + "\n";
			           if(settings.maxTotalSize!==NaN) summary += messages.TOTAL_SIZE_CONSTRAINT.replace('{maxTotalSize}', methods.formatSize(settings.maxTotalSize)) + "\n";
			           
			           if(settings.fileExt) summary += messages.ALLOWED_FILE_TYPES_CONSTRAINT.replace('{fileTypes}', settings.fileExt) + "\n";
					   
			           // Vincoli specifici per tipo di file
			           if (Object.keys(settings.fileConstraints).length > 0) {
			               summary += "\n" + messages.SPECIFIC_CONSTRAINTS_TITLE + "\n";
			               for (const type in settings.fileConstraints) {
			                   const constraints = settings.fileConstraints[type];
			                   summary += messages.FILE_TYPE_CONSTRAINT
			                       .replace('{fileType}', type.toUpperCase())
			                       .replace('{maxCount}', constraints.maxCount)
			                       .replace('{maxSize}',  methods.formatSize(constraints.maxSize))
			                       .replace('{minSize}',  methods.formatSize(constraints.minSize));
			                   summary += "\n";
			               }
			           }
			           return summary+='\n\n';
			       }
	    };

	    // La funzione principale del plugin
	    $.fn.uploader = function(methodOrOptions) {
			// Gestisce le chiamate ai metodi
			        if (typeof methodOrOptions === 'string' && methods[methodOrOptions]) {
			            return methods[methodOrOptions].apply(this, Array.prototype.slice.call(arguments, 1));
			        }

			        // Gestisce l'inizializzazione del plugin
			        const options = (typeof methodOrOptions === 'object') ? methodOrOptions : {};

			        return this.each(function() {
			            const $this = $(this);
						
			            // Non inizializzare due volte
			            if ($this.data('myUploader')) {
			                return;
			            }

			            // Unisce le opzioni predefinite con quelle fornite dall'utente
			            const settings = $.extend(true, {}, defaults, options);
						
						// Converte l'espressione regolare in stringa in un oggetto RegExp
						if (typeof settings.acceptFileTypes === 'string') {
						                 settings.acceptFileTypes = new RegExp(settings.acceptFileTypes,"i");
						            }
						
			            let selectedFiles = [];
			            let currentLang = settings.defaultLang || 'it';
						let multiple='';
						
						
						if(settings.maxNumberOfFiles>0) multiple='multiple';
			            // Costruisce l'HTML dell'uploader
			            const htmlContent = `<div class="myuploader upload-container" id="${settings.uploaderId}">
				                <input type="file" id="${settings.uploaderId}" accept="${settings.fileExt}" name="${settings.fileInputName}" ${multiple} style="display: none;">
					                    <div id="drop-zone-${settings.uploaderId}" class="drop-zone ui-state-default">
					                        ${MESSAGES[currentLang].DROP_ZONE_TEXT} 
					                        <span id="constraints-info-icon-${settings.uploaderId}"  style='cursor: pointer' class="constraints-icon" title="${MESSAGES[currentLang].CONSTRAINTS_MODAL_TITLE}">
					                            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					                                <circle cx="12" cy="12" r="10"></circle>
					                                <path d="M9.09 9a3 3 0 0 1 5.8 1c0 2-3 2-3 2"></path>
					                                <path d="M12 17h.01"></path>
					                            </svg>
					                        </span>
					                    </div>
					                    <div id="file-list-container">
					                        <table role="presentation" class="file-list-table">
					                            <tbody id="file-list-${settings.uploaderId}" class="files">
					                                <!-- I file verranno aggiunti qui tramite JS -->
					                            </tbody>
					                        </table>
					                    </div>
					                </div>
					                <!-- Div per il modale di errore di jQuery UI -->
					                <div id="error-dialog-${settings.uploaderId}" title="${MESSAGES[currentLang].ERROR_TITLE}" style="display: none;">
					                    <p></p>
					                </div>
					                 <!-- Div per il modale dei vincoli -->
					                <div id="constraints-dialog-${settings.uploaderId}" title="${MESSAGES[currentLang].CONSTRAINTS_MODAL_TITLE}" style="display: none;">
					                    <pre id="constraints-summary-${settings.uploaderId}"></pre>
					                </div>
					            `;
					            $this.html(htmlContent);

					            // Inizializza i dialog di jQuery UI
					            const $errorDialog = $this.find(`#error-dialog-${settings.uploaderId}`);
					            const $constraintsDialog = $this.find(`#constraints-dialog-${settings.uploaderId}`);
					            
					            $errorDialog.dialog({
					                autoOpen: false,
					                modal: true,
					                resizable: false,
					                draggable: false,
					                buttons: [
					                    {
					                        text: MESSAGES[currentLang].CLOSE_MODAL_BTN,
					                        click: function() {
					                            $(this).dialog("close");
					                        },
					                        class: 'myuploader btn myuploader btn-warning'
					                    }
					                ],
					                open: function(event, ui) {
					                    $(this).closest('.ui-dialog').find('.ui-dialog-buttonpane button').eq(0).focus();
					                },
					                close: function() {
					                    $this.find('.upload-container').attr('aria-hidden', 'false');
					                }
					            });
					            
					            $constraintsDialog.dialog({
					                autoOpen: false,
					                modal: true,
					                resizable: false,
					                draggable: false,
					                width: 500,
					                position: { my: "center", at: "center", of: window },
					                buttons: [
					                    {
					                        text: MESSAGES[currentLang].CLOSE_MODAL_BTN,
					                        click: function() {
					                            $(this).dialog("close");
					                        },
					                        class: 'myuploader btn myuploader btn-warning'
					                    }
					                ],
					                open: function(event, ui) {
					                    $(this).closest('.ui-dialog').find('.ui-dialog-buttonpane button').eq(0).focus();
					                }
					            });

					            // Funzioni interne
					            const internalFunctions = {
					                updateUIForLanguage: function(lang) {
					                    currentLang = lang;
					                    $this.find('h1').text(MESSAGES[lang].UPLOAD_TITLE);
					                    $this.find('#start-upload-btn').text(MESSAGES[lang].START_UPLOAD_BTN);
					                    $this.find('#cancel-upload-btn').text(MESSAGES[lang].CANCEL_UPLOAD_BTN);
					                    $this.find(`#drop-zone-${settings.uploaderId} p`).text(MESSAGES[lang].DROP_ZONE_TEXT);
					                    $errorDialog.dialog('option', 'title', MESSAGES[lang].ERROR_TITLE);
					                    $constraintsDialog.dialog('option', 'title', MESSAGES[lang].CONSTRAINTS_MODAL_TITLE);
					                    $this.find(`#constraints-info-icon-${settings.uploaderId}`).attr('title', MESSAGES[lang].CONSTRAINTS_MODAL_TITLE);
					                },
					                showMessage: function(message, isSuccess = false) {
					                    $this.find('.upload-container').attr('aria-hidden', 'true');
					                    $errorDialog.find('p').html(message); // Usa .html() per inserire il markup
					                    $errorDialog.dialog('option', 'title', isSuccess ? MESSAGES[currentLang].SUCCESS_TITLE : MESSAGES[currentLang].ERROR_TITLE);
					                    $errorDialog.dialog("open");
					                },
									
									
					                renderFiles: function() {
					                    const $fileList = $this.find(`#file-list-${settings.uploaderId}`);
					                    $fileList.empty();
					                    selectedFiles.forEach(file => {
					                        const extension = file.name.split('.').pop().toLowerCase();
					                        const iconUrl = settings.path+'MyUploadIcones/'+extension+'.gif';
					                        
					                        let fileUrl;
					                        let fileDownload = false;

					                        // Gestisce i file precaricati dal server
					                        if (file.isServerFile) {
					                            fileUrl = file.url;
												fileDownload = true; // Imposta il flag per l'attributo 'download'
					                        } else {
					                            // Gestisce i file caricati dall'utente
					                            fileUrl = URL.createObjectURL(file);
					                        }
											let name=settings.fileInputName.replace(/\[\]$/, "");
					                        const rowHtml = `
					                            <tr data-filename="${file.name}" class="ui-dialog-titlebar ui-state-default ">
													<td class="myuploader file-actions" style="width:1.2em">
																    <button type="button"  aria-label="${MESSAGES[currentLang].REMOVE_BTN_ARIA} ${file.name}" 	class="myuploader remove-btn ui-corner-all ui-widget ui-button-icon-only ui-dialog-titlebar-close ui-button" style="padding:0.7em 0.7em;width:0.7em;height:0.7em" aria-label="${MESSAGES[currentLang].REMOVE_BTN_ARIA} ${file.name}"  title="${MESSAGES[currentLang].REMOVE_BTN_ALT}">
																			<span class="ui-button-icon ui-icon ui-icon-closethick"></span><span class="ui-button-icon-space"> </span>${MESSAGES[currentLang].REMOVE_BTN_ALT}
																	</button>
													</td>
													<td class="myuploader file-name ">
														${fileDownload ?`<input type='hidden'  name='${name}[NODELETE][]' value='${file.hash}' />`:``}
														<a href="${fileUrl}" target="_blank" title='Download' ${fileDownload ? 'download' : ''}>
					                                    	<img src="${iconUrl}"   alt="${MESSAGES[currentLang].FILE_ICON_ALT} ${extension}" onerror="this.onerror=null; this.src='${settings.path}MyUploadIcones/default.gif'" style="height:1.7em;width:1.7em" />&nbsp;${file.name}
														</a>
					                                </td>
					                                <td>&nbsp;
													<td>&nbsp;${methods.formatSize(file.size)}
													<span style="display:none" class="myuploader file-size">${file.size}</span>
													</td>
					                                
					                            </tr>
					                        `;
					                        $fileList.append(rowHtml);
					                    });
					                },
					                addFile: function(file) {
					                    const extension = file.name.split('.').pop().toLowerCase();
					                    const isDuplicate = selectedFiles.some(f => f.name === file.name);

					                    // Esegue la validazione e restituisce il motivo dello scarto
					                    const validateFile = (f) => {

											if (isDuplicate) return MESSAGES[currentLang].DUPLICATE_FILE.replace('{fileName}', f.name);
					                        if (selectedFiles.length >= settings.maxNumberOfFiles) return MESSAGES[currentLang].FILE_COUNT_EXCEEDED.replace('{maxCount}', settings.maxNumberOfFiles);
					                        if (!settings.acceptFileTypes.test(extension)) return MESSAGES[currentLang].FILE_TYPE_NOT_ALLOWED.replace('{fileName}', f.name);
					                        if (f.size < settings.minFileSize) return MESSAGES[currentLang].FILE_SIZE_MIN_EXCEEDED.replace('{fileName}', f.name).replace('{minSize}', (settings.minFileSize / 1000));
					                        if (f.size > settings.maxFileSize) return MESSAGES[currentLang].FILE_SIZE_EXCEEDED.replace('{fileName}', f.name).replace('{maxSize}', (settings.maxFileSize / 1000000));
					                        
					                        const totalSize = selectedFiles.reduce((sum, f) => sum + f.size, 0) + f.size;
					                        if (totalSize > settings.maxTotalSize) return MESSAGES[currentLang].TOTAL_SIZE_EXCEEDED.replace('{maxSize}', (settings.maxTotalSize / 1000000));

					                        if (settings.fileConstraints[extension]) {
					                            const count = selectedFiles.filter(f => f.name.split('.').pop().toLowerCase() === extension).length;
					                            if (count >= settings.fileConstraints[extension].maxCount) {
					                                return MESSAGES[currentLang].FILE_COUNT_EXCEEDED.replace('{maxCount}', settings.fileConstraints[extension].maxCount).replace('file', `file ${extension}`);
					                            }
					                            if (f.size > settings.fileConstraints[extension].maxSize) {
					                                return MESSAGES[currentLang].FILE_SIZE_EXCEEDED.replace('{fileName}', f.name).replace('{maxSize}', (settings.fileConstraints[extension].maxSize / 1000000));
					                            }
					                            if (settings.fileConstraints[extension].minSize && f.size < settings.fileConstraints[extension].minSize) {
					                                 return MESSAGES[currentLang].FILE_SIZE_MIN_EXCEEDED.replace('{fileName}', f.name).replace('{minSize}', (settings.fileConstraints[extension].minSize / 1000));
					                            }
					                        }
					                        const customResult = settings.customValidation(f);
					                        if (!customResult.valid) return customResult.message;

					                        return null; // File valido
					                    };

					                    const rejectedReason = validateFile(file);
					                    if (rejectedReason) {
					                        return { file, reason: rejectedReason };
					                    }

					                    selectedFiles.push(file);
					                    internalFunctions.renderFiles();
					                    return null; // File aggiunto con successo
					                },
					            };

					            // Gestisce i file iniziali passati come opzione
					            if (Array.isArray(settings.initialFiles)) {
					                settings.initialFiles.forEach(fileData => {
					                    const serverFile = {
					                        name: fileData.name,
					                        url: fileData.url,
											hash: fileData.hash,
					                        size: fileData.size || 0,
					                        isServerFile: true
					                    };
					                    selectedFiles.push(serverFile);
					                });
					            }
					            
								 
								
					            // Memorizza l'istanza e le funzioni sull'elemento DOM per l'accesso futuro
					            $this.data('myUploader', {
					                settings: settings,
					                selectedFiles: selectedFiles,
					                currentLang: currentLang,
					                showMessage: internalFunctions.showMessage,
					                showConstraints: function() {
					                    const summaryText = methods.getConstraintsSummary.call($this);
					                    $constraintsDialog.find(`#constraints-summary-${settings.uploaderId}`).text(summaryText);
					                    $constraintsDialog.dialog("open");
					                },
									getFiles: () => selectedFiles
					            });

					            // Eventi
					            $this.find(`#file-input-${settings.uploaderId}`).on('change', function() {
					                const files = this.files;
					                const rejectedFiles = [];
					                $.each(files, (index, file) => {
					                    const rejected = internalFunctions.addFile(file);
					                    if (rejected) {
					                        rejectedFiles.push(rejected);
					                    }
					                });
					                
					                if (rejectedFiles.length > 0) {
					                    const rejectedSummary = `
					                        <p>${MESSAGES[currentLang].SKIPPED_FILES_TITLE}</p>
					                        <ul class="error-list">
					                            ${rejectedFiles.map(r => `<li>${MESSAGES[currentLang].FILE_REASON.replace('{fileName}', r.file.name).replace('{reason}', r.reason)}</li>`).join('')}
					                        </ul>
					                    `;
					                    internalFunctions.showMessage(rejectedSummary);
					                }
					            });

					            const $dropZone = $this.find(`#drop-zone-${settings.uploaderId}`);
					            $dropZone.on('dragover', function(e) {
					                e.preventDefault();
					                e.stopPropagation();
					                $(this).addClass('dragover');
					            });

					            $dropZone.on('dragleave', function(e) {
					                e.preventDefault();
					                e.stopPropagation();
					                $(this).removeClass('dragover');
					            });

					            $dropZone.on('drop', function(e) {
					                e.preventDefault();
					                e.stopPropagation();
					                $(this).removeClass('dragover');
					                const files = e.originalEvent.dataTransfer.files;
					                const rejectedFiles = [];
					                $.each(files, (index, file) => {
					                     const rejected = internalFunctions.addFile(file);
					                     if (rejected) {
					                         rejectedFiles.push(rejected);
					                     }
					                });

					                if (rejectedFiles.length > 0) {
					                    const rejectedSummary = `
					                        <p>${MESSAGES[currentLang].SKIPPED_FILES_TITLE}</p>
					                        <ul class="error-list">
					                            ${rejectedFiles.map(r => `<li>${MESSAGES[currentLang].FILE_REASON.replace('{fileName}', r.file.name).replace('{reason}', r.reason)}</li>`).join('')}
					                        </ul>
					                    `;
					                    internalFunctions.showMessage(rejectedSummary);
					                }
					            });
					            
					            $dropZone.on('click', function(e) {
					                if ($(e.target).closest(`#constraints-info-icon-${settings.uploaderId}`).length === 0) {
					                    $this.find(`#file-input-${settings.uploaderId}`).trigger('click');
					                }
					            });

					            $this.find(`#file-list-${settings.uploaderId}`).on('click', '.remove-btn', function() {
					                const fileName = $(this).closest('tr').data('filename');
					                selectedFiles = selectedFiles.filter(file => file.name !== fileName);
					                internalFunctions.renderFiles();
					            });

					            $this.find('#start-upload-btn').on('click', function() {
					                if (settings.notNull && selectedFiles.length === 0) {
					                    internalFunctions.showMessage(MESSAGES[currentLang].NULL_UPLOAD_FORBIDDEN);
					                    return;
					                }

					                if (selectedFiles.length < settings.minNumberOfFiles) {
					                    internalFunctions.showMessage(MESSAGES[currentLang].MIN_FILES_REQUIRED.replace('{minFiles}', settings.minNumberOfFiles));
					                    return;
					                }
					                
					                console.log('Avvio upload dei seguenti file:', selectedFiles.map(f => f.name));
					                
					                internalFunctions.showMessage(MESSAGES[currentLang].UPLOAD_SUCCESS, true);
					            });

					            $this.find('#cancel-upload-btn').on('click', function() {
					                selectedFiles = [];
					                internalFunctions.renderFiles();
					            });

					            $this.find('#lang-it').on('click', () => internalFunctions.updateUIForLanguage('it'));
					            $this.find('#lang-en').on('click', () => internalFunctions.updateUIForLanguage('en'));
					            $this.find('#lang-es').on('click', () => internalFunctions.updateUIForLanguage('es'));
					            $this.find('#lang-fr').on('click', () => internalFunctions.updateUIForLanguage('fr'));
					            $this.find('#lang-de').on('click', () => internalFunctions.updateUIForLanguage('de'));

					            $this.find(`#constraints-info-icon-${settings.uploaderId}`).on('click', function() {
					                $this.data('myUploader').showConstraints();
					            });

					            internalFunctions.updateUIForLanguage(currentLang);
					            internalFunctions.renderFiles(); // Renderizza i file iniziali all'avvio
								
					        });
					    };
					})(jQuery);