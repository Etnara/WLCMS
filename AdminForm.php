<?php require_once 'addAdmin.php'; ?>
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
