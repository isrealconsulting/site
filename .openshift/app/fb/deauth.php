<?php
// Facebook DEAUTH page

// ISREAL CONSULTING, LLC

// Processes when a user deauthorizes our app and performs actions

// Check that we're using SSL
if (empty($_SERVER['HTTPS'])) {
    header('Location: https://www.isrealconsulting.com/app/fb/deauth.php');
    exit;
}

// FB Dev
$signed_request = $_REQUEST['signed_request'];
function base64_url_decode($input) {
    return base64_decode(strtr($input, '-_', '+/'));
}
list($encoded_sig, $payload) = explode('.', $signed_request, 2);
// decode the data
$sig = base64_url_decode($encoded_sig); // Use this to make sure the signature is correct
$data = json_decode(base64_url_decode($payload), true);
$user_id = $data['user_id'];

// Make a call to the API to get the user_id
// then perform actions on the specific user_id
?>
