function loadDialog(url, show){
    $('#dialog').html('<center><img src="img/spinner.gif"/></center>')
    .load(url);
    
    if(show){
		$('#dialog').dialog({
		    height: 400,
		    width: 600,
		    modal: true
		});
    }
}