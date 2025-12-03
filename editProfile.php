<?php
    // Author: Lauren Knight
    // Description: Profile edit page
    session_cache_expire(30);
    session_start();
    ini_set("display_errors",1);
    error_reporting(E_ALL);
    if (!isset($_SESSION['_id'])) {
        header('Location: login.php');
        die();
    }

    require_once('include/input-validation.php');

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["modify_access"]) && isset($_POST["id"])) {
        $id = $_POST['id'];
        header("Location: /gwyneth/modifyUserRole.php?id=$id");
    } else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["profile-edit-form"])) {
        require_once('domain/Person.php');
        require_once('database/dbPersons.php');
        require_once('database/dbSpeaker_Months.php');
        // make every submitted field SQL-safe except for password
        $ignoreList = array('password');
        $args = sanitize($_POST, $ignoreList);

        $editingSelf = true;
        if ($_SESSION['access_level'] >= 2 && isset($_POST['id'])) {
            $id = $_POST['id'];
            $editingSelf = $id == $_SESSION['_id'];
            $id = $args['id'];
            // Check to see if user is a lower-level manager here
        } else {
            $id = $_SESSION['_id'];
        }

        // echo "<p>The form was submitted:</p>";
        // foreach ($args as $key => $value) {
        //     echo "<p>$key: $value</p>";
        // }

        $required = array(
            'first_name', 'last_name',
            'email', 'phone1',
        );
        $errors = false;
        if (!wereRequiredFieldsSubmitted($args, $required)) {
            $errors = true;
        }

        $first_name = $args['first_name'];

        $last_name = $args['last_name'];

        $email = validateEmail($args['email']);
        if (!$email) {
            $errors = true;
            // echo 'bad email';
        }

        $phone1 = validateAndFilterPhoneNumber($args['phone1']);
        if (!$phone1) {
            $errors = true;
            // echo 'bad phone';
        }

        $status = $args['status'];
        $archived = isset($args['archived']) ? 1 : 0;

       $type = 'v';

        
        include 'uploadHeadshot.php';

        $temp = upload_image('image');
        $headshot = $temp['headshot'];
        $MIME = $temp['MIME'];

        $data = getHeadshotData($id);
        $oldMIME = trim($data['mime'] ?? '');
        $oldHeadshot = $data['headshot'] ?? '';

        if ($headshot == null) {
            $headshot = $oldHeadshot;
            $MIME = $oldMIME;
        }

        $allMonths = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"];

        // Months selected or empty
        $selectedMonths = isset($args['months']) ? $args['months'] : [];

        foreach ($allMonths as $month) {
            if (in_array($month, $selectedMonths)) {
                addMonth($id,$month);
            } else {
                removeMonth($id,$month);
            }
        }

        // For the new fields, default to 0 if not set


        if ($errors) {
            $updateSuccess = false;
        }

        $result = update_person_required(
            $id, $first_name, $last_name,
            $email, $phone1,
            $status, $archived, $headshot, $MIME
        );
        if ($result) {
            if ($editingSelf) {
                header('Location: viewProfile.php?editSuccess');
            } else {
                header('Location: viewProfile.php?editSuccess&id='. $id);
            }
            die();
        }

    }
?>
<!DOCTYPE html>
<html>
    <style>
    .headshot img{
        width: 400px;
        height: 400px;
        object-fit: cover;
        border-radius: 8px; /* optional */
    }
    </style>
<head>
    <?php require_once('universal.inc'); ?>
    <title>Women's Leadership Colloquium | Manage Profile</title>
</head>
<body>
    <?php
        require_once('header.php');
        $isAdmin = $_SESSION['access_level'] >= 2;
        require_once('profileEditForm.php');
    ?>
</body>
</html>
