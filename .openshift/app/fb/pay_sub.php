<?php
// Facebook Real-Time Payment Update page

// ISREAL CONSULTING, LLC

// Processes Real-Time Subscription Updates Notifications via Facebook

// App verify token for callback URL
$verify_token = 'ICLLCJKgpTgkHFo0rPTGPOUygVHJP8p';

// Then, check mode and challenge and fill $obj with result of changes
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['hub_mode'])
    && $_GET['hub_mode'] == 'subscribe' && isset($_GET['hub_verify_token'])
    && $_GET['hub_verify_token'] == $verify_token) {
      echo $_GET['hub_challenge'];
  } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_body = file_get_contents('php://input');
    $obj = json_decode($post_body, true);
    // $obj will contain the list of fields that have changed
  }
 
?>
