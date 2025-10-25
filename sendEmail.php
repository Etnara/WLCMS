<?php
/* Implemented by Caleb Lineberry by following guides created by Dave Hollingworth and Guide Realm.
Hollingworth's guide: "Send email with PHP | Create a Working Contact Form Using PHP" https://www.youtube.com/watch?v=fIYyemqKR58&t=206s
Guide Realm's guide: "How To Set Up Gmail SMTP Server - Full Guide" https://www.youtube.com/watch?v=ZfEK3WP73eY

Mail only works if the recipient's email is of "@gmail.com"

All mailing functions created by Caleb Lineberry */

require_once('vendor/autoload.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer(true);

//$mail->SMTPDebug = SMTP::DEBUG_SERVER;  //Uncomment if errors with mailing

$mail->isSMTP();
$mail->SMTPAuth = true;

$mail->Host = 'smtp.gmail.com';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->Username = 'wlccoffeetalks@gmail.com';
$mail->Password = '------------------------';

$mail->setFrom('wlccoffeetalks@gmail.com','WLC Coffee Talks');


function sendEmail($to, $name, $subject, $message) {
    global $mail;
    $mail->addAddress($to, $name);
    $mail->Subject = $subject;
    $mail->Body = $message;
    $mail->send();
}

function sendFormConfirmation($to, $first_name, $last_name) {
    global $mail;
    $mail->addAddress($to, $first_name . ' '. $last_name);
    $mail->Subject = "Interest Form Recieved";
    $mail->Body = "Thank you for your interest in speaking for the Women's Leadership Colloquium's Coffee Talks!
    We will let you know when your form has been reviewed and accepted.";
    $mail->send();
}

function sendFormApproved($to, $first_name, $last_name) {
    global $mail;
    $mail->addAddress($to, $first_name . " " . $last_name);
    $mail->Subject = "Interest Form Approved";
    $mail->Body = "Your interest form for the Women's Leadership Coloquium Coffee Talks has been approved!
    You will be notified when you have been scheduled for a Coffee Talk.";
    $mail->send();
}

function sendScheduledSpeaker($to, $first_name, $last_name, $date) {
    global $mail;
    $mail->addAddress($to, $first_name. " " . $last_name);
    $mail->Subject = "Scheduled for Coffee Talk on **Date**";
    $mail->Body = "You have been scheduled to speak on " . $date . "
    Please email **real email** if you need to rechedule or cancel.";
    $mail->send();
}

?>
