<?php



//hotaru's GET/POST function is not used here -this POST data retrieval is ONLY
//used for ajax purposes! it is the quickest method as you don't need to
//construct the new hotaru object first, which may take a while, so is done completely
//in the background.

$url= "http://localhost/Yoursitename/"; //this should be your hotaru SITENAME
//problem- can't get it cos $h object not made until later...


if (isset($_POST["text"]) && !empty($_POST["text"])) {
   $display_comment=$_POST["text"];
   $pid=$_POST["post"];
    $name= $_POST["username"]; 
    $pic= $_POST["pic"];
    $comment_id= $_POST["commentid"];
    //add one to post id server side
    function inc($matches) {
        return ++$matches[1];
}
    $commentid= preg_replace_callback( "|(\d+)|", "inc", $comment_id);

	//below, this is just formatting to make the comment look the same as other comments..
?>

<div class="clear"></div>
  <a id="c<?php echo $commentid ?>"></a>
    <div class="comment" style="margin-left:0em;">
        
        <div class="clear"></div>
         <div class="comment_main">
            <div class="comment_content">
                
                <img class="avatar" src="<?php echo $pic;?>" style="height:16px; width:16px; margin-left:-2px"/>
                &nbsp;
                 <a style="font-weight: bolder; font-size: 14px; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; color: #0088cc; line-height: 20px; margin-left: -3px;" ><?php echo $name;?></a>     
           &nbsp;  <?php
              echo $display_comment;
                ?>
           <br>
               <div class="comment_author" style="margin-top:10px;">
                   <span style="font-size:11px; margin-left:-5px;">a few seconds ago.</span>
                   <span style="float:right; margin-right:27px;"><i class='icon-check-sign'></i> 
                     
                   </span>

               </div>
            </div>

          
        </div>


            
    </div>
    
<?php }

    //NOW create a new Hotaru object
    require_once('../../../../hotaru_settings.php');
    require_once('../../../../Hotaru.php');    // Not the cleanest way of getting to the root...
    $h = new Hotaru();
    
    //Run checks on the user and the post data, to safely store in database
    checkThenSendPostData($h);
    
/*
 * checkThenSendPostData
 * 
 * sanitizes data so it is safe to store
 * then finally makes a call to storeAndDisplayData store the data
 * 
 * @param $h
 *
 */       
        function checkThenSendPostData($h){
        require_once(LIBS . 'Comment.php');
        $h->comment = new Comment();
        
        // Get settings from database if they exist...
        $comments_settings = $h->getSerializedSettings();
    
        // Assign settings to class member
        $h->comment->avatars = $comments_settings['comment_avatars'];
        $h->comment->avatarSize = $comments_settings['comment_avatar_size'];
        $h->comment->voting = $comments_settings['comment_voting'];
        $h->comment->email = $comments_settings['comment_email'];
        $h->comment->allowableTags = $comments_settings['comment_allowable_tags'];
        $h->comment->levels = $comments_settings['comment_levels'];
        $h->comment->setPending = $comments_settings['comment_set_pending'];
        $h->comment->allForms = $comments_settings['comment_all_forms'];
        $h->vars['comment_hide'] = $comments_settings['comment_hide'];
        
		// return false if not posting or editing a comment
        if (($h->cage->post->getAlpha('process') != 'newcomment') && 
            ($h->cage->post->getAlpha('process') != 'editcomment')) { return false; }

		// start filling the comment object
        if ($h->cage->post->keyExists('text')) {
      
            //for displaying
          //  $display_comment= $h->cage->post->testRegex('text', '/[^A-Za-z0-9_ -]/'); 
            //to store in database
            $h->comment->content = sanitize($h->cage->post->getHtmLawed('text'), 'tags', $h->comment->allowableTags);
        }
        
        if ($h->cage->post->keyExists('post')) {
            //to store in database
            $h->comment->postId = $h->cage->post->testInt('post');
        }

        if ($h->cage->post->keyExists('user')) {
             //to store in database
            $h->comment->author = $h->cage->post->testInt('user');
        }
    
      
          //store data
          commentLogger($h);    
    }
    
    

    
    
    
/**
 * commentLogger
 * 
 * -sends the data to the database
 * functionality has been copied from comments.php, but excludes a couple things
 * which can be seen where they are commented out in the function
 * -so use at your own risk
 * 
 * @param $h
 * @param $comments_settings
 */    
    
    function commentLogger($h)
    {
        // Get comment settings from database if they exist
   $comments_settings = $h->getSerializedSettings();
        
        
     //create a new comment object
    require_once('../comments.php');
    $commentobj= new Comments();
        

    if ($h->cage->post->getAlpha('process') == 'newcomment')
    {
         

//// could not get this part working
//
//  before posting, we need to be certain this user has permission:
//    }
//            $safe = false;
//            $can_comment = $h->currentUser->getPermission('can_comment');
//            if ($can_comment == 'yes') { $safe = true; }
//            if ($can_comment == 'mod') { $safe = true; $h->comment->status = 'pending'; }
//            
//            $result = array(); // holds results from addComment function
//            
//            // Okay, safe to add the comment...
//            if ($safe) {

        
// A user can unsubscribe by submitting an empty comment, so...
    if ($h->comment->content != '')
    {
        // if Hotaru is older than 1.4.1, don't use preAddComment because it's part of AddComment
        if (version_compare($h->version, '1.4.1') < 0)
       {
          $result = $h->comment->addComment($h);
        } else {

         $result = $commentobj->preAddComment($h); // used for setting comment status
           $h->comment->addComment($h);
       }

       // notify chosen mods of new comment by email if enabled and UserFunctions file exists
       if (($comments_settings['comment_email_notify']) && (file_exists(PLUGINS . 'users/libs/UserFunctions.php')))
       {
           require_once(PLUGINS . 'users/libs/UserFunctions.php');
           $uf = new UserFunctions();
           $uf->notifyMods($h, 'comment', $h->comment->status, $h->comment->postId, $h->comment->id);
       }

//could not get this working on my localhost
//
//                  // email comment subscribers if this comment has 'approved' status:
//                    if ($h->comment->status == 'approved') {
//                        $this->emailCommentSubscribers($h, $h->comment->postId);
//                    }

            } else {
                //comment empty so just check subscribe box:
                $h->comment->updateSubscribe($h, $h->comment->postId);
                $h->messages[$h->lang['comment_moderation_unsubscribed']] = 'green';
            }


        if (isset($result['exceeded_daily_limit']) && $result['exceeded_daily_limit']) {
            $h->messages[$h->lang['comment_moderation_exceeded_daily_limit']] = 'green';
        } elseif (isset($result['exceeded_url_limit']) && $result['exceeded_url_limit']) {
            $h->messages[$h->lang['comment_moderation_exceeded_url_limit']] = 'green';
        } elseif (isset($result['not_enough_comments']) && $result['not_enough_comments']) {
            $h->messages[$h->lang['comment_moderation_not_enough_comments']] = 'green';
        }
    }

    }
    
    
    
?>
