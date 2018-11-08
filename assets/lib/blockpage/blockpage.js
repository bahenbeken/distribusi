function blockPageinit(){
  $("body").append( "<div class='blockpage'><div class='spinner'></div></div>");
}
function blockPage(){
  $(".blockpage").show();
  $(".blockpage").animate({
    opacity:1
  }, 1000);
}
function unblockPage(){
  $(".blockpage").animate({
    opacity:0
  }, 500);

  setTimeout(function(){
    $(".blockpage").hide();
  }, 500);
}
