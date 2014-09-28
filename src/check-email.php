<?php

require '/usr/local/share/opcyberpaint/inschrijving/class.php';
require '/usr/local/share/opcyberpaint/inschrijving/func_inschrijving.php';

date_default_timezone_set('Etc/UTC');

function display_success() {
    $raw = file_get_contents($GLOBALS['email_stage_3_body_success']);
    echo $raw;
}

function display_failure() {
    $raw = file_get_contents($GLOBALS['email_stage_3_body_failure']);
    echo $raw;
}


/* Only GET */
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    show_error();
    return;
}

/* Open DB */
$dbh = db_connect();
if ($dbh === NULL) {
    display_failure();
    http_response_code(500);
    return;
}


/* Get the authkey from the query string */
$authkey = trim($_GET['authkey']);
if (! filter_var($authkey, FILTER_SANITIZE_STRING)) {
    show_error();
    return;
}

/* Fetch player from authkey */
$player = retrieve_player_from_authkey($dbh, $authkey);
if ($player === NULL) {
    show_error();
    print "Unknown authkey";
}

/* Status change: STAGE_3_EMAIL_CONFIRMED */
update_player_status($dbh, $player, STAGE_3_EMAIL_CONFIRMED);

/* Send payment information */
if (!email_player_stage_3($dbh, $player)) {
    display_failure();
    http_response_code(500);
    return;
}

/* Status change: STAGE_4_PAYMENT_INFO_SEND */
update_player_status($dbh, $player, STAGE_4_PAYMENT_INFO_SEND);

display_success();

?>
