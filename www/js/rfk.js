$( function(){
	$("#loginbox").hide();
});

function showLoginBox(element){
	$(element).hide();
	$("#loginbox").show('slow');
}