function myJQGetStyle(el,prop,asString) {
	  var cs = window.getComputedStyle(el.get(0),null);
	  if (prop) return cs.getPropertyValue(prop);
	  if(!asString) return cs;
	  var out='';
	  for (var i=0;i< cs.length;i++) 
				if(cs[i]!='display') out+=cs[i]+":"+cs.getPropertyValue(cs[i])+";";
	  return out;
}

function myJQCalcWidth(el,text){
	  var out=el.width();
	  if(!el.is(':visible')) 
				{	
				  if(!text) text=el.val();
				  var parent=el;
				  do{
					parent=parent.parent();
				   } while(!parent.is(':visible'))		
				  var id=('X'+Math.random()).replace('.','');
			      parent.append('<span id="'+id+'" style="'+myJQGetStyle(el,null,true)+';display:inline">'+text+'</span>');
	   			  out=$('#'+id).width();
				  $('#'+id).remove();
				}
	 	  	else
				  try {
				      if(!text) text=el.val();
					  el.before('<span style="'+myJQGetStyle(el,null,true)+'">'+text+'</span>');
				      out=el.prev().width();
					  el.prev().remove();
				  } catch (e){ }
  return out;
}



function myJQAutocomplete_accoda_parametri_ajax(campi,nomi){
	var par="",campo=null,nome=''; 
	var labels=document.getElementsByTagName('label');
	var labelOf={};
	for (var i=0;i<labels.length;i++) 
					if(labels[i].getAttribute('for')) 
							 labelOf[labels[i].getAttribute('for')]=labels[i].innerHTML;
	
	for (var i=0;i<campi.length;i++) 
	if(campi[i]) 
		try
		{campo=document.getElementById(campi[i]);
		
		 if(!campo.disabled){ 
		 	 nome=nomi[i];
			 if(campo.options)
					   par+='&'+nome+'[desc]='+campo.options[campo.selectedIndex].text+'&'+nome+'[val]='+campo.options[campo.selectedIndex].value;
				else {
					   if(campo.type!='checkbox' || campo.checked) par+='&'+nome+'[val]='+campo.value+'&'+nome+'[desc]='+labelOf[campi[i]];
					 }
			 }
		} catch (Exception) {}
		
	return par;
}

function FiltroJSBase(a){
	 return trim(a);
	}
	function trim(stringa){
		if(stringa===null || stringa==undefined) return '';
		while (stringa.substring(0,1) == ' '){
				stringa = stringa.substring(1, stringa.length);
	    }
	    while (stringa.substring(stringa.length-1, stringa.length) == ' '){
	    	stringa = stringa.substring(0,stringa.length-1);
	    }
	    return stringa;
	}
	function TipiDato(valore){
		var espressione = /^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/;
		if (espressione.test(valore)) return 'date';
		else return 'generico';
	}
	function dateIt(a, b) {
		dataA=a.text;
		dataB=b.text;
		sa = dataA.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/, "$3/$2/$1");
		sb = dataB.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/, "$3/$2/$1");
		return sa == sb ? 0 : sa < sb ? -1 : 1;
	}
	function text(a, b) {
		return a.text == b.text ? 0 : a.text < b.text ? -1 : 1;
	}
	function numeric(a,b){
		 return a.text - b.text;
		
	}
	


(function($) {
		$.fn.serializefiles = function() {
		    var obj = $(this);
		    var formData = new FormData();
		    $.each($(obj).find("input[type='file']"), function(i, tag) {
		        $.each($(tag)[0].files, function(i, file) {  formData.append(tag.name, file); });
		    });
		    var params = $(obj).serializeArray();
		    $.each(params, function (i, val) {formData.append(val.name, val.value);});
		    return formData;
		};
})(jQuery);
			
	
//Retrocompatibilita 1.9
if(!jQuery.browser){
;
		jQuery.browser = {};
		jQuery.browser.mozilla = false;
		jQuery.browser.webkit = false;
		jQuery.browser.opera = false;
		jQuery.browser.safari = false;
		jQuery.browser.chrome = false;
		jQuery.browser.msie = false;
		jQuery.browser.android = false;
		jQuery.browser.blackberry = false;
		jQuery.browser.ios = false;
		jQuery.browser.operaMobile = false;
		jQuery.browser.windowsMobile = false;
		jQuery.browser.mobile = false;

		var nAgt = navigator.userAgent;
		jQuery.browser.ua = nAgt;

		jQuery.browser.name  = navigator.appName;
		jQuery.browser.fullVersion  = ''+parseFloat(navigator.appVersion);
		jQuery.browser.majorVersion = parseInt(navigator.appVersion,10);
		var nameOffset,verOffset,ix;

	// In Opera, the true version is after "Opera" or after "Version"
		if ((verOffset=nAgt.indexOf("Opera"))!=-1) {
			jQuery.browser.opera = true;
			jQuery.browser.name = "Opera";
			jQuery.browser.fullVersion = nAgt.substring(verOffset+6);
			if ((verOffset=nAgt.indexOf("Version"))!=-1)
				jQuery.browser.fullVersion = nAgt.substring(verOffset+8);
		}

	// In MSIE < 11, the true version is after "MSIE" in userAgent
		else if ( (verOffset=nAgt.indexOf("MSIE"))!=-1) {
			jQuery.browser.msie = true;
			jQuery.browser.name = "Microsoft Internet Explorer";
			jQuery.browser.fullVersion = nAgt.substring(verOffset+5);
		}

	// In TRIDENT (IE11) => 11, the true version is after "rv:" in userAgent
		else if (nAgt.indexOf("Trident")!=-1 ) {
			jQuery.browser.msie = true;
			jQuery.browser.name = "Microsoft Internet Explorer";
			var start = nAgt.indexOf("rv:")+3;
			var end = start+4;
			jQuery.browser.fullVersion = nAgt.substring(start,end);
		}

	// In Chrome, the true version is after "Chrome"
		else if ((verOffset=nAgt.indexOf("Chrome"))!=-1) {
			jQuery.browser.webkit = true;
			jQuery.browser.chrome = true;
			jQuery.browser.name = "Chrome";
			jQuery.browser.fullVersion = nAgt.substring(verOffset+7);
		}
	// In Safari, the true version is after "Safari" or after "Version"
		else if ((verOffset=nAgt.indexOf("Safari"))!=-1) {
			jQuery.browser.webkit = true;
			jQuery.browser.safari = true;
			jQuery.browser.name = "Safari";
			jQuery.browser.fullVersion = nAgt.substring(verOffset+7);
			if ((verOffset=nAgt.indexOf("Version"))!=-1)
				jQuery.browser.fullVersion = nAgt.substring(verOffset+8);
		}
	// In Safari, the true version is after "Safari" or after "Version"
		else if ((verOffset=nAgt.indexOf("AppleWebkit"))!=-1) {
			jQuery.browser.webkit = true;
			jQuery.browser.name = "Safari";
			jQuery.browser.fullVersion = nAgt.substring(verOffset+7);
			if ((verOffset=nAgt.indexOf("Version"))!=-1)
				jQuery.browser.fullVersion = nAgt.substring(verOffset+8);
		}
	// In Firefox, the true version is after "Firefox"
		else if ((verOffset=nAgt.indexOf("Firefox"))!=-1) {
			jQuery.browser.mozilla = true;
			jQuery.browser.name = "Firefox";
			jQuery.browser.fullVersion = nAgt.substring(verOffset+8);
		}
	// In most other browsers, "name/version" is at the end of userAgent
		else if ( (nameOffset=nAgt.lastIndexOf(' ')+1) < (verOffset=nAgt.lastIndexOf('/')) ){
			jQuery.browser.name = nAgt.substring(nameOffset,verOffset);
			jQuery.browser.fullVersion = nAgt.substring(verOffset+1);
			if (jQuery.browser.name.toLowerCase()==jQuery.browser.name.toUpperCase()) {
				jQuery.browser.name = navigator.appName;
			}
		}

		/*Check all mobile environments*/
		jQuery.browser.android = (/Android/i).test(nAgt);
		jQuery.browser.blackberry = (/BlackBerry/i).test(nAgt);
		jQuery.browser.ios = (/iPhone|iPad|iPod/i).test(nAgt);
		jQuery.browser.operaMobile = (/Opera Mini/i).test(nAgt);
		jQuery.browser.windowsMobile = (/IEMobile/i).test(nAgt);
		jQuery.browser.mobile = jQuery.browser.android || jQuery.browser.blackberry || jQuery.browser.ios || jQuery.browser.windowsMobile || jQuery.browser.operaMobile;


	// trim the fullVersion string at semicolon/space if present
		if ((ix=jQuery.browser.fullVersion.indexOf(";"))!=-1)
			jQuery.browser.fullVersion=jQuery.browser.fullVersion.substring(0,ix);
		if ((ix=jQuery.browser.fullVersion.indexOf(" "))!=-1)
			jQuery.browser.fullVersion=jQuery.browser.fullVersion.substring(0,ix);

		jQuery.browser.majorVersion = parseInt(''+jQuery.browser.fullVersion,10);
		if (isNaN(jQuery.browser.majorVersion)) {
			jQuery.browser.fullVersion  = ''+parseFloat(navigator.appVersion);
			jQuery.browser.majorVersion = parseInt(navigator.appVersion,10);
		}
		jQuery.browser.version = jQuery.browser.majorVersion;
	}
	
