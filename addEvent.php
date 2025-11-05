<?php session_cache_expire(30);
    session_start();
    // Make session information accessible, allowing us to associate
    // data with the logged-in user.

    ini_set("display_errors",1);
    error_reporting(E_ALL);

    $loggedIn = false;
    $accessLevel = 0;
    $userID = null;
    if (isset($_SESSION['_id'])) {
        $loggedIn = true;
        // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
        $accessLevel = $_SESSION['access_level'];
        $userID = $_SESSION['_id'];
    } 
    // Require admin privileges
    if ($accessLevel < 2) {
        header('Location: login.php');
        //echo 'bad access level';
        die();
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        require_once('include/input-validation.php');
        require_once('database/dbEvents.php');
        $args = sanitize($_POST, null);
        $required = array(
            "name", "description", "date", "start-time", "end-time", "speaker"
        );
        if (!wereRequiredFieldsSubmitted($args, $required)) {
            echo 'bad form data';
            die();
        } else {
            $validated = validate12hTimeRangeAndConvertTo24h($args["start-time"], $args["end-time"]);
            if (!$validated) {
                echo 'bad time range';
                die();
            }

            $startTime = $args['start-time'] = $validated[0];
            $endTime = $args['end-time'] = $validated[1];
            $date = $args['date'] = validateDate($args["date"]);
    
            if (!$startTime || !$endTime || !$date > 11){
                echo 'bad args';
                die();
            }

            $id = create_event($args);
            if(!$id){
                die();
            } else {
                header('Location: eventSuccess.php');
                exit();
            }
            
        }
    }
    $date = null;
    if (isset($_GET['date'])) {
        $date = $_GET['date'];
        $datePattern = '/[0-9]{4}-[0-9]{2}-[0-9]{2}/';
        $timeStamp = strtotime($date);
        if (!preg_match($datePattern, $date) || !$timeStamp) {
            header('Location: calendar.php');
            die();
        }
    }

    include_once('database/dbinfo.php'); 
    $con=connect();  
    $query = "
        SELECT id, first_name, last_name
        FROM dbpersons
        WHERE status='Accepted Speaker'
    ";
    $people = mysqli_query($con, $query);

?><!DOCTYPE html>
<html>
    <head>
        <?php require_once('universal.inc') ?>
        <title>Fredericksburg SPCA | Create Event</title>
    </head>
    <body>
        <?php require_once('header.php') ?>
        <h1>Create Event</h1>
        <main class="date">
            <h2>New Event Form</h2>
            <form id="new-event-form" method="POST">
                <label for="name">* Event Name </label>
                <input type="text" id="name" name="name" required placeholder="Enter name"> 
                <label for="name">* Description </label>
                <input type="text" id="description" name="description" required placeholder="Enter description">
                <label for="name">* Date </label>
                <input type="date" id="date" name="date" <?php if ($date) echo 'value="' . $date . '"'; ?> min="<?php echo date('Y-m-d'); ?>" required>
                <label for="name">* Start Time </label>
                <input type="text" id="start-time" name="start-time" pattern="([1-9]|10|11|12):[0-5][0-9] ?([aApP][mM])" required placeholder="Enter start time. Ex. 12:00 PM">
                <label for="name">* End Time </label>
                <input type="text" id="end-time" name="end-time" pattern="([1-9]|10|11|12):[0-5][0-9] ?([aApP][mM])" required placeholder="Enter end time. Ex. 1:00 PM">
                <label for="name">* Speaker </label>
                <?php
                        $people = [];
                        $result = mysqli_query($con, "SELECT * FROM dbpersons");
                        while ($row = mysqli_fetch_assoc($result)) {
                            if ($row['status'] === 'Accepted Speaker') {
                                $people[] = $row;
                            }
                        }

                        foreach ($people as &$person) {
                            $person_id = $person['id'];
                            $topics_result = mysqli_query($con, "
                                SELECT GROUP_CONCAT(topic SEPARATOR ', ') AS topic_summary
                                FROM speaker_topics
                                WHERE speaker = '$person_id'
                            ");
                            $topics_row = mysqli_fetch_assoc($topics_result);
                            $person['topic_summary'] = $topics_row['topic_summary'] ?? 'No topic';
                        }
                        unset($person); 
                        ?>

                        <!--<label for="searchSpeaker"> Search Speaker </label>-->
                        <input type="text" id="searchSpeaker" placeholder="Search Speaker (Name or Topic)" style="margin-bottom: 0.5rem; width: 100%;">

                <select id="speaker" name="speaker">
                  <option value="null">None</option>
                  <?php
                   foreach ($people as $person) {
                        $selected = $person['id'] == $event['speaker'] ? "selected" : "";
                        echo "<option value=\"{$person['id']}\" {$selected}>"
                        . htmlspecialchars("{$person['first_name']} {$person['last_name']} - {$person['topic_summary']}")
                        . "</option>\n";
                    }
                  ?>
                </select>
                <script>
                    const searchInput = document.getElementById("searchSpeaker");
                        const select = document.getElementById("speaker");
                        const allOptions = Array.from(select.options).slice(1); 

                        
                        allOptions.sort((a, b) => {
                            const topicA = a.text.split(" - ")[1] || "";
                            const topicB = b.text.split(" - ")[1] || "";
                            return topicA.localeCompare(topicB);
                        });

                        allOptions.forEach(option => select.appendChild(option));

                        
                        searchInput.addEventListener("input", () => {
                            const query = searchInput.value.toLowerCase();
                            select.innerHTML = '<option value="null">None</option>'; 

                            allOptions.forEach(option => {
                                if (option.text.toLowerCase().includes(query)) {
                                    select.appendChild(option);
                                }
                            });
                        });
                </script>

                <!-- TODO: Fix bug with the time not accepting 12:00 PM -->
                <!-- Might do something with these later -->
                <input type="hidden" id="location" value="None">
                <input type="hidden" id="capacity" value="None">

                <input type="submit" value="Create Event">
                
            </form>
                <?php if ($date): ?>
                    <a class="button cancel" href="calendar.php?month=<?php echo substr($date, 0, 7) ?>" style="margin-top: -.5rem">Return to Calendar</a>
                <?php else: ?>
                    <a class="button cancel" href="index.php" style="margin-top: -.5rem">Return to Dashboard</a>
                <?php endif ?>

                <!-- Require at least one checkbox be checked -->
                <script type="text/javascript">
                    $(document).ready(function(){
                        var checkboxes = $('.checkboxes');
                        checkboxes.change(function(){
                            if($('.checkboxes:checked').length>0) {
                                checkboxes.removeAttr('required');
                            } else {
                                checkboxes.attr('required', 'required');
                            }
                        });
                    });
                </script>
        </main>
    </body>
</html>

