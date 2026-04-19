<?php
session_start();
require_once 'googleClient.php';

$client = get_google_client();
header('Location: ' . $client->createAuthUrl());
exit;