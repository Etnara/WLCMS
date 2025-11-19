<?php
require_once 'database/dbPersons.php';
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $speakerName = $_GET['name'] ?? '';
    $speakerID   = $_GET['id'] ?? '';

    if (!empty($speakerID)) {
        $result = acceptSpeaker($speakerID);

        if ($result['success']) {
            $message = json_encode("Speaker {$speakerName} accepted successfully!");
            echo "<script>
                alert($message);
                window.location.href = 'checkedInVolunteers.php';
            </script>";
        } else {
            $message = json_encode("Failed to accept {$speakerName}: {$result['message']}");
            echo "<script>
                alert($message);
                window.history.back();
            </script>";
        }
    } else {
        echo "<script>
            alert('No speaker ID provided.');
            window.history.back();
        </script>";
    }
}
?>