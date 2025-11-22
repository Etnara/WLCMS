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
$persons = [];
$where = 'where ';
include_once('database/dbinfo.php');
$con = connect();
$query = "SELECT * FROM dbpersons WHERE status='Admin'";
$people = mysqli_query($con, $query);
$query = "SELECT count(*) FROM dbpersons WHERE status='Pending Speaker'";
$numPending = mysqli_query($con, $query)->fetch_assoc()["count(*)"];
?>

<!DOCTYPE html>
<html>
    <style>
    .notification {
        position: absolute;
        right: 13rem;
        top: -0.5rem;
        min-width: 1.6em;
        height: 1.6em;
        border-radius: 0.8em;
        border: 0.05em solid white;
        background-color: red;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 0.8em;
        color: white;
    }
    </style>

    <head>
        <title>Admin List</title>
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
                <h1>Admin List</h1>
            </div>
        </header>
        <main>
            <div class="main-content-box w-[80%] p-8 mb-8">
                <div class="flex justify-center mb-8">
                    <a href="index.php" class="return-button">Return to Dashboard</a>

                    
                </div>

            <form id="person-search" class="space-y-6" method="get">
            <!--
            <div>
                <label for="name">Search Admins by name</label>
                <input type="text" id="name" name="name" class="w-full" value="<?php //if (isset($name)) echo htmlspecialchars($_GET['name']); ?>" placeholder="Enter the admins's name">
                        <table style="border: 0">
                            <td>
                                <input type="submit" name="submit" value="Search" class="blue-button">
                            </td>
                            <td>
                                <input type="submit" name="submit" value="Clear" class="blue-button">
                            </td>
                        </table>
            </div>
-->
            <!--<div class="text-center pt-4">
                <input type="submit" value="Search" class="blue-button">
            </div> -->
                <?php
                echo' <div class="overflow-x-auto">
                    <table>
                        <thead class="bg-blue-400">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Notes</th>
                                
                                <th>Profile</th>
                            </tr>
                        </thead>
                        <tbody>';

                            require_once('include/output.php');
                            foreach ($people as $person) {
                            echo '
                            <tr>
                            <td>' . $person["first_name"] . " " . $person["last_name"] . '</td>
                            <td><a href="mailto:' . $person["email"] . '" class="text-blue-700 underline">' . $person["email"] . '</a></td>
                            
                            
                            <td>' . $person["notes"] . '</td>
                            <td><a href="viewProfile.php?id=' . $person["id"] . '" class="text-blue-700 underline">Edit</a></td>
                            </tr>';
                            }echo'

                        </tbody>
                    </table>
                </div>';
                    ?>
            </form>

            
            </div>
        </main>
    </body>
</html>

