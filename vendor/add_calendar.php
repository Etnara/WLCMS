<?php
require_once __DIR__.'/autoload.php';

if ($argc < 2) {
    echo "Supply the calendar name as an argument\n";
    exit;
}

$calendarId = $argv[1];

$client = new Google_Client();
$client->setAuthConfig('C:\Users\mikey\OneDrive\Desktop\wlcms-479913-421d47904d97.json');

$client->setScopes('https://www.googleapis.com/auth/calendar');
$client->setApplicationName("WLCMS Calendar");

$service = new Google_Service_Calendar($client); 

$calendarListEntry = new Google_Service_Calendar_CalendarListEntry();
$calendarListEntry->setId($calendarId);

$service->calendarList->insert($calendarListEntry);

$calendarList = $service->calendarList->listCalendarList();

while(true) {
  foreach ($calendarList->getItems() as $calendarListEntry) {
    echo $calendarListEntry->getSummary() . "\n";
  }
  $pageToken = $calendarList->getNextPageToken();
  if ($pageToken) {
    $optParams = array('pageToken' => $pageToken);
    $calendarList = $service->calendarList->listCalendarList($optParams);
  } else {
    break;
  }
}
?>