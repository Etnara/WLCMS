<?php

require_once('vendor/autoload.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer(true);

//$mail->SMTPDebug = SMTP::DEBUG_SERVER;

$mail->isSMTP();
$mail->SMTPAuth = true;

$mail->Host = 'smtp.gmail.com';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->Username = 'wlccoffeetalks@gmail.com';
$mail->Password = '------------------------';

$mail->setFrom('wlccoffeetalks@gmail.com','WLC Coffee Talks');
$mail->addAddress('calebalineberry@gmail.com','LebLeb');

$mail->Subject='Hello World!';
$mail->Body = 'Tuff';

$mail->send();

?>
