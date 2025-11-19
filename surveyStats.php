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

// search filters
$searchType = $_GET['searchType'] ?? 'speaker'; // 'speaker' or 'topic'
$searchQuery = $_GET['searchQuery'] ?? '';

$invalidDate = false;

// invalid if only one date chosen
if (($start && !$end) || (!$start && $end)) {
    $invalidDate = true;
    $filterSQL = "";
} else {
    $filterSQL = "";
    if ($start && $end) {
        $startEsc = $con->real_escape_string($start);
        $endEsc   = $con->real_escape_string($end);
        $filterSQL = "WHERE talk_date BETWEEN '$startEsc' AND '$endEsc'";
    }
}

// build search filter
$searchFilter = "";
if ($searchQuery) {
    $searchEsc = $con->real_escape_string($searchQuery);
    if ($searchType === 'speaker') {
        $searchFilter = $filterSQL ? " AND speaker_name LIKE '%$searchEsc%'" : " WHERE speaker_name LIKE '%$searchEsc%'";
    } elseif ($searchType === 'topic') {
        $searchFilter = $filterSQL ? " AND topic_title LIKE '%$searchEsc%'" : " WHERE topic_title LIKE '%$searchEsc%'";
    }
}

//speaker sort
$sortS = $_GET['sortS'] ?? 'avg_rating';
$directionS = $_GET['directionS'] ?? 'desc';
if (!in_array($directionS, ['asc', 'desc'])) $directionS = 'desc';

$sortableSpeaker = [
    'speaker_name' => 'speaker_name',
    'avg_rating'   => 'avg_rating'
];

$sortKeyS = $sortableSpeaker[$sortS] ?? 'avg_rating';

//topic sort
$sortT = $_GET['sortT'] ?? 'avg_rating';
$directionT = $_GET['directionT'] ?? 'desc';
if (!in_array($directionT, ['asc', 'desc'])) $directionT = 'desc';

$sortableTopic = [
    'topic_title' => 'topic_title',
    'avg_rating'  => 'avg_rating'
];

$sortKeyT = $sortableTopic[$sortT] ?? 'avg_rating';

//speaker sorting
$speakers = $con->query("
    SELECT speaker_name,
           COUNT(*) AS survey_count,
           AVG(speaker_rating) AS avg_rating
    FROM dbsurveys
    $filterSQL $searchFilter
    GROUP BY speaker_name
    ORDER BY $sortKeyS $directionS
");

//topic sorting
$topics = $con->query("
    SELECT topic_title,
           COUNT(*) AS survey_count,
           AVG(topic_rating) AS avg_rating
    FROM dbsurveys
    $filterSQL $searchFilter
    GROUP BY topic_title
    ORDER BY $sortKeyT $directionT
");

// sorting
function sortLinkSpeaker($col, $label) {
    // preserve topic sort when clicking speaker sort
    global $sortS, $directionS, $sortT, $directionT, $start, $end, $searchType, $searchQuery;

    $newDir = ($sortS === $col && $directionS === 'asc') ? 'desc' : 'asc';

    $arrow = '';
    if ($sortS === $col) {
        $arrow = $directionS === 'asc' ? ' ▲' : ' ▼';
    }

    return "<a href=\"?sortS=$col&directionS=$newDir&sortT=$sortT&directionT=$directionT&start=$start&end=$end&searchType=$searchType&searchQuery=$searchQuery\" style=\"color:#111;text-decoration:none;\">$label$arrow</a>";
}

function sortLinkTopic($col, $label) {
    // preserve speaker sort when clicking topic sort
    global $sortT, $directionT, $sortS, $directionS, $start, $end, $searchType, $searchQuery;

    $newDir = ($sortT === $col && $directionT === 'asc') ? 'desc' : 'asc';

    $arrow = '';
    if ($sortT === $col) {
        $arrow = $directionT === 'asc' ? ' ▲' : ' ▼';
    }

    return "<a href=\"?sortT=$col&directionT=$newDir&sortS=$sortS&directionS=$directionS&start=$start&end=$end&searchType=$searchType&searchQuery=$searchQuery\" style=\"color:#111;text-decoration:none;\">$label$arrow</a>";
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
        box-shadow: -4px 4px 4px rgba(0,0,0,0.25) inset;
        color: white;
        font-size: 24px;
        font-weight: 700;
        text-align: center;
    }

    th a { font-weight: 600; }
    th a:hover { color: #374151; }

    th { padding: 6px !important; }
    td { padding: 6px !important; }

    .col-wide { width: 65%; }
    .col-narrow { width: 35%; text-align:center; }

    /* popup */
    .popup {
      position: absolute;
      top: 260px;
      left: 50%;
      transform: translateX(-50%);
      padding: 16px 24px;
      border-radius: 8px;
      color: white;
      font-weight: 500;
      font-size: 1rem;
      opacity: 0;
      animation: fadeInOut 4s forwards;
      z-index: 100;
    }
    .popup.err { background-color: #b91c1c; }

    @keyframes fadeInOut {
      0% { opacity: 0; transform: translateX(-50%) translateY(-10px); }
      10%, 90% { opacity: 1; transform: translateX(-50%) translateY(0); }
      100% { opacity: 0; transform: translateX(-50%) translateY(-10px); }
    }
</style>

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
    /*
    .hero-header{
        background-color: #800000; 
    }*/
    
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
</head>

<body>
<header class="hero-header">
    <div class="center-header"><h1>Survey Statistics</h1></div>
</header>

<?php if ($invalidDate): ?>
    <div class="popup err">Please select BOTH a start and end date to filter.</div>
<?php endif; ?>

<main>
    <div class="main-content-box w-[60%] p-8 mb-8">


    <div class="flex justify-center gap-8 mb-8">
        <a href="index.php" class="return-button">Return to Dashboard</a>
    </div>


    <!-- date boxy thing -->
    <div class="p-4 mb-10 rounded-xl shadow-md border border-gray-300 bg-white">
        <form method="GET" class="flex flex-col gap-8 w-full">

            <!-- date section -->
            <div class="flex flex-wrap gap-6 justify-center">

                <div>
                    <label class="font-semibold block mb-1">Start:</label>
                    <input type="date" name="start" value="<?= htmlspecialchars($start) ?>"
                        class="border p-2 rounded w-[180px]">
                </div>

                <div>
                    <label class="font-semibold block mb-1">End:</label>
                    <input type="date" name="end" value="<?= htmlspecialchars($end) ?>"
                        class="border p-2 rounded w-[180px]">
                </div>

                <div class="flex flex-col justify-end">
                    <label class="mb-1 text-white select-none">.</label>
                    <button class="blue-button px-6" style="height:40px;">Apply</button>
                </div>

                <div class="flex flex-col justify-end">
                    <label class="mb-1 text-white select-none">.</label>
                    <a href="surveyStats.php" class="blue-button px-6" 
                    style="height:40px; background:#6b7280;">Clear</a>
                </div>

            </div>


            <!-- search section -->
            <div class="flex flex-wrap gap-6 justify-center">

                <div>
                    <label class="font-semibold block mb-1">Search Type:</label>
                    <select name="searchType" class="border p-2 rounded w-[180px]">
                        <option value="speaker" <?= $searchType === 'speaker' ? 'selected' : '' ?>>Speaker Name</option>
                        <option value="topic" <?= $searchType === 'topic' ? 'selected' : '' ?>>Topic Name</option>
                    </select>
                </div>

                <div>
                    <label class="font-semibold block mb-1">Search:</label>
                    <input type="text" name="searchQuery" value="<?= htmlspecialchars($searchQuery) ?>"
                        placeholder="Enter name..." class="border p-2 rounded w-[200px]">
                </div>

                <div class="flex flex-col justify-end">
                    <label class="mb-1 text-white select-none">.</label>
                    <button class="blue-button px-6" style="height:40px;">Search</button>
                </div>

                <div class="flex flex-col justify-end">
                    <label class="mb-1 text-white select-none">.</label>
                    <a href="surveyStats.php" class="blue-button px-6" 
                    style="height:40px; background:#6b7280;">Clear </a>
                </div>

            </div>

        </form>

    </div> 


    <!-- SPEAKER table -->
    <h3 class="mb-4">Speaker Ratings</h3>

    <div class="overflow-x-auto mb-10">
        <table class="mx-auto w-[70%] rounded-lg overflow-hidden border border-gray-300">
            <thead class="bg-blue-400">
                <tr>
                    <th class="col-wide"><?= sortLinkSpeaker('speaker_name', 'Speaker') ?></th>
                    <th class="col-narrow"><?= sortLinkSpeaker('avg_rating', 'Average Rating') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($speakers && $speakers->num_rows > 0): ?>
                <?php while ($s = $speakers->fetch_assoc()): ?>
                    <tr>
                        <td class="col-wide"><?= htmlspecialchars($s['speaker_name']) ?></td>
                        <td class="col-narrow"><?= round((float)$s['avg_rating'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="2" class="text-center py-4">No data.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- TOPIC table -->
    <h3 class="mb-4">Topic Ratings</h3>

    <div class="overflow-x-auto mb-10">
        <table class="mx-auto w-[70%] rounded-lg overflow-hidden border border-gray-300">
            <thead class="bg-blue-400">
                <tr>
                    <th class="col-wide"><?= sortLinkTopic('topic_title', 'Topic') ?></th>
                    <th class="col-narrow"><?= sortLinkTopic('avg_rating', 'Average Rating') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($topics && $topics->num_rows > 0): ?>
                <?php while ($t = $topics->fetch_assoc()): ?>
                    <tr>
                        <td class="col-wide"><?= htmlspecialchars($t['topic_title']) ?></td>
                        <td class="col-narrow"><?= round((float)$t['avg_rating'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="2" class="text-center py-4">No data.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</main>

<script>
// restore scroll
document.addEventListener("DOMContentLoaded", function() {
    const scrollPos = sessionStorage.getItem("surveyScroll");
    if (scrollPos) {
        window.scrollTo(0, parseInt(scrollPos));
        sessionStorage.removeItem("surveyScroll");
    }
});

// save scroll before sorting
document.querySelectorAll("th a").forEach(a => {
    a.addEventListener("click", function() {
        sessionStorage.setItem("surveyScroll", window.scrollY);
    });
});

// remove invalid params so popup doesn't repeat
<?php if ($invalidDate): ?>
document.addEventListener("DOMContentLoaded", function() {
    const url = new URL(window.location);
    url.searchParams.delete("start");
    url.searchParams.delete("end");
    window.history.replaceState({}, document.title, url.pathname + url.search);
});
<?php endif; ?>
</script>

</body>
</html>
