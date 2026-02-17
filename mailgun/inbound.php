<?php
require_once("../database/dbinfo.php");
require_once("../database/dbEmails.php");

// 1) Verify Mailgun signature (recommended)
function verify_mailgun_signature($api_key, $timestamp, $token, $signature) {
  // Reject very old requests (replay protection)
  if (abs(time() - intval($timestamp)) > 15 * 60) return false;

  $hmac = hash_hmac('sha256', $timestamp . $token, $api_key);
  return hash_equals($hmac, $signature);
}

$api_key = getenv('MAILGUN_WEBHOOK_SIGNING_KEY');

// Mailgun sends these fields for signing
$timestamp = $_POST['timestamp'] ?? '';
$token     = $_POST['token'] ?? '';
$signature = $_POST['signature'] ?? '';

if (!$api_key || !$timestamp || !$token || !$signature) {
  http_response_code(400);
  echo "Missing signature fields.";
  exit;
}

if (!verify_mailgun_signature($api_key, $timestamp, $token, $signature)) {
  http_response_code(403);
  echo "Invalid signature.";
  exit;
}

// 2) Parse inbound payload
$from    = $_POST['from'] ?? '';
$to      = $_POST['recipient'] ?? ($_POST['To'] ?? '');
$subject = $_POST['subject'] ?? '';
$bodyTxt = $_POST['body-plain'] ?? '';
$bodyHtml= $_POST['body-html'] ?? '';
$messageId = $_POST['Message-Id'] ?? ($_POST['message-id'] ?? '');

storeEmail($to, $from, $subject, $bodyTxt);

// 4) Respond 200 OK quickly  
http_response_code(200);
echo "OK";
