<?php
session_start();
require_once 'google_client.php';

$client = get_google_client();
header('Location: ' . $client->createAuthUrl());
exit;