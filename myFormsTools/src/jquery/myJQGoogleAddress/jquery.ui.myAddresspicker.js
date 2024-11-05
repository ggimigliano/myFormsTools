/*
 * jQuery UI addresspicker @VERSION
 * 
 *  street_address indicates a precise street address.
*    route indicates a named route (such as "US 101").
*    intersection indicates a major intersection, usually of two major roads.
*    political indicates a political entity. Usually, this type indicates a polygon of some civil administration.
*    country (nazione) indicates the national political entity, and is typically the highest order type returned by the Geocoder.
*    administrative_area_level_1 (Regione) indicates a first-order civil entity below the country level. Within the United States, these administrative levels are states. Not all nations exhibit these administrative levels.
*    administrative_area_level_2 (Città metropolitana) indicates a second-order civil entity below the country level. Within the United States, these administrative levels are counties. Not all nations exhibit these administrative levels.
*    administrative_area_level_3 (Provincia). Not all nations exhibit these administrative levels.
*    colloquial_area indicates a commonly-used alternative name for the entity.
*    locality (Comune) indicates an incorporated city or town political entity.
*    sublocality indicates an first-order civil entity below a locality.
*    neighborhood indicates a named neighborhood.
*    establishment (ENTE)
*    premise indicates a named location, usually a building or collection of buildings with a common name
*    subpremise indicates a first-order entity below a named location, usually a singular building within a collection of buildings with a common name.
*    postal_code (CAP) , indicates a postal code as used to address postal mail within the country.
*    natural_feature indicates a prominent natural feature.
*    airport indicates an airport.
*    park indicates a named park.
*    post_box indicates a specific postal box.
*    street_number (CIVICO) indicates the precise street number.
*    floor indicates the floor of a building address.
*    room indicates the room of a building address.
 *
 *
 * Depends:
 *   jquery.ui.core.js
 *   jquery.ui.widget.js
 *   jquery.ui.autocomplete.js
 */
(function( $ ) {

  $.widget( "ui.myAddresspicker", {
	options: {
		___cache:{},
		___trovati:{},
		___indispensabili:[['STRADA']],
		___possibili_campi:{country:"STATO",
						 street_number:"CIVICO",
						 route:"STRADA",
						 administrative_area_level_1:"REGIONE",
						 administrative_area_level_2:"TARGA",
						 administrative_area_level_2:"PROVINCIA",
						 locality:"COMUNE",
						 postal_code:"CAP",
						 establishment:"ENTE"
						 },  
	    appendAddressString: " ",
	    minLength:7,
        draggableMarker: true,
        lingua: "IT-it",
        READY:false,
        formato_label:function(){return "{#STRADA#}{,#CIVICO#} {- #COMUNE#} {(#provincia#)}";},
        formato_value:function() {return "{#STRADA#}{,#CIVICO#}";},
        JQOUTFields:[],
        JQINFields:[],
        JQINVals:[]
        },
        
    selected: function() {
      return this.selectedResult;
    },

	  

     _minit:function(self){
	    	 self.geocoder = new google.maps.Geocoder();	
  		     self.element.autocomplete({
					source: jQuery.proxy(self._geocode,self),
					focus:  jQuery.proxy(self._focusAddress, self),
					select: jQuery.proxy(self._selectAddress,self),
					open:   jQuery.proxy(self._openAddress, self),
					minLength:self.options.minLength
					});
		 self.READY=true;
	}, 
	
    _inits:function(){
    	
    	do{
           var self=jQuery.prototype.MapsToLoad.pop();
           if(self){ self._minit(self);}
		}while(jQuery.prototype.MapsToLoad.length>0);	
    },

    _create: function() {
    	var self=this;
    	
    	if(typeof(jQuery.prototype.MapsToLoad) =='undefined') 
    		 try { jQuery.prototype.MapsToLoad=[];
    		       jQuery.prototype.MapsToLoad.push(this); 
				   jQuery.prototype.MapsLoaded=0;
				   jQuery.getScript('https://maps.google.com/maps/api/js?sensor=false',
						   function(){jQuery.prototype.MapsLoaded=1;
						   			  self._inits();} );
				    return;	
				 }catch (err) {jQuery.prototype.MapsLoaded=-1;}
	   jQuery.prototype.MapsToLoad.push(this);				 
	   if(jQuery.prototype.MapsToLoad==1)  this._inits();
    },

    
    _build_hash: function(x){
    	var inutili=["il","lo","la","i","gli","le","un","uno","una",
    	             "di","de","dei","degli","del","dell",
    	             "a","al","ad","al","all","alle","agli",	
     	             "da","dai","dagli","dalle","dal","dall",
    	             "in","ne","nei","negli","nelle","nella","nel","nell",
    	             "su","sul","sull","sugli","sulla","sulle","sui",
    	             "per",
    	             "tra",
    	             "fra"];
    	x=x.toLowerCase().replace(/[^a-z0-9 ]+/g,' ').replace(/\s+/g,' ').replace('citt metropolitana di ','').replace('provincia di ','');
    	var nuova="";
    	var parole=x.split(' ');
    	for(var i=0;i<parole.length;i++) 
    			if(inutili.indexOf(parole[i])==-1) nuova+=parole[i];
    	return nuova;
    },
    
    _congruenti: function(a,b){
    	if(b==undefined || b.length==0) return false;
    	//alert(this._build_hash(a)+'=='+this._build_hash(b));
    	return this._build_hash(a)==this._build_hash(b);
    },
    
    // Autocomplete source method: fill its suggests with google geocoder results
    _geocode: function(request, response) {
        if(!this.READY) return;
        var address = request.term, self = this;
        var ente='';var cap=''; var provincia=''; var comune=''; var stato=''; var civico='';var strada='';var targa='';
        
        for(var i in this.options.JQINFields) {
        	if(this.options.JQINFields[i]['ENTE']!=undefined && 
              	   ente.length==0)  ente=this.options.JQINFields[i]['ENTE']($);
        	
        	if(this.options.JQINFields[i]['STRADA']!=undefined) 
        							address=this.options.JQINFields[i]['STRADA']($);
        	
        	if(this.options.JQINFields[i]['COMUNE']!=undefined && 
        			comune.length==0)  comune=this.options.JQINFields[i]['COMUNE']($);
        	
        	if(this.options.JQINFields[i]['CIVICO']!=undefined && 
             	   civico.length==0)  civico=this.options.JQINFields[i]['CIVICO']($);
            
        	if(this.options.JQINFields[i]['PROVINCIA']!=undefined && 
        			provincia.length==0) provincia=this.options.JQINFields[i]['PROVINCIA']($);
         	
            if(this.options.JQINFields[i]['CAP']!=undefined &&
            		cap.length==0) cap=this.options.JQINFields[i]['CAP']($);
           
         	if(this.options.JQINFields[i]['STATO']!=undefined && 
         			stato.length==0) stato=this.options.JQINFields[i]['STATO']($);
        }
      
        
        for(var i in this.options.JQINVals) {
           if(this.options.JQINVals[i]['ENTE']!=undefined && 
       	    		ente.length==0) ente=this.options.JQINVals[i]['ENTE'];
       	
           if(this.options.JQINVals[i]['COMUNE']!=undefined && 
             	   comune.length==0)  comune=this.options.JQINVals[i]['COMUNE'];
        	
        	if(this.options.JQINVals[i]['CIVICO']!=undefined && 
              	   civico.length==0) civico=this.options.JQINVals[i]['CIVICO'];
        	
        	if(this.options.JQINVals[i]['PROVINCIA']!=undefined && 
                    provincia.length==0) provincia=this.options.JQINVals[i]['PROVINCIA'];
             
        	 if(this.options.JQINVals[i]['CAP']!=undefined &&
             		cap.length==0) cap=this.options.JQINVals[i]['CAP'];
         
        	 if(this.options.JQINVals[i]['STATO']!=undefined && 
                   	stato.length==0) stato=this.options.JQINVals[i]['STATO'];
        	 
        	 if(this.options.JQINVals[i]['STRADA']!=undefined && 
        			 address.length==0) address=this.options.JQINVals[i]['STRADA'];
        }
        
        var components='';
        if(address!=undefined && address!='')     components+='|address:'+address;
        if(comune!=undefined  && comune!='')      components+='|locality:'+comune;
        if(cap!=undefined     && cap!='')         components+='|postalCode:'+cap;
        if(provincia!=undefined && provincia!='') components+='|administrative_area_3:'+provincia;
        this.options.appendAddressString=address;
        this.options.appendAddressString+=","+civico;		this.options.appendAddressString=this.options.appendAddressString.replace(/\,\,/,',');
        this.options.appendAddressString+=","+comune;		this.options.appendAddressString=this.options.appendAddressString.replace(/\,\,/,',');
        this.options.appendAddressString+=","+provincia;	this.options.appendAddressString=this.options.appendAddressString.replace(/\,\,/,',');	
        this.options.appendAddressString+=","+cap;			this.options.appendAddressString=this.options.appendAddressString.replace(/\,\,/,',');
        this.options.appendAddressString+=","+stato;		this.options.appendAddressString=this.options.appendAddressString.replace(/\,\,/,',');	
        //if(components!='') this.options.appendAddressString+='&components='+components.substring(1); //pare che blocchi
	    
        if( self.options.___cache[this.options.appendAddressString]!=undefined) 
        							{self.options.___trovati=self.options.___cache[this.options.appendAddressString]['trovati'];
        							 response(self.options.___cache[this.options.appendAddressString]['out']);
        							 return;
        							}
        
        this.geocoder.geocode({
            'address':this.options.appendAddressString,
            'region': this.options.lingua
        	}, 
       
        
        function(results, status) {
        	
            if (status == google.maps.GeocoderStatus.OK) {
            	var out=[];
            	self.options.___trovati={};
            	
                for (var i = 0; i < results.length; i++) {
                	var salta=0;
                    self.options.___trovati[i]={}; 
                  // alert(JSON.stringify(results[i]));
   				    for (var j=0;j<results[i].address_components.length;j++) 
                    	for(var campo in self.options.___possibili_campi) 
                    		{
                    		if(results[i].address_components[j].types.indexOf(campo)>=0) 
                    				{
                    				 var mioCampo=self.options.___possibili_campi[campo];
                    				 self.options.___trovati[i][mioCampo.toLowerCase()]=results[i].address_components[j].short_name;
                    				 self.options.___trovati[i][mioCampo.toUpperCase()]=results[i].address_components[j].long_name;
                    				}
                    		}	
                   
   				   results[i].value =  self.options.formato_value(self.options.___trovati[i]);
   				   results[i].label =  self.options.formato_label(self.options.___trovati[i]);
   				  
                   for (var campo in self.options.___trovati[i]) {
                	   				if(campo=='provincia' && 
			        					   self.options.___trovati[i]['targa']!=undefined
			        					   ) self.options.___trovati[i]['provincia']=self.options.___trovati[i]['targa'];
               						if(campo=='provincia' && 
					                					   self.options.formato_label(self.options.___trovati[i]).indexOf("#COMUNE#")>=0 &&
					                					   self.options.___trovati[i]['COMUNE']!=undefined &&
					                					   self.options.___trovati[i]['PROVINCIA']!=undefined &&
					                					   self.options.___trovati[i]['COMUNE']==self.options.___trovati[i]['PROVINCIA']) 
					                									continue;
               						var patt=new RegExp("\\{([^#]*)#"+campo+"#([^\\}]*)\\}");
                					results[i].label=results[i].label.replace(patt,'$1'+self.options.___trovati[i][campo]+'$2');	
                					results[i].value=results[i].value.replace(patt,'$1'+self.options.___trovati[i][campo]+'$2');
                					}		
                	

               	

                   
                   for(var campo in self.options.___possibili_campi) {
                					var patt=new RegExp("\\{[^#]*#"+self.options.___possibili_campi[campo]+"#[^\\}]*\\}","i");
                					results[i].label=results[i].label.replace(patt,"");
                					results[i].value=results[i].value.replace(patt,"");
                					}
                	
                	results[i].label=results[i].label.replace(/^\s+|\s+$/g,"");
                	results[i].value=results[i].value.replace(/^\s+|\s+$/g,"");
                	
                	
                	for(var j=0;j<self.options.___indispensabili.length;j++) 
                		    for(var k=0;k<self.options.___indispensabili[j].length;k++) 
                		    	if(self.options.___trovati[i][self.options.___indispensabili[j][k]]==undefined) {salta++;break;}
                		    	
                	if(salta==self.options.___indispensabili.length) salta=-1;
                	//	alert(salta);
                	for(var campo in self.options.___possibili_campi) {
                		var mioCampo=self.options.___possibili_campi[campo];
                		/*
                		 * per verifiche puntuali
                		if(mioCampo=='ENTE' && ente.length>0 && 
    		 		 			!self._congruenti(ente,self.options.___trovati[i][mioCampo]) 
    		 		 			) {
                					alert('Incongruenza '+mioCampo);
                				  }
    					if(mioCampo=='STRADA' && strada.length>0 && 
         						!self._congruenti(strada,self.options.___trovati[i][mioCampo])
    							)  {
		        					alert('Incongruenza '+mioCampo);
    								} 
    					if(mioCampo=='PROVINCIA' && provincia.length>0 && 
     						 	!self._congruenti(provincia,self.options.___trovati[i][mioCampo])
     							)  {
                					alert('Incongruenza '+mioCampo+' '+provincia+'!='+self.options.___trovati[i][mioCampo]);
              				  	}
     					if(mioCampo=='provincia' && provincia.length>0 && 
     		 					!self._congruenti(provincia,self.options.___trovati[i][mioCampo]) 
     		 					)  {
                					alert('Incongruenza '+mioCampo);
              				  		}
     					if(mioCampo=='COMUNE' && comune.length>0 && 
    						 	!self._congruenti(comune,self.options.___trovati[i][mioCampo])
    						 	)  {
                					alert('Incongruenza '+mioCampo);
              				  		}
    					if(mioCampo=='CAP' && cap.length>0 && 
    						 	!self._congruenti(cap,self.options.___trovati[i][mioCampo])
    						 	)  {
                					alert('Incongruenza '+mioCampo);
    						   	}
    					
    					if( (mioCampo=='STATO' && stato.length>0 && 
    						 	!self._congruenti(stato,self.options.___trovati[i][mioCampo])
    						    ) &&
    						   (mioCampo=='stato' && stato.length>0 && 
    							!self._congruenti(stato,self.options.___trovati[i][mioCampo])
    							)
    						   )   {
	               					alert('Incongruenza '+mioCampo);
	             				  }
    					if(mioCampo=='CIVICO' && civico.length>0 && 
    						 	!self._congruenti(civico,self.options.___trovati[i][mioCampo])
    						 	) {
		        					alert('Incongruenza '+mioCampo);
		      				  		}
                		*/
                		
                		if((mioCampo=='ENTE' && ente.length>0 && 
		 		 			!self._congruenti(ente,self.options.___trovati[i][mioCampo]) 
		 		 			) || 
						   (mioCampo=='STRADA' && strada.length>0 && 
     						!self._congruenti(strada,self.options.___trovati[i][mioCampo])
							) || 
						   (mioCampo=='PROVINCIA' && provincia.length>0 && 
 						 	!self._congruenti(provincia,self.options.___trovati[i][mioCampo])
 							) ||
 						   (mioCampo=='provincia' && provincia.length>0 && 
 		 					!self._congruenti(provincia,self.options.___trovati[i][mioCampo]) 
 		 					) || 
 					       (mioCampo=='COMUNE' && comune.length>0 && 
						 	!self._congruenti(comune,self.options.___trovati[i][mioCampo])
						 	) ||
						   (mioCampo=='CAP' && cap.length>0 && 
						 	!self._congruenti(cap,self.options.___trovati[i][mioCampo])
						 	) ||
						  ( (mioCampo=='STATO' && stato.length>0 && 
						 	!self._congruenti(stato,self.options.___trovati[i][mioCampo])
						    ) &&
						   (mioCampo=='stato' && stato.length>0 && 
							!self._congruenti(stato,self.options.___trovati[i][mioCampo])
							)
						   )  ||
						   (mioCampo=='CIVICO' && civico.length>0 && 
						 	!self._congruenti(civico,self.options.___trovati[i][mioCampo])
						 	)){ salta=-1;
						 		break;
						 	   }
                	}
              
                	if(salta!=-1 &&
                	//   self.options.___trovati[i]['STRADA']!=undefined && 
                	   results[i].label.length>0 &&
                	   self.options.___trovati[results[i].label]==undefined) 
                			{
                			 self.options.___trovati[results[i].label]= self.options.___trovati[i];
                			 out[out.length]={label:results[i].label, value:results[i].value};
                			}
                    }
            }
         
            self.options.___cache[self.options.appendAddressString]={'out':out,'trovati':self.options.___trovati};
            
           // for (i=0; i<out.length;i++) alert(out[i].value);
            response(out);
        })
    },


    _openAddress: function(event, ui) {
    	var address = ui.item,self=this;
        if(this.options.open!=undefined) return this.options.open(event,ui);
      },
    
    _focusAddress: function(event, ui) {
    	var address = ui.item,self=this;
      if(this.options.focus!=undefined) return this.options.focus(event,ui);
      
    },

    _selectAddress: function(event, ui) {
      this.selectedResult = ui.item;
      for (var j in this.options.JQOUTFields)	
    	  for(var campo in this.options.JQOUTFields[j]) 
      		 if(this.options.___trovati[ui.item.label][campo]!=undefined || campo.indexOf('_')==0) 
      			 this.options.JQOUTFields[j][campo](this.options.___trovati[ui.item.label][campo],this.options.___trovati[ui.item.label],$);
       if(this.options.select!=undefined) return this.options.select(event,ui);
    }
  });


  // make IE think it doesn't suck
  if(!Array.indexOf){
    Array.prototype.indexOf = function(obj){
      for(var i=0; i<this.length; i++){
        if(this[i]==obj){
          return i;
        }
      }
      return -1;
    }
  }

})( jQuery );
