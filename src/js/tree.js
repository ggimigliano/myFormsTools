var nodes                = new Array();
var openNodes       	 = new Array();
var icons                = new Array();

function oc(Id,node, bottom) {
	var theDiv  = document.getElementById("div"  +Id+"_"+ node);
      var theJoin = document.getElementById("join" +Id+"_"+ node);
      var theIcon = document.getElementById("icon" +Id+"_"+ node);
	if( openNodes[Id][node]== 0) {
           if (node==1)  theJoin.src = icons[Id][7].src;
                	else{ 
				if (bottom==1) theJoin.src = icons[Id][3].src;
               		 	    else theJoin.src = icons[Id][2].src;
			    }
                theIcon.src = icons[Id][5].src;
                theDiv.style.display = 'inline';
		    openNodes[Id][node]=1;
        	} 
           else {//chiude
		if (node==1)  theJoin.src = icons[Id][6].src;
                	else{ if (bottom==1) theJoin.src = icons[Id][1].src;
               		  	    else theJoin.src = icons[Id][0].src;
			    }  
	        theIcon.src = icons[Id][4].src;
		  theDiv.style.display = 'none';
		  openNodes[Id][node]=0;
        	}
}


/*

var campiMyTree=new Array();
var hrefMyTree=new Array();
var campiMyTreeScelti=new Array();
var JSMyTreeScelti=new Array();
var readonlyMyTreeScelti=new Array();
function preparaParametri(Id,a,b) {
var start=b;
var valoriNodo;
var percorso=new Array();
do {
	valoriNodo = (nodes[Id][b-1]);
	b=valoriNodo[1];
	percorso[percorso.length]=valoriNodo[2];
   } while (valoriNodo[1]!=0);
eval(JSMyTreeScelti[Id]+"(a,percorso);");
}


function PremiInvio(id) {
 for(i=0;i<document.forms.length;i++)
	for(j=0;j<document.forms[i].elements.length;j++)
			if (id==document.forms[i].elements[j].id) 
						{
						document.forms[i].submit();
						return false;
						}
}

function percorso(Id,node){
	var valoriNodo = nodes[Id][node];
	var prox=valoriNodo [1]-1;
	if (prox>=0) {if (prox==0) oc(Id,valoriNodo [1],0); 
			          else oc(Id,valoriNodo [1],1); 
		        percorso(Id,prox);
		       }
}
*/





