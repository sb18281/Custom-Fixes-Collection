
$(document).ready(function(){
  console.log("Overlay.js loaded ------- M&B");
  var gallery = $("body").find("#block-5b3c4adb2e7a74445e6e");

  var elements = gallery.find(".slide a").hover(function(){
    $(this).append('<div class="overlay"><img src="/s/overlay.gif"/></div>');
  }, function(){
    $(this).find(".overlay").remove();
  });

})
