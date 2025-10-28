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

</html>
