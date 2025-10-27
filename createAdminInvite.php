<?php
session_cache_expire(30);
session_start();

$loggedIn = isset($_SESSION['_id']);
$accessLevel = $loggedIn ? ($_SESSION['access_level'] ?? 0) : 0;

if ($accessLevel < 2) { header('Location: index.php'); die(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link href="css/normal_tw.css" rel="stylesheet">
    <title>Invite Admin</title>
  <?php $tailwind_mode = true; require_once('header.php'); ?>
</head>
<body>
<header class="hero-header">
  <div class="center-header">
    <h1>Create Admin Invite</h1>
  </div>
</header>

<main>
  <div class="main-content-box w-full max-w-3xl p-8">
    <p class="text-white bg-green-700 text-center p-2 rounded-lg mb-4">
      Anyone who visits this link can create an Admin account:
    </p>

    <pre style="padding:10px;border:1px solid #ccc;border-radius:8px;background:#f9fafb;">
http://localhost/GitHub/WLCMS/addAdmin.php
    </pre>

    <p class="mt-2 text-sm text-center text-gray-200">
      Copy this link and send it to the person you want to make an admin.
    </p>
  </div>

  <div class="text-center mt-6">
    <a href="groupManagement.php" class="return-button">Back to Group Management</a>
  </div>
</main>
</body>
</html>
