<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'google_config.php';

function get_google_client(): Google\Client {
    $client = new Google\Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri(GOOGLE_REDIRECT_URI);
    $client->addScope(Google\Service\Calendar::CALENDAR);
    $client->setAccessType('offline');
    $client->setPrompt('consent'); // ensures refresh token is always returned
    return $client;
}