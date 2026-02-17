<?php

require_once("dbinfo.php");

function getAllEmails(){
    $query = "SELECT id, speaker_email, subject, time_sent FROM dbemails where next_email is null ORDER BY time_sent DESC";
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
    $query = "SELECT admin_email, subject, body, time_sent FROM dbemails WHERE speaker_email = ? and next_email is null ORDER BY time_sent DESC";
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

function getEmail( $emailID ) {
    $query = "SELECT id, speaker_email, admin_email, subject, body, time_sent, next_email FROM dbemails WHERE id = ?";
    $conn = connect();
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $emailID);
    $stmt->execute();
    $result = $stmt->get_result();
    $email = null;
    if ($row = $result->fetch_assoc()) {
        $email = [
            'id' => $row['id'], 
            'speaker_email' => $row['speaker_email'],
            'admin_email' => $row['admin_email'],
            'subject' => $row['subject'],
            'body' => $row['body'],
            'time_sent' => $row['time_sent'],
            'next_email' => $row['next_email'],
        ];
    }
    $stmt->close();
    $conn->close();
    return $email;
}

function storeSentEmail( $admin_email, $speaker_email, $subject, $body ) {
    $query = "INSERT INTO dbemails (admin_email, speaker_email, subject, body, time_sent) VALUES (?, ?, ?, ?, NOW())";
    $conn = connect();
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $admin_email, $speaker_email, $subject, $body);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

function addEmailChainLink( $emailID, $nextEmailID ) {
    $query = "UPDATE dbemails SET next_email = ? WHERE id = ?";
    $conn = connect();
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $nextEmailID, $emailID);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}