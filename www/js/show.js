$( function(){
    $('.planned,.unplanned,.mixed')
    .hover(function(){JT_show(webroot+'api/site/show.php?id='+$(this).attr('shows'),this.id,lshow)},function(){$('#JT').remove()})
    .click(function(){
        loadDialog(webroot+'show.php?ajax=true&show='+$(this).attr('shows'),true);
    });
    ;
});

function editShow(showid){
        if($('#dialog').dialog("isOpen")){
                $('#dialog').dialog("open");
        }
        loadDialog(webroot+'show.php?action=edit&ajax=true&show='+showid,false);
}

var addingshow = false;
var newshow = new Array();
var counter = 0;


function addShow() {
    if(addingshow)
        return;
    startFreeListener();
   $('#addshow').fadeIn();
    addingshow = true;
}

function abortAddShow() {
    newshow = new Array();
    stopFreeListener();
    clearFreeClassNames();
    addingshow = false;
    $('#addshow').fadeOut();
    window.location.reload();
}

function commitAddShow(){
    if(!checkNewShow()){
        return;
    }
    $.post(webroot+'api/site/show.php?w=add&cw='+currweek,
                  'name='+encodeURIComponent($('#showname').val())+
                  '&thread='+encodeURIComponent($('#showthread').val())+
              '&description='+encodeURIComponent($('#showdescription').val())+
              '&length='+newshow.length+
              '&start='+getStart(),
              function(data) {
                  var obj = data;
                  if(obj.error){
                      alert(obj.error[0]['errid']+": "+obj.error[0]['desc']);
                  }else if(obj.ok){
                      newshow = new Array();
                      clearFreeClassNames();
                      addingshow = false;
                      alert(lsavesuccess);
                      window.location.reload();
                  }
              }
              ,"json");
}

function getStart() {
    return parseInt(newshow[0].id.substring(1), 10);
}

function clearFreeClassNames() {
    $('td.free').each(function(element){
        clearClassNames(element);
    });
   }

 function startFreeListener() {
  $('td.free').bind('click', startAddShow)
 }
 
 function stopFreeListener() {
  $('td.free').unbind('click')
 }

 function checkNewShow(){
    if(newshow.length == 0){
        alert(lnodatafield);
        return false;
    }
    return true;
 }
 function startAddShow(event) {
   var element = event.target;
   newshow.push(element);
   stopFreeListener();
   refreshNewShow();
 }
 
 function refreshNewShow(){
  for (var index = 0; index < newshow.length; ++index) {
    var element = newshow[index];
    clearClassNames(element);
    if(index == 0){
        $(element).addClass('freetime-start');
        $('#showbegin').html(slotToTime(element,false));
    }
    if (index == newshow.length -1){
        $(element).addClass('freetime-end');
        $('#showend').html(slotToTime(element,true));
    }
    $(element).addClass('freetime-mid');
  }
 }
 
 function clearClassNames(element) {
  $(element).removeClass('freetime-start');
  $(element).removeClass('freetime-mid');
  $(element).removeClass('freetime-end');
 }

 function slotToTime(element,end){
    var id
    if(end){
        id = parseInt(element.id.substring(1), 10)+1;
    }else{
        id = parseInt(element.id.substring(1), 10); 
    }
    var id = id%100;
    var hour = Math.floor(id/2);
    var minutes = (id%2==0)?'00':'30'; 
    return hour+":"+minutes
 }
 
 function removeSlot(first) {
  var element;
  if(first){
   element = newshow.shift();
  }else{
   element = newshow.pop();
  }
  clearClassNames(element);
 }
 
 function isFree(id){
  if($('#'+id).hasClass('free')){
   return true;
  }
  return false;
 }
 
 function getNextId(element) {
  var nid = parseInt(element.id.substring(1), 10)+1;
  if(nid > 647){
   alert(lnospanning);
   return;
  }
  if(nid%100>47){
   if(nid >= 100){
    nid = (Math.floor(nid/100)+1)*100;
   }else {
    nid = 100;
   }
  }
  if(nid < 100){
        nid = dezInt(nid,3)
  }
  return 't'+nid;
 }
 
 function getPrevId(element) {
  var nid = parseInt(element.id.substring(1), 10)-1;
  if(nid < 0){
   alert(lnospanning);
   return;
  }
  if(nid%100>47){
   if(nid >= 100){
    nid = ((Math.floor(nid/100))*100)+47;
   }else {
    nid = 47;
   }
  }
  if(nid < 100){
   nid = dezInt(nid,3);
  }
  return 't'+nid;
 }
 
 function newShowPlus() {
     if(!checkNewShow()){
         return;
     }
  var nid = getNextId(newshow[newshow.length-1]);
  if(!nid){
   return;
  }
  if(isFree(nid)){
   newshow.push($('#'+nid)[0]);
   refreshNewShow();
  }else {
   alert(lerrwrongtime);
  }
 }
 function newShowMinus() {
     if(!checkNewShow()){
         return;
     }
  if(newshow.length > 1){
   removeSlot(false);
   refreshNewShow();
  }
 }
 
 function newShowUp() {
     if(!checkNewShow()){
         return;
     }
  var pid = getPrevId(newshow[0]);
  if($('#'+pid)){
   if(isFree(pid)){
    newshow.unshift($('#'+pid)[0]);
    removeSlot(false);
    refreshNewShow();
   }
  }
 }
 
 function newShowDown() {
     if(!checkNewShow()){
         return;
     }
  var nid = getNextId(newshow[newshow.length-1]);
  if(nid){
   if(isFree(nid)){
    newshow.push($('#'+nid)[0]);
    removeSlot(true);
    refreshNewShow();
   }
  }
 }
 function dezInt(num,size,prefix) {
   prefix=(prefix)?prefix:"0";
   var minus=(num<0)?"-":"",
   result=(prefix=="0")?minus:"";
   num=Math.abs(parseInt(num,10));
   size-=(""+num).length;
   for(var i=1;i<=size;i++) {
    result+=""+prefix;
   }
   result+=((prefix!="0")?minus:"")+num;
   return result;
 }