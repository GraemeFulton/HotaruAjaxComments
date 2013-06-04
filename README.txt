This is just an attempt at adding ajax to the hotaru comment system. 

Main additions

 comment_ajax.php
---------------------
 - this file handles the ajax requests from the jquery ajax calls. 
 - it is pretty well commented so have a read to see what's going on there
 
 
 ajaxcomments.js
 ----------------
 
 this grabs data from the form and sends it to the php file. It gets the comment text and the user name etc
 just from what is displayed on the screen (by the id)