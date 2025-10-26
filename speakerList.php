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
<html>
    <head>
        <title>Speaker List</title>
        <link rel="icon" type="image/x-icon" href="images/real-women-logo.webp">
        <link href="css/normal_tw.css" rel="stylesheet">
        <?php
        $tailwind_mode = true;
        require_once('header.php');
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
        </main>
    </body>
</html>

