<?php
header('Content-Type: application/json');

require_once 'database/dbPersons.php';
require_once 'include/input-validation.php';
require_once 'database/dbCommunications.php';
require_once('include/output.php');
require_once 'database/dbSpeaker_Months.php';
session_cache_expire(30);
session_start();
$admin = retrieve_person($_SESSION['_id']);

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$merged = [];

if ($q === '') {
    //get all
    $con = connect();
    $all = mysqli_query($con, "SELECT id FROM dbpersons WHERE status = 'Accepted Speaker' ORDER BY last_name ASC");

    while ($row = mysqli_fetch_assoc($all)) {
        $merged[] = $row['id'];
    }
} else{
    //get specific
    $result = search_speakers($q);

    if (isset($result['message']) && $result['message'] === 'worked') {
        foreach ($result['names'] as $n) {
            $merged[] = $n['id'];
        }

        foreach ($result['topics'] as $t) {
            $merged[] = $t['speaker'];
        }

        $merged = array_values(array_unique($merged));
    } else {
        echo '<div class="error-block">There was an error loading results.</div>';
        exit;
    }
    
    $result = getAllSpeakersFor($q);   

    if ($result != []){
        foreach ($result as $r){
            $merged[] = $r;
        }
        $merged = array_values(array_unique($merged));
    }
}

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

    $months = getAllMonthsFor($person_id);
    /*while ($row = mysqli_fetch_assoc($monthsResult)) {
        $months[] = $row['month'];
    }*/

    $data[] = [
        'id' => $person_id,
        'headshot' => $rawPerson['headshot'],
        'mime' => $rawPerson['mime'],
        'name' => $rawPerson['first_name'] . ' ' . $rawPerson['last_name'],
        'email' => $rawPerson['email'],
        'phone' => $rawPerson['phone1'],
        'months' => implode(', ', $months),
        'topics' => implode(', ', $topics),
        'notes' => $rawPerson['notes']
    ];
}
$table = '<table>
        <thead class="bg-blue-400">
            <tr>
            <th>Headshot</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Months Available</th>
            <th>Topics</th>
            <th>Notes</th>
            <th></th>
            </tr>
        </thead>
        <tbody>';

$exist = false;

foreach ($data as $row) {
    $exist = true;

    $table .= '<tr>';
    $headshot_row = '';
    if ($row['mime'] != NULL && $row['mime'] !== ''){
        $headshot_row = '<img src="getHeadshot.php?id=' . $row['id'] .'" class="block max-w-full h-auto mx-auto">';
    }
    $table .= '<td>' . $headshot_row . '</td>';
    $table .= '<td>' . htmlspecialchars($row['name']) . '</td>';
    $table .= '<td><a href="mailto:' . htmlspecialchars($row['email']) . 
            '" class="text-blue-700 underline" onclick="addNewCommunication(\'' . $admin->get_email() . '\', \'' 
            . htmlspecialchars($row['email']) . '\'); return false;">' . htmlspecialchars($row['email']) . '</a></td>';
    $table .= '<td><a href="tel:' . $row['phone'] . '" class="text-blue-700 underline">' . 
            formatPhoneNumber($row['phone']) . '</a></td>';
    $table .= '<td>' . htmlspecialchars($row['months'] ?? '') . '</td>';
    $table .= '<td>' . htmlspecialchars($row['topics'] ?? '') . '</td>';
    $table .= '<td>' . htmlspecialchars($row['notes'] ?? '') . '</td>';
    $table .= '<td><a href="viewProfile.php?id=' . htmlspecialchars($row['id']) . 
            '" class="text-blue-700 underline">Edit</a></td>';
    $table .= '</tr>';
}

if (!$exist) {
    $table .= '<tr><td colspan="7"><div class="error-block">Your search returned no results.</div></td></tr>';
}

$table .= '</tbody></table>';

echo $table; 