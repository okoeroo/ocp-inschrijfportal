<?php

/* Enable output buffering */
/* ob_start(); */

require '/usr/local/share/opcyberpaint/inschrijving/class.php';
require '/usr/local/share/opcyberpaint/inschrijving/func_inschrijving.php';

date_default_timezone_set('Etc/UTC');

$cookie_name = 'do_you_believe_in_magic';
$cookie_hours = 6;
$cookie_scope = '.opcyberpaint.nl';


/* Open DB */
$dbh = db_connect();
if ($dbh === NULL) {
    http_response_code(500);
    return;
}


/* GET */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    /* Controller? */
    if (isset($_GET['authkey'])) {
        /* Cookie not set, login */
        $controller = is_authorized($dbh);
        if (get_class($controller) === "Message") {
            /* Not authorized */
            show_error();
            
            /* echo json_encode($controller); */
            return;
        } else if (get_class($controller) === "Controller") {
            /* Authorized */
            /* echo json_encode($controller); */
        }

        /* Login cool, generate cookie, store cookie, set cookie, reload page */
        $sessiontoken = generate_session($dbh, $cookie_hours * 3600);
        setcookie($cookie_name, $sessiontoken, time()+(3600 * $cookie_hours), '/inschrijving/', $cookie_scope, TRUE);

        echo js_reload(5,
                       '<h3>you are logged in now, do your thing, you have ' . $cookie_hours . ' hours.</h3>', 
                       'https://opcyberpaint.nl');

        return;
    }


    /* If cookie is set, user is logged in, can authenticate users per cookie lifetime */
    if (isset($_COOKIE[$cookie_name])) {
        /* Check if there is a token. No need to proceed if absent */
        if (! isset($_GET['doortoken'])) {
            /* Error no doortoken provided */
            show_error();
            return;
        }
        $doortoken = trim($_GET['doortoken']);

        /* Lookup cookie, to check if it's a valid and known cookie. */
        $session_data = check_sessiontoken($dbh, $_COOKIE[$cookie_name]);
        if (is_object($session_data) && (get_class($session_data) === "Message") || ($session_data === NULL)) {
            /* The cookie is unknown or session expired */

            /* unset cookie */
            setcookie($cookie_name, $sessiontoken, $time-3600, '/inschrijving/', $cookie_scope, TRUE);
            show_error();

            return;
        }

        /* Cookie is cool, check for doortoken */
        $player = retrieve_player_from_doortoken($dbh, $doortoken);
        if ($player === NULL) {
            /* Doortoken is fucked up: not in database, hacker detected */
            $html = display_player_unknown();
            echo $html;
            return;
        }

        /* Doortoken is cool: green light, show profile */
        $html = display_player_at_door($player);
        echo $html;

        /* Status change: STAGE_6_CHECKED_IN */
        update_player_status($dbh, $player, STAGE_6_CHECKED_IN);
        return;
    } else {
        /* Cookie not set, login */
        $controller = is_authorized($dbh);
        if (get_class($controller) === "Message") {
            /* Not authorized */
            show_error();
            
            /* echo json_encode($controller); */
            return;
        } else if (get_class($controller) === "Controller") {
            /* Authorized */
            /* echo json_encode($controller); */
        }

        /* Login cool, generate cookie, store cookie, set cookie, reload page */
        $sessiontoken = generate_session($dbh, $cookie_hours * 3600);
        setcookie($cookie_name, $sessiontoken, time()+(3600 * $cookie_hours), '/inschrijving/', $cookie_scope, TRUE);

        echo js_reload(5,
                       '<h3>you are logged in now, do your thing, you have ' . $cookie_hours . ' hours.</h3>', 
                       'https://opcyberpaint.nl');

        return;
    }
    return;

/* All else */
} else {
    show_error();
    return;
}

?>
