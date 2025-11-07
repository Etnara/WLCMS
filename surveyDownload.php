<?php
require_once 'database/dbinfo.php';
$con = connect();

$id = (int)($_GET['id'] ?? 0);
$stmt = $con->prepare("SELECT filename, mime, content FROM dbsurveys WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
  http_response_code(404);
  echo 'Not found';
  exit;
}

$stmt->bind_result($fn, $mime, $blob);
$stmt->fetch();

header('Content-Type: ' . ($mime ?: 'application/pdf'));
header('Content-Disposition: inline; filename="' . rawurlencode($fn) . '"');
header('Content-Length: ' . strlen($blob));
echo $blob;
