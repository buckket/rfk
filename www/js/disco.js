$( function(){
	$("#disco").hover(
	  function () {
	    $("#disco #minmax").show('fast');
	    $("#disco #hide").show('fast');
	  }, 
	  function () {
		  $("#disco #minmax").hide('fast');
		  $("#disco #hide").hide('fast');
	  }
	);
	
	$("#disco #minmax").click(function(){
		$("#disco #dancearea").slideToggle('slow');
	});
	$("#disco #hide").click(function(){
		$("#disco").slideToggle('slow',function(){
			$("#discohidden").toggle('fast');
		});
	});
	$("#discohidden").click(function(){
		$("#discohidden").slideToggle('fast',function(){
			$("#disco").toggle('slow');
		});
	});
});