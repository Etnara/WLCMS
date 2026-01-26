<?php

require_once("dbinfo.php");


function getAllEmails(){
    $query = "SELECT admin_email, speaker_email, subject, body, time_sent FROM dbemails ORDER BY time_sent DESC";
    $conn = connect();
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $allEmails = [];
    while($row = $result->fetch_assoc()){
        $allEmails[] = $row;
    }
    $stmt->close();
    $conn->close();
    return $allEmails;
}
function getAllEmailsForSpeaker( $speaker_email ) {
    $query = "SELECT admin_email, subject, body, time_sent FROM dbemails WHERE speaker_email = ? ORDER BY time_sent DESC";
    $conn = connect();
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $speaker_email);
    $stmt->execute();
    $result = $stmt->get_result();

    $allEmails = [];

    while ($row = $result->fetch_assoc()) {
        $date = new DateTime($row['time_sent']);
        $formattedDate = $date->format("m/d/Y");
        $allEmails[] = [
            'admin_email' => $row['admin_email'],
            'subject' => $row['subject'],
            'body' => $row['body'],
            'time_sent' => $formattedDate
        ];
    }

    $stmt->close();
    $conn->close();
    return $allEmails;
}

function getAllEmailsForAdmin( $admin_email ) {
    $query = "SELECT speaker_email, subject, body, time_sent FROM dbemails WHERE admin_email = ? ORDER BY time_sent DESC";
    $conn = connect();
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $admin_email);
    $stmt->execute();
    $result = $stmt->get_result();

    $allEmails = [];

    while ($row = $result->fetch_assoc()) {
        $date = new DateTime($row['time_sent']);
        $formattedDate = $date->format("m/d/Y");
        $allEmails[] = [
            'speaker_email' => $row['speaker_email'],
            'subject' => $row['subject'],
            'body' => $row['body'],
            'time_sent' => $formattedDate
        ];
    }

    $stmt->close();
    $conn->close();
    return $allEmails;
}