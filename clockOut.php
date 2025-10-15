<?php
require_once 'database/dbShifts.php';
require_once 'database/dbPersons.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $personID = $_POST['personID'];
    //$test = retrieve_persons_by_name ("Bob SPCA");
    //alert($test);
    if (!empty($personID)){
        $result = archive_volunteer($personID);
        if ($result == true){
            echo "success";
        } else{
            echo "failed";
        }
        
    } else{
        echo "person does not exist";
    }
}
?>
