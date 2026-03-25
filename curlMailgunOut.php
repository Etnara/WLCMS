<?php

session_cache_expire(30);
session_start();

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
require 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__  . '/');
$dotenv->load();

// Prefer $_ENV when using dotenv
$apiKey = $_ENV['MAILGUN_API_KEY'] ?? null;
$domain = $_ENV['MAILGUN_DOMAIN'] ?? null;


if (!$apiKey || !$domain) {
  http_response_code(500);
  exit("Missing MAILGUN_API_KEY or MAILGUN_DOMAIN");
}

if (isset($_POST['to'])) {
    $to = trim($_POST['to']);
} else {
    die("Missing 'to' field");
}
$subject = trim($_POST['subject'] ?? 'Hello World!');
$text    = trim($_POST['body'] ?? 'Unideal');
$html    = trim($_POST['html'] ?? '');



$from = "WLC Coffee Talks <mail@{$domain}>";

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

// Guard against null session/user before touching the DB
if ($userID && $status === 200) {
    $admin = retrieve_person($userID);
    if ($admin) {
        storeEmail($admin->get_email(), $to, $subject, $text, false);
        if (isset($_POST['parentID'])) {
            addEmailChainLink($_POST['parentID'], getMostRecentID());
        }
    } else {
        error_log("curlMailgunOut: could not retrieve admin for userID=$userID");
    }
}

http_response_code($status);
echo $response;