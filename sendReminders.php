<?php 
/* 
This file runs off of a daily cron job in SiteGround. It takes the current day and checks if there 
is an event a day/week from now and sends a reminder email to the scheduled speaker.
*/

include_once('sendEmail.php');
include_once('database/dbinfo.php');
include_once('database/dbEvents.php');
include_once('database/dbPersons.php');

$today = new DateTime(); //today's date

//Check for all events with a speaker that are a week from now
$targetWeekAhead = (clone $today)->modify(modifier: '+7 days');
$targetWeekAheadStr = $targetWeekAhead->format('Y-m-d');

$query = "select speaker from dbevents where date=? and speaker is not null";

$conn = connect();

$stmt = $conn->prepare($query);
$stmt->bind_param('s', $targetWeekAheadStr);
$stmt->execute();

$resultWeekAhead = $stmt->get_result();

while($row = $resultWeekAhead->fetch_assoc()){
    $speaker = retrieve_person($row['speaker']);
    speakerReminder($speaker->get_email(), $speaker->get_first_name(), $speaker->get_last_name(), $targetWeekAhead);
}


//Check for all events with a speaker that are a day from now
$targetDayAhead = (clone $today)->modify(modifier: '+1 days');
$targetDayAheadStr = $targetDayAhead->format('Y-m-d');

$query = "select speaker from dbevents where date=? and speaker is not null";

$stmt = $conn->prepare($query);
$stmt->bind_param('s', $targetDayAheadStr);
$stmt->execute();

$resultDayAhead = $stmt->get_result();
while($row = $resultDayAhead->fetch_assoc()){
    $speaker = retrieve_person($row['speaker']);
    speakerReminder($speaker->get_email(), $speaker->get_first_name(), $speaker->get_last_name(), $targetDayAhead);
}

$conn->close();
$stmt->close();



