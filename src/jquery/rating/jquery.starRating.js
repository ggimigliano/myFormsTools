/*
 * Plugin che permette di sostituire con dell'html gli oggetti: radio, checkbox e select
 * Si deve passare come opzione un array:
 * Checkbox e radio
 * var data={chiave:[
 						{
					'id':'#RADIO_1',
 						'htmlBase':'<img src=\"../img/cookie-on.png\">',
 						'htmlSelezionata':'<img src=\"../img/cookie-off.png\">',
 						'nascondiField':false
 						},
 						{
 						'id':'#RADIO_2',
 						'htmlBase':'<img src=\"../img/0.png\">',
 						'htmlSelezionata':'<img src=\"../img/5.png\">',
 						'nascondiField':false

 						},
 						{
 						'id':'#RADIO_3',
 						'htmlBase':'<img src=\"../img/cancel-custom-off.png\">',
 						'htmlSelezionata':'<img src=\"../img/cancel-custom-on.png\">',
 						'nascondiField':false

 						}]};
 						
 * select
 * In questo caso si fa riferimento ad un unico oggetto con id=RADIO, ma le varie voci dell'array corrispondono agli option
 * var data={chiave:[
 						{
					'id':'#RADIO',
 						'htmlBase':'<img src=\"../img/cookie-on.png\">',
 						'htmlSelezionata':'<img src=\"../img/cookie-off.png\">',
 						'nascondiField':false
 						},
 						{
 						'id':'#RADIO',
 						'htmlBase':'<img src=\"../img/0.png\">',
 						'htmlSelezionata':'<img src=\"../img/5.png\">',
 						'nascondiField':false

 						},
 						{
 						'id':'#RADIO',
 						'htmlBase':'<img src=\"../img/cancel-custom-off.png\">',
 						'htmlSelezionata':'<img src=\"../img/cancel-custom-on.png\">',
 						'nascondiField':false

 						}]};
 */
(function($){
	 $.fn.star = function(options,chiave) {
		 var defaults = {
				 htmlBase: "",
				 htmlSelezionata:'',
				 nascondiField:true
			};
		
		 var options = $.extend(defaults, options);
		 var idOggetto=new Array();
		 var htmlBase=new Array();
		 var htmlSelezionata=new Array();
		 var nascondiField=new Array();
		 var selettore;
		 var puntatore;
		 var defaults={};
	/*	 var mouseOver=function(x){attivo(x);}
		 var mouseOut=function(x) {non_attivo(x)}
		*/
		 
		 
		 /*
		  * Metodo che gestisce i select
		  */
		 function oggetti_select(idx){
			 htmlBase[idx]=(options[chiave][i]['htmlBase']);
			 htmlSelezionata[idx]=(options[chiave][i]['htmlSelezionata']);
			 nascondiField[idx]=(options[chiave][i]['nascondiField']);
			 var accapo=(options[chiave][i]['accapo']);
			 var  selettore=idOggetto[idx];
			 var azioni=(options[chiave][i]['azioni']);
			//Aggiungo lo span contenente l'html richiesto
			 var label= $(selettore + '> option:eq(' + idx + ')').text();
			 var isattivo=$(selettore + '> option:eq(' + idx + ')').prop("selected")==true;
			 
			 if($('#SPANRATING_CONTAINER_'+chiave).length==0) {
				if (options[chiave][idx]['nascondiField']!=false) $(selettore).hide();
				$(selettore).before('<div id="SPANRATING_CONTAINER_'+chiave+'" style="white-space:nowrap"></div>');
		 		}
			
		
			 $('#SPANRATING_CONTAINER_'+chiave).append('<div id="SPANRATING_'+chiave+'_' + idx + '" class="SPANRATINGCLASS_' + chiave+ '" title="' + label + '" style="cursor:pointer;float:left"><span class="ratingOff" style="display:inline">'+htmlBase[idx]+'</span><span class="ratingOn" style="display:none">'+htmlSelezionata[idx] +'</span>'+(options[chiave][idx]['nascondiLabel']!=false?'':label)+'</div>');
			 if(accapo>0 && (idx+1)%accapo==0) $('#SPANRATING_CONTAINER_'+chiave).append('<br style="clear:left" />');

		 	 $('#SPANRATING_' + chiave+'_' +idx).mouseover(function(){if ($(selettore + '> option:eq(' + idx + ')').prop("selected")==false) defaults[chiave]['attivo']($(this));});
			 $('#SPANRATING_' + chiave+'_' +idx).mouseout(function(){if ($(selettore + '> option:eq(' + idx + ')').prop("selected")==false) defaults[chiave]['non_attivo']($(this));});
			 
			 $('#SPANRATING_' + chiave+'_' +idx).click(function(){
				    var continua=defaults[chiave]['riempiSX'];
				    if($(selettore).get(0).selectedIndex===idx && !defaults[chiave]['notNull']) 
				    												{
				    					 							  idx=-1;
				    					 							  var continua=false;
				    				 								 }
				    $(selettore).val('');
				    $(selettore + '> option').each(function(i){
				    		//	alert(i+' '+idx+' ('+continua+')');
				    			if(i!=idx) var checked=false;
				    			      else {
				    				  	    var checked=true;
				    				  	    $(this).prop("selected",true);
				    						}
			  					if (continua || checked){
					  					    $('#SPANRATING_' + chiave+'_' +i+' .ratingOff').css({'display':'none'});
						  					$('#SPANRATING_' + chiave+'_' +i+' .ratingOn').css({'display':'inline'});
						  					
						  					if(!checked) defaults[chiave]['non_attivo']($('#SPANRATING_' + chiave+'_' +i+' .ratingOn').parent());
						  							else{
									  				    defaults[chiave]['su_accensione']($('#SPANRATING_' + chiave+'_' +i+' .ratingOn'));
									  					defaults[chiave]['attivo']($('#SPANRATING_' + chiave+'_' +i+' .ratingOn').parent());
									  					continua=false;
						  								}
						  				}
			  					else 	{
				  					    $('#SPANRATING_' + chiave+'_' +i+' .ratingOn').css({'display':'none'});
				  					    $('#SPANRATING_' + chiave+'_' +i+' .ratingOff').css({'display':'inline'});
				  					    defaults[chiave]['su_spegnimento']($('#SPANRATING_' + chiave+'_' +i+' .ratingOn'));
				  					    defaults[chiave]['non_attivo']($('#SPANRATING_' + chiave+'_' +i+' .ratingOff').parent());
				  						}
					  			  
			  					});
			  	
			  	 });
			 
			 for(var j=0;j<azioni.length;j++) eval("$('#SPANRATING_" + chiave+'_'+idx+"')"+azioni[j]);
			
			  if(isattivo) $('#SPANRATING_' + chiave+'_' +idx).trigger('click');
					  else defaults[chiave]['non_attivo']($('#SPANRATING_' + chiave+'_' +idx));
				  
		 }
		 /*
		  * Metodo che gestisce i radio e i check
		  */
		 function oggetti_vari(idx){
			 	
			 	idOggetto[idx]= (options[chiave][idx]['id']);
				htmlBase[idx]=(options[chiave][i]['htmlBase']);
			    htmlSelezionata[idx]=(options[chiave][i]['htmlSelezionata']);
			 	nascondiField[idx]=(options[chiave][idx]['nascondiField']);
			  	var selettore=$(options[chiave][idx]['id']);
			  	var accapo=(options[chiave][i]['accapo']);
			  	var azioni=(options[chiave][i]['azioni']);
			  	if (options[chiave][idx]['nascondiField']!=false) 
			  								{$(selettore).hide();
			  								 $('#lbl_'+selettore.prop('id')).hide();
			  								}
			  	
			  	var label= ($('#lbl_'+ selettore.prop('id')).html());
			  	
				var isattivo=$(selettore).is(":checked");
				if($('#SPANRATING_CONTAINER_'+chiave).length==0) 
				 		{
						//costruisco opzioni
					 	$(selettore).before('<div id="SPANRATING_CONTAINER_'+chiave+'" style="white-space:nowrap"></div>');
				 		}
							
				$('#SPANRATING_CONTAINER_'+chiave).append('<div id="SPANRATING_'+chiave+'_' + idx + '" class="SPANRATINGCLASS_' + chiave+ '" title="' + label + '" style="cursor:pointer;float:left"><span class="ratingOff">'+htmlBase[idx]+'</span><span class="ratingOn" style="display:none">'+htmlSelezionata[idx] +'</span>'+(options[chiave][idx]['nascondiLabel']!=false?'':label)+'</div>');
				if(accapo>0 && (idx+1)%accapo==0) $('#SPANRATING_CONTAINER_'+chiave).append('<br style="clear:left" />');
				
			 	//Catturo l'evento click sullo span
			  	 $('#SPANRATING_' + chiave+'_' +idx).click(function(){
			  		 	
			  		    if (!$(selettore).is(":checked")) $(selettore).prop("checked",true);
				  			else {
				  					if ($(selettore).prop('type')!='radio' || !defaults[chiave]['notNull']) $(selettore).prop("checked",false);
							 	 }
				  		var continua=false;
			  			for(var i=options[chiave].length-2;i>=0;i--) {
			  				var rate=$(options[chiave][i]['id']);
			  				if (continua || $(rate).is(":checked")) 
					  				{   
					  				    $('#SPANRATING_' + chiave+'_' +i+' .ratingOff').css({'display':'none'});
					  					$('#SPANRATING_' + chiave+'_' +i+' .ratingOn').css({'display':'inline'});
					  					if(!continua) { defaults[chiave]['su_accensione']($('#SPANRATING_' + chiave+'_' +i+' .ratingOn'));
					  									defaults[chiave]['attivo']($('#SPANRATING_' + chiave+'_' +i+' .ratingOn').parent());
					  									}
					  							else  defaults[chiave]['non_attivo']($('#SPANRATING_' + chiave+'_' +i+' .ratingOn').parent());
					  					if(defaults[chiave]['riempiSX']) continua=true;
					  				}
			  					else{
				  					    $('#SPANRATING_' + chiave+'_' +i+' .ratingOn').css({'display':'none'});
				  					    $('#SPANRATING_' + chiave+'_' +i+' .ratingOff').css({'display':'inline'});
				  					    defaults[chiave]['su_spegnimento']($('#SPANRATING_' + chiave+'_' +i+' .ratingOff'));
				  					    defaults[chiave]['non_attivo']($('#SPANRATING_' + chiave+'_' +i+' .ratingOff').parent());
			  						}
			  				}
			  			//$("[id^=SPANRATING_"+ chiave+"_]").each(function(){defaults[chiave]['non_attivo']($(this))});
				  });
			 	 
			  	$('#SPANRATING_' + chiave+'_' +idx).mouseover(function(){
			  															defaults[chiave]['attivo']($(this));
			  															});
			  	$('#SPANRATING_' + chiave+'_' +idx).mouseout(function(){
															  			 if ($(selettore).prop("checked")==false ||
																		     $(selettore).prop("checked")==undefined) defaults[chiave]['non_attivo']($(this));
															  		 	});
			  	
			  	for(var j=0;j<azioni.length;j++) eval("$('#SPANRATING_" + chiave+'_'+idx+"')"+azioni[j]);
			  	if(isattivo)   {$(selettore).prop("checked",false); //deseleziona valore 
			  					$('#SPANRATING_' + chiave+'_' +idx).trigger('click'); //simula click e scatena eventuali eventi
			  					$(selettore).prop("checked",true);//ad ogni buon conto forza selezione del valore
			  					}
			  			  else defaults[chiave]['non_attivo']($('#SPANRATING_' + chiave+'_' +idx));
			  	
			  	
		 }
		 
		 $('#div_'+chiave.split('_RATING_')[0]).css('width','auto');
		 defaults[chiave]=options[chiave][options[chiave].length-1];
		 
		 for (var i = 0; i < options[chiave].length-1 ; i++) {
			 idOggetto[i]= (options[chiave][i]['id']);
			 var tipo=$(idOggetto[i]).prop('type');
			 if (tipo!='select-one' || typeof(tipo)=="undefined") oggetti_vari(i);
			 												 else oggetti_select(i);
	 	}
		 $('#SPANRATING_CONTAINER_'+chiave).append('<br style="clear:left" />');
		
	 }
})(jQuery)
	
