<?php
// Facebook Real-Time Payment Update page

// ISREAL CONSULTING, LLC

// Processes Real-Time Payment Updates Notifications via Facebook

// App verify token for callback URL
$verify_token = 'ICLLCUTflIt8oRFLuVHo6RFLjgclfurfguL';

// Then, check mode and challenge and either return the hub_challenge or
// fill $obj with result of changes
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['hub_mode'])
    && $_GET['hub_mode'] == 'subscribe' && isset($_GET['hub_verify_token'])
    && $_GET['hub_verify_token'] == $verify_token) {
      
       // Only echo hub_challenge
       echo $_GET['hub_challenge']; exit;
  } 
else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_body = file_get_contents('php://input');
    $obj = json_decode($post_body, true);
    // $obj will contain the list of fields that have changed
  }
 
?>
