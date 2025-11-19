<?php

//One to Many speaker-to-month table

require_once("dbinfo.php");

function addMonth($speakerID, $month){
    $query = 'select * from speaker_months where id=? and month=?';
    $conn = connect();
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $speakerID, $month);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $stmt->close();
        $conn->close();
        return;
    }

    $query = 'insert into speaker_months(id, month) values (?, ?)';
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $speakerID, $month);
    $stmt->execute();

    $stmt->close();
    $conn->close();
}

function rejectedSpeakerMonthsRemoval($speakerID){
    $query = 'select month from speaker_months where id=?';
    $conn = connect();
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $speakerID);
    $stmt->execute();

    $result = $stmt->get_result();
    $months = array_column($result->fetch_all(MYSQLI_ASSOC), "month");

    foreach($months as $month){
        $query = 'delete from speaker_months where id=? and month=?';
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ss', $speakerID, $month);
        $stmt->execute();
    }
    $stmt->close();
    $conn->close();
}

function getAllMonthsFor($speakerID){
    $query = 'select month from speaker_months where id=? 
    order by field(month,"January","February","March","April","May","June","July","August","September","October","November","December")';
    $conn = connect();
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $speakerID);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $months = array_column($result->fetch_all(MYSQLI_ASSOC), "month");
    
    $stmt->close();
    $conn->close();

    return $months;
}

function getAllSpeakersFor($month){
    //$query = 'select id from speaker_months where month=?'; 
    $query = "select id from speaker_months where month like CONCAT('%', ?, '%')";
    $conn = connect();
    $month = ucfirst($month);
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s',$month);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $speakers = array_column($result->fetch_all(MYSQLI_ASSOC), "id");
    
    $stmt->close();
    $conn->close();

    return $speakers;
}

?>