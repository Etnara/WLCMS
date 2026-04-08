<?php
session_start();
require_once 'google_client.php';
require_once '../database/dbinfo.php';

$client = get_google_client();

if (!isset($_GET['code'])) {
    die('No code returned from Google.');
}

$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

if (isset($token['error'])) {
    die('Error: ' . $token['error_description']);
}

// Get logged-in user's ID from your session (adjust to match your session variable)
$user_id = $_SESSION['user_id'];

$access_token    = $token['access_token'];
$refresh_token   = $token['refresh_token'] ?? null;
$expires_at      = date('Y-m-d H:i:s', time() + $token['expires_in']);

$db = connect();
$stmt = $db->prepare("
    UPDATE dbpersons
    SET google_access_token = ?,
        google_refresh_token = COALESCE(?, google_refresh_token),
        google_token_expires_at = ?
    WHERE id = ?
");
$stmt->bind_param('ssss', $access_token, $refresh_token, $expires_at, $user_id);
$stmt->execute();

header('Location: /dashboard.php?google=connected');
exit;