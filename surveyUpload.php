<?php
session_cache_expire(30);
session_start();

$loggedIn   = isset($_SESSION['_id']);
$accessLevel= $loggedIn ? ($_SESSION['access_level'] ?? 0) : 0;
if ($accessLevel < 2) { header('Location: index.php'); die(); }

require_once 'database/dbinfo.php';
$con = connect();

$ok = $err = null;

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

function is_real_pdf($tmpPath): bool {
  $f = new finfo(FILEINFO_MIME_TYPE);
  return $f->file($tmpPath) === 'application/pdf';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
    $err = 'Invalid request token.';
  } else {
    $action = $_POST['action'] ?? '';
    if ($action === 'upload') {
      if (!isset($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
        $err = 'Please choose a PDF file.';
      } else {
        $file = $_FILES['pdf'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']) ?: '';

        if ($ext !== 'pdf' || $mime !== 'application/pdf') {
          $err = 'File must be a valid .pdf';
        } else {
          $data = file_get_contents($file['tmp_name']);
          if ($data === false || $data === '') {
            $err = 'Could not read uploaded file.';
          } else {
            $stmt = $con->prepare("INSERT INTO dbsurveys (filename, mime, content) VALUES (?, ?, ?)");
            $fn   = basename($file['name']);
            $stmt->bind_param('ssb', $fn, $mime, $data);
            $stmt->send_long_data(2, $data);
            if ($stmt->execute()) $ok = 'Survey uploaded.'; else $err = 'Upload failed.';
            $stmt->close();
          }
        }
      }
    } elseif ($action === 'delete') {
      $id = (int)($_POST['id'] ?? 0);
      if ($id > 0) {
        if ($con->query("DELETE FROM dbsurveys WHERE id=$id")) $ok = 'Survey deleted.';
        else $err = 'Could not delete.';
      }
    }
  }
}

// pagination settings
$limit = 10; // items per page
$pageNum = max(1, (int)($_GET['page'] ?? 1));
$offset = ($pageNum - 1) * $limit;

// handle sorting
$sortBy = $_GET['sort'] ?? 'uploaded_at'; // default sort by date
$sortOrder = $_GET['order'] ?? 'desc'; // default descending
if (!in_array($sortOrder, ['asc', 'desc'])) $sortOrder = 'desc';

// validate sort column
$sortKeyMap = [
  'filename' => 'filename',
  'uploaded_at' => 'uploaded_at',
];
$sortKey = $sortKeyMap[$sortBy] ?? 'uploaded_at';

// get total count
$countResult = $con->query("SELECT COUNT(*) as cnt FROM dbsurveys");
$countRow = $countResult->fetch_assoc();
$totalRows = $countRow['cnt'] ?? 0;
$totalPages = max(1, (int)ceil($totalRows / $limit));

// fetch surveys from database
$query = "SELECT id, filename, uploaded_at FROM dbsurveys ORDER BY $sortKey $sortOrder LIMIT $offset, $limit";
$result = $con->query($query);
$list = [];
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $list[] = $row;
  }
}

// helper function to build sort link
function sortLink($column, $label) {
  global $sortBy, $sortOrder, $pageNum;
  $newOrder = ($sortBy === $column && $sortOrder === 'asc') ? 'desc' : 'asc';
  $arrow = ($sortBy === $column) ? ($sortOrder === 'asc' ? ' ▲' : ' ▼') : '';
  return '<a href="?sort=' . $column . '&order=' . $newOrder . '&page=' . $pageNum . '" style="text-decoration:none; cursor:pointer; color:inherit;">' . $label . $arrow . '</a>';
}

$tailwind_mode = true;
require_once('header.php');
?>

<!-- BANDAID FIX FOR HEADER BEING WEIRD -->
<?php
$tailwind_mode = true;
require_once('header.php');
?>
<style>
  .date-box {
      /*background: #274471;*/
      padding: 7px 30px;
      border-radius: 50px;
      box-shadow: -4px 4px 4px rgba(0, 0, 0, 0.25) inset;
      color: white;
      font-size: 24px;
      font-weight: 700;
      text-align: center;
  }   
  .dropdown { padding-right: 50px; }

  .file-input {
    width: 100%;
    border: 1px solid #e5e7eb;         
    background-color: #f3f4f6;         
    color: #374151;                    
    border-radius: 8px;
    padding: 10px;           /* space around the filename thingy */
    margin-bottom: 0;
  }
  .file-input::file-selector-button {
    margin-right: 12px;
    border: 0;
    background: #9ca3af;
    color: #fff;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
  }
  .file-input::file-selector-button:hover { background: #6b7280; }

  /* make date/file-meta more prominent */
  .file-meta { font-size:1.1rem; color:#4b5563; font-weight:700; }

  .popup {
  position: fixed;
  top: 325px; /* CHANGE THIS SO IT MOVES LOWER ON SCREEN */
  left: 50%;
  transform: translateX(-50%);
  padding: 16px 24px;
  border-radius: 8px;
  color: white;
  font-weight: 500;
  font-size: 1rem;
  opacity: 0;
  animation: fadeInOut 4s forwards;
  z-index: 9999;
}

  .popup.ok { background-color: #15803d; }
  .popup.err { background-color: #b91c1c; }

  @keyframes fadeInOut {
    0% { opacity: 0; transform: translateX(-50%) translateY(-10px); }
    10%, 90% { opacity: 1; transform: translateX(-50%) translateY(0); }
    100% { opacity: 0; transform: translateX(-50%) translateY(-10px); }
  }

  /* Pagination styles */
  .pagination {
    display: flex;
    gap: 8px;
    justify-content: center;
    align-items: center;
    margin-top: 1.5rem;
    padding: 10px 0;
    flex-wrap: wrap;
  }
  .page-num {
    display: inline-block;
    padding: 8px 10px;
    border-radius: 6px;
    background: #f3f4f6;
    color: #111;
    border: 1px solid #d1d5db;
    text-decoration: none;
    min-width: 36px;
    text-align: center;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .page-num:hover { background: #e5e7eb; }
  .page-num.active {
    background: #800000;
    color: #fff;
    border-color: #800000;
    box-shadow: 0 2px 8px rgba(128, 0, 0, 0.3);
  }
  .page-num:disabled {
    opacity: 0.5;
    cursor: default;
    background: #f3f4f6;
  }
</style>
<!-- BANDAID END, REMOVE ONCE SOME GENIUS FIXES -->

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Survey Uploads</title>
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

    <h3 class="mb-2">Upload a Survey (PDF)</h3>
    <form method="post" enctype="multipart/form-data" class="space-y-4 mb-10">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="upload">
      <div style="display: flex; align-items: center; gap: 10px;">
        <input type="file" name="pdf" accept="application/pdf" class="file-input" style="flex: 1;" required>
        <button type="submit" class="blue-button" style="white-space: nowrap; padding: 12px 20px; height: 54px; display: flex; align-items: center;">Upload</button>
      </div>
    </form>

    <h3 class="mb-2">Uploaded Surveys</h3>
    <div class="overflow-x-auto">
      <table>
        <thead class="bg-blue-400">
          <tr>
            <th><?= sortLink('filename', 'File') ?></th>
            <th><?= sortLink('uploaded_at', 'Date') ?></th>
            <th style="width:120px;"></th>
          </tr>
        </thead>
        <tbody>
        <?php
        // Render database rows
        if (!empty($list)) {
          foreach ($list as $r) {
            ?>
            <tr>
              <td>
                <a class="text-blue-700 underline" href="surveyDownload.php?id=<?= (int)$r['id'] ?>" target="_blank"><?= htmlspecialchars($r['filename']) ?></a>
              </td>
              <td class="file-meta"><?= date('m/d/Y', strtotime($r['uploaded_at'])) ?></td>
              <td>
                <form method="post" onsubmit="return confirm('Delete this survey?');">
                  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <button class="blue-button">Delete</button>
                </form>
              </td>
            </tr>
            <?php
          }
        } else {
          // placeholder row for layout/testing
          ?>
          <tr>
            <td><span class="text-blue-700">No surveys uploaded yet</span></td>
            <td class="file-meta">-</td>
            <td><button class="blue-button" disabled>Delete</button></td>
          </tr>
          <?php
        }
        ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination (numbered pages, centered) -->
    <div class="pagination" role="navigation" aria-label="Pagination">
      <?php
        // build a small window of pages around current page
        $window = 2; // pages on each side of current
        $startPage = max(1, $pageNum - $window);
        $endPage = min($totalPages, $pageNum + $window);

        // if near edges, extend the window to show more pages
        if ($endPage - $startPage < $window * 2) {
          $needed = $window * 2 - ($endPage - $startPage);
          $startPage = max(1, $startPage - $needed);
          $endPage = min($totalPages, $endPage + $needed);
        }
      ?>

      <!-- Back / Previous -->
      <?php if ($pageNum > 1): ?>
        <a class="page-num" href="?page=<?= $pageNum - 1 ?>" aria-label="Previous page">Back</a>
      <?php else: ?>
        <button class="page-num" disabled aria-hidden="true">Back</button>
      <?php endif; ?>

      <!-- Page numbers -->
      <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
        <?php if ($p == $pageNum): ?>
          <span class="page-num active" aria-current="page"><?= $p ?></span>
        <?php else: ?>
          <a class="page-num" href="?page=<?= $p ?>"><?= $p ?></a>
        <?php endif; ?>
      <?php endfor; ?>

      <!-- Next -->
      <?php if ($pageNum < $totalPages): ?>
        <a class="page-num" href="?page=<?= $pageNum + 1 ?>" aria-label="Next page">Next</a>
      <?php else: ?>
        <button class="page-num" disabled aria-hidden="true">Next</button>
      <?php endif; ?>
    </div>
  </div>
</main>
</body>
</html>
