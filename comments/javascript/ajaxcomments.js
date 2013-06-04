$(document).ready(function(){

//you need to first set the url the comment_ajax.php file - i had a globalurl set 
   var page= GLOBALURL+'content/plugins/comments/templates/comment_ajax.php'; 

  
    $('.commentajax').click(function(){
        var name= $('#username').text();
        var pic= $('#profile_avatar').find('.avatar').attr('src');
        
        //get values from form
           var comment = $('textarea#comment_content_0').val();
           var comment_process= $("#c_process").val();
           var comment_id=$('.comment:last').prev('a').attr("id");
           var comment_id= comment_id.substring(1);
           var comment_parent= $("#c_parent").val();
           var comment_post_id= $("#c_post_id").val();
           var comment_user_id= $("#c_user_id").val();

           //prevent submission
            event.preventDefault();
            
            //make ajax request
       request=   $.ajax({
           
          url: page,
          data:{text:comment,
                username: name,
                pic: pic,
                commentid: comment_id,
                process:comment_process,
                parent:comment_parent,
                post:comment_post_id,
                user: comment_user_id},
                type:'post',
          
            success:function(data){
              $('.clear:last').after(data);
             event.unbind('ajaxComplete');
          }
           
       });
              

    });
  
});
  