<?php
require_once 'database/dbShifts.php';
//require_once 'database/dbPersons.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shiftID = $_POST['shiftID'];
    $description = $_POST['description'];
    //$test = retrieve_persons_by_name ("Bob SPCA");
    //alert($test);

    if (!empty($shiftID) && !empty($description)) {
        clockOutByShiftID($shiftID, $description)
        //archive_volunteer($volunteerID);
        echo "Success";
    } else {
        echo "Missing shiftID or description.";
    }
}
?>
