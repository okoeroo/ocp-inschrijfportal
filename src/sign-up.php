<?php

require '/usr/local/share/opcyberpaint/inschrijving/class.php';
require '/usr/local/share/opcyberpaint/inschrijving/func_inschrijving.php';


date_default_timezone_set('Etc/UTC');

function display_success() {
    $raw = file_get_contents($GLOBALS['email_stage_1_body_success']);
    echo $raw;
}

function display_failure() {
    $raw = file_get_contents($GLOBALS['email_stage_1_body_failure']);
    echo $raw;
}


/* Only POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

/* Fetch POST info and generate a Player */
try {
    $player = new Player();
    $player->fillFromPost();

} catch (Exception $e) {
    show_error();
}

/* Store player */
if (!store_player($player, $dbh)) {
    display_failure();
    return;
}

/* Email player */
$rc = email_player_stage_1($dbh, $player);
if (!$rc) {
    display_failure();
    http_response_code(500);
    return;
}

/* Status change: STAGE_2_EMAIL_CHECK_SEND */
update_player_status($dbh, $player, STAGE_2_EMAIL_CHECK_SEND);

display_success();

?>

