<?php
session_cache_expire(30);
session_start();

$loggedIn   = isset($_SESSION['_id']);
$accessLevel= $loggedIn ? ($_SESSION['access_level'] ?? 0) : 0;
if ($accessLevel < 2) { header('Location: index.php'); die(); }

require_once 'database/dbinfo.php';

$con = connect();

$ok = $err = null;
// show messsages once
if (isset($_GET['ok'])) $ok = $_GET['ok'];
if (isset($_GET['err'])) $err = $_GET['err'];

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

// !updated! to handle csv files
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
        $err = 'Invalid request token.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'upload') {

            if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
                $err = 'Please choose a CSV file.';
            } else {
                $file = $_FILES['csv'];
                $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if ($ext !== 'csv') {
                    $err = 'File must be a .csv';
                } else {
                    $data = file_get_contents($file['tmp_name']);
                    if ($data === false || $data === '') {
                        $err = 'Could not read uploaded CSV.';
                    } else {
                        $handle = fopen($file['tmp_name'], "r");
                        if (!$handle) {
                            header("Location: " . $_SERVER['PHP_SELF'] . "?err=" . urlencode('Could not open CSV.'));
                            exit;
                        }

                        // read header row for the categories (like search for ratings etc)
                        $header = fgetcsv($handle, escape: '');
                        if (!$header) {
                            fclose($handle);
                            header("Location: " . $_SERVER['PHP_SELF'] . "?err=" . urlencode('CSV appears to have no header row.'));
                            exit;
                        }

                        // normalize headers to lowercase 
                        $normalized = [];
                        foreach ($header as $idx => $col) {
                            $key = strtolower(trim($col));
                            if ($key !== '') {
                                $normalized[$key] = $idx;
                            }
                        }

                        // helper to resolve column index by possible names
                        $resolveIndex = function(array $normalized, array $candidates) {
                            foreach ($candidates as $c) {
                                $k = strtolower(trim($c));
                                if (isset($normalized[$k])) {
                                    return $normalized[$k];
                                }
                            }
                            return null;
                        };

                        // try to find Speaker Rating and Topic Rating columns
                        $speakerIdx = $resolveIndex($normalized, [
                            'speaker rating', 'speaker_rating', 'speaker score', 'speaker'
                        ]);
                        $topicIdx = $resolveIndex($normalized, [
                            'topic rating', 'topic_rating', 'topic score', 'topic'
                        ]);

                        if ($speakerIdx === null || $topicIdx === null) {
                            fclose($handle);
                            header("Location: " . $_SERVER['PHP_SELF'] . "?err=" . urlencode("CSV must contain 'Speaker Rating' and 'Topic Rating' header columns."));
                            exit;
                        }

                        $speakerTotal = 0;
                        $speakerCount = 0;
                        $topicTotal   = 0;
                        $topicCount   = 0;

                        // read each row and accumulate ratings if numeric
                        while (($row = fgetcsv($handle, escape: '')) !== false) {
                            // Speaker rating
                            if (isset($row[$speakerIdx]) && $row[$speakerIdx] !== '' && is_numeric($row[$speakerIdx])) {
                                $speakerTotal += (float)$row[$speakerIdx];
                                $speakerCount++;
                            }
                            // Topic rating
                            if (isset($row[$topicIdx]) && $row[$topicIdx] !== '' && is_numeric($row[$topicIdx])) {
                                $topicTotal += (float)$row[$topicIdx];
                                $topicCount++;
                            }
                        }
                        fclose($handle);

                        if ($speakerCount === 0 || $topicCount === 0) {
                            header("Location: " . $_SERVER['PHP_SELF'] . "?err=" . urlencode('No valid Speaker Rating and/or Topic Rating values found in CSV.'));
                            exit;
                        }

                        $speakerAvg = $speakerTotal / $speakerCount;
                        $topicAvg   = $topicTotal / $topicCount;

                        $storedSpeakerRating = (int)round($speakerAvg);
                        $storedTopicRating   = (int)round($topicAvg);

                        /* ---- EVENT selecting ---- */
                        $event_id = intval($_POST['event_id']);

                        // pull event speaker & topic
                        $eventQ = $con->prepare("SELECT name, description, date, speaker FROM dbevents WHERE id = ?");
                        $eventQ->bind_param("i", $event_id);
                        $eventQ->execute();
                        $eventData = $eventQ->get_result()->fetch_assoc();

                        if (!$eventData) {
                            header("Location: " . $_SERVER['PHP_SELF'] . "?err=" . urlencode('Invalid event selected.'));
                            exit;
                        }

                        $eventName   = $eventData['name'];
                        $description = $eventData['description'];
                        $talkDate    = $eventData['date'];
                        $speakerID   = $eventData['speaker']; // persons ID

                        // lookup speaker name for posting
                        $speakerName = null;

                        if (!empty($speakerID) && $speakerID !== "null") {
                            $speakerQ = $con->prepare("SELECT first_name, last_name FROM dbpersons WHERE id = ?");
                            // dbpersons.id is varchar
                            $speakerQ->bind_param("s", $speakerID);
                            $speakerQ->execute();
                            $sp = $speakerQ->get_result()->fetch_assoc();
                            if ($sp) {
                                $speakerName = $sp['first_name'] . ' ' . $sp['last_name'];
                            }
                        }

                        // show blank if speaker not set
                        if (!$speakerName) {
                            $speakerName = 'No Speaker Assigned';
                        }

                        // insert in dbsurveys
                        $stmt = $con->prepare("
                            INSERT INTO dbsurveys
                            (filename, mime, content, speaker_name, topic_title, talk_date, speaker_rating, topic_rating)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ");

                        $filename    = basename($file['name']);
                        $mime        = "text/csv";

                        $stmt->bind_param(
                            "ssbsssii",
                            $filename,
                            $mime,
                            $data,
                            $speakerName,
                            $description,
                            $talkDate,
                            $storedSpeakerRating,
                            $storedTopicRating
                        );

                        $stmt->send_long_data(2, $data);

                        if ($stmt->execute()) {
                            $stmt->close();
                            header("Location: " . $_SERVER['PHP_SELF'] . "?ok=" . urlencode('Survey uploaded.'));
                            exit;
                        } else {
                            $stmt->close();
                            header("Location: " . $_SERVER['PHP_SELF'] . "?err=" . urlencode('Upload failed.'));
                            exit;
                        }
                    }
                }
            }

        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                if ($con->query("DELETE FROM dbsurveys WHERE id=$id")) {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?ok=" . urlencode('Survey deleted.'));
                    exit;
                } else {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?err=" . urlencode('Could not delete.'));
                    exit;
                }
            }
        }
    }
}

// pagination stuffs
$limit   = 10;
$pageNum = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($pageNum - 1) * $limit;

$sortBy    = $_GET['sort'] ?? 'uploaded_at';
$sortOrder = $_GET['order'] ?? 'desc';
if (!in_array($sortOrder, ['asc', 'desc'])) $sortOrder = 'desc';

$sortKeyMap = [
    'filename'    => 'filename',
    'uploaded_at' => 'uploaded_at',
];
$sortKey = $sortKeyMap[$sortBy] ?? 'uploaded_at';

$countRow  = $con->query("SELECT COUNT(*) as cnt FROM dbsurveys")->fetch_assoc();
$totalRows = $countRow['cnt'] ?? 0;
$totalPages = max(1, ceil($totalRows / $limit));

$query  = "
    SELECT 
        id, 
        filename, 
        speaker_name,
        topic_title,
        talk_date
    FROM dbsurveys
    ORDER BY $sortKey $sortOrder
    LIMIT $offset, $limit
";
$result = $con->query($query);

$list = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $list[] = $row;
    }
}

function sortLink($column, $label) {
    global $sortBy, $sortOrder, $pageNum;
    $newOrder = ($sortBy === $column && $sortOrder === 'asc') ? 'desc' : 'asc';
    $arrow    = ($sortBy === $column) ? ($sortOrder === 'asc' ? ' ▲' : ' ▼') : '';
    return '<a href="?sort=' . $column . '&order=' . $newOrder . '&page=' . $pageNum . '" style="text-decoration:none; cursor:pointer; color:inherit;">' . $label . $arrow . '</a>';
}

$tailwind_mode = true;
require_once('header.php');
?>

<?php
$tailwind_mode = true;
require_once('header.php');
?>
<style>
.date-box {
    padding: 7px 30px;
    border-radius: 50px;
    box-shadow: -4px 4px 4px rgba(0,0,0,0.25) inset;
    color: white;
    font-size: 24px;
    font-weight: 700;
    text-align: center;
}
.dropdown { padding-right:50px; }
.file-input {
    width:100%;
    border:1px solid #e5e7eb;
    background:#f3f4f6;
    color:#374151;
    border-radius:8px;
    padding:10px;
}
.file-input::file-selector-button {
    margin-right:12px;
    border:0;
    background:#9ca3af;
    color:#fff;
    padding:8px 12px;
    border-radius:6px;
    cursor:pointer;
}
.file-input::file-selector-button:hover { background:#6b7280; }
.file-meta { font-size:1.1rem; color:#4b5563; font-weight:700; }
.popup {
    position:absolute;
    top:320px;
    left:50%;
    transform:translateX(-50%);
    padding:16px 24px;
    border-radius:8px;
    color:white;
    font-weight:500;
    font-size:1rem;
    opacity:0;
    animation:fadeInOut 4s forwards;
    z-index:100;
}
.popup.ok  { background-color:#15803d; }
.popup.err { background-color:#b91c1c; }
@keyframes fadeInOut {
    0%   { opacity:0; transform:translateX(-50%) translateY(-10px); }
    10%,90% { opacity:1; transform:translateX(-50%) translateY(0); }
    100% { opacity:0; transform:translateX(-50%) translateY(-10px); }
}
.pagination {
    display:flex;
    gap:8px;
    justify-content:center;
    align-items:center;
    margin-top:1.5rem;
    padding:10px 0;
    flex-wrap:wrap;
}
.page-num {
    display:inline-block;
    padding:8px 10px;
    border-radius:6px;
    background:#f3f4f6;
    color:#111;
    border:1px solid #d1d5db;
    text-decoration:none;
    min-width:36px;
    text-align:center;
    font-weight:500;
    cursor:pointer;
    transition:all .2s;
}
.page-num:hover { background:#e5e7eb; }
.page-num.active {
    background:#800000;
    color:#fff;
    border-color:#800000;
    box-shadow:0 2px 8px rgba(128,0,0,.3);
}
.page-num:disabled {
    opacity:.5;
    cursor:default;
    background:#f3f4f6;
}
</style>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Coffee Talk Surveys</title>
  <link rel="icon" type="image/x-icon" href="images/real-women-logo.webp">
  <link href="css/normal_tw.css" rel="stylesheet">
</head>
<body>
<header class="hero-header">
  <div class="center-header"><h1>Coffee Talk Surveys</h1></div>
</header>

<?php if ($ok): ?>
  <div class="popup ok"><?= htmlspecialchars($ok) ?></div>
<?php endif; ?>
<?php if ($err): ?>
  <div class="popup err"><?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<main>
  <div class="main-content-box w-[80%] p-8 mb-8">

    <div class="flex justify-center gap-8 mb-8">
      <a href="index.php" class="return-button">Return to Dashboard</a>
    </div>

    <h3 class="mb-2">Upload a Survey (CSV)</h3>

    <form method="post" enctype="multipart/form-data" class="space-y-4 mb-10">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="upload">

      <label class="font-semibold">Select Coffee Talk Event:</label>
      <select name="event_id" class="border p-2 rounded w-full mb-3" required>
          <option value="" disabled selected>— Choose Event —</option>
          <?php
          $evQ = $con->query("SELECT id, name, date FROM dbevents ORDER BY date DESC");
          while ($ev = $evQ->fetch_assoc()):
          ?>
              <option value="<?= $ev['id'] ?>">
                  <?= htmlspecialchars($ev['date'] . " — " . $ev['name']) ?>
              </option>
          <?php endwhile; ?>
      </select>

      <div style="display: flex; align-items: center; gap: 10px;">
          <input 
              type="file" 
              name="csv" 
              accept=".csv" 
              class="file-input" 
              style="flex: 1;" 
              required
          >

          <button 
              type="submit" 
              class="blue-button"
              style="white-space: nowrap; padding: 10px 20px; margin-top: -3px; display: flex; align-items: center; justify-content: center;"
          >
              Upload
          </button>
      </div>

    </form>

    <h3 class="mb-2">Uploaded Surveys</h3>

    <div class="overflow-x-auto">
      <table>
        <thead class="bg-blue-400">
          <tr>
            <th><?= sortLink('filename', 'File') ?></th>
            <th>Event Date</th>
            <th>Speaker</th>
            <th>Topic</th>
            <th style="width:120px;"></th>
          </tr>
        </thead>
        <tbody>

        <?php if (!empty($list)): ?>
          <?php foreach ($list as $r): ?>
            <tr>
              <!-- File -->
              <td>
                <a class="text-blue-700 underline" href="surveyDownload.php?id=<?= (int)$r['id'] ?>" target="_blank">
                  <?= htmlspecialchars($r['filename']) ?>
                </a>
              </td>

              <!-- Event Date -->
              <td class="file-meta">
                  <?= $r['talk_date'] ? date('m/d/Y', strtotime($r['talk_date'])) : '—' ?>
              </td>

              <!-- Speaker -->
              <td class="file-meta">
                  <?= htmlspecialchars($r['speaker_name'] ?: '—') ?>
              </td>

              <!-- Topic -->
              <td class="file-meta">
                  <?= htmlspecialchars($r['topic_title'] ?: '—') ?>
              </td>

              <!-- Delete button -->
              <td>
                <form method="post" onsubmit="return confirm('Delete this survey?');">
                  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <button class="blue-button">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>

        <?php else: ?>
          <tr>
            <td><span class="text-blue-700">No surveys uploaded yet</span></td>
            <td class="file-meta">—</td>
            <td class="file-meta">—</td>
            <td class="file-meta">—</td>
            <td class="file-meta">—</td>
            <td><button class="blue-button" disabled>Delete</button></td>
          </tr>
        <?php endif; ?>

        </tbody>
      </table>
    </div>

    <div class="pagination" role="navigation" aria-label="Pagination">
      <?php
        $window = 2;
        $startPage = max(1, $pageNum - $window);
        $endPage   = min($totalPages, $pageNum + $window);

        if ($endPage - $startPage < $window * 2) {
            $needed = $window * 2 - ($endPage - $startPage);
            $startPage = max(1, $startPage - $needed);
            $endPage   = min($totalPages, $endPage + $needed);
        }
      ?>

      <?php if ($pageNum > 1): ?>
        <a class="page-num" href="?page=<?= $pageNum - 1 ?>">Back</a>
      <?php else: ?>
        <button class="page-num" disabled>Back</button>
      <?php endif; ?>

      <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
        <?php if ($p == $pageNum): ?>
          <span class="page-num active"><?= $p ?></span>
        <?php else: ?>
          <a class="page-num" href="?page=<?= $p ?>"><?= $p ?></a>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($pageNum < $totalPages): ?>
        <a class="page-num" href="?page=<?= $pageNum + 1 ?>">Next</a>
      <?php else: ?>
        <button class="page-num" disabled>Next</button>
      <?php endif; ?>
    </div>

  </div>
</main>

<script>
if (window.history.replaceState) {
    const url = new URL(window.location);
    if (url.searchParams.has('ok') || url.searchParams.has('err')) {
      url.searchParams.delete('ok');
      url.searchParams.delete('err');
      window.history.replaceState({}, document.title, url.pathname + url.search);
    }
}
</script>

</body>
</html>
