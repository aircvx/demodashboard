
$(function(){
    $(".iCheck-helper,[for=video]").click(function(){
        
       if($("[name=about_type]:checked").val()=="mp4"){
           $("#show_img").hide();
           $("#show_video").show();
       }else{
           $("#show_img").show();
           $("#show_video").hide();
       }
    })
})
