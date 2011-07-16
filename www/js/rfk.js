$( function(){
	$("#loginbox").hide();
	$('#mask').click(function () {
		$(this).hide();
		$('.window').hide();
	});
});

function showLoginBox() {
	//Get the screen height and width
	var maskHeight = $(document).height();
	var maskWidth = $(window).width();
	$('#mask').css({'width':maskWidth,'height':maskHeight});	
	$('#mask').fadeTo("slow",0.8);
	
	var winH = $(window).height();
	var winW = $(window).width();
    var id= '#logindialog';
	$(id).css('top',  winH/2-$(id).height()/2);
	$(id).css('left', winW/2-$(id).width()/2);
	$(id).fadeIn('fast');
}