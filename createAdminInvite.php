<?php
include_once("sendEmail.php");
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
      /*background: #274471;*/
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
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Send Admin Invite</title>
  <link href="css/normal_tw.css" rel="stylesheet">
  <?php $tailwind_mode = true; require_once('header.php'); ?>
  <style>
    .form-row {
      margin-bottom: .75rem;
    }
    .form-row label {
      display: block;
      font-weight: 600;
      margin-bottom: .25rem;
      color: #fff;
    }
    .form-row input {
      width: 100%;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
    .blue-button {
      /*background: #274471;*/
      color: #fff;
      font-weight: 600;
      border: none;
      padding: 10px 18px;
      border-radius: 8px;
      cursor: pointer;
      text-decoration: none;
    }
    .blue-button:hover {
      /*background: #1f365a;*/
    }
    .return-button {
    border-radius: var(--radius-lg);
    background-color: var(--color-gray-500);
    padding-inline: calc(var(--spacing) * 6);
    padding-block: calc(var(--spacing) * 2);
    color: var(--color-white);
    &:hover {
      @media (hover: hover) {
        background-color: var(--color-gray-600);
      }
    }
  }
  </style>
</head>
<body>
<header class="hero-header">
  <div class="center-header">
    <h1>Send Admin Invite</h1>
  </div>
</header>

<main>
  <div class="main-content-box w-full max-w-3xl p-8">
    <form method="post" action="#">
      <div class="form-row">
        <label>Admin Email</label>
        <input id="emailInput" type="email" name="email" placeholder="name@example.com" required>
      </div>

      <!-- SEND BUTTON NOT IMPLEMENTED YET!!!!! WILL NOT WORK!!!! -->
      <!-- works! -Caleb --> 
        <div class="text-center mt-6">
          <div style="display: flex; justify-content: center; gap: 15px;">
            <button type="button" class="blue-button" onclick="confirmInvite()">Send Invite</button>
            <a href="AdminForm.php" class="blue-button">Go to Form</a>
          </div>
        </div>

    </form>
  </div>

  <div class="text-center mt-6">
    <a href="index.php" class="return-button">Back to Dashboard</a>
  </div>
</main>
<script> 
    function confirmInvite() {
    const email = document.getElementById('emailInput').value.trim();
    if (!email) {
    alert('Please enter an email first.');
    return;
  }
    if (confirm(`Are you sure you want to invite ${email}?`)) {
        window.location.href = `sendAdminInvite.php?email=${email}`;
    }
  }
</script>
</body>
</html>
