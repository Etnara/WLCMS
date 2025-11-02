<?php
session_cache_expire(30);
session_start();

$loggedIn = isset($_SESSION['_id']);
$accessLevel = $loggedIn ? ($_SESSION['access_level'] ?? 0) : 0;

if ($accessLevel < 2) { header('Location: index.php'); die(); }

// email sending helper file:
require_once __DIR__ . '/sendInvite.php';

$statusMsg = null;
$statusErr = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $statusErr = 'Please enter a valid email address.';
    } else {
        try {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
            $base   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            $inviteLink = $scheme . $_SERVER['HTTP_HOST'] . $base . '/AdminForm.php';

            $subject = 'WLCMS Admin Invitation';
            $body = "Hello,

You have been invited to create an admin account for the Women's Leadership Colloquium Management System (WLCMS).

To get started, please visit:
$inviteLink

If you did not expect this email, you can kindly ignore it.";

            sendInvite($email, $subject, $body);
            $statusMsg = 'Invite sent to ' . htmlspecialchars($email) . '.';
        } catch (Throwable $e) {
            $statusErr = 'There was a problem sending the invite.';
        }
    }
}
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
      background: #274471;
      color: #fff;
      font-weight: 600;
      border: none;
      padding: 10px 18px;
      border-radius: 8px;
      cursor: pointer;
      text-decoration: none;
    }
    .blue-button:hover {
      background: #1f365a;
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
  .status-ok {
    background: #e6ffed; color: #056b34; border: 1px solid #9de7b4;
    padding: 10px 12px; border-radius: 8px; margin-bottom: 12px;
  }
  .status-err {
    background: #ffecec; color: #8a1f11; border: 1px solid #f5a3a3;
    padding: 10px 12px; border-radius: 8px; margin-bottom: 12px;
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

    <!-- show status messages -->
    <?php if (!empty($statusMsg)): ?>
      <div class="status-ok"><?php echo $statusMsg; ?></div>
    <?php endif; ?>
    <?php if (!empty($statusErr)): ?>
      <div class="status-err"><?php echo $statusErr; ?></div>
    <?php endif; ?>

    <form method="post" action="#">
      <div class="form-row">
        <label>Admin Email</label>
        <input type="email" name="email" placeholder="name@example.com" required>
      </div>

      <!-- SEND BUTTON IMPLEMENTED IT WORKS!!! after you download stuff composer/mailer sigh -->
        <div class="text-center mt-6">
          <div style="display: flex; justify-content: center; gap: 15px;">
            <button type="submit" class="blue-button">Send Invite</button>
            <a href="AdminForm.php" class="blue-button">Go to Form</a>
          </div>
        </div>

    </form>
  </div>

  <div class="text-center mt-6">
    <a href="index.php" class="return-button">Back to Dashboard</a>
  </div>
</main>
</body>
</html>
