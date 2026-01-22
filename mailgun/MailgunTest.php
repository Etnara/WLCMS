<?php
// Include the Autoloader (see "Libraries" for install instructions)
require '../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Prefer $_ENV when using dotenv
$apiKey = $_ENV['MAILGUN_API_KEY'] ?? null;
$domain = $_ENV['MAILGUN_DOMAIN'] ?? null;

if (!$apiKey || !$domain) {
  die("Missing MAILGUN_API_KEY or MAILGUN_DOMAIN. Check your .env path.\n");
}


// Use the Mailgun class from mailgun/mailgun-php v4.2
use Mailgun\Mailgun;

// Instantiate the client.
$mg = Mailgun::create($apiKey);
// When you have an EU-domain, you must specify the endpoint:
// $mg = Mailgun::create(getenv('API_KEY') ?: 'API_KEY', 'https://api.eu.mailgun.net');

// Compose and send your message.
$result = $mg->messages()->send($domain, [
  'from' => "Mailgun Sandbox <postmaster@$domain>",
  'to' => 'Caleb Lineberry <wlccoffeetalks@gmail.com>',
  'subject' => 'Hello Caleb Lineberry',
  'text' => 'Congratulations, you just sent an email with Mailgun!',
]);

echo $result->getMessage() . "\n";
