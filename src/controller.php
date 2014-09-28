<?php

require '/usr/local/share/opcyberpaint/inschrijving/class.php';
require '/usr/local/share/opcyberpaint/inschrijving/func_inschrijving.php';

date_default_timezone_set('Etc/UTC');


function process_payment($dbh) {
    /* check if the paymenttoken is set */
    $paymenttoken = trim($_POST["paymenttoken"]);
    if (! filter_var($paymenttoken, FILTER_SANITIZE_STRING)) {
        return new Message('error', 'input check failure paymenttoken');
    }

    /* retrieve player info from the paymenttoken */
    $player = retrieve_player_from_paymenttoken($dbh, $paymenttoken);
    if ($player === NULL) {
        return new Message('error', 'paymenttoken not found');
    }

    /* TODO match player type vs. money payed */

    /* Update player status to stage_5_payment_received */
    update_player_status($dbh, $player, STAGE_5_PAYMENT_RECEIVED);

    return $player;
}

function generate_pdf($dbh, $player) {
    /* Generate QR */
    $qr_file = generate_qr($player);

    /* generate PDF */
    $raw = file_get_contents($GLOBALS['pdf_stage_5_body_template']);
    $raw = str_replace('$NICKNAME', $player->nickname, $raw);
    $raw = str_replace('$CODE', $player->doortoken, $raw);
    $raw = str_replace('$GOTGAME', $player->gotgame, $raw);
    $raw = str_replace('$QRCODEFILE', $qr_file, $raw);
    $html = $raw;


    $mpdf=new mPDF('c');
    $mpdf->SetDisplayMode('fullpage');
    /* $stylesheet = file_get_contents('/usr/local/share/opcyberpaint/inschrijving/stage_5.css'); */
    /* $mpdf->WriteHTML($stylesheet, 1); // The parameter 1 tells that this is css/style only and no body/html/text */

    $mpdf->WriteHTML($html);

    /* TODO */
    $filename = '/tmp/OpCyberpaint_' . $player->doortoken . md5($player->nickname . $player->doortoken) . '.pdf';
    $mpdf->Output($filename);

    return $filename;
}


/************** MAIN *************/

/* Sanity checks */
if (($_SERVER['REQUEST_METHOD'] !== 'GET') &&
     ($_SERVER['REQUEST_METHOD'] !== 'POST')) {
    echo json_encode(new Message('error', 'Unsupported method'));
    return;
}

/* Open DB */
$dbh = db_connect();
if ($dbh === NULL) {
    return;
}

/* Return value is a Message or Controller class */
$controller = is_authorized($dbh);
if (get_class($controller) === "Message") {
    /* Not authorized */
    echo json_encode($controller);
    return;
} else if (get_class($controller) === "Controller") {
    /* Authorized */
}


/* If method is GET: go to show all participants */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    html_print_spelers($dbh);
    return;

/* If this is a POST */
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo json_encode($controller);

    $rc = process_payment($dbh);
    if (get_class($rc) === "Message") {
        /* Failure */
        echo json_encode($rc);
        return;
    } else if (get_class($rc) === "Player") {
        /* Successfull processing, got the Player */
        $player = $rc;
        echo json_encode(new Message('OK', 'payment processed'));

        /* Generate the PDF */
        $pdffilename = generate_pdf($dbh, $player);
        echo json_encode(new Message('OK', 'PDF generated'));

        /* Email PDF */
        $rc = email_player_stage_5($dbh, $player, $pdffilename);
        if ($rc === True) {
            unlink($pdffilename);
        }
    }
}

return;

?>
