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

// the output has to be clean before or it fails :(
if (ob_get_level()) {
  ob_end_clean();
}

header('Content-Type: ' . ($mime ?: 'application/pdf'));
header('Content-Disposition: inline; filename="' . basename($fn) . '"');

// handle blob stuff carefully or php freaks out about it lol
echo $blob;

$stmt->close();
$con->close();
