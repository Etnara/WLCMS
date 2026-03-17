<?php
session_cache_expire(30);
session_start();
require_once('database/dbEmails.php');

$loggedIn = false;
$userID = null;

if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    $userID = $_SESSION['_id'];
} else {
    header('Location: inbox.php');
    exit;
}


// SINGLE DELETE (via GET)
if (isset($_GET['id']) && intval($_GET['id']) > 0) {
    $id = intval($_GET['id']);
    deleteEmailChain($id);
}

header('Location: inbox.php');
exit;