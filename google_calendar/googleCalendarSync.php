<?php
require_once 'googleClient.php';
require_once __DIR__ . '/../database/dbinfo.php';

function sync_event_to_google(int $event_id): void {
    $db = connect();

    // Load user tokens
    $stmt = $db->prepare("SELECT * FROM dbpersons WHERE google_access_token IS NOT NULL");
    $stmt->execute();
    $users = $stmt->get_result();
    


    // Load event
    $stmt = $db->prepare("SELECT * FROM dbevents WHERE id = ?");
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();

    if (!$event) return;
    
    // Build authorized client
    $client = get_google_client();
    foreach($users as $user) {
        $client->setAccessToken([
            'access_token'  => $user['google_access_token'],
            'refresh_token' => $user['google_refresh_token'],
            'expires_in'    => strtotime($user['google_token_expires_at']) - time(),
        ]);


        // Refresh token if expired
        if ($client->isAccessTokenExpired()) {
            $new_token = $client->fetchAccessTokenWithRefreshToken($user['google_refresh_token']);
            $expires_at = date('Y-m-d H:i:s', time() + $new_token['expires_in']);
            $stmt = $db->prepare("UPDATE dbpersons SET google_access_token = ?, google_token_expires_at = ? WHERE id = ?");
            $stmt->bind_param('sss', $new_token['access_token'], $expires_at, $user['id']);
            $stmt->execute();
            $client->setAccessToken($new_token);
        }

        $service = new Google\Service\Calendar($client);
        $cal_id  = $user['google_calendar_id'] ?? 'primary';

        // Combine date + startTime/endTime into full datetime strings
        // date is like "2026-03-31", startTime/endTime are like "09:00"
        $start_datetime = new DateTime($event['date'] . ' ' . $event['startTime']);
        $end_datetime   = new DateTime($event['date'] . ' ' . $event['endTime']);

        // Build the Google Event
        $google_event = new Google\Service\Calendar\Event([
            'summary'     => $event['name'],
            'description' => $event['description'] ?? '',
            'start' => [
                'dateTime' => $start_datetime->format(DateTime::RFC3339),
                'timeZone' => 'America/New_York',
            ],
            'end' => [
                'dateTime' => $end_datetime->format(DateTime::RFC3339),
                'timeZone' => 'America/New_York',
            ],
        ]);

        if (!empty($event['google_event_id'])) {
            // Update existing Google Calendar event
            $service->events->update($cal_id, $event['google_event_id'], $google_event);
        } else {
            // Create new and store the returned Google event ID
            $created = $service->events->insert($cal_id, $google_event);
            $google_event_id = $created->getId();
            $stmt = $db->prepare("UPDATE dbevents SET google_event_id = ? WHERE id = ?");
            $stmt->bind_param('si', $google_event_id, $event_id);
            $stmt->execute();
        }
    }
}