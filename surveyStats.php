<?php
session_cache_expire(30);
session_start();

$loggedIn   = isset($_SESSION['_id']);
$accessLevel= $loggedIn ? ($_SESSION['access_level'] ?? 0) : 0;
if ($accessLevel < 2) { header('Location: index.php'); die(); }

require_once 'database/dbinfo.php';
$con = connect();

// date filters
$start = $_GET['start'] ?? '';
$end   = $_GET['end'] ?? '';

$filterSQL = "";
if ($start && $end) {
    $startEsc = $con->real_escape_string($start);
    $endEsc   = $con->real_escape_string($end);
    $filterSQL = "WHERE talk_date BETWEEN '$startEsc' AND '$endEsc'";
}

// sorting
$sort = $_GET['sort'] ?? 'avg_rating';
$direction = $_GET['direction'] ?? 'desc';
if (!in_array($direction, ['asc', 'desc'])) $direction = 'desc';

// sortable columns
$sortableCols = [
    'speaker_name'   => 'speaker_name',
    'survey_count'   => 'survey_count',
    'avg_rating'     => 'avg_rating',
    'topic_title'    => 'topic_title'
];

// pick the correct column
$sortKey = $sortableCols[$sort] ?? 'avg_rating';

// SPEAKER ratings 
$speakers = $con->query("
    SELECT speaker_name,
           COUNT(*) AS survey_count,
           AVG(speaker_rating) AS avg_rating
    FROM dbsurveys
    $filterSQL
    GROUP BY speaker_name
    ORDER BY $sortKey $direction
");

//  TOPIC ratings 
$topics = $con->query("
    SELECT topic_title,
           COUNT(*) AS survey_count,
           AVG(topic_rating) AS avg_rating
    FROM dbsurveys
    $filterSQL
    GROUP BY topic_title
    ORDER BY $sortKey $direction
");

// sorting arrow helper thingy
function sortLink($col, $label) {
    global $sort, $direction, $start, $end;

    $newDir = ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';

    $arrow = '';
    if ($sort === $col) {
        $arrow = ($direction === 'asc')
            ? ' <span style="font-weight:700;color:#111;">▲</span>'
            : ' <span style="font-weight:700;color:#111;">▼</span>';
    }

    return "<a href=\"?sort=$col&direction=$newDir&start=$start&end=$end\" 
              style=\"color:#111;text-decoration:none;\">$label$arrow</a>";
}

?>

<!-- BANDAID FIX FOR HEADER BEING WEIRD -->
<?php
$tailwind_mode = true;
require_once('header.php');
?>
<link href="css/normal_tw.css" rel="stylesheet">
<style>
    .date-box {
        background: #800000;
        padding: 7px 30px;
        border-radius: 50px;
        box-shadow: -4px 4px 4px rgba(0, 0, 0, 0.25) inset;
        color: white;
        font-size: 24px;
        font-weight: 700;
        text-align: center;
    }
    
    .dropdown {
        padding-right: 50px;
    }
</style>
<!-- BANDAID END, REMOVE ONCE SOME GENIUS FIXES -->

<!DOCTYPE html>
<html>
<head>
    <title>Survey Statistics</title>
    <link rel="icon" type="image/x-icon" href="images/real-women-logo.webp">
    <link href="css/normal_tw.css" rel="stylesheet">

    <style>
        th a { font-weight: 600; }
        th a:hover { color: #374151; }
    </style>
</head>

<body>
<header class="hero-header">
    <div class="center-header"><h1>Survey Statistics</h1></div>
</header>

<main>
<div class="main-content-box w-[80%] p-8 mb-8">

    <!-- Return button -->
    <div class="flex justify-center gap-8 mb-8">
        <a href="index.php" class="return-button">Return to Dashboard</a>
    </div>

    <!-- Date filter -->
    <h3 class="mb-4">Filter by Date</h3>
    <form method="GET" class="flex gap-4 mb-10 items-end">
        <div>
            <label class="font-semibold">Start:</label>
            <input type="date" name="start" value="<?= htmlspecialchars($start) ?>" class="border p-2 rounded">
        </div>

        <div>
            <label class="font-semibold">End:</label>
            <input type="date" name="end" value="<?= htmlspecialchars($end) ?>" class="border p-2 rounded">
        </div>

        <button class="blue-button" style="height:42px;">Apply</button>
    </form>

    <!-- SPEAKER AVG -->
    <h3 class="mb-4">Speaker Ratings</h3>

    <div class="overflow-x-auto mb-10">
        <table>
            <thead class="bg-blue-400">
                <tr>
                    <th><?= sortLink('speaker_name', 'Speaker') ?></th>
                    <th><?= sortLink('survey_count', '# Surveys') ?></th>
                    <th><?= sortLink('avg_rating', 'Average Rating') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($speakers && $speakers->num_rows > 0): ?>
                <?php while ($s = $speakers->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['speaker_name']) ?></td>
                        <td><?= (int)$s['survey_count'] ?></td>
                        <td><?= round((float)$s['avg_rating'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3" class="text-center py-4">No data.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>


    <!-- TOPIC AVG -->
    <h3 class="mb-4">Topic Ratings</h3>

    <div class="overflow-x-auto mb-10">
        <table>
            <thead class="bg-blue-400">
                <tr>
                    <th><?= sortLink('topic_title', 'Topic') ?></th>
                    <th><?= sortLink('survey_count', '# Surveys') ?></th>
                    <th><?= sortLink('avg_rating', 'Average Rating') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($topics && $topics->num_rows > 0): ?>
                <?php while ($t = $topics->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['topic_title']) ?></td>
                        <td><?= (int)$t['survey_count'] ?></td>
                        <td><?= round((float)$t['avg_rating'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3" class="text-center py-4">No data.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</main>

<script>
// restore last scroll position so no weird reloads
document.addEventListener("DOMContentLoaded", function() {
    const scrollPos = sessionStorage.getItem("surveyScroll");
    if (scrollPos) {
        window.scrollTo(0, parseInt(scrollPos));
        sessionStorage.removeItem("surveyScroll");
    }
});

// save scroll position before clicking sort
document.querySelectorAll("th a").forEach(a => {
    a.addEventListener("click", function() {
        sessionStorage.setItem("surveyScroll", window.scrollY);
    });
});
</script>


</body>
</html>
