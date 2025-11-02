<?php

// compser autoload
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

// creates and configures mailer
$mail = new PHPMailer(true);

// $mail->SMTPDebug = SMTP::DEBUG_SERVER; //debugging thingy dw

$mail->isSMTP();
$mail->SMTPAuth   = true;
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;

// CHANGE FOR SECURITY OR TESTING !!!!!!
$mail->Username   = 'PUT THE EMAIL @gmail.com';
$mail->Password   = 'PUT PASSWORDDDDDDD'; // USE "APP PASSWORD" NOT ACTUAL PASSWORD

$mail->setFrom('PUT A DIFF EMAIL @gmail.com', 'WLC Coffee Talks');

// nice defaults but not required lol
$mail->isHTML(false);
$mail->CharSet = 'UTF-8';

/**
 * Sends an invite email.
 * @param string $to - recipient email
 * @param string $subject - email subject
 * @param string $body - email body text
 */
function sendInvite($to, $subject, $body) {
    global $mail;

    // clear previous recipients/attachments in case of reuse
    $mail->clearAddresses();
    $mail->clearAttachments();

    $mail->addAddress($to);
    $mail->Subject = $subject;
    $mail->Body    = $body;

    $mail->send();
}
