<?php
include("database/dbCommunications.php");
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $speaker_email = $_GET['speaker_email'] ?? '';
    $admin_email = $_GET['admin_email'] ??'';
    $message = json_encode("Communication Recorded!");
    addCommunication($admin_email, $speaker_email);
        echo "<script>
            alert($message);
            window.history.back();
        </script>";
} else {
    $message = json_encode("Issues with communications");
        echo "<script>
        alert($message);
        window.history.back();
        </script>";
}

?>