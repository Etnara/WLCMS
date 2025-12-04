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
if ($accessLevel < 2) {
    header('Location: index.php');
    die();
}
include_once "database/dbPersons.php";

//added
//$hasUnapproved = count(get_unapproved_speakers()) > 0; // for when speakers have not been approved yet
$hasUnapproved = true; // default testing for when speakers have not been approved yet

include_once "database/dbShifts.php";



//added
if (isset($_GET['status']) && isset($_GET['name'])) {
    $action = $_GET['status'] === 'approved' ? 'approved' : 'rejected';
    $name = htmlspecialchars($_GET['name']);
    echo "<script>alert('You {$action} {$name}');</script>";
}


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <title>WLCMS | Review Rejected Speakers</title>
  	<link href="css/normal_tw.css" rel="stylesheet">

<!-- BANDAID FIX FOR HEADER BEING WEIRD -->
<?php
$tailwind_mode = true;
require_once('header.php');
?>
<style>
        .date-box {
            /*background: #274471;*/
            padding: 7px 30px;
            border-radius: 50px;
            box-shadow: -4px 4px 4px rgba(0, 0, 0, 0.25) inset;
            color: white;
            font-size: 24px;
            font-weight: 700;
            text-align: center;
        }
        .dropdown {
            padding-right: 2px;
        }

</style>
<!-- BANDAID END, REMOVE ONCE SOME GENIUS FIXES -->

</head>
<body>

    <div class="hero-header">
        <div class="center-header">
            <!--<h1>View Checked In Volunteers</h1>-->
            <h1>Review Rejected Speakers</h1>
        </div>
    </div>

    <main>
        <div class="main-content-box w-full max-w-3xl p-6" style="max-width:80rem">

            <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <!--<th><input type="checkbox" id="selectAll" class="w-4 h-4"></th>-->
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Organization</th>
                            <th>Topic Summary</th>
                            <th>Accept/Delete</th>
                        </tr>
                    </thead>
                    <tbody>

<?php
$pageNum = isset($_GET['page']) ? intval($_GET['page']) : 0;
function display ($pageNum){
        $offset = $pageNum * 10;
        $limit = 10;
        $result = getRejectedSpeakers($offset,$limit);
        if ($result['message']=="success"){
            return $result['array'];
        }
        //echo "<script>console.error(" . json_encode($result['message']) . ");</script>";
        return $result['array'];
    }

    function pageExists($pageNum){
        $arr = display($pageNum);
        if (empty($arr)){
            return false;
        }
        return true;
    }

                        $date = date('Y-m-d');
                        //$checkedInPersons = display(0);

                        $checkedInPersons = display($pageNum);
                        $nextExists = pageExists($pageNum+1);
                        $prevExists = pageExists($pageNum-1);


                        //$all_volunteers = getall_volunteers();

                        /*foreach ($all_volunteers as $volunteer) {
                            $status = $volunteer->get_status();
                            if ($status=="Pending Speaker"){
                                $checkedInPersons[] = $volunteer;
                            }
                        } */
                            /*$volunteer_id = $volunteer->get_id();
                            $shift_id = get_open_shift($volunteer_id, $date);
                            if ($shift_id) {
                                $check_in_info = get_checkin_info_from_shift_id($shift_id);
                                $checkedInPersons[] = $check_in_info;
                            } */


                        if (empty($checkedInPersons)) {
                            echo "<tr><td colspan='6' class='text-center py-6'>No speakers awaiting review.</td></tr>";

                        } else {
                            foreach ($checkedInPersons as $check_in_info) {
                                $volunteer = $check_in_info;
                                if ($volunteer) {
                                  $firstName = htmlspecialchars((string)($volunteer->get_first_name()));
                                    $lastName = htmlspecialchars((string)($volunteer->get_last_name()));
                                    $fullName = "{$firstName} {$lastName}";


                                    $organization = method_exists($volunteer, 'get_organization') ? htmlspecialchars((string)($volunteer->get_organization()))
                                        : 'Unknown Org';
                                    $topics = method_exists($volunteer, 'get_topic_summary') ? htmlspecialchars((string)($volunteer->get_topic_summary()))
                                            : 'No topics listed';
                                    $isApproved = method_exists($volunteer, 'get_approved') ? $volunteer->get_approved() : false;
                                    $isRejected = function_exists('is_person_rejected') ? is_person_rejected($volunteer->get_id()) : false;

                                    echo "<tr>";
                                    //echo "<td><input type='checkbox' class='rowCheckbox w-4 h-4' value='{$fullName}'></td>";
                                    echo "<td>{$firstName}</td>";
                                    echo "<td>{$lastName}</td>";
                                    //added
                                    echo "<td>{$organization}</td>";
                                    echo "<td>{$topics}</td>";
                                    //added accept and reject buttons w/red exclamation to unapproved speakers
                                    echo "<td>
                                    <div style='display: flex; gap: 8px;'>
                                            <button type='button' style='background-color: #294877; color: white; border: 2px solid var(--color-gray-300); padding: 8px 16px; border-radius: 1rem;' onclick=\"confirmAction('accept', '{$fullName}','{$volunteer->get_id()}')
                                                \">Accept</button>
                                            <button type='button' style='background-color: #db393b; color: white; border: 2px solid var(--color-gray-300); padding: 8px 16px; border-radius: 1rem;' onclick=\"confirmAction('delete', '{$fullName}','{$volunteer->get_id()}')
                                                \">Delete</button>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>


        </div>

            

    <div class="info-section">
        <div class="blue-div"></div>
        <p class="info-text" style="margin-bottom:3rem">
            <!--
            Use this tool to filter and search for volunteers or participants by their role, event involvement, and status. Mailing list support is built in.
                    -->
            <?php if ($prevExists): ?>
                <a href="?page=<?= $pageNum - 1 ?>" class="page-link" style="border: 1px solid #800000;  padding: 8px 12px; border-radius: 5px;"><- Previous</a>
            <?php else: ?>
                <span class="disabled-link" style="border: 1px solid #800000;  padding: 8px 12px; border-radius: 5px; background-color: lightgrey; color: darkgrey; cursor: not-allowed;">Previous</span>
            <?php endif; ?>

            <span class="current-page" style="font-weight: bold; color: #800000">Page <?= $pageNum + 1 ?></span>

                <?php if ($nextExists): ?>
                    <a href="?page=<?= $pageNum + 1 ?>" class="page-link" style="border: 1px solid #800000;  padding: 8px 12px; border-radius: 5px;">Next -></a>
                <?php else: ?>
                    <span class="disabled-link" style="border: 1px solid #800000;  padding: 8px 12px; border-radius: 5px; background-color: lightgrey; color: darkgrey; cursor: not-allowed;">Next</span>
            <?php endif; ?>
        </p>
    </div>

    <div class="flex justify-center mt-6">
                <a href="index.php" class="return-button">Return to Dashboard</a>
                <a href="speakerList.php" class="return-button" style="margin-left: 2rem;">Return to Speaker List</a>
            </div>
    </main>

    <script>
        function bulkClockOut() {
            const selectedCheckboxes = document.querySelectorAll('.rowCheckbox:checked');
            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one volunteer to clock out.');
                return;
            }

            const description = prompt("Please enter a work description for the selected volunteers:");
            if (!description) return;

            const shiftIds = Array.from(selectedCheckboxes).map(cb => cb.value.split('|')[0]);
            const formData = new FormData();
            formData.append('description', description);
            shiftIds.forEach(id => formData.append('shift_ids[]', id));

            fetch('clockOutBulk.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Selected volunteers successfully clocked out!');
                    location.reload();
                } else {
                    alert('Failed to clock out: ' + data.error);
                }
            })
            .catch(error => {
                alert('Error occurred: ' + error.message);
            });
        }

        function clockOut(personID) {
            $doArchive = confirm("Are you sure you want to reject "+ personID +"?");
            if ($doArchive){
                fetch('clockOut.php',{
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `personID=${encodeURIComponent(personID)}`
                }).then(response => response.text())
                .then(data => {
                    console.log("PHP response:", data);
                    //alert("Response from PHP:\n" + data);
                    if (data.trim() === 'success') {
                        alert(personID + " was successfully archived!");
                    } else {
                        alert("Archiving failed for " + personID);
                    }
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    //alert("There was an error rejecting "+personID);
                    alert(error);
                });
            } else{
                alert("Rejection cancelled.")
            }
            /*if (description !== null && description.trim() !== "") {
                fetch('clockOut.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `shiftID=${encodeURIComponent(shiftID)}&description=${encodeURIComponent(description)}`
                })
                .then(response => response.text())
                .then(data => {
                    alert("Clocked out successfully!");
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("There was an error clocking out.");
                });
            } else {
                alert("Clock out cancelled. Description is required.");
            }*/
        }

        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById('selectAll').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.rowCheckbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
                toggleBulkActions();
            });

            document.querySelectorAll('.rowCheckbox').forEach(cb => {
                cb.addEventListener('change', toggleBulkActions);
            });

            function toggleBulkActions() {
                const anyChecked = [...document.querySelectorAll('.rowCheckbox')].some(cb => cb.checked);
                document.getElementById('bulk-actions').style.display = anyChecked ? 'flex' : 'none';
            }
        });

    //added
    function confirmAction(action, fullName,id) {
    const target = action === 'accept'
        ? `AcceptSpeaker.php?name=${fullName}&id=${id}`
        : `DeleteSpeaker.php?name=${fullName}&id=${id}`;
    if (confirm(`Are you sure you want to ${action} ${fullName}?`)) {
        window.location.href = target;
    }
}


    </script>

</body>
</html>