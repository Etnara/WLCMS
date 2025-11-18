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
include_once('database/dbPersons.php');
$con = connect();
$query = "SELECT * FROM dbpersons WHERE status='Accepted Speaker'";
$people = mysqli_query($con, $query);
$query = "SELECT count(*) FROM dbpersons WHERE status='Pending Speaker'";
$numPending = mysqli_query($con, $query)->fetch_assoc()["count(*)"];
$admin = retrieve_person($_SESSION['_id']);
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

    .search-container {
    position: relative; 
    }

    .search-results {
        position: absolute;
        top: 100%; 
        left: 0;
        right: 0;
        z-index: 10;
        
        background: white;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin-top: 2px;
        padding: 0;
        list-style: none;
        max-height: 200px;
        overflow-y: auto;
    }

    .search-results li {
        padding: 8px;
        cursor: pointer;
    }

    .search-results li:hover {
        background-color: #f0f0f0;
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
        <main>
            <div class="main-content-box w-[80%] p-8 mb-8">
                <div class="flex justify-center mb-8">
                    <a href="index.php" class="return-button">Return to Dashboard</a>

                    <!-- Link to Review Speakers Page -->
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


            
            <div>
                <div class="search-container">
                <label for="name">Search Speakers by name or topic</label>
                <input type="text" id="name" name="name" class="w-full" value="<?php if (isset($name)) echo htmlspecialchars($_GET['name']); ?>"
                 placeholder="Enter the speaker's name">
                <ul id="nameResults" class="search-results"></ul>
                </div>
                <!--
                <label for="topic">Search Speakers by topic</label>
                <input type="text" id="topic" name="topic" class="w-full" value="<?php //if (isset($topic)) echo htmlspecialchars($_GET['topic']); ?>" 
                placeholder="Enter a topic">
                -->
                        <!--<table style="border: 0">
                            <td>
                                <input type="submit" name="submit" value="Search" class="blue-button">
                            </td>
                            <td>
                                <input type="submit" name="submit" value="View all" class="blue-button">
                            </td>
                        </table>-->
            </div>

            <div id="results"></div>

            <script>
            const input = document.getElementById('name');
            const resultsContainer = document.getElementById('results');
            let searchTimeout = null;

            input.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();

                if (!query) {
                    fetch(`searchSpeakers.php`)
                        .then(res => res.json())
                        .then(html => {
                            resultsContainer.innerHTML = html;
                        });
                    return;
                }

                // Delay typing by 300ms before fetching
                searchTimeout = setTimeout(() => {
                    fetch(`searchSpeakers.php?q=${encodeURIComponent(query)}`)
                        .then(res => res.json())
                        .then(html => {
                            resultsContainer.innerHTML = html;
                        })
                        .catch(err => {
                            console.error(err);
                            resultsContainer.innerHTML = '<div class="error-block">Error loading results.</div>';
                        });
                }, 300);
            });
            </script>

            <script>
                window.addEventListener('DOMContentLoaded', () => {
                fetch(`searchSpeakers.php`)
                    .then(res => {
                        console.log("Response:", res);
                        return res.json();   
                    })
                    .then(html => {
                        console.log("HTML:", html)
                        resultsContainer.innerHTML = html;
                    })
                    .catch(err => {
                        console.error("Error:", err);
                        resultsContainer.innerHTML = '<div class="error-block">Error loading results.</div>';
                    });
            });
            </script>

            <!--<div class="text-center pt-4">
                <input type="submit" value="Search" class="blue-button">
            </div> -->
            
                <?php
                /*
                    if ( (isset($_GET['submit']) && $_GET['submit'] != "View all")) {
                        require_once('include/input-validation.php');
                        require_once('database/dbPersons.php');
                        require_once('database/dbCommunications.php');

                        $args = sanitize($_GET);
                        $name = $args['name'];
                        $persons = array();

                        if ($name) {
                            echo "<h3>Search Results</h3>";
                            $persons = find_speakers_by_topic($name);
                            $persons2 = find_users($name);
                            foreach ($persons2 as $p) {
                                $persons[] = $p;
                            }

                            require_once('include/output.php');
                            //echo '<div class="error-block">Returned to Full List.</div>';
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
                    */
                        /* TODO: Add this function to person class or raw dog it */
                        /* <td>' . $person->get_notes() . '</td> */
                        /*
                        $query = "SELECT * FROM dbpersons WHERE id='{$person->get_id()}'";
                        $rawPerson = mysqli_query($con, $query)->fetch_assoc();
                        $query = "SELECT * FROM speaker_topics WHERE speaker='{$person->get_id()}'";
                        $topics = mysqli_query($con, $query);
                        $topicString = "";
                        foreach ($topics as $topic)
                            $topicString = $topicString . $topic['topic'] . ", ";
                        $topicString = rtrim($topicString, ", ");
                        echo '
                            <tr>
                            <td>' . $person->get_first_name() . " " . $person->get_last_name() . '</td>
                            <td><a href="mailto:' . $person->get_email() . '" class="text-blue-700 underline">' . $person->get_email() . '</a></td>
                            <td><a href="tel:' . $person->get_phone1() . '" class="text-blue-700 underline">' . formatPhoneNumber($person->get_phone1()) . '</a></td>
                            <td>' . $topicString . '</td>
                            <td>' . $rawPerson['notes'] . '</td>
                            <td><a href="viewProfile.php?id=' . $person->get_id() . '" class="text-blue-700 underline">Edit</a></td>
                            </tr>';
                    }echo '
                            </tbody>
                            </table>
                        </div>';

                    } else {
                        echo '<div class="error-block">Your search returned no results.</div>';
                    }

                    }if ( (isset($_GET['submit']) && $_GET['submit'] == "View all")) {
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
                            //$admin = retrieve_person($_SESSION['_id']);
                            foreach ($people as $person) {
                            $query = "SELECT * FROM speaker_topics WHERE speaker='{$person['id']}'";
                            $topics = mysqli_query($con, $query);
                            $topicString = "";
                            foreach ($topics as $topic)
                                $topicString = $topicString . $topic['topic'] . ", ";
                            $topicString = rtrim($topicString, ", ");
                            echo '
                            <tr>
                            <td>' . $person["first_name"] . " " . $person["last_name"] . '</td>
                            <td><a href="mailto:' . $person["email"] . '" 
               class="text-blue-700 underline" 
               onclick="addNewCommunication(\'' . $admin->get_email() . '\', \'' . $person["email"] . '\'); return false;">
               ' . $person["email"] . '
            </a></td>
                            <td><a href="tel:' . $person["phone1"] . '" class="text-blue-700 underline">' . formatPhoneNumber($person["phone1"]) . '</a></td>
                            <td>' . $topicString . '</td>
                            <td>' . $person["notes"] . '</td>
                            <td><a href="viewProfile.php?id=' . $person["id"] . '" class="text-blue-700 underline">Edit</a></td>
                            </tr>';
                            }echo'

                        </tbody>
                    </table>
                </div>';
                    }
*/
                ?>

            

            </div>
        </main>
        <script> 
    function addNewCommunication(admin_email, speaker_email) {
  if (confirm(`Are you sure you want to email ${speaker_email}?`)) {
    window.location.href =
      'addCommunication.php?admin_email=' +
      encodeURIComponent(admin_email) +
      '&speaker_email=' +
      encodeURIComponent(speaker_email);
  }
}
</script>
    </body>
</html>

