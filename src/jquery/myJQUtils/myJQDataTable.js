if(myJQDataTable==undefined) 
var myJQDataTable={
    DataIt:false,
    Inizializzata:false,
    Data:null,
    Numerico:false,
    FunzioneFiltroSelect:null,
    FunzioneOrdineSelect:null,
    FunzioneDescrizioneSelect:function(v){return v},
	FunzioneRicalcolaSelect:function(v){return v},
    
    formattaDataIt:function(a){
    	a = a.split(/[^0-9]+/);	
    	while (a.length<6) a[a.length]='0';
    	for(var i=0;i<a.length;i++) while (a[i].length<2) a[i]='0'+a[i];
    	var tmp=a[0];a[0]=a[2];a[2]=tmp;
    	return a.join('');
    },
    
    confrontaD:function(a, b) {
    			return this.formattaDataIt(a).localeCompare(this.formattaDataIt(b));
		},
		

     CreateSelect:function( jqtab,colonna ,id,classe,$){
    	var aData=this.FiltroSelect(jqtab,colonna,$);
    	if(iLen=aData.length<=1) return false;
		var r='<select  id="'+id+'" class="'+classe+'"'+(this.Numerico?' style="text-align:right"':'')+'><option value=".*"></option>', i, iLen=aData.length;
		for ( i=0 ; i<iLen ; i++ )	r += '<option value="'+aData[i]+'">'+this.FunzioneDescrizioneSelect(aData[i])+'</option>';
		return r+'</select>';
	},
	
	
	
	FiltroSelect:function (jqtab,iColumn,$) 
	{
		var asResultData = new Array();
		var chiavi={};
		var self=this;
		this.Numerico=true;
		/*
		if(self.Data==null || !self.Data[0][iColumn]) 
						{var i,j=0;
							 self.Data=[];
							 jqtab.find("tbody tr").each(function(riga){
														 self.Data[riga]=new Array();	
														 $(this).find('td').each(
																 				function(col){
																 							  self.Data[riga][col]=$(this).text();
														 								  }
																 				)	
														});	
							}
		*/
		
		jqtab.find("tbody tr td:nth-child("+(iColumn+1)+")[headers^='colonna_']").each(
				function(riga){	
						//console.log($(this).text().trim());
						var sValue=self.FunzioneRicalcolaSelect($(this).text().trim());
		            	if (sValue.trim().length == 0 || chiavi[sValue]) return;
									else {
										  if(self.FunzioneFiltroSelect!=null && !self.FunzioneFiltroSelect(sValue)) return;
										  asResultData.push(sValue);
										  chiavi[sValue]=true;
										  if(self.Numerico) self.Numerico=/^[\+\-]{0,1}[0-9]*[\,\.]{0,1}[0-9]*$/.test(sValue);
										 }
					}
				);
		if(self.FunzioneOrdineSelect!=null) return asResultData.sort(self.FunzioneOrdineSelect);
			else {
				if(self.Numerico==true) return asResultData.sort(function(a,b) {return (parseFloat(a.replace(',','.'))<parseFloat(b.replace(',','.'))?-1:1);});								
				else {
	                 if (self.DataIt!=false) return asResultData.sort(function(a,b){return self.confrontaD(a,b);});
	                 				   else  return asResultData.sort(function(a,b){return a.toLowerCase()<b.toLowerCase()?-1:1;});
				  	 }
				}
	}
}	