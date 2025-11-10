<?php
/* Implemented by Caleb Lineberry by following guides created by Dave Hollingworth and Guide Realm.
Hollingworth's guide: "Send email with PHP | Create a Working Contact Form Using PHP" https://www.youtube.com/watch?v=fIYyemqKR58&t=206s
Guide Realm's guide: "How To Set Up Gmail SMTP Server - Full Guide" https://www.youtube.com/watch?v=ZfEK3WP73eY

All mailing functions created by Caleb Lineberry */

require_once('vendor/autoload.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
include_once('database/dbinfo.php');

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

    $mail->Body =
"Thank you for your interest in speaking for the Women's Leadership Colloquium's Coffee Talks!
We will let you know when your form has been reviewed and accepted.";

    $mail->send();
}

function sendFormApproved($to, $first_name, $last_name) {
    global $mail;
    $mail->addAddress($to, $first_name . " " . $last_name);
    $mail->Subject = "Interest Form Approved";

    $mail->Body =
"Your interest form for the Women's Leadership Coloquium Coffee Talks has been approved!
You will be notified when you have been scheduled for a Coffee Talk.";

    $mail->send();
}

function sendScheduledSpeaker($to, $first_name, $last_name, $date) {
    global $mail;
    $mail->addAddress($to, $first_name. " " . $last_name);
    $mail->Subject = "Scheduled for Coffee Talk on **Date**";

    $mail->Body =
"You have been scheduled to speak on " . $date . "
Please email bwilli22@umw.edu if you need to rechedule or cancel.";

    $mail->send();
}

function generate_uuid_v4() {
    $data = random_bytes(16); // Cryptographically Secure
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Set bits 6-7 to 10
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function sendAdminInvite($to){
    global $mail;
    $mail->addAddress($to);
    $mail->Subject = "Invite for Admin Account Creation";

    $uuid = generate_uuid_v4();

    $link = $_SERVER['HTTP_REFERER'];
    $link = substr($link, 0, strrpos($link, "/")) . "/AdminForm.php?uuid={$uuid}&email={$to}";

    $mail->isHTML();
    $mail->Body = "
        Welcome to the Coffee Talks Management System! <br>
        Please create your admin account <a href =\"{$link}\">here</a>. <br>
        This link expires in 24 hours.
    ";

    $con = connect();
    mysqli_query($con, "INSERT INTO authentication_tokens VALUES ('{$uuid}', NOW());");
    $con->close();

    $mail->send();
}

?>
