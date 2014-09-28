<?php

require '/usr/local/share/opcyberpaint/inschrijving/config.php';
require '/usr/local/share/PHPMailer/PHPMailerAutoload.php';

require '/usr/local/share/MPDF57/mpdf.php';
require '/usr/local/share/phpqrcode/qrlib.php';


/* Send email test */
function email_player_stage_1($dbh, $player) {
    $raw = file_get_contents($GLOBALS['email_stage_1_body_template']);
    $raw1 = str_replace('$NICKNAME', $player->nickname, $raw);

    $url = $GLOBALS['email_stage_1_url_check_root'] . '?' . 'authkey=' . $player->authkey;

    $body = str_replace('$URL', $url, $raw1);

    $mail = new PHPMailer();
    $mail->isSMTP();

    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $mail->SMTPDebug = 0;
    //Ask for HTML-friendly debug output
    $mail->Debugoutput = 'html';

    $mail->Host = $GLOBALS['email_host'];
    $mail->Port = $GLOBALS['email_port'];

    $mail->Username = $GLOBALS['email_user'];
    $mail->Password = $GLOBALS['email_pass'];
    $mail->SMTPAuth = $GLOBALS['email_SMTPAuth'];
    $mail->SMTPSecure = $GLOBALS['email_SMTPSecure'];

    $mail->setFrom($GLOBALS['email_from'], $GLOBALS['email_from_name']);
    $mail->addReplyTo($GLOBALS['email_replyto'], $GLOBALS['email_replyto_name']);

    /* Where to? */
    $mail->addAddress($player->email, $player->nickname);
    $mail->Subject = $GLOBALS['email_stage_1_subject'];

    $mail->Body = $body;

    /* $mail->addAttachment('/usr/local/share/PHPMailer/examples/images/phpmailer_mini.png'); */

    //send the message, check for errors
    if (!$mail->send()) {
        echo "Mailer Error: " . $mail->ErrorInfo;
        return False;
    }

    return True;
}

/* Send payment information */
function email_player_stage_3($dbh, $player) {
    $raw = file_get_contents($GLOBALS['email_stage_3_body_template']);

    $raw1 = str_replace('$NICKNAME', $player->nickname, $raw);
    $raw = $raw1;

    $raw1 = str_replace('$GOTGAME', $player->gotgame, $raw);
    $raw = $raw1;

    $raw1 = str_replace('$PAYMENTTOKEN', $player->paymenttoken, $raw);
    $raw = $raw1;

    /* UGLY */
    if ($player->gotgame === 'observator') {
        $raw1 = str_replace('$PRICE', "5,00 euro", $raw);
    } else if ($player->gotgame === 'speler') {
        $raw1 = str_replace('$PRICE', "12,50 euro", $raw);
    }
    $raw = $raw1;


    $body = $raw;

    $mail = new PHPMailer();
    $mail->isSMTP();

    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $mail->SMTPDebug = 0;
    //Ask for HTML-friendly debug output
    $mail->Debugoutput = 'html';

    $mail->Host = $GLOBALS['email_host'];
    $mail->Port = $GLOBALS['email_port'];

    $mail->Username = $GLOBALS['email_user'];
    $mail->Password = $GLOBALS['email_pass'];
    $mail->SMTPAuth = $GLOBALS['email_SMTPAuth'];
    $mail->SMTPSecure = $GLOBALS['email_SMTPSecure'];

    $mail->setFrom($GLOBALS['email_from'], $GLOBALS['email_from_name']);
    $mail->addReplyTo($GLOBALS['email_replyto'], $GLOBALS['email_replyto_name']);

    /* Where to? */
    $mail->addAddress($player->email, $player->nickname);
    $mail->Subject = $GLOBALS['email_stage_3_subject'];

    $mail->Body = $body;

    /* $mail->addAttachment('/usr/local/share/PHPMailer/examples/images/phpmailer_mini.png'); */

    //send the message, check for errors
    if (!$mail->send()) {
        echo "Mailer Error: " . $mail->ErrorInfo;
        return False;
    }

    return True;
}


/* Send door token */
function email_player_stage_5($dbh, $player, $pdffilename) {
    $raw = file_get_contents($GLOBALS['email_stage_5_body_template']);

    $raw = str_replace('$NICKNAME', $player->nickname, $raw);
    $raw = str_replace('$GOTGAME', $player->gotgame, $raw);


    $body = $raw;

    $mail = new PHPMailer();
    $mail->isSMTP();

    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $mail->SMTPDebug = 0;
    //Ask for HTML-friendly debug output
    $mail->Debugoutput = 'html';

    $mail->Host = $GLOBALS['email_host'];
    $mail->Port = $GLOBALS['email_port'];

    $mail->Username = $GLOBALS['email_user'];
    $mail->Password = $GLOBALS['email_pass'];
    $mail->SMTPAuth = $GLOBALS['email_SMTPAuth'];
    $mail->SMTPSecure = $GLOBALS['email_SMTPSecure'];

    $mail->setFrom($GLOBALS['email_from'], $GLOBALS['email_from_name']);
    $mail->addReplyTo($GLOBALS['email_replyto'], $GLOBALS['email_replyto_name']);

    /* Where to? */
    $mail->addAddress($player->email, $player->nickname);
    $mail->Subject = $GLOBALS['email_stage_5_subject'];

    $mail->Body = $body;

    $mail->addAttachment($pdffilename);

    //send the message, check for errors
    if (!$mail->send()) {
        echo json_encode(new Message('error', $mail->ErrorInfo));
        return False;
    }

    echo json_encode(new Message('OK', 'Email with door token sent'));
    return True;
}

function db_connect() {
    try {
        $dbh = new PDO($GLOBALS['db_dsn'], $GLOBALS['db_user'], $GLOBALS['db_pass']);
        if ($GLOBALS['db_debug']) {
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    } catch (Exception $e) {
        return NULL;
    }

    return $dbh;
}

function store_player($player, $dbh) {
    $sql = 'INSERT INTO spelers' .
           '            (nickname, realname, email, faction, ' .
           '             gotgame, dieet, dieetoverige, status, ' .
           '             authkey, paymenttoken, grouptoken, ' .
           '             doortoken, paid, created_on) ' . 
           '     VALUES (:nickname, :realname, :email, :faction, ' . 
           '             :gotgame, :dieet, :dieetoverige, :status, ' .
           '             :authkey, :paymenttoken, :grouptoken, ' . 
           '             :doortoken, :paid, NOW())';

    try {
        $sth = $dbh->prepare($sql);
        $sth->execute(array(
                ':nickname'=>$player->nickname,
                ':realname'=>$player->realname,
                ':email'=>$player->email,
                ':faction'=>$player->faction,
                ':gotgame'=>$player->gotgame,
                ':dieet'=>$player->dieet,
                ':dieetoverige'=>$player->dieetoverige,
                ':status'=>$player->status,
                ':authkey'=>$player->authkey,
                ':paymenttoken'=>$player->paymenttoken,
                ':grouptoken'=>$player->grouptoken,
                ':doortoken'=>$player->doortoken,
                ':paid'=>$player->paid));
    } catch (Exception $e) {
        var_dump($e);
        return False;
    }
    return True;
}

function read_file_and_parse_json_to_players($filename) {
    try {
        $handle = fopen($filename, "r");
        $contents = fread($handle, filesize($filename));
        fclose($handle);

        try {
            $tmp = json_decode($contents);
            if ($tmp != NULL) {
                $players = $tmp;
            } else {
                $players = array();
            }
        } catch (Exception $e) { 
            http_response_code(401);
            printf("Failed to parse node file\n");
            return NULL;
        }

    } catch (Exception $e) { 
        http_response_code(500);
    }
    return $players;
}

function write_players_to_json_file($players, $json_path) {
    try {
        $json = json_encode($players);
        try {
            $handle = fopen($json_path, "w");
            fwrite($handle, $json, strlen($json));
            fflush($handle);
            fclose($handle);
        } catch (Exception $e) {
            http_response_code(401);
            return False;
        }
    } catch (Exception $e) {
        http_response_code(500);
        return False;
    }
    return True;
}


function show_error() {
    printf('<img id="titelimg" src="/inschrijving/gotcha.png">');
    http_response_code(418);
    return;
}


function retrieve_player_from_authkey($dbh, $authkey) {
    $sql = 'SELECT * FROM spelers WHERE authkey = :authkey';
    $sth = $dbh->prepare($sql);
    $sth->bindParam(':authkey', $authkey);
    if (! $sth->execute()) {
        return NULL;
    }

    if ($sth->rowCount() !== 1) {
        /* Wrong number of rowcount found */
        show_error();
        return NULL;
    }

    /* Only 1 row, fetch it */
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    if (! $row) {
        return NULL;
    }
    $player = new Player();
    $player->fillFromRow($row);

    return $player;
}

function retrieve_player_from_paymenttoken($dbh, $paymenttoken) {
    $sql = 'SELECT * FROM spelers WHERE paymenttoken = :paymenttoken';
    $sth = $dbh->prepare($sql);
    $sth->bindParam(':paymenttoken', $paymenttoken);
    if (! $sth->execute()) {
        return NULL;
    }

    if ($sth->rowCount() !== 1) {
        /* Wrong number of rowcount found */
        return NULL;
    }

    /* Only 1 row, fetch it */
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    if (! $row) {
        return NULL;
    }
    $player = new Player();
    $player->fillFromRow($row);

    return $player;
}

function retrieve_player_from_doortoken($dbh, $doortoken) {
    $sql = 'SELECT * FROM spelers WHERE doortoken = :doortoken';
    $sth = $dbh->prepare($sql);
    $sth->bindParam(':doortoken', $doortoken);
    if (! $sth->execute()) {
        return NULL;
    }

    if ($sth->rowCount() !== 1) {
        /* Wrong number of rowcount found */
        return NULL;
    }

    /* Only 1 row, fetch it */
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    if (! $row) {
        return NULL;
    }
    $player = new Player();
    $player->fillFromRow($row);

    return $player;
}


function update_player_status($dbh, $player, $new_status) {
    $sql = 'UPDATE spelers SET status = :status WHERE authkey = :authkey';
    $sth = $dbh->prepare($sql);
    $sth->bindParam(':status', $new_status);
    $sth->bindParam(':authkey', $player->authkey);

    try {
        $sth->execute();
    } catch (Exception $e) {
        http_response_code(500);
        return False;
    }
    return True;
}

function check_controller_authkey($dbh, $authkey) {
    $sql = 'SELECT * FROM controllers WHERE authkey = :authkey';
    $sth = $dbh->prepare($sql);
    $sth->bindParam(':authkey', $authkey);
    try {
        if (! $sth->execute()) {
            return new Message('error', 'execute failure');
        }
    } catch (Exception $e) {
        return new Message('error', 'execute failure');
    }

    if ($sth->rowCount() < 1) {
        /* Wrong number of rowcount found */
        return new Message('error', 'unknown key');
    }

    /* Only 1 row, fetch it */
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    if (! $row) {
        return NULL;
    }

    $controller = new Controller();
    $controller->fillFromRow($row);

    return $controller;
}


/* Returns a controller if authorized */
function is_authorized($dbh) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        /* Get the authkey from the query string */
        $authkey = trim($_GET['authkey']);
        if (! filter_var($authkey, FILTER_SANITIZE_STRING)) {
            return new Message('error', 'No proper authkey');
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $authkey = trim($_POST["authkey"]);
        if (! filter_var($authkey, FILTER_SANITIZE_STRING)) {
            return new Message('error',
                               'input check failure authkey');
        }
    } else {
        return new Message('error', 'Unsupported method');
    }

    /* Return value is a Message or Controller class */
    $rc = check_controller_authkey($dbh, $authkey);
    if (get_class($rc) === "Message") {
        return $rc;
    } else if (get_class($rc) === "Controller") {
        return $rc;
    } else {
        return new Message('error', 'YU ' . get_class($rc));
    }
}

function sql_to_html_table($dbh, $sql) {
    $sth = $dbh->prepare($sql);
    if (! $sth->execute()) {
        return NULL;
    }

    echo '<html><body><table border=1>';
    $rs = $sth->fetchAll(PDO::FETCH_ASSOC); 
    echo '<tr>';
    for ($i = 0; $i < $sth->columnCount(); $i++) {
        $meta = $sth->getColumnMeta($i);
        echo '<th>' . $meta['name'] . '</th>';
    }
    echo '</tr>';

    foreach($rs as $row) {
        echo '<tr>';
        foreach($row as $field) {
            echo '<td>' . $field . '</td>';
        }
        echo '</tr>';
    }
    echo '</table></body></html>';
}

function html_print_spelers($dbh) {
    $sql = 'SELECT status, count(*) FROM spelers GROUP BY status UNION SELECT "Total", count(*) FROM spelers';
    sql_to_html_table($dbh, $sql);
    echo '<br>';
    $sql = 'SELECT gotgame, count(*) FROM spelers GROUP BY gotgame';
    sql_to_html_table($dbh, $sql);
    echo '<br>';
    $sql = 'SELECT faction, count(*) FROM spelers GROUP BY faction';
    sql_to_html_table($dbh, $sql);
    echo '<br>';
    $sql = 'SELECT dieet, count(*) FROM spelers GROUP BY dieet';
    sql_to_html_table($dbh, $sql);
    echo '<br>';
    $sql = 'SELECT dieet, dieetoverige, count(*) FROM spelers GROUP BY dieet, dieetoverige';
    sql_to_html_table($dbh, $sql);
    echo '<br>';
    $sql = 'SELECT * FROM spelers';
    sql_to_html_table($dbh, $sql);

    return;
}

function generate_qr($player) {
    // how to save PNG codes to server
    $codeContents = $GLOBALS['email_stage_5_url_check_doortoken'] . '?doortoken=' . $player->doortoken;
    $pngAbsoluteFilePath = '/tmp/qr_code_'.md5($codeContents).'.png';

    if (!file_exists($pngAbsoluteFilePath)) {

        $code = new QRcode();
        QRcode::png($codeContents, 
	            $pngAbsoluteFilePath, 
                    QR_ECLEVEL_L, 4);
            
    }

    return $pngAbsoluteFilePath;
}

function generate_session($dbh, $valid_for_seconds) {
    $i = 128;
    $cstrong = True;
    $sessiontoken = hash('sha512',
                         openssl_random_pseudo_bytes($i, $cstrong),
                         False);

    $sql = 'INSERT INTO sessions' .
           '            (token, valid_for_seconds, created_on) ' .
           '     VALUES (:token, :valid_for_seconds, NOW())';

    try {
        $sth = $dbh->prepare($sql);
        $sth->execute(array(
                ':token'=>$sessiontoken,
                ':valid_for_seconds'=>$valid_for_seconds));
    } catch (Exception $e) {
        return Null;
    }
    return $sessiontoken;
}

function js_reload($wait_for_seconds, $message, $url) {
    $html = '<html><head>';
    $html = $html . '<meta http-equiv="refresh" content="'.$wait_for_seconds.'; url='. $url .'"></head>';
    $html = $html . '<body>'.$message.'</body></html>';
    return $html;
}

function check_sessiontoken($dbh, $sessiontoken) {
    $sql = 'SELECT * FROM sessions WHERE token = :sessiontoken';
    $sth = $dbh->prepare($sql);
    $sth->bindParam(':sessiontoken', $sessiontoken);
    try {
        if (! $sth->execute()) {
            return new Message('error', 'execute failure');
        }
    } catch (Exception $e) {
        return new Message('error', 'execute failure');
    }

    if ($sth->rowCount() < 1) {
        /* Wrong number of rowcount found */
        return new Message('error', 'none found');
    }

    /* Only 1 row, fetch it */
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    if (! $row) {
        return new Message('error', 'no row data');
    }

    /* Is session still valid */
    if (time() < (strtotime($row['created_on']) + $row['valid_for_seconds'])) {
        /* Session is fresh */
        return $row;
    } else {
        return new Message('error', 'Session is expired');;
    }

    return $row;
}

function display_player_at_door($player) {
    if ($player->status === 'stage_5_payment_received') {
	/* Good - Green */
        $html = '<html><body bgcolor="green">';
    } else if ($player->status === 'stage_6_checked_in') {
	/* Already checked in - Orange */
        $html = '<html><body bgcolor="orange">';
    } else {
	/* Everything else, red */
    	$html = '<html><body bgcolor="red">';
    }

    $html = $html . '<h1>Nickname: ' . $player->nickname . '</h1><br>';
    $html = $html . '<h1>Dieet:    ' . $player->dieet. '</h1><br>';
    $html = $html . '<h1>Faction:  ' . $player->faction. '</h1><br>';
    $html = $html . '<h1>Status:   ' . $player->status. '</h1><br>';
    $html = $html . '</body></html>';
    return $html;
}

function display_player_unknown() {
    $html = '<html><body bgcolor="red">';
    $html = $html . '<h1>Token unknown</h1>';
    $html = $html . '</body></html>';
    return $html;
}

?>
