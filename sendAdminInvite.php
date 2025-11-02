<?php 
require_once('include/input-validation.php');
include_once("sendEmail.php");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $email = $_GET['email'] ?? '';
    if (!empty($email) && validateEmail($email)) {
        sendAdminInvite($email);
            $message = json_encode("Email sent successfully!");
            echo "<script>
                alert($message);
                window.location.href = 'createAdminInvite.php';
            </script>";
        } else {
            $message = json_encode("Invalid Email");
            echo "<script>
                alert($message);
                window.history.back();
            </script>";
        }
    } else {
        echo "<script>
            alert('No email provided.');
            window.history.back();
        </script>";
    }
?>