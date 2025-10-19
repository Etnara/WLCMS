<?php
require_once __DIR__ . '/database/dbPersons.php';
require_once __DIR__ . '/database/dbShifts.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $personID = $_POST['personID'];
    //$test = retrieve_persons_by_name ("Bob SPCA");
    //alert($test);
    if (!empty($personID)){
        $result = archive_volunteer($personID);
        if ($result["success"]){
            echo "success";
        } else{
            echo "Error: " + $result['message'];
        }
        
    } else{
        echo "person does not exist";
    }
}
?>
