<?php
// PHP Page that Web Orders get Posted to for processing

// ISREAL CONSULTING, LLC - Copyright (c) 2015.  All rights reserved.

$mandrill_json = $_POST['mandrill_events'];
print_r(json_decode(stripslashes($mandrill_json),true));



?>

