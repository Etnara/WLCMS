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
            $stmt->bind_param('sss', $fn, $mime, $data);
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

// fetch list
$list = $con->query("SELECT id, filename, uploaded_at FROM dbsurveys ORDER BY uploaded_at DESC");

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

  .file-meta { font-size:.9rem; color:#4b5563; }

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
        <button type="submit" class="blue-button" style="white-space: nowrap;">Upload</button>
      </div>
    </form>

    <h3 class="mb-2">Uploaded Surveys</h3>
    <?php if ($list && $list->num_rows): ?>
      <div class="overflow-x-auto">
        <table>
          <thead class="bg-blue-400">
            <tr>
              <th>File</th>
              <th>Uploaded</th>
              <th style="width:120px;"></th>
            </tr>
          </thead>
          <tbody>
          <?php while ($r = $list->fetch_assoc()): ?>
            <tr>
              <td>
                <a class="text-blue-700 underline" href="survey_download.php?id=<?= (int)$r['id'] ?>" target="_blank">
                  <?= htmlspecialchars($r['filename']) ?>
                </a>
              </td>
                <td class="file-meta"><?= date('Y-m-d H:i', strtotime($r['uploaded_at'])) ?></td>
              <td>
                <form method="post" onsubmit="return confirm('Delete this survey?');">
                  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <button class="blue-button">Delete</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="info-block">No surveys uploaded yet.</div>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
