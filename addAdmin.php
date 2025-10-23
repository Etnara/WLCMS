<?php
session_cache_expire(30);
session_start();
require_once 'database/dbinfo.php'; // must provide connect()

$con = connect();
$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pwd   = $_POST['password'] ?? '';
    $pwd2  = $_POST['password_confirm'] ?? '';

    if ($first==='' || $last==='')                $error='Please enter your first and last name.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error='Please enter a valid email address.';
    elseif (strlen($pwd) < 8)                     $error='Password must be at least 8 characters.';
    elseif ($pwd !== $pwd2)                       $error='Passwords do not match.';

    if (!$error) {
        $hash = password_hash($pwd, PASSWORD_DEFAULT);

        // Check if user already exists
        $check = $con->prepare("SELECT id FROM dbpersons WHERE email=?");
        $check->bind_param('s', $email);
        $check->execute();
        $exists = $check->get_result()->fetch_assoc();

        if ($exists) {
            $update = $con->prepare("UPDATE dbpersons SET password=?, access_level=2, role='admin' WHERE email=?");
            $update->bind_param('ss', $hash, $email);
            $update->execute();
        } else {
            // insert with minimal fields — missing ones get NULL/default
            $insert = $con->prepare("
                INSERT INTO dbpersons (first_name,last_name,email,password,role,access_level,status)
                VALUES (?,?,?,?, 'admin', 2, 'active')
            ");
            $insert->bind_param('ssss', $first, $last, $email, $hash);
            $insert->execute();
        }

        // auto-login
        $_SESSION['_id'] = $email;
        $_SESSION['access_level'] = 2;
        $_SESSION['role'] = 'admin';
        session_regenerate_id(true);

        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link href="css/normal_tw.css" rel="stylesheet">
  <?php $tailwind_mode = true; require_once('header.php'); ?>
  <style>
    .form-row{margin-bottom:.75rem}
    .form-row label{display:block;font-weight:600;margin-bottom:.25rem}
    .form-row input{width:100%;padding:8px;border:1px solid #ccc;border-radius:8px}
  </style>
</head>
<body>
<header class="hero-header"><div class="center-header"><h1>Create Admin Account</h1></div></header>
<main>
<div class="main-content-box w-full max-w-3xl p-8">
<?php if ($success): ?>
  <p class="text-white bg-green-700 text-center p-2 rounded-lg mb-4">
    Admin account created! You’re signed in.
  </p>
  <div class="text-center"><a class="blue-button" href="groupManagement.php">Go to Admin Area</a></div>
<?php else: ?>
  <?php if ($error): ?><p class="error-block"><?=htmlspecialchars($error)?></p><?php endif; ?>
  <form method="post">
    <div class="form-row"><label>First Name</label><input name="first_name" required></div>
    <div class="form-row"><label>Last Name</label><input name="last_name" required></div>
    <div class="form-row"><label>Email</label><input type="email" name="email" required></div>
    <div class="form-row"><label>Password (8+ chars)</label><input type="password" name="password" minlength="8" required></div>
    <div class="form-row"><label>Confirm Password</label><input type="password" name="password_confirm" minlength="8" required></div>
    <div class="text-center mt-6"><button class="blue-button">Create Admin Account</button></div>
  </form>
<?php endif; ?>
</div>
</main>
</body>
</html>
