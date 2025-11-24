<?php
require_once 'database/dbPersons.php';

$data = getHeadshotData($_GET['id']);

if (!$data || empty($data['headshot'])) {
    header("HTTP/1.1 404 Not Found");
    exit;
}
//var_dump($data['mime']);
//error_log("MIME: " . $data['mime']);
$mime = trim($data['mime']);
//error_log("trimmed_MIME: " . $mime);
//var_dump($mime);
header("Content-Type: " . $mime);
echo $data['headshot']; 
exit;