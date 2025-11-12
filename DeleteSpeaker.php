<?php
require_once __DIR__ . '/database/dbPersons.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $speakerName = $_GET['name'] ?? '';
    $speakerID   = $_GET['id'] ?? '';

    if (!empty($speakerID)) {
        $result = deleteSpeaker($speakerID);

        if ($result['success']) {
            $message = json_encode("Speaker {$speakerName} deleted successfully!");
            echo "<script>
                alert($message);
                window.location.href = 'rejectedSpeakers.php';
            </script>";
        } else {
            $message = json_encode("Failed to delete {$speakerName}: {$result['message']}");
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