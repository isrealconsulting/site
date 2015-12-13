<?php

  $app_id = 'YOUR_APP_ID';
  $app_secret = 'YOUR_APP_SECRET';
  $app_url = 'http://YOURAPPURL';
  $fields = 'location';
  $verify_token = 'ICLLCUTflIt8oRFLuVHo6RFLjgclfurfguL';

  // Fetching an App Token
  $app_token_url = 'https://graph.facebook.com/oauth/access_token?client_id='
                   .$app_id.'&client_secret='.$app_secret
                   .'&grant_type=client_credentials';
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $app_token_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $res = curl_exec($ch);
  parse_str($res, $token);

  if (isset($token['access_token'])) {
    // Let's register a callback
    $params = array(
      'object'
        =>  'user',
      'fields'
        =>  $fields,
      'callback_url'
        // This is the endpoint that will be called when
        // a User updates the location field
        =>  $app_url . '/index.php?action=callback',
      'verify_token'
        =>  $verify_token,
    );

    curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/'
                                  .$app_id.'/subscriptions?access_token='
                                  .$token['access_token']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    $res = curl_exec($ch);
    if ($res && $res != 'null') {
      print_r($res);
    }

    // Fetch list of all callbacks
    curl_setopt($ch, CURLOPT_POST, 0);
    $res = curl_exec($ch);
  }
  if ($res && $res != 'null') {
    print_r($res);
  }
  curl_close($ch);

?>
