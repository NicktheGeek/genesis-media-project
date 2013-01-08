jQuery(document).ready(function($) {
    

    $('ul.tabs li').live('click', function(){ 
		
		var mainContent = $("#gmp-tab"),
		link = $(this).find("a").attr("href"),
		ID = link.replace(/#gmp-video-/g,"");
		
		mainContent.animate({opacity: "0.1"}).html('<div id="circularG"><div id="circularG_1" class="circularG"></div><div id="circularG_2" class="circularG"></div><div id="circularG_3" class="circularG"></div><div id="circularG_4" class="circularG"></div><div id="circularG_5" class="circularG"></div><div id="circularG_6" class="circularG"></div><div id="circularG_7" class="circularG"></div><div id="circularG_8" class="circularG"></div></div></div>');
		
		jQuery.post(
		
		gmpTabAjax.ajaxurl,
		
		{
		  action: 'gmpAjaxVideoTab',  
		  data: ID
		},
		
		function(response){
		    
			mainContent.html(response).animate({opacity: "1"}); 
			
		  }
		  

	);
	
	return false;
	
	});
});

