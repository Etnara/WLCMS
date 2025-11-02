<?php 
/*
    Many to many table of speakers to board members and board member's communications with them
*/
require_once('database/dbinfo.php');
date_default_timezone_set("America/New_York");

function getAllCommunicationsFor($speaker_email){
    $query = "SELECT admin_email, date FROM dbcommunications WHERE speaker_email = ? ORDER BY date DESC";
    $conn = connect();
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $speaker_email);
    $stmt->execute();
    $result = $stmt->get_result();

    $allCommunications = [];

    while ($row = $result->fetch_assoc()) {
        $formattedDate = date("m/d/Y", strtotime($row['date']));
        $allCommunications[] = [$row['admin_email'], $formattedDate];
    }

    $stmt->close();
    $conn->close();
    return $allCommunications;
}

function addCommunication($admin_email, $speaker_email){
    $date = date('y-m-d');
    $query = 'Select * from dbcommunications where admin_email = ? and speaker_email = ? and date = ' . $date;
    $conn = connect();
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $admin_email, $speaker_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $stmt->close();
        $conn->close();
        return;
    }

    $query = 'insert into dbcommunications values(?, ?, '. $date . ')';
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $admin_email, $speaker_email);
    $stmt->execute();

    $stmt->close();
    $conn->close();
}

?>