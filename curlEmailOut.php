<?php

echo "huh";

/*
session_cache_expire(30);
session_start();
/*
$loggedIn = false;
$accessLevel = 0;
$userID = null;
if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    //$accessLevel = $_SESSION['access_level'];
    $userID = $_SESSION['_id'];
}
require_once('database/dbinfo.php');
require_once('database/dbEmails.php');
require_once('database/dbPersons.php');


//declare(strict_types=1);

$apiKey = getenv('MAILGUN_API_KEY');
$domain = getenv('MAILGUN_DOMAIN');

if (!$apiKey || !$domain) {
  http_response_code(500);
  exit("Missing MAILGUN_API_KEY or MAILGUN_DOMAIN");
}

$to      = trim($_POST['to'] ?? 'calebalineberry@gmail.com');
$subject = trim($_POST['subject'] ?? 'Hello');
$text    = trim($_POST['text'] ?? 'Test email from my website');
$html    = trim($_POST['html'] ?? '');

$from = "WLC Coffee Talks <no-reply@{$domain}>";

$postData = [
  'from'    => $from,
  'to'      => $to,
  'subject' => $subject,
  'text'    => $text,
];

if ($html !== '') $postData['html'] = $html;

$ch = curl_init();
curl_setopt_array($ch, [
  CURLOPT_URL => "https://api.mailgun.net/v3/{$domain}/messages",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => $postData,
  CURLOPT_USERPWD => "api:{$apiKey}",
]);

$response = curl_exec($ch);
$err      = curl_error($ch);
$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

header('Content-Type: application/json');

if ($response === false) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => $err]);
  exit;
}

http_response_code($status);
echo $response;

$admin = retrieve_person($userID);

storeSentEmail( $admin->get_email(), $to, $subject, $text );
?>