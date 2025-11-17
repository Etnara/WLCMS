<?php
    // Template for new VMS pages. Base your new page on this one

    // Make session information accessible, allowing us to associate
    // data with the logged-in user.
    session_cache_expire(30);
    session_start();

    $loggedIn = false;
    $accessLevel = 0;
    $userID = null;
    if (isset($_SESSION['_id'])) {
        $loggedIn = true;
        // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
        $accessLevel = $_SESSION['access_level'];
        $userID = $_SESSION['_id'];
    }  
    include 'database/dbEvents.php';
    
    //include 'domain/Event.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <?php require_once('universal.inc') ?>
        <link rel="stylesheet" href="css/messages.css"></link>
        <script src="js/messages.js"></script>
        <title>Fredericksburg SPCA Volunteer System | Events</title>
    </head>
    <body>
        <?php require_once('header.php') ?>
        <?php require_once('database/dbEvents.php');?>
        <?php require_once('database/dbPersons.php');?>
        <h1>Events</h1>
        <main class="general">
            <?php 
                //require_once('database/dbMessages.php');
                //$messages = get_user_messages($userID);
                //require_once('database/dbevents.php');
                //require_once('domain/Event.php');
                //$events = get_all_events();
                
                //$events = get_all_events_sorted_by_date_not_archived();
                //$archivedevents = get_all_events_sorted_by_date_and_archived();

                $allEvents = get_all_events_sorted_by_date_not_archived();
                $allArchivedEvents = get_all_events_sorted_by_date_and_archived();

                //$events = array_slice(get_all_events_sorted_by_date_not_archived(), $offset, $limit);
                //$archivedevents = array_slice(get_all_events_sorted_by_date_and_archived(), $offset, $limit);

                //$pageNum = isset($_GET['page']) ? max(0, intval($_GET['page'])) : 0;
                //$upcomingPageNum = isset($_GET['upcomingPage']) ? max(0, intval($_GET['upcomingPage'])) : 0;
                //$archivedPageNum = isset($_GET['archivedPage']) ? max(0, intval($_GET['archivedPage'])) : 0;
                $type = $_GET['type'] ?? 'upcoming';
                    $pageNum = isset($_GET['page']) ? max(0, intval($_GET['page'])) : 0;

                    if ($type === 'upcoming') {
                        $upcomingPageNum = $pageNum;
                        $archivedPageNum = 0; // default
                    } else {
                        $archivedPageNum = $pageNum;
                        $upcomingPageNum = 0; // default
                    }
                $limit = 10;
                $upcomingOffset = $upcomingPageNum * $limit;
                $archivedOffset = $archivedPageNum * $limit;
                $today = new DateTime();

                $filteredUpcoming = array_filter($allEvents, function($event) use ($today) {
                    return new DateTime($event->getDate()) >= $today;
                });
                $upcomingEvents = array_slice($filteredUpcoming, $upcomingOffset, $limit);

                $filteredArchived = array_filter($allArchivedEvents, function($event) use ($today) {
                    return new DateTime($event->getDate()) < $today;
                });
                $upcomingArchivedEvents = array_slice($filteredArchived, $archivedOffset, $limit);

                function pageExists($pageNum, $eventsArray) {
                    $limit = 10;
                    $offset = $pageNum * $limit;
                    return $pageNum >= 0 && $offset < count($eventsArray);
                }

                $upcomingNextExists = pageExists($upcomingPageNum + 1, $filteredUpcoming);
                $upcomingPrevExists = pageExists($upcomingPageNum - 1, $filteredUpcoming);

                $archivedNextExists = pageExists($archivedPageNum + 1, $filteredArchived);
                $archivedPrevExists = pageExists($archivedPageNum - 1, $filteredArchived);

                $upcomingEvents = array_slice($filteredUpcoming, $upcomingOffset, $limit);
                $upcomingArchivedEvents = array_slice($filteredArchived, $archivedOffset, $limit);

               //$eventsPage = array_slice($events, $offset, $limit);
               //$archivedPage = array_slice($archivedevents, $offset, $limit);

                //$allEvents = get_all_events_sorted_by_date_not_archived();
                //$allArchivedEvents = get_all_events_sorted_by_date_and_archived();

                //$events = array_slice($allEvents, $upcomingOffset, $limit);
                //$archivedevents = array_slice($allArchivedEvents, $archivedOffset, $limit);
                
/*
                function pageExists($pageNum, $archived = false) {
                    $limit  = 10;
                    $offset = $pageNum * $limit;
                    if ($pageNum < 0) return false;

                    global $events, $archivedevents;
                    $all = $archived ? $archivedevents : $events;
                    $slice = array_slice($all, $offset, $limit);
                    return !empty($slice);
                }
*/
                
                /*
                $upcomingNextExists = pageExists($upcomingPageNum + 1, $allEvents);
                $upcomingPrevExists = pageExists($upcomingPageNum - 1, $allEvents);

                $archivedNextExists = pageExists($archivedPageNum + 1, $allArchivedEvents);
                $archivedPrevExists = pageExists($archivedPageNum - 1, $allArchivedEvents);
*/


                //$nextExists = pageExists($pageNum + 1);
                //$prevExists = pageExists($pageNum - 1);

                //$today = new DateTime(); // Current date
                
                // Filter out expired events
               /* $upcomingEvents = array_filter($events, function($event) use ($today) {
                    $eventDate = new DateTime($event->getDate());
                    return $eventDate >= $today; // Only include events on or after today
                });*/


               /* $upcomingArchivedEvents = array_filter($archivedevents, function($event) use ($today) {
                    $eventDate = new DateTime($event->getDate());
                    return $eventDate >= $today; // Only include events on or after today
                });*/

                $user = retrieve_person($userID);
                //sizeof($upcomingEvents
                if (sizeof($upcomingEvents) > 0 || sizeof($upcomingArchivedEvents) > 0): ?>
                <div class="table-wrapper">
                    <h2>Upcoming Events</h2>
                    <table class="general">
                        <thead>
                            <tr>
                                <th style="width:1px">Title</th>
                                <th>Event Type</th>
                                <th>Description</th>
                                <th style="width:1px">Date</th>
                                
                            
                            </tr>
                        </thead>
                        <tbody class="standout">
                            <?php 
                                #require_once('database/dbPersons.php');
                                #require_once('include/output.php');
                                #$id_to_name_hash = [];
                                foreach ($upcomingEvents as $event) {
                                    $eventID = $event->getID();
                                    $title = $event->getName();
                                    $date = $event->getDate();
                                    $startTime = $event->getStartTime();
                                    $endTime = $event->getEndTime();
                                    $description = $event->getDescription();
                                    $capacity = $event->getCapacity();
                                    $completed = $event->getCompleted();
                                    $restricted_signup = $event->getRestrictedSignup();
                                    $training_level_required = $event->getTrainingLevelRequired();
                                    $type = $event->getEventType();
                                     if ($training_level_required == null) {
                                         $training_level_required = "N/A";
                                     }

                                    // Fetch signups for the event
                                    $signups = fetch_event_signups($eventID);
                                    $numSignups = count($signups); // Number of people signed up
                                    // Check if the user is signed up for this event
                                    $isSignedUp = check_if_signed_up($eventID, $userID);

                                    echo "
                                    <tr data-event-id='$eventID'>
                                        <td><a href='event.php?id=$eventID'>$title</a></td>
                                        <td>$type</td>
                                        <td>$description</td>
                                        <td>$date</td>";
                                        
                                    
                                    // Display Sign Up or Cancel button based on user sign-up status
                                       // if ($user_training_level != $training_level_required) {
                                        //    echo "
                                       //     <td><a class='button sign-up' style='background-color:#c73d06'>Training Not Met!</a></td>";
                                       // }
                                       // elseif ($isSignedUp) {
                                       //     echo "
                                       //     <td>
                                      //      <a class='button cancel' href='viewMyUpcomingEvents.php' >Already Signed Up!</a>
                                       //     </td>";
                                       // } elseif($numSignups >= $capacity) {
                                       //     echo "
                                       // } else {
                                       // echo "<td><a class='button sign-up' href='eventSignUp.php?event_name=" . urlencode($title) . "&restricted=" . urlencode($restricted_signup) . "'>Sign Up</a></td>";
                                      //  }
                                   // echo "</tr>";

                                    /*echo "
                                        <td>
                                            <a class='button cancel' href='#' onclick='document.getElementById(\"cancel-confirmation-wrapper-$eventID\").classList.remove(\"hidden\")'>Cancel</a>
                                            <div id='cancel-confirmation-wrapper-$eventID' class='modal hidden'>
                                                <div class='modal-content'>
                                                    <p>Are you sure you want to cancel your sign-up for this event?</p>
                                                    <p>This action cannot be undone.</p>
                                                    <form method='post' action='cancelEvent.php'>
                                                        <input type='submit' value='Cancel Sign-Up' class='button danger'>
                                                        <input type='hidden' name='event_id' value='$eventID'>
                                                        <input type='hidden' name='user_id' value='$userID'>
                                                    </form>
                                                    <button onclick=\"document.getElementById('cancel-confirmation-wrapper-$eventID').classList.add('hidden')\" class='button cancel'>Cancel</button>
                                                </div>
                                            </div>
                                        </td>";*/
                                    //if($accessLevel < 3) {
                                    //if($numSignups < $capacity) {
                                        /*echo "
                                        <tr data-event-id='$eventID'>
                                            <td>$restricted_signup</td>
                                            <td><a href='event.php?id=$eventID'>$title</a></td>
                                            <td>$date</td>
                                            <td>$numSignups / $capacity</td>
                                            <td><a class='button sign-up' href='eventSignUp.php?event_name=" . urlencode($title) . '&restricted=' . urlencode($restricted_signup) . "'>Sign Up</a></td>
                                        </tr>";*/
                                    //} else {
                                        /*echo "
                                        <tr data-event-id='$eventID'>
                                            <td>$restricted_signup</td>
                                            <td><a href='event.php?id=$eventID'>$title</a></td>
                                            <td>$date</td>
                                            <td>$numSignups / $capacity</td>
                                            <td><a class='button sign-up' style='background-color:#c73d06'>Sign Ups Closed!</a></td>
                                        </tr>";*/
                                    //}
                                    
                                    //} else {
                                        /*echo "
                                        <tr data-event-id='$eventID'>
                                            <td>$restricted_signup</td>
                                            <td><a href='Event.php?id=$eventID'>$title</a></td> <!-- Link updated here -->
                                            <td>$date</td>
                                            <td></td>
                                        </tr>";
                                    }
                                */}
                            ?>
                        </tbody>
                    </table>
                    <div class="info-section">
                        <p class="info-text" style="margin-bottom:3rem">
                            <?php if ($upcomingPrevExists): ?>
                                <a href="?type=upcoming&page=<?= $upcomingPageNum - 1 ?>" class="page-link" style="border: 1px solid #800000;  padding: 8px 12px; border-radius: 5px;"><- Previous</a>
                            <?php else: ?>
                                <span class="disabled-link" style="border: 1px solid #800000;  padding: 8px 12px; border-radius: 5px; background-color: lightgrey; color: darkgrey; cursor: not-allowed;">Previous</span>
                            <?php endif; ?>

                            <span class="current-page" style="font-weight: bold; color: #800000">Page <?= $upcomingPageNum + 1  ?></span>

                                <?php if ($upcomingNextExists): ?>
                                    <a href="?type=upcoming&page=<?= $upcomingPageNum + 1 ?>" class="page-link" style="border: 1px solid #800000;  padding: 8px 12px; border-radius: 5px;">Next -></a>
                                <?php else: ?>
                                    <span class="disabled-link" style="border: 1px solid #800000;  padding: 8px 12px; border-radius: 5px; background-color: lightgrey; color: darkgrey; cursor: not-allowed;">Next</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="table-wrapper">
                    <h2>Archived Events</h2>
                    <table class="general">
                        <thead>
                            <tr>
                                <th style="width:1px">Title</th>
                               <th style="width:1px">Event Type</th>
                               <th style="width:1px">Description</th>
                                <th style="width:1px">Date</th>
                                <th style="width:1px"></th>
                            </tr>
                        </thead>
                        <tbody class="standout">
                            <?php 
                                #require_once('database/dbPersons.php');
                                #require_once('include/output.php');
                                #$id_to_name_hash = [];
                                foreach ($upcomingArchivedEvents as $event) {
                                    $eventID = $event->getID();
                                    $title = $event->getName();
                                    $date = $event->getDate();
                                    $startTime = $event->getStartTime();
                                    $endTime = $event->getEndTime();
                                    $description = $event->getDescription();
                                    $capacity = $event->getCapacity();
                                    $completed = $event->getCompleted();
                                    $restricted_signup = $event->getRestrictedSignup();
                                    $type = $event->getEventType();
                                    if ($restricted_signup == 0) {
                                        $restricted_signup = "No";
                                    } else {
                                        $restricted_signup = "Yes";
                                    }

                                    // Fetch signups for the event
                                    $signups = fetch_event_signups($eventID);
                                    $numSignups = count($signups); // Number of people signed up
                                    //if($accessLevel < 3) {
                                        echo "
                                        <tr data-event-id='$eventID'>
                                            <td><a href='event.php?id=$eventID'>$title</a></td>
                                            <td>$type</td>
                                            <td>$description</td>
                                            <td>$date</td>
                                            
                                        </tr>";
                                    //} else {
                                        /*echo "
                                        <tr data-event-id='$eventID'>
                                            <td>$restricted_signup</td>
                                            <td><a href='Event.php?id=$eventID'>$title</a></td> <!-- Link updated here -->
                                            <td>$date</td>
                                            <td></td>
                                        </tr>";
                                    }
                                */}
                            ?>
                        </tbody>
                    </table>
                   <div class="info-section">
                        <div class="info-section">
                        <p class="info-text" style="margin-bottom:3rem">
                            <?php if ($archivedPrevExists): ?>
                                <a href="?type=archived&page=<?= $archivedPageNum - 1 ?>" class="page-link" style="border: 1px solid #800000;  padding: 8px 12px; border-radius: 5px;"><- Previous</a>
                            <?php else: ?>
                                <span class="disabled-link" style="border: 1px solid #800000;  padding: 8px 12px; border-radius: 5px; background-color: lightgrey; color: darkgrey; cursor: not-allowed;">Previous</span>
                            <?php endif; ?>

                            <span class="current-page" style="font-weight: bold; color: #800000">Page <?= $archivedPageNum  + 1  ?></span>

                                <?php if ($archivedNextExists): ?>
                                    <a href="?type=archived&page=<?= $archivedPageNum + 1 ?>" class="page-link" style="border: 1px solid #800000;  padding: 8px 12px; border-radius: 5px;">Next -></a>
                                <?php else: ?>
                                    <span class="disabled-link" style="border: 1px solid #800000;  padding: 8px 12px; border-radius: 5px; background-color: lightgrey; color: darkgrey; cursor: not-allowed;">Next</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <?php else: ?>
                <p class="no-events standout">There are currently no events available to view.<a class="button add" href="addEvent.php">Create a New Event</a> </p>
            <?php endif ?>
            <a class="button cancel" href="index.php">Return to Dashboard</a>
        </main>
    

    </body>
</html>

