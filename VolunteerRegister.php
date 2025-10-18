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

    $showPopup = false;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $ignoreList = array('password', 'password-reenter');
        $args = sanitize($_POST, $ignoreList);

        $required = array(
            'first_name', 'last_name',
            'email', 'phone', 'event_topic', 'event_topic_summary'
        );

        /* $required = array(
            'first_name', 'last_name', 'birthdate',
            'street_address', 'city', 'state', 'zip', 
            'email', 'phone', 'phone_type',
            'event_topic', 'event_topic_summary',
            'username', 'password',
            'is_community_service_volunteer',
            'is_new_volunteer', 
            'total_hours_volunteered'
        ); */

        $errors = false;

        if (!wereRequiredFieldsSubmitted($args, $required)) {
            $errors = true;
        }

        $first_name = $args['first_name'];
        $last_name = $args['last_name'];
        /*
        $birthday = validateDate($args['birthdate']);
        if (!$birthday) {
            echo "<p>Invalid birthdate.</p>";
            $errors = true;
        }

        $street_address = $args['street_address'];
        $city = $args['city'];
        $state = $args['state'];
        if (!valueConstrainedTo($state, array(
            'AK','AL','AR','AZ','CA','CO','CT','DC','DE','FL','GA','HI','IA','ID','IL','IN','KS','KY','LA','MA','MD','ME',
            'MI','MN','MO','MS','MT','NC','ND','NE','NH','NJ','NM','NV','NY','OH','OK','OR','PA','RI','SC','SD','TN','TX',
            'UT','VA','VT','WA','WI','WV','WY'))) {
            echo "<p>Invalid state.</p>";
            $errors = true;
        }

        $zip_code = $args['zip'];
        if (!validateZipcode($zip_code)) {
            echo "<p>Invalid ZIP code.</p>";
            $errors = true;
        }
        */

        $email = strtolower($args['email']);
        if (!validateEmail($email)) {
            echo "<p>Invalid email.</p>";
            $errors = true;
        }

        $phone1 = validateAndFilterPhoneNumber($args['phone']);
        if (!$phone1) {
            echo "<p>Invalid phone number.</p>";
            $errors = true;
        }

        /* $phone1type = $args['phone_type'];
        if (!valueConstrainedTo($phone1type, array('cellphone', 'home', 'work'))) {
            echo "<p>Invalid phone type.</p>";
            $errors = true;
        } */



        // Event topic fields (speaker/topic information)
        $event_topic = isset($args['event_topic']) ? trim(strip_tags($args['event_topic'])) : '';
        $event_topic_summary = isset($args['event_topic_summary']) ? trim(strip_tags($args['event_topic_summary'])) : '';

        // Basic validation: topic required and reasonably bounded
        if ($event_topic === '') {
            echo "<p>Event topic is required.</p>";
            $errors = true;
        } elseif (strlen($event_topic) > 255) {
            echo "<p>Event topic is too long (max 255 characters).</p>";
            $errors = true;
        }

        if ($event_topic_summary === '') {
            echo "<p>Event topic summary is required.</p>";
            $errors = true;
        } elseif (strlen($event_topic_summary) > 4000) {
            echo "<p>Event topic summary is too long (max 4000 characters).</p>";
            $errors = true;
        }


        $skills = isset($args['skills']) ? $args['skills'] : '';
        $interests = isset($args['interests']) ? $args['interests'] : '';

        $is_community_service_volunteer = isset($args['is_community_service_volunteer']) && $args['is_community_service_volunteer'] === 'yes' ? 1 : 0;
        $is_new_volunteer = isset($args['is_new_volunteer']) ? (int)$args['is_new_volunteer'] : 1;
        $total_hours_volunteered = isset($args['total_hours_volunteered']) ? (float)$args['total_hours_volunteered'] : 0.00;

        $type = ($is_community_service_volunteer === 1) ? 'volunteer' : 'participant';
        $archived = 0;
        $status = "Inactive";
        $training_level = "None";

        // Provide defaults for fields not present on the speaker interest form
        $birthday = isset($args['birthdate']) ? validateDate($args['birthdate']) : '';
        $street_address = isset($args['street_address']) ? $args['street_address'] : '';
        $city = isset($args['city']) ? $args['city'] : '';
        $state = isset($args['state']) ? $args['state'] : '';
        $zip_code = isset($args['zip']) ? $args['zip'] : '';
        $phone1type = isset($args['phone_type']) ? $args['phone_type'] : '';
        $emergency_contact_first_name = isset($args['emergency_contact_first_name']) ? $args['emergency_contact_first_name'] : '';
        $emergency_contact_last_name = isset($args['emergency_contact_last_name']) ? $args['emergency_contact_last_name'] : '';
        $emergency_contact_phone = isset($args['emergency_contact_phone']) ? validateAndFilterPhoneNumber($args['emergency_contact_phone']) : '';
        $emergency_contact_phone_type = isset($args['emergency_contact_phone_type']) ? $args['emergency_contact_phone_type'] : '';
        $emergency_contact_relation = isset($args['emergency_contact_relation']) ? $args['emergency_contact_relation'] : '';

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
            $id, $first_name, $last_name,
            $phone1, $email, $archived,
            $event_topic, $event_topic_summary
        );

        $result = add_person($newperson);
        if (!$result) {
            $showPopup = true;
        } else {
            echo '<script>document.location = "login.php?registerSuccess";</script>';
            $title = $id . " has been added as a speaker";
            $body = "New volunteer account has been created";
            system_message_all_admins($title, $body);
        }
    } else {
        require_once('registrationForm.php');
    }
?>

<?php if ($showPopup): ?>
<div id="popupMessage" class="absolute left-[40%] top-[20%] z-50 bg-red-800 p-4 text-white rounded-xl text-xl shadow-lg">
    That username is already taken.
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
