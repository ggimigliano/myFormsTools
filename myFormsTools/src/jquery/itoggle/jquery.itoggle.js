(function($){  
	 $.fn.itoggle = function(options) {  
		 var defaults = {  
					/*
					 * Spostamento verso sx dell'immagine
					 */
					spostamentoSx:"-53px"
					
			};  
	 
		 var options = $.extend(defaults, options); 
		 var classe=new Date().getTime();
		 var tipo=$(this).attr('type');
		 var spostamento=options.spostamentoSx;
		
	    this.each(function() {  
	    //qui metteremo il codice da eseguire
	    	 $(this).before("<div class='itoggle " + classe + "'></div>")
			 $(this).css('display','none');
	    	 
	    	 startcheckios();
	       
	    });
	    function startcheckios(){
	    	  $(':' + tipo).each(function(){
	    		if ($(this).is(':checked'))  $(this).prev().css( {backgroundPosition: "0px 0px"} );
	    			else $(this).prev().css( {backgroundPosition: spostamento + " 0px"} );
	    		})
	    	         
	    	 }
	    
	    $('.' + classe).click(function(){
	    	var idCliccato= ($(this).next().attr('id'));
	   	    if ($(this).next().attr('type')=='checkbox'){
	   	    	if($('#' + idCliccato).is(':checked')){
	   	            $(this).css( {backgroundPosition: spostamento + " 0px"} )
	   	            $('#' + idCliccato).attr('checked', false);
	   	         }else{
	   	            $(this).css( {backgroundPosition: "0px 0px"} );
	   	            $('#' + idCliccato).attr('checked', true);
	   	         }
	         }
	          else{
	        	  if($('#' + idCliccato).is(':checked')){
	             		$(this).css( {backgroundPosition: spostamento + " 0px"} )
	   	            	$('#' + idCliccato).attr('checked', false);
	   	         }else{
	   	         		$('.' + classe).css( {backgroundPosition: spostamento + " 0px"} )
	   	         		$(':radio').attr('checked', false);
	   	            	$(this).css( {backgroundPosition: "0px 0px"} );
	   	            	$('#' + idCliccato).attr('checked', true);
	   	         	}
	             }
	         })
	 };  
	})(jQuery);