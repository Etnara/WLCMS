<?php
    session_cache_expire(30);
    session_start();
    header("refresh:2;url=calendar.php");
?>

    <!DOCTYPE html>
    <html>
        <head>
            <?php require_once('universal.inc') ?>
            <title>Create Event</title>
        </head>
        <body>
            <?php require_once('header.php') ?>
            <h1>Event Created!</h1>
        </body>
    </html>
