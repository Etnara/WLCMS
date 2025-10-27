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
$query = "SELECT * FROM dbpersons";
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
                    <a href="index.php" class="return-button">Return to Dashboard</a>

                    <?php
                    if ($numPending) {
                        echo "
                            <a href=\"checkedInVolunteers.php\" class=\"return-button\" style=\"position: relative; margin-left: 2rem;\">
                            View Pending Speakers
                            <div class=\"notification\" role=\"status\">{$numPending}</div>
                            </a>
                        ";
                    }
                    ?>
                </div>

           
            <form id="person-search" class="space-y-6" method="get">
                <div>
                <label for="name">Search Speaker Name. Search Nothing to Return to Full List</label>
                <input type="text" id="name" name="name" class="w-full" value="<?php if (isset($name)) echo htmlspecialchars($_GET['name']); ?>" placeholder="Enter the user's first and/or last name.">
            </div>
            
            <div class="text-center pt-4">
                <input type="submit" value="Search" class="blue-button">
            </div>
                <?php
                    if (isset($_GET['name'])){
                        require_once('include/input-validation.php');
                        require_once('database/dbPersons.php');
                        $args = sanitize($_GET);
                        $name = $args['name'];
                        
                        if (!$name) {
                            echo '<div class="error-block">Returned to Full List.</div>';
                        } else if ($name) {
                            echo "<h3>Search Results</h3>";
                             $persons = find_users($name);
                             require_once('include/output.php');
                        }
                        if ((count($persons) > 0)){
                            echo'<div class="overflow-x-auto">
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
                        <tbody>';
                     foreach ($persons as $person) {
                        echo '
                            <tr>
                            <td>' . $person->get_first_name() . " " . $person->get_last_name() . '</td>
                            <td><a href="mailto:' . $person->get_email() . '" class="text-blue-700 underline">' . $person->get_email() . '</a></td>
                            <td><a href="tel:' . $person->get_phone1() . '" class="text-blue-700 underline">' . formatPhoneNumber($person->get_phone1()) . '</a></td>
                            <td>' . '</td>
                            
                            <td><<a href="viewProfile.php?id=' . $person->get_id() . '" class="text-blue-700 underline">Edit</a></td>
                            </tr>';
                     }echo '
                            </tbody>
                            </table>
                        </div>';
    
                    }elseif($name==''){

                    }else {
                        echo '<div class="error-block">Your search returned no results.</div>';
                    }

                    }if (!isset($_GET['name']) || $_GET['name']==''){
                       echo' <div class="overflow-x-auto">
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
                        <tbody>';
                            
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
                            }echo'
                            
                        </tbody>
                    </table>
                </div>';
                    }

                ?>
            
            </form>

            <!--
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
                            
                        removed php start tag here!!
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
                            removed php end tag here!!
                        </tbody>
                    </table>
                </div>
                        -->      
            </div>
        </main>
    </body>
</html>

