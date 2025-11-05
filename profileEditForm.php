<?php
    require_once('domain/Person.php');
    require_once('database/dbPersons.php');
    require_once('include/output.php');

    $args = sanitize($_GET);
    if ($_SESSION['access_level'] >= 2 && isset($args['id'])) {
        $id = $args['id'];
        $editingSelf = $id == $_SESSION['_id'];
        // Check to see if user is a lower-level manager here
    } else {
        $editingSelf = true;
        $id = $_SESSION['_id'];
    }

    $person = retrieve_person($id);
    if (!$person) {
        echo '<main class="signup-form"><p class="error-toast">That user does not exist.</p></main></body></html>';
        die();
    }

    $times = [
        '12:00 AM', '1:00 AM', '2:00 AM', '3:00 AM', '4:00 AM', '5:00 AM',
        '6:00 AM', '7:00 AM', '8:00 AM', '9:00 AM', '10:00 AM', '11:00 AM',
        '12:00 PM', '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', '5:00 PM',
        '6:00 PM', '7:00 PM', '8:00 PM', '9:00 PM', '10:00 PM', '11:00 PM',
        '11:59 PM'
    ];
    $values = [
        "00:00", "01:00", "02:00", "03:00", "04:00", "05:00",
        "06:00", "07:00", "08:00", "09:00", "10:00", "11:00",
        "12:00", "13:00", "14:00", "15:00", "16:00", "17:00",
        "18:00", "19:00", "20:00", "21:00", "22:00", "23:00",
        "23:59"
    ];

    function buildSelect($name, $disabled=false, $selected=null) {
        global $times;
        global $values;
        if ($disabled) {
            $select = '
                <select id="' . $name . '" name="' . $name . '" disabled>';
        } else {
            $select = '
                <select id="' . $name . '" name="' . $name . '">';
        }
        if (!$selected) {
            $select .= '<option disabled selected value>Select a time</option>';
        }
        $n = count($times);
        for ($i = 0; $i < $n; $i++) {
            $value = $values[$i];
            if ($selected == $value) {
                $select .= '
                    <option value="' . $values[$i] . '" selected>' . $times[$i] . '</option>';
            } else {
                $select .= '
                    <option value="' . $values[$i] . '">' . $times[$i] . '</option>';
            }
        }
        $select .= '</select>';
        return $select;
    }
?>

<?php
$con = connect();
$tmpPerson = mysqli_query($con, "
    SELECT *
    FROM dbpersons
    WHERE id = '$id'
")->fetch_assoc();
$hasPassword = $tmpPerson['status'] == "Admin";
$accept = $tmpPerson['status'] == "Accepted Speaker" ? "Checked" : "";
$pending = $tmpPerson['status'] == "Pending Speaker" ? "Checked" : "";
$reject = $tmpPerson['status'] == "Rejected Speaker" ? "Checked" : "";
$archived = $tmpPerson['archived'] == "1" ? "Checked" : "";
?>
<h1>Edit Profile</h1>
<main class="signup-form">
    <h2>Modify Volunteer Profile</h2>
    <?php if (isset($updateSuccess)): ?>
        <?php if ($updateSuccess): ?>
            <div class="happy-toast">Profile updated successfully!</div>
        <?php else: ?>
            <div class="error-toast">An error occurred.</div>
        <?php endif ?>
    <?php endif ?>
    <?php if ($isAdmin): ?>
        <?php if (strtolower($id) == 'vmsroot') : ?>
            <div class="error-toast">The root user profile cannot be modified</div></main></body>
            <?php die() ?>
        <?php elseif (isset($_GET['id']) && $_GET['id'] != $_SESSION['_id']): ?>
            <!-- <a class="button" href="modifyUserRole.php?id=<?php echo htmlspecialchars($_GET['id']) ?>">Modify User Access</a> -->
        <?php endif ?>
    <?php endif ?>
    <form class="signup-form" method="post">
        <br>
	<p>An asterisk (<em>*</em>) indicates a required field.</p>

        <fieldset class="section-box">
            <legend>Login Credentials</legend>
            <label>Username</label>
            <p><?php echo $person->get_id() ?></p>

            <!--<label>Password</label>-->
        <?php
        if ($hasPassword)
            echo "<p><a href='changePassword.php'>Change Password</a></p>";
        ?>
        </fieldset>

        <fieldset class="section-box">
            <legend>Personal Information</legend>

            <p>The following information helps us identify you within our system.</p>
            <label for="first_name"><em>* </em>First Name</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo hsc($person->get_first_name()); ?>" required placeholder="Enter your first name">

            <label for="last_name"><em>* </em>Last Name</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo hsc($person->get_last_name()); ?>" required placeholder="Enter your last name">
        </fieldset>

        <fieldset class="section-box">
            <legend>Contact Information</legend>

            <p>The following information helps us determine the best way to contact you regarding event coordination.</p>
            <label for="email"><em>* </em>E-mail</label>
            <input type="email" id="email" name="email" value="<?php echo hsc($person->get_email()); ?>" required placeholder="Enter your e-mail address">

            <label for="phone1"><em>* </em>Phone Number</label>
            <input type="tel" id="phone1" name="phone1" value="<?php echo formatPhoneNumber($person->get_phone1()); ?>" pattern="\([0-9]{3}\) [0-9]{3}-[0-9]{4}" required placeholder="Ex. (555) 555-5555">


        </fieldset>

        <fieldset class="section-box">
            <legend>Status</legend>
            <div class="radio-group">
            <input type="radio" id="accept" name="status" value="Accepted Speaker" <?php echo $accept; ?>> <label for="accept">Accepted</label>
                <input type="radio" id="pending" name="status" value="Pending Speaker" <?php echo $pending; ?>> <label for="pending">Pending</label>
                <input type="radio" id="reject" name="status" value="Rejected Speaker" <?php echo $reject; ?>> <label for="reject">Rejected</label>

            </div>
            <br>
            <div class="radio-group">
            <input id="archived" name="archived" type="checkbox" <?php echo $archived; ?>> <label for="archived">Archive</label>
            </div>
        </fieldset>










        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="submit" name="profile-edit-form" value="Update Profile">
        <?php if ($editingSelf): ?>
            <a class="button cancel" href="viewProfile.php" style="margin-top: -.5rem">Cancel</a>
        <?php else: ?>
            <a class="button cancel" href="viewProfile.php?id=<?php echo htmlspecialchars($_GET['id']) ?>" style="margin-top: -.5rem">Cancel</a>
        <?php endif ?>
    </form>
</main>
