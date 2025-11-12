<?php
header('Content-Type: application/json');

require_once __DIR__ . '/database/dbPersons.php';

// Database connection (adjust as needed)


$q = isset($_GET['q']) ? $_GET['q'] : '';
if ($q === '') {
  echo json_encode([]);
  exit;
}
$result = search_speakers($q);
if (isset($result['message']) && $result['message'] === 'worked') {
    $merged = [];

    // Add all name matches
    foreach ($result['names'] as $n) {
        // handle combined names like "first_name last_name" or alias 'name'
        if (isset($n['name'])) {
            $merged[] = $n['name'];
        } elseif (isset($n['first_name']) && isset($n['last_name'])) {
            $merged[] = $n['first_name'] . ' ' . $n['last_name'];
        }
    }

    // Add all topic matches
    foreach ($result['topics'] as $t) {
        $merged[] = $t['topic'];
    }

    // Remove duplicates and reindex
    $merged = array_values(array_unique($merged));

    echo json_encode($merged);
} else {
    echo json_encode([]);
}