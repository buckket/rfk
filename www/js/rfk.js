var addingshow = false;
function addShow(){
	if(!addingshow){
		$("#addshowlink").hide('fast');
		$("#addshow").show('fast');
		$(".free").addClass("free-selectable");
		$(".free").click(function(){
			selectFree(this);
		});
	}	
}

function clickFree(){

}
function abortAddShow(){
	if(addingshow){
		$("#addshowlink").show('fast');
		$("#addshow").hide('fast');
	}
}

function selectFree(element){
	$(element).addClass('freetime-start').addClass('freetime-end');
}

function addFreeShowEffects(){
	

}

function removeFreeShowEffects(){


}