<?php
    require_once('include/input-validation.php');
?>

<!DOCTYPE html>
<html>
<head>
    <?php require_once('database/dbMessages.php'); ?>
    <title>WLCMS | Speaker Interest Form</title>
    <link href="css/normal_tw.css" rel="stylesheet">
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
</head>
<body class="relative">
<?php
    require_once('domain/Person.php');
    require_once('database/dbPersons.php');
    require_once('database/dbinfo.php');
    require_once('sendEmail.php');

    $showPopup = false;
    $popupText = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $ignoreList = array('password', 'password-reenter');
        $args = sanitize($_POST, $ignoreList);

        $required = array(
          'first_name', 'last_name',
          'email', 'phone',
          'topic_summary',
        );

        $errors = false;

        if (!wereRequiredFieldsSubmitted($args, $required)) {
            $errors = true;
        }

        $first_name = $args['first_name'];
        $last_name = $args['last_name'];

        // email validation and duplicate check
        $email = strtolower(trim($args['email']));
        if (!validateEmail($email)) {
            $errors = true;
            echo "<p>Invalid email.</p>";
        } elseif (($con = connect()) 
            && ($res = mysqli_query($con, "SELECT 1 FROM dbpersons WHERE email = '" . mysqli_real_escape_string($con, $email) . "' LIMIT 1"))
            && mysqli_num_rows($res) > 0) {
            $showPopup = true;
            $popupText = "That email is already registered.";
        }

        $phone1 = validateAndFilterPhoneNumber($args['phone']);
        if (!$phone1) {
            echo "<p>Invalid phone number.</p>";
            $errors = true;
        }

        $topic_summary = isset($args['topic_summary']) ? trim(strip_tags($args['topic_summary'])) : '';

        // Basic validation: topic required and reasonably bounded
        if ($topic_summary === '') {
            echo "<p>A topic summary is required.</p>";
            $errors = true;
        } elseif (strlen($topic_summary) > 4000) {
            echo "<p>The topic summary is too long (max 4000 characters).</p>";
            $errors = true;
        }

        $organization = isset($args['organization']) ? trim(strip_tags($args['organization'])) : '';
        if (strlen($organization) > 255) {
            echo "<p>Organization is too long (max 255 characters).</p>";
            $errors = true;
        }

        $archived = 0;
        $status = "Pending Speaker";

        // If username/password not provided by this simplified form, generate them so add_person can still create an account
        if (!empty($args['username'])) {
            $id = $args['username'];
        } else {
            // generate id from email prefix + uniqid
            $prefix = '';
            if (strpos($email, '@') !== false) {
                $prefix = preg_replace('/[^a-z0-9]/', '', strtolower(strstr($email, '@', true)));
            }
            if ($prefix === '') $prefix = 'user';
            $id = $prefix . substr(uniqid(), -6);
        }

        if (!empty($args['password'])) {
            $password_ok = isSecurePassword($args['password']);
            if (!$password_ok) {
                echo "<p>Password is not secure enough.</p>";
                $errors = true;
            } else {
                $password = password_hash($args['password'], PASSWORD_BCRYPT);
            }
        } else {
            // generate a secure random password for the account
            try {
                $generated = bin2hex(random_bytes(8));
            } catch (Exception $e) {
                // fallback
                $generated = bin2hex(openssl_random_pseudo_bytes(8));
            }
            $password = password_hash($generated, PASSWORD_BCRYPT);
            // NOTE: consider emailing $generated to the user or require password reset on first login
        }

        if ($errors) {
            echo '<p class="error">Your form submission contained unexpected or invalid input.</p>';
            die();
        }

        $newperson = new Person(
            $id, $password,
            $first_name, $last_name,
            $status,
            $phone1, $email,
            $archived,
            $topic_summary,
            $organization
        );

        $result = add_person($newperson);

        if (!$result) {
            $showPopup = true;
        } else {
            sendFormConfirmation($email, $first_name, $last_name); //email speaker confirmation
            $title = "New Interest Form to Review: " . $first_name . " " . $last_name;
            message_all_users("vmsroot", $title, "");

            echo '<script>document.location = "login.php?registerSuccess";</script>';
        }
    } else {
        require_once('registrationForm.php');
    }
?>

<?php if ($showPopup): ?>
<div id="popupMessage" class="absolute left-[40%] top-[20%] z-50 bg-red-800 p-4 text-white rounded-xl text-xl shadow-lg">
    That email is already taken.
</div>
<?php endif; ?>

<!-- Auto-hide popup -->
<script>
window.addEventListener('DOMContentLoaded', () => {
    const popup = document.getElementById('popupMessage');
    if (popup) {
        popup.style.transition = 'opacity 0.5s ease';
        setTimeout(() => {
            popup.style.opacity = '0';
            setTimeout(() => {
                popup.style.display = 'none';
            }, 500);
        }, 4000);
    }
});
</script>

</body>
</html>
