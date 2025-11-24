<?php
require_once 'database/dbPersons.php';

$data = getHeadshotData($_GET['id']);

if (!$data || !isset($data['headshot'])) {
    header("HTTP/1.1 404 Not Found");
    exit;
}

header("Content-Type: " . $data['mime_type']);
echo $data['headshot'];  
exit;

?>