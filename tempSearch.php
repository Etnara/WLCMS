<?php
session_cache_expire(30);
session_start();

$loggedIn = false;
$accessLevel = 0;
$userID = null;
if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    $accessLevel = $_SESSION['access_level'];
    $userID = $_SESSION['_id'];
}

include_once('database/dbinfo.php');
$con=connect();
$query = "
SELECT *
FROM dbpersons
";
$people = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<<<<<<< HEAD:tempSearch.php
<html lang="en">
<head>
    <title>Fredericksburg SPCA | Volunteer/Participant Search</title>
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
<body>

<header class="hero-header">
    <div class="center-header">
        <h1>Speaker List</h1>
    </div>
</header>

<main>
    <div class="main-content-box w-[80%] p-8">

        <div class="text-center mb-8">
            <h2>Full list of speakers</h2>
            <p class="sub-text">Use Search features below to find a specific speaker.</p>
        </div>

        <form id="person-search" class="space-y-6" method="get">

        <?php
        require_once('include/input-validation.php');
        require_once('database/dbPersons.php');
        $persons = listAllSpeakers();
        echo '
                        <div class="overflow-x-auto">
                            <table>
                                <thead class="bg-blue-400">
                                    <tr>
                                        <th>First</th>
                                        <th>Last</th>
                                        <th>Topic Summary</th>
                                        <th>Profile</th>
                                    </tr>
                                </thead>
                                <tbody>';
                        $mailingList = '';
                        $notFirst = false;
                        foreach ($persons as $person) {
                            if ($notFirst) {
                                $mailingList .= ', ';
                            } else {
                                $notFirst = true;
                            }
                            $mailingList .= $person->get_email();
                            echo '
                                    <tr>
                                        <td>' . $person->get_first_name() . '</td>
                                        <td>' . $person->get_last_name() . '</td>
                                        <td>' . $person->get_topic_summary() . '</td>
                                        <td><a href="viewProfile.php?id=' . $person->get_id() . '" class="text-blue-700 underline">Profile</a></td>
                                    </tr>';
                        }
                        echo '
                                </tbody>
                            </table>
                        </div>';

                        echo '
                        <div class="mt-4">
                            <label>Result Mailing List:</label>
                            <p class="text-gray-700 break-words">' . $mailingList . '</p>
                        </div>';
            if (isset($_GET['name']) || isset($_GET['id']) || isset($_GET['phone']) || isset($_GET['zip'])  || isset($_GET['status']) || isset($_GET['photo_release'])) {
                require_once('include/input-validation.php');
                require_once('database/dbPersons.php');
                $args = sanitize($_GET);
                $required = ['name', 'id', 'phone', 'zip', 'role', 'status', 'photo_release'];

                /*if (!wereRequiredFieldsSubmitted($args, $required, true)) {
                    echo '<div class="error-block">Missing expected form elements.</div>';
                }*/

                $name = $args['name'];
                $id = $args['id'];
                $phone = preg_replace("/[^0-9]/", "", $args['phone']);
                $status = $args['status'];
                

               /* if (!($name || $id || $phone || $zip || $role || $status || $photo_release)) {
                    echo '<div class="error-block">At least one search criterion is required.</div>';
                } else */ if (!valueConstrainedTo($status, ['Active', 'Inactive', ''])) {
                    echo '<div class="error-block">The system did not understand your request.</div>';
                } /*else if (!valueConstrainedTo($photo_release, ['Restricted', 'Not Restricted', ''])) {
                    echo '<div class="error-block">The system did not understand your request.</div>';
                    
                } */else {
                    echo "<h3>Search Results</h3>";
                    $persons = find_users($name, $id, $phone, $status,);
                    //$persons = listAllSpeakers();
                    require_once('include/output.php');

                    if (count($persons) > 0) {
                        echo '
                        <div class="overflow-x-auto">
                            <table>
                                <thead class="bg-blue-400">
                                    <tr>
                                        <th>First</th>
                                        <th>Last</th>
                                        <th>Topic Summary</th>
                                        <th>Profile</th>
                                    </tr>
                                </thead>
                                <tbody>';
                        $mailingList = '';
                        $notFirst = false;
                        foreach ($persons as $person) {
                            if ($notFirst) {
                                $mailingList .= ', ';
                            } else {
                                $notFirst = true;
                            }
                            $mailingList .= $person->get_email();
                            echo '
                                    <tr>
                                        <td>' . $person->get_first_name() . '</td>
                                        <td>' . $person->get_last_name() . '</td>
                                        <td>' . $person->get_topic_summary() . '</td>
                                        <td><a href="viewProfile.php?id=' . $person->get_id() . '" class="text-blue-700 underline">Profile</a></td>
                                    </tr>';
                        }
                        echo '
                                </tbody>
                            </table>
                        </div>';

                        echo '
                        <div class="mt-4">
                            <label>Result Mailing List:</label>
                            <p class="text-gray-700 break-words">' . $mailingList . '</p>
                        </div>';
                    } else {
                        echo '<div class="error-block">Your search returned no results.</div>';
                    }
                    echo '<h3>Search Again</h3>';
                }
            }
=======
<html>
    <head>
        <title>Speaker List</title>
        <link rel="icon" type="image/x-icon" href="images/real-women-logo.webp">
        <link href="css/normal_tw.css" rel="stylesheet">
        <?php
        $tailwind_mode = true;
        require_once('header.php');
>>>>>>> 0cb336d65277191af691c3efb81a3164bdfcd747:speakerList.php
        ?>
    </head>

    <body>

        <header class="hero-header">
            <div class="center-header">
                <h1>Speaker List</h1>
            </div>
        </header>
        <!-- Link to Review Speakers Page -->
        <main>
            <div class="main-content-box w-[80%] p-8 mb-8">
                <div class="flex justify-center mb-8">
                    <a href="index.php" class="return-button" style="margin-right: 2rem;">Return to Dashboard</a>
                    <a href="checkedInVolunteers.php" class="return-button">View Pending Speakers</a>
                </div>

                <div class="overflow-x-auto">
                    <table>
                        <thead class="bg-blue-400">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Topics</th>
                                <th>Notes</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            require_once('include/output.php');
                            foreach ($people as $person) {
                            echo '
                            <tr>
                            <td>' . $person["first_name"] . " " . $person["last_name"] . '</td>
                            <td><a href="mailto:' . $person["email"] . '" class="text-blue-700 underline">' . $person["email"] . '</a></td>
                            <td><a href="tel:' . $person["phone1"] . '" class="text-blue-700 underline">' . formatPhoneNumber($person["phone1"]) . '</a></td>
                            <td>' . '</td>
                            <td>' . $person["notes"] . '</td>
                            <td><a href="viewProfile.php?id=' . $person["id"] . '" class="text-blue-700 underline">Edit</a></td>
                            </tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

            </div>
<<<<<<< HEAD:tempSearch.php

            <div>
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="w-full" value="<?php if (isset($phone)) echo htmlspecialchars($_GET['phone']); ?>" placeholder="Enter the user's phone number">
            </div>

        


            <div>
                <label for="status">Archive Status</label>
                <select id="status" name="status" class="w-full">
                    <option value="">Any</option>
                    <option value="Active" <?php if (isset($status) && $status == 'Active') echo 'selected'; ?>>Active</option>
                    <option value="Inactive" <?php if (isset($status) && $status == 'Inactive') echo 'selected'; ?>>Archived</option>
                </select>
            </div>


            <div class="text-center pt-4">
                <input type="submit" value="Search" class="blue-button">
            </div>

        </form>
    </div>

    <div class="text-center mt-6">
        <a href="index.php" class="return-button">Return to Dashboard</a>
    </div>

    <div class="info-section">
        <div class="blue-div"></div>
        <p class="info-text">
            Use this tool to filter and search for volunteers or participants by their role, zip code, phone, archive status, and more. Mailing list support is built in.
        </p>
    </div>
</main>

</body>
=======
        </main>
    </body>
>>>>>>> 0cb336d65277191af691c3efb81a3164bdfcd747:speakerList.php
</html>

