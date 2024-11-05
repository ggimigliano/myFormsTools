if (typeof myEventManager=='undefined') { 
var myRequiredFields={};
var myEventManager={
  __myTrackAllAddedEvents:{},
  
  __addEventRegister:function(id,evt) {
	  if(typeof this.__myTrackAllAddedEvents[id]=='undefined') this.__myTrackAllAddedEvents[id]=[];
	  if(this.__myTrackAllAddedEvents[id].indexOf(evt)==-1) this.__myTrackAllAddedEvents[id][this.__myTrackAllAddedEvents[id].length]=evt; 
  },
  
  start:function() { 
  try{	  
    var interfaces = [HTMLElement ];
    var self=this;
    for (var i = 0; i < interfaces.length; i++) {
        (function(original) {
        	interfaces[i].prototype.addEventListener =
            function(type, listener, useCapture) {
            	if(this && this.id) self.__addEventRegister(this.id,arguments[0]); 
                 if(this.attachEvent){ //IE
                    arguments[0] = 'on' + arguments[0];
                    return this.attachEvent.apply(this, arguments);
                } else { //other browsers
                    return original.apply(this, arguments);
                }
            }
        })(interfaces[i].prototype.addEventListener);
      }
    } catch(err) {}
   },
	
   
   fire:function(nomeEvento,id) {
	   var event; // The custom event that will be created
	   elemento=document.getElementById(id);
	   if (document.createEvent) {
	   				event = document.createEvent("Events");
	   				event.initEvent(nomeEvento, true, true);
	   				} 
	   		  else {
	   			  	event = document.createEventObject();
	   			  	event.eventType = nomeEvento;
	   		  	   }

	   event.eventName = nomeEvento;
	   if (document.createEvent) elemento.dispatchEvent(event);
	                       else  elemento.fireEvent("on" + event.eventType, event);
	   },
	   
	  fireAll:function(id){
		   var ele=document.getElementById(id);
		   for (var attr=0;attr<ele.attributes.length;attr++) 
		   			if(ele.attributes[attr].name &&
			   		   ele.attributes[attr].name.indexOf('on')==0 &&			
			   		   isNaN(ele.attributes[attr].value) 
				   		 )
		   				{ 
		   				   this.__addEventRegister(id,ele.attributes[attr].name.substr(2));
		   				}
		   		
	//	   alert("ARRAY:"+implode(',',myTrackAllAddedEvents[id]));
		   if(this.__myTrackAllAddedEvents[id] && typeof  this.__myTrackAllAddedEvents[id]!='undefined')
			   for(var i=0;i<this.__myTrackAllAddedEvents[id].length;i++)  this.fire(this.__myTrackAllAddedEvents[id][i], id);
	   },
	   
	  add:function(event,action,element)  {
		  if(!element) element=window;
		  			else {
		  				  if(element.id) this.__addEventRegister(element.id,event); 
		            	 }
		  if(window.addEventListener) element.addEventListener(event,action,false);
		  		else if(window.attachEvent) element.attachEvent('on'+event,action);
	  }
}

	   
	 

function startStopForm(element,funzione) {
	try{
	 if(window.addEventListener) {
 						element.form.addEventListener('submit',
 							function (evt) {
                						if (element.disabled==true && evt.preventDefault) evt.preventDefault();
                						return funzione(element);
 										},false);
 						}
	 else if(window.attachEvent) element.form.attachEvent('onsubmit',funzione(element));
	} catch (Exception) {}
}



function myGetXmlHttpObject()
{
var objMyXmlHttp=null;	 
if (window.XMLHttpRequest) objMyXmlHttp=new window.XMLHttpRequest();
else  {
		if (objMyXmlHttp==null) try{objMyXmlHttp= new ActiveXObject("MSXML3.XMLHTTP");} catch (Exception) {objMyXmlHttp=null;} 
		if (objMyXmlHttp==null) try{objMyXmlHttp= new ActiveXObject("MSXML2.XMLHTTP.3.0");} catch (Exception) {objMyXmlHttp=null;}
        if (objMyXmlHttp==null) try{objMyXmlHttp= new ActiveXObject("Msxml2.XMLHTTP") ;} catch (Exception) {objMyXmlHttp=null;}
        if (objMyXmlHttp==null) try{objMyXmlHttp= new ActiveXObject("Microsoft.XMLHTTP") ;} catch (Exception) {objMyXmlHttp=null;}
       }

return objMyXmlHttp;
}





function selectOptionsSetFromXml(element,xml) {
var xmlDoc;
try{	 
	 if (window.DOMParser)  xmlDoc=(new DOMParser()).parseFromString(xml,'text/xml');
					else // Internet Explorer
						  {
						  var xmlDoc=new ActiveXObject('Microsoft.XMLDOM');
						  xmlDoc.async=false;
						  xmlDoc.loadXML(xml);
						  }

	 if(element.options) while (element.options.length>0) element.remove(element.options.length-1);
	 if(!xmlDoc || !xmlDoc.firstChild) return false;
	}catch (err){return false;}
	
	element.innerHTML=xml.replace(/<select[^>]*>|<\/select>/ig,'');
	element.options.selectedIndex=0;
	return true;
/*	
	
var x=xmlDoc.getElementsByTagName('select')[0];
if(!x) return false;
var x=x.getElementsByTagName('option');
var i=0;

while (!element.options || element.options.length<x.length) 
				{var opt=document.createElement('option');
				if(x[i].childNodes[0] && x[i].childNodes[0].nodeValue)
										opt.text=''+x[i].childNodes[0].nodeValue;
						       else opt.text='';
				
				try {	  
				  if(x[i].getAttribute('value')!='')
					  				  			 opt.setAttribute('value',x[i].getAttribute('value'));
							 			  else   opt.setAttribute('value','');
					
					 } catch (e) {
					 			  var val=document.createAttribute('value');	
					 			  if(x[i].getAttributeNode('value').nodeValue!='')
					  				  			 val.value= x[i].getAttributeNode('value').nodeValue;
							 			  else   val.value='';
								   opt.setAttributeNode(val);	
									 }	   
				element.add(opt,element.options[null]);
				i++;			   
				}  	
element.options.selectedIndex=0;
return true;
*/
}


function myCheckAll(value,rule){
	var inputs=document.getElementsByTagName('input');
	for (var i=0;i<inputs.length;i++)
		 if(inputs[i].type=='checkbox' && rule(inputs[i]))
		 			inputs[i].checked=value;
}


//-----------------------------------------------------------------------------------


function checkKeycode(e) {
if (window.event) keycode = window.event.keyCode;
	  else if (e) keycode = e.which;
return keycode;
}



function MyTabulazione(campo) {
	if (keycode<33 || keycode>126 ) return;
	var trovato=false;
	j=0;
	while (j<document.forms.length && !trovato)
			  {i=0;
				while (i<document.forms[j].elements.length && !trovato)
														  {  if (document.forms[j].elements[i].id==campo.id) {trovato=true;
															 												  i++;
															 												  break;
															 												  }
															 i++;
														  }
			    if (!trovato) j++;
			  }
	if (!trovato) return;


	while(j<document.forms.length)
		 {
		 while(i<document.forms[j].elements.length)
		 	{
		 	if (document.forms[j].elements[i].type==null ||
		 		document.forms[j].elements[i].type=="hidden" ||
		 		document.forms[j].elements[i].readonly ||
		 		document.forms[j].elements[i].tagName=="label" ||
		 		document.forms[j].elements[i].style.display=="none"
		 		) {i++;}
		 		else  {try
						  {document.forms[j].elements[i].focus();
		 			  	   return;
						  }
						 catch(err){i++;}
  		 			  }
		 	}
		  j++;
		 }

}

 

function isUndefined(x){
	return typeof x=='undefined'; 
}

//COMPONENTI smyText::set_molteplicita()
var myMultiTextJSElements={};

function myMultiTextJSrefresh(idx) {
	   var sel=document.getElementById(idx.ids[0]);
	   var disp='none';
	   var values=new Array();
	
	   for (var i=0;i<sel.options.length-1;i++) {
		    if(isUndefined(sel.options[i].text) || 
		       trim(sel.options[i].text)=='' || 
			   trim(sel.options[i].text)==null ) 	
		    					{
		    					 sel.options[i].text=sel.options[i+1].text;
		    					 sel.options[i].value=sel.options[i+1].value;
		    					 sel.options[i+1].text='';
		    					 sel.options[i+1].value='';
		    					}
		    }
	   for (var i=0;i<sel.options.length;i++) {
		    if(!isUndefined(sel.options[i].text) && 
		       trim(sel.options[i].text)!='' && 
			   trim(sel.options[i].text)!=null ) {disp='inline'; 
			   									  values[values.length]=sel.options[i].text;
		    									 }
		}
		if(disp=='none') sel.selectedIndex=0;
		sel.style.display=disp;
		document.getElementById(idx.ids[1]).value=implode(idx.separator,values);
	}	
		
	function myMultiTextJSPreset(idx) {
		var sel=document.getElementById(idx.ids[0]);
		if(sel==null) return;
		myMultiTextJSrefresh(idx);
		for (var i=sel.options.length-1;i>=0;i--) {
		  	  if(isUndefined(sel.options[i].text) || trim(sel.options[i].text)=='' || trim(sel.options[i].text)==null) 
		  		  				 sel.selectedIndex=i;
						  	else {
								  var selected=idx.onupdate(sel.options[i].text);	
						    	  sel.options[i].text=selected[0];
						    	  sel.options[i].value=(selected[1]==null || selected[1]==undefined?selected[0]:selected[1]);
							   }
			}
		while ( sel.selectedIndex>0 &&  
	    		(isUndefined(sel.options[sel.selectedIndex-1].text) || 
	    		 trim(sel.options[sel.selectedIndex-1].text)==''  || 
	    		 trim(sel.options[sel.selectedIndex-1].text)==null)
	    	    ) sel.selectedIndex--;	
			
	}	
		
   function myMultiTextJS(idTesto,idx,operazione) {
	    if(operazione=='add') 	 return this.add_myMultiTextJS(idTesto,idx);
	    if(operazione=='remove') return this.del_myMultiTextJS(idTesto,idx);
   }  
   
   function myMultiTextJSkeypress(e,idHidden,selector,text,me){
	   var keynum;
	   if(window.event) keynum = e.keyCode;
	   		else if(e.which) keynum = e.which;
	  
	   if(me.tagName.toLowerCase()=='select') 
       			{
    	   		  if(keynum==46) 	 return del_myMultiTextJS(selector.id,myMultiTextJSElements[idHidden]);
    	   		  	else if(keynum==13){    
    	   		  						text.value=trim(myMultiTextJSElements[idHidden].onupdate(selector.options[selector.selectedIndex].text,selector.options[selector.selectedIndex].value)[0]);
    	   		  						text.focus();
    	   		  				 		}
    	   		  			else return true;
       			}
       
       if(me.type=='text') 
				{
    	   			if(keynum==13) add_myMultiTextJS(text.id,myMultiTextJSElements[idHidden]);
    	   					else return true;
    	   		}  
       
       try{
    	   if (e.cancelBubble)  e.cancelBubble = true;
	   		 		      else  e.stopPropagation();
			} catch (Exception) {}
		return false;
   }
   
   function del_myMultiTextJS(idTesto,idx){	    	
	    var sel=document.getElementById(idx.ids[0]);
	    if ( isUndefined(sel.selectedIndex) || 
	    		isUndefined(sel.options[sel.selectedIndex].text) || 
	         trim(sel.options[sel.selectedIndex].text)==''  || 
	    	 trim(sel.options[sel.selectedIndex].text)==null
	    	) {alert(idx.messaggi['assente']); return false;}
	    
		try {	var selected=idx.ondelete(sel.options[sel.selectedIndex].text);
				var j=sel.selectedIndex;
				for (var i=j;i<sel.options.length-1;i++) 
								{
								sel.options[i].text=sel.options[i+1].text;
								sel.options[i].value=sel.options[i+1].value;
								}
				sel.options[i].text='';
				sel.options[i].value='';
				myMultiTextJSrefresh(idx);
	    	} catch (Exception)  {return true;}
	    	return true;
		}
	    	
   function add_myMultiTextJS(idTesto,idx){	    	
	    var testo=document.getElementById(idTesto).value;
	    if(trim(testo)=='' || trim(testo)==null) return false;
	   
	    var sel=document.getElementById(idx.ids[0]);
	   
	    while ( sel.selectedIndex>0 &&  
	    		(isUndefined(sel.options[sel.selectedIndex-1].text) || 
	    		 trim(sel.options[sel.selectedIndex-1].text)==''  || 
	    		 trim(sel.options[sel.selectedIndex-1].text)==null)
	    	    ) sel.selectedIndex--;	
	    
		try {	var selected=idx.onupdate(testo);
				var libero=false;
				for (var i=0;i<sel.options.length;i++) {
						if(trim(sel.options[i].text)==selected[0]) {alert(idx.messaggi['inelenco']); return false;}
						if(sel.options[i].text==undefined || trim(sel.options[i].text)=='' || trim(sel.options[i].text)==null) libero=true;
					 }
			    if(!libero) {alert(idx.messaggi['pieno']); return false;}		
					
				for (var i=sel.options.length-1;i>sel.selectedIndex;i--) 
								{
								sel.options[i].text=sel.options[i-1].text;
								sel.options[i].value=sel.options[i-1].value;
								}
	    		sel.options[sel.selectedIndex].text=selected[0];
	    		sel.options[sel.selectedIndex].value=(selected[1]==null || isUndefined(selected[1])?selected[0]:selected[1]);
			} catch (Exception)  {return;}
	   
		for (var i=0;i<sel.options.length;i++) {
		  if(i!=sel.selectedIndex && sel.options[i].text==testo) sel.options[i].text='';
		}
		sel.selectedIndex++;
		document.getElementById(idTesto).value='';
		myMultiTextJSrefresh(idx);
	}

//PHP=>js Math===================================================================================
function abs(s){if(s==null) return s; else return Math.abs(s);}
function mt_rand(min,max){if(!min || !max) {max=1; min=0;}
					      return Math.round(Math.random()*(max-min))+min;
						 }
function rand(min,max)	{return mt_rand(min,max);}
function round(s,decimali)
						{if(s==null) return s;
						 if(!decimali) decimali=0;
						 return Math.round(s*Math.pow(10,decimali))/Math.pow(10,decimali);
						}
function min() {var min;
				if(arguments.length==0) return null;
				min=arguments[0];
				for(i=1;i<arguments.length;i++)
					if(min<arguments[i]) min=arguments[i];
				return min;
			    }
function max() {var max;
	            if(arguments.length==0) return null;
				max=arguments[0];
				for(i=1;i<arguments.length;i++)
					if(max>arguments[i]) max=arguments[i];
				return max;
			    }

//PHP=>js String===================================================================================
function strtolower(s){if(s==null || s==undefined) return s; else return s.toLowerCase();}
function strtoupper(s){if(s==null || s==undefined) return s; else return s.toUpperCase();}
function ucfirst(s)   {if(s==null || s==undefined) return s; else return strtoupper(substr(s,0,1))+substr(s,1,s.length);}
function strlen(s)    {if(s==null || s==undefined) return 0; else return s.length;}
function ucwords(s)   {if(s==null || s==undefined) return s;
						parti=s.split(' ');
	   					for(i=0;i<s.length;i++) if(parti[i]) parti[i]=ucfirst(parti[i]);
	   					return parti.join(' ');
					   }
function str_replace(cosa,con,dove){if(dove==null || dove==undefined || dove.length==0) return dove;
									while(true)
									 	{nuova=dove.replace(cosa,con);
									  	 if(dove==nuova) return dove;
									  	 dove=nuova;
									 	 }
									 }
function str_ireplace(cosa,con,dove){if(dove==null  || dove==undefined || cosa==null  || cosa==undefined || con==null  || con==undefined) return null;
									  cosa=eval('/'+cosa+'/i');
									  return str_replace(cosa,con,dove);
									}
function substr(s,from,ncar){if(s==null|| s==undefined) return s; else return s.substring(from, from+ncar);}
function strpos(s,cosa,offset){if(s==null|| s==undefined) return -1; else return s.indexOf(cosa, offset+0);}
function spad(s,car,lunghezza) {
	if(s==null) return s;
	s+='';
	while (s.length<lunghezza) s=car+s;
	return s;
}

function trim(s){if(s==null || s==undefined || s.length=='') return s;
				return s.replace(/^\s+|\s+$/g,"");
}



function explode(separatore,s,limit)
      {if(s==null || s==undefined) return null;
                  else {  if(!limit) return s.split(separatore);
                           return s.split(separatore,limit);
                        }
      }


function implode(separatore,s)
	{if(s==null|| s==undefined) return null;
            else return s.join(separatore);
	}

function preg_match(preg,s)
{if(s==null) return null;
            else {eval('preg='+preg+';');
            	  return (s.match(preg)!=null);
                   }
}


function in_array(cosa,dove){
	if(dove==null  || dove==undefined || cosa==null  || cosa==undefined) return null;
	for (var i=0;i<dove.length;i++)
		   if(dove[i]==cosa) return true;
	return false;
}

function is_array(cosa){
	if(cosa==null  || cosa==undefined) return null;
	if(cosa.constructor.toString().indexOf("Array") == -1) return false;
	   												 else  return true;
}

function array_push(dove,cosa){
	if(dove==null  || dove==undefined) return null;
	dove.push(cosa);
}

function array_shift(dove){
	if(dove==null  || dove==undefined) return null;
	return dove.shift();
}

function array_reverse(dove){
	if(dove==null  || dove==undefined) return null;
	return dove.reverse();
}

function array_pop(dove){
	if(dove==null  || dove==undefined) return null;
	return dove.pop();
}

function array_intercept(a,b){
	if(a==null || b==null || b==undefined|| a==undefined ) return null;
	var j=0;
	var out=new Array();
	for (var i=0; i<a.length; i++) if(in_array(a[i],b)) out[out.length]=a[i];
	return out;
}

function array_merge(a,b){
	if(a==null || a==undefined) return b;
	if(b==null || b==undefined) return a;
	var out=a;
	for (var i=0; i<b.length; i++) if(!in_array(b[i],a)) out[out.length]=b[i];
	return out;
}

function array_diff(a,b){
	if(a==null || a==undefined) return null;
	if(b==null || b==undefined) return a;
	var out=new Array();
	for (var i=0; i<a.length; i++) if(!in_array(a[i],b)) out[out.length]=a[i];
	if(out.length==0) return null;
	return out;
}



//==========================================================================================================

function format_data(v){
	v=str_replace('.','-',str_replace('/','-',v));
	if(v.length==0) return '';
	var d=v.split('-');
	if (d.length!=3) return null;
	for(var i=0;i<3;i++) d[i]=parseInt(d[i],10);
	if(d[2]>1000) {i=d[0];d[0]=d[2];d[2]=i;}
	if(d[0]<1000 || d[1]>12 || d[2]>31 || d[0]*d[1]*d[2]==0) return null;

	data=new Date();
	data.setFullYear((d[0]<1970?1970-d[0]:d[0]), d[1]-1, d[2]);
	if(data.getDate()!=d[2] || data.getMonth()!=d[1]-1) return null;

	for(i=0;i<3;i++) d[i]=spad(d[i],'0',2);
	return d.join('-');
}

function format_ora(v){
	if(v.length==0) return '';
	var d=str_replace('.',':',str_replace('-',':',v)).split(':');
	if (d.length<2 || d.length>3) return null;
	for(var i=0;i<d.length;i++) d[i]=parseInt(d[i]);
	if(v[1]>59 || (d.length==3 && v[2]>59)) return null;
	for(i=0;i<d.length;i++) d[i]=spad(d[i],'0',2);
	return d.join(':');
}


function myGetValueCampo(id,cast,funzione,altro) {
	var i=0;
	if(cast=='radio_hidden')
					  {var campi=document.getElementById(id+'_1').form.elements;
					  if(!campi) return null;
					   var trovato=false;
					   for (i=0;i<campi.length;i++)
					      if(campi[i].id.indexOf(id+'_')===0 && campi[i].value)
					      		{id=campi[i].id;
					      		 trovato=true;
					      		 break;
					      		}
					   if(!trovato) return null;
					  }

	if(cast=='radio') {var campi=document.getElementById(id+'_1').form.elements;
						if(!campi) return null;
					   var trovato=false;
					   for (i=0;i<campi.length;i++)
					      if(campi[i].id.indexOf(id+'_')===0 && campi[i].checked)
					      		{id=campi[i].id;
					      		 trovato=true;
					      		 break;
					      		}
					   if(!trovato) return null;
					  }

	if(cast=='select') {
						var campo=document.getElementById(id);
						if(!campo) return null;
						if (isUndefined(campo.selectedIndex) || campo.selectedIndex==null || isUndefined(campo.options[campo.selectedIndex])) return null;

						var trovato=false;
						return campo.options[campo.selectedIndex].value;
	  					}
	
	if(cast=='multicheck') {campi=document.getElementById(id+'_1').form.elements;
							if(!campi) return null;
	   						trovato=new Array();
	   						var j=0;
	   						for (i=0;i<campi.length;i++)
	   							if(campi[i].id.indexOf(id+'_')===0 && campi[i].checked)
	   								{
	   								trovato[j++]=campi[i].value;
	   								}
	   						if(j==0) return null;
	   						return trovato;
	  					  }

	if(!document.getElementById(id)) return null;
	if(document.getElementById(id).disabled) return null;
	if(funzione!=null && funzione()!=null && defaultDipendenze==0)
				{
				//meglio non fare niente in alcuni casi il campo potrebbe risultare obbligatorio durante costr JS ma poi non esserlo pi� e poi rimane il vincolo
				/* labels=document.getElementsByTagName('label');
				 for(i=0;i<labels.length;i++)
					   		if (labels[i].htmlFor==id)
					   				 {
					   					  alert('"'+labels[i].innerHTML+'" '+funzione());
					   					  break;
									 }
									 */
				throw 'Exception';

				}

	if(cast=='check') {
		if(document.getElementById(id).checked) {if(document.getElementById(id).value!='') 
																	 return document.getElementById(id).value;
																else return true;
												}
	   								       else return '';
	  }


	
	if(document.getElementById(id).value===null) v='';
											else v=''+document.getElementById(id).value;
	v=trim(v);

  	if(cast=='int')   return parseInt(v);
	if(cast=='float') return parseFloat(str_replace(altro,'.',v));
	if(cast=='date')  return format_data(v+'');
	if(cast=='hour')  return format_ora(v+'');
	return v;
}


function nascondi_campo(id,campo,stato){
	//campo.innerHTML+=campo.id.lastIndexOf(id)+'<>'+(campo.id.length-id.length);
	//alert(id+">"+campo.id+" Lastid="+campo.id.lastIndexOf(id));
	if(campo.id && campo.id.lastIndexOf(id)>=0 && campo.id.lastIndexOf(id)==campo.id.length-id.length)
		{
		 try {campo.disabled=stato;} catch(err){}
		 if(campo.tagName=='TR') campo.style.display=(stato==true?'none':'table-row');
		    else if(campo.tagName=='TD') campo.style.display=(stato==true?'none':'table-cell');
		      else if(campo.tagName=='TABLE') campo.style.display=(stato==true?'none':'table');
			  	 else {if(campo.tagName=='DIV') campo.style.display=(stato==true?'none':'block');
						                   else campo.style.display=(stato==true?'none':'inline');

					 }
		 return true;
		}
	return false;
}


function myFieldStatoCampi(campi,stato,modo,id){
 var campo;
 for (var i=0;i<campi.length;i++)
 	{
     try {
     	 //alert(campi[i]+' '+stato);
     	 campo=document.getElementById(campi[i]);
     	 campo.disabled=stato;
     	 if (campo.className=='hasDatepicker') campo.nextSibling.style.display=(stato==true?'none':'inline');
     	 if(campo.id.indexOf('indicatore_')==0) continue;
     	 if(campo.type=='file' && document.getElementById(campo.id+'_remove')) continue;
     } catch (Exception ){}
   
    try {
     	 if(stato && campo.required)   {myRequiredFields[campo.id]=true;
     	 	 						    campo.removeAttribute("required"); 
     	 	 						    }
     	 if(!stato && myRequiredFields[campo.id]) campo.required=true;	
     } catch (Exception ){}
     
     try{
	 if (modo=='hidden')
	 	 	{campo.style.display=(stato==true?'none':'inline');
    	 	         var parent=campo.parentNode;
    	 	         var tentativi=0;
			 while (parent && parent.tagName!='FORM' )
			 				 {if(!nascondi_campo(id,parent,stato)) tentativi++;
			 				 								  else tentativi=0;
			 				  parent=parent.parentNode;
			 				  }
			 }

     if(campo.className.indexOf('hidden')>=0)  campo.style.display='none';
									     else {
									     	 	if ((campi.length==1 && campo.innerHTML) || campo.src) campo.style.display=(stato==true?'none':'inline');
									     	  }
     } catch (Exception ){}
 	}
 }



	if(!Array.indexOf){
	  Array.prototype.indexOf = function(obj){ for(var i=0; i<this.length; i++) if(this[i]==obj)    return i;   
	    									   return -1;
	  										 }
	}
	
	
	myEventManager.start();
	

}
