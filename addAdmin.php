<?php
session_cache_expire(30);
session_start();
require_once 'database/dbinfo.php'; // must provide connect()
require_once __DIR__ . '/include/input-validation.php';

require_once __DIR__ . '/domain/Person.php';
require_once __DIR__ . '/database/dbPersons.php';

$con = connect();
$error = null;
$success = false;

// Only process when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs except passwords (we'll handle those separately)
    $san = sanitize($_POST, ['password', 'password_confirm']);
    $first = $san['first_name'] ?? '';
    $last  = $san['last_name'] ?? '';
    $email = $san['email'] ?? '';
    $pwd   = $_POST['password'] ?? '';
    $pwd2  = $_POST['password_confirm'] ?? '';

    // Basic server-side validation (mirrors client-side rules)
    if ($first === '' || $last === '') {
        $error = 'Please enter your first and last name.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif ($pwd === '' || $pwd2 === '') {
        $error = 'Please enter and confirm a password.';
    } elseif ($pwd !== $pwd2) {
        $error = 'Passwords do not match.';
    } elseif (!isSecurePassword($pwd)) {
        $error = 'Password must be at least 8 characters and include upper/lowercase letters and a number.';
    }

    if (!$error) {
        // Normalize email to lower-case to avoid duplicates with different case
        $email_lc = strtolower($email);

        $prefix = '';
        if (strpos($email_lc, '@') !== false) {
            $prefix = preg_replace('/[^a-z0-9]/', '', strtolower(strstr($email_lc, '@', true)));
        }
        if ($prefix === '') $prefix = 'user';
        $id = $prefix . substr(uniqid(), -6);

        // Hash password
        $password_hashed = password_hash($pwd, PASSWORD_BCRYPT);

    // Minimal admin account: status 'admin' and access_level 2 (regular admin)
    $status = 'Admin';
        $phone = ''; // no phone collected on this form
        $archived = 0;
        $topic_summary = '';
        $organization = '';

        // Create Person object and use existing add_person helper
        $newperson = new Person(
            $id,
            $password_hashed,
            $first,
            $last,
            $status,
            $phone,
            $email_lc,
            $archived,
            $topic_summary,
            $organization
        );

        $result = add_person($newperson);
        if ($result) {
            // // set access_level=2 in the database for this new admin
            // $safe_id = mysqli_real_escape_string($con, $id);
            // $upd = "UPDATE dbpersons SET access_level = 2 WHERE id = '" . $safe_id . "'";
            // @mysqli_query($con, $upd);

            // auto-login by storing the person's id and access level
            $_SESSION['_id'] = $id;
            $_SESSION['access_level'] = 2;
            session_regenerate_id(true);
            $success = true;
        } else {
            $error = 'Account could not be created (username may already exist).';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link href="css/normal_tw.css" rel="stylesheet">
    <title>Add Admin</title>
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
  <div class="text-center"><a class="blue-button" href="index.php"></a></div>
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
