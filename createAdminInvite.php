<?php
session_cache_expire(30);
session_start();

$loggedIn = isset($_SESSION['_id']);
$accessLevel = $loggedIn ? ($_SESSION['access_level'] ?? 0) : 0;

if ($accessLevel < 2) { header('Location: index.php'); die(); }
?>
<!-- BANDAID FIX FOR HEADER BEING WEIRD -->
<?php
$tailwind_mode = true;
require_once('header.php');
?>
<style>
        .date-box {
            background: #274471;
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
    <a href="index.php" class="return-button">Back to Dashboard</a>
  </div>
</main>
</body>
</html>
