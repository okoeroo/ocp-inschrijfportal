<?php

$db_dsn  = 'mysql:host=127.0.0.1;port=3306;dbname=inschrijvingen';
$db_user = 'userinsch';
$db_pass = 'CHANGEME';

$db_debug = True;

/* Gmail */
$email_user = 'MAILUSER';
$email_pass = 'MAILPASSPHRASE';
$email_from = 'MAILFROM';
$email_from_name = 'MAILFROMNAME';
$email_replyto = 'MAILREPLYTO';
$email_replyto_name = 'Inschrijvingen Service Operation Cyberpaint';
$email_SMTPAuth = True;
$email_SMTPSecure = "ssl";
$email_host = 'smtp.googlestuff.something';
$email_port = 465;

/* Stage 1 */
$email_stage_1_subject = 'Operation Cyberpaint: Email check';
$email_stage_1_body_template = '/usr/local/share/opcyberpaint/inschrijving/stage_1.tmpl';
$email_stage_1_url_check_root = 'https://opcyberpaint.nl/inschrijving/check-email.php';
$email_stage_1_body_success = '/usr/local/share/opcyberpaint/inschrijving/great_success_stage_1.html';
$email_stage_1_body_failure = '/usr/local/share/opcyberpaint/inschrijving/great_failure.html';

/* Stage 3 */
$email_stage_3_subject = 'Operation Cyberpaint: Betalingsinformatie';
$email_stage_3_body_template = '/usr/local/share/opcyberpaint/inschrijving/stage_3.tmpl';
$email_stage_3_body_success = '/usr/local/share/opcyberpaint/inschrijving/great_success_stage_3.html';
$email_stage_3_body_failure = '/usr/local/share/opcyberpaint/inschrijving/great_failure.html';

/* Stage 5 */
$email_stage_5_subject = 'Operation Cyberpaint: Deelnemersbewijs';
$email_stage_5_body_template = '/usr/local/share/opcyberpaint/inschrijving/stage_5_email.tmpl';

$pdf_stage_5_body_template = '/usr/local/share/opcyberpaint/inschrijving/stage_5_pdf.tmpl';
$email_stage_5_url_check_doortoken = 'https://opcyberpaint.nl/inschrijving/doortoken.php';

?>
