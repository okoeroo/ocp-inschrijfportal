<?php

define("STAGE_1_PRECHECK",              "stage_1_precheck");
define("STAGE_2_EMAIL_CHECK_SEND",      "stage_2_emaili_check_send");
define("STAGE_3_EMAIL_CONFIRMED",       "stage_3_email_confirmed");
define("STAGE_4_PAYMENT_INFO_SEND ",    "stage_4_payment_info_send");
define("STAGE_5_PAYMENT_RECEIVED",      "stage_5_payment_received");
define("STAGE_6_PDF_GENERATED",         "stage_6_pdf_generated");
define("STAGE_7_PDF_SEND",              "stage_7_pdf_send");

define("STAGE_6_CHECKED_IN",            "stage_6_checked_in");

class Player {
    public $nickname; /* Nick name */
    public $realname; /* Real life name */
    public $email;    /* Checked email address */
    public $faction;  /* List of 4 factions, but people may POST creative text */
    public $gotgame;  /* player or observer */
    public $dieet;    /* List of diets */
    public $dieetoverige; /* Specific diet details provided as text */
    public $status;   /* status, as in which stage is the user */
    public $authkey;  /* authentication token, used for account retrieval and account activation (email check) */
    public $paymenttoken; /* Token used to link a payment with the account */
    public $grouptoken; /* Store the grouptoken underwhich this account was initiated */
    public $doortoken; /* Token that will show in QR */
    public $paid; /* yes or no, did the user pay or not ... currently not used */

    /* function __construct() { */
    function fillFromPost() {
        $this->nickname     = trim($_POST["nickname"]);
        if (! filter_var($this->nickname, FILTER_SANITIZE_STRING)) {
            throw new Exception('input check failure nickname');
        }
        $this->realname     = trim($_POST["realname"]);
        if (! empty($this->realname)) {
            if (! filter_var($this->realname, FILTER_SANITIZE_STRING)) {
                throw new Exception('input check failure realname');
            }
        }
        $this->email        = trim($_POST["email"]);
        if (! filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Not an emailaddress email');
        }
        $this->faction      = trim($_POST["faction"]);
        if (! filter_var($this->faction, FILTER_SANITIZE_STRING)) {
            throw new Exception('input check failure faction');
        }
        $this->gotgame      = trim($_POST["gotgame"]);
        if (! filter_var($this->gotgame, FILTER_SANITIZE_STRING)) {
            throw new Exception('input check failure gotgame');
        }
        if (!($this->gotgame === "observator") && !($this->gotgame === "speler")) {
            throw new Exception('input check failure gotgame - WTF is this shit.');
        }

        $this->dieet        = trim($_POST["dieet"]);
        if (! filter_var($this->dieet, FILTER_SANITIZE_STRING)) {
            throw new Exception('input check failure dieet');
        }
        $this->dieetoverige = trim($_POST["dieetoverige"]);
        if (! empty($this->dieetoverige)) {
            if (! filter_var($this->dieetoverige, FILTER_SANITIZE_STRING)) {
                throw new Exception('input check failure dieetoverige');
            }
        }

        $this->status = STAGE_1_PRECHECK;

        $i = 128;
        $cstrong = True;
        $this->authkey = hash('sha512',
                              openssl_random_pseudo_bytes($i, $cstrong),
                              False);
        /* To easy the payment process: 1. make the payment token short. 2.
         * make it shareable, i.e. not linked to the authkey. */
        $i = 128;
        $cstrong = True;
        $rawtoken = hash('sha256',
                         openssl_random_pseudo_bytes($i, $cstrong),
                         False);
        $this->paymenttoken = substr($rawtoken, 0, 10);
        $this->grouptoken   = '';

        $i = 128;
        $cstrong = True;
        $rawtoken = hash('sha256',
                         openssl_random_pseudo_bytes($i, $cstrong),
                         False);
        $this->doortoken    = substr($rawtoken, 0, 10);;
        $this->paid         = 'no';
    }

    function fillFromRow($row) {
        $this->nickname     = $row['nickname'];
        $this->realname     = $row['realname'];
        $this->email        = $row['email'];
        $this->faction      = $row['faction'];
        $this->gotgame      = $row['gotgame'];
        $this->dieet        = $row['dieet'];
        $this->dieetoverige = $row['dieetoverige'];
        $this->status       = $row['status'];
        $this->authkey      = $row['authkey'];
        $this->paymenttoken = $row['paymenttoken'];
        $this->grouptoken   = $row['grouptoken'];
        $this->doortoken    = $row['doortoken'];
        $this->paid         = $row['paid'];
    }
}

class Message {
    public $status;
    public $msg;

    function __construct($status, $msg) {
        $this->status = $status;
        $this->msg    = $msg;
    }
}

class Controller {
    public $id;
    public $authkey;

    function fillFromRow($row) {
        $this->id           = $row['id'];
        $this->authkey      = $row['authkey'];
    }
}


?>
