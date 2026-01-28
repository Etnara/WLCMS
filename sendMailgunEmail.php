<?php
require '../vendor/autoload.php';

use Mailgun\Mailgun;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$to = $_POST['to'] ?? '';
$subject = $_POST['subject'] ?? '';
$body = $_POST['body'] ?? '';

if (!$to || !$subject || !$body) {
    die("Missing fields");
}

$mg = Mailgun::create(getenv('MAILGUN_API_KEY'));

$mg->messages()->send(getenv('MAILGUN_DOMAIN'), [
    'from'    => 'Your Site <mail@' . getenv('MAILGUN_DOMAIN') . '>',
    'to'      => $to,
    'subject' => $subject,
    'text'    => $body,
]);

header("Location: inbox.php?sent=1");
exit;
