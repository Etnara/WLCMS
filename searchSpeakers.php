<?php
header('Content-Type: application/json');

require_once __DIR__ . '/database/dbPersons.php';
require_once 'include/input-validation.php';
require_once 'database/dbCommunications.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
    echo json_encode([]);
    exit;
}

$result = search_speakers($q);

if (isset($result['message']) && $result['message'] === 'worked') {
    $merged = [];

    foreach ($result['names'] as $n) {
        $merged[] = $n['id'];
    }

    foreach ($result['topics'] as $t) {
        $merged[] = $t['speaker'];
    }

    $merged = array_values(array_unique($merged));

    $data = [];

    foreach ($merged as $person_id) {
        $con = connect();
        $query = "SELECT * FROM dbpersons WHERE id='$person_id'";
        $rawPerson = mysqli_query($con, $query)->fetch_assoc();

        $topicsResult = mysqli_query($con, "SELECT topic FROM speaker_topics WHERE speaker='$person_id'");
        $topics = [];
        while ($row = mysqli_fetch_assoc($topicsResult)) {
            $topics[] = $row['topic'];
        }

        $data[] = [
            'id' => $person_id,
            'name' => $rawPerson['first_name'] . ' ' . $rawPerson['last_name'],
            'email' => $rawPerson['email'],
            'phone' => $rawPerson['phone1'],
            'topics' => implode(', ', $topics),
            'notes' => $rawPerson['notes']
        ];
    }

    echo json_encode($data);
} else {
    echo json_encode([]);
}