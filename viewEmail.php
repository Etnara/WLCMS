<?php
session_cache_expire(30);
session_start();

$loggedIn = false;
$accessLevel = 0;
$userID = null;
if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    $userID = $_SESSION['_id'];
}

require_once("database/dbEmails.php");
require_once("database/dbPersons.php");

$email = getEmail($_GET["id"]);
$currentEmail = $email;
$speaker = retrieve_person_by_email($email['speaker_email']);

?>

<!DOCTYPE html>
<html>
<head>
    <?php require_once('universal.inc'); ?>
    <link rel="stylesheet" href="css/messages.css">
    <link rel="stylesheet" href="css/normal_base.css">
    <!--<link href="css/normal_tw.css" rel="stylesheet">-->

    <title><?= htmlspecialchars($email['subject']) ?></title>
</head>
<body>
    <?php require_once('header.php') ?>
    <h1><?= htmlspecialchars($email['subject']) ?></h1>
    <main class="general justify-left p-6  ">    
        <div class="mb-8">
            <p><strong>From:</strong> <?= htmlspecialchars($speaker->get_first_name()) ?> <?= htmlspecialchars($speaker->get_last_name()) ?> (<?= htmlspecialchars($email['speaker_email']) ?>)</p>
        </div>
        <div class="mb-8">
            <p style="border: 1px solid black; padding: 10px; margin: 10px; border-radius: 5px;"><?= htmlspecialchars($email['body']) ?></p>
            <p className="text-left"><?= htmlspecialchars($email['time_sent']) ?></p>
        </div>
        <?php
    if ($email['next_email'] !== null) {
        $email = getEmail($email['next_email']);
        
    
    while ($email !== null) {
    $speaker = retrieve_person_by_email($email['speaker_email']);
    ?>
    <div class="mb-8">
        <p>
            <strong>
                Reply from <?= htmlspecialchars($speaker->get_first_name()) ?>
                <?= htmlspecialchars($speaker->get_last_name()) ?>
                (<?= htmlspecialchars($email['speaker_email']) ?>):
            </strong>
        </p>

        <p style="border: 1px solid black; padding: 10px; margin: 10px; border-radius: 5px;">
            <?= htmlspecialchars($email['body']) ?>
        </p>

        <p class="text-left"><?= htmlspecialchars($email['time_sent']) ?></p>
    </div>
    <?php


    if ($email['next_email'] !== null) {
        $currentEmail = $email;
        $email = getEmail($email['next_email']);

    } else {
        break;
    }
    }
}
?>
    
        <a href="#" class="button mr-4" onclick="openReplyComposer(
        '<?= htmlspecialchars($currentEmail['speaker_email'], ENT_QUOTES) ?>',
        '<?= htmlspecialchars($currentEmail['subject'], ENT_QUOTES) ?>'
        )">Reply</a>
        <div id="replyModal" class="modal hidden">
        <div class="modal-content">
            <h2>Reply</h2>

            <form id="replyForm" method="POST" action="curlMailgunOut.php">
            <input type="hidden" name="to" value='<?= htmlspecialchars($currentEmail['speaker_email'], ENT_QUOTES) ?>' id="replyTo">
            <input type="hidden" name="subject" value="<?= htmlspecialchars($currentEmail['subject'], ENT_QUOTES) ?>"id="replySubject">
            <input type="hidden" name="parentID" value="<?= $currentEmail['id'] ?>" id="replyParent">

            <textarea
                name="body"
                placeholder="Write your reply..."
                required
                rows="8"
                style="width:100%;"
            ></textarea>

            <div style="margin-top:10px;">
                <button type="submit" class="button mb-8" style="margin-bottom:8px;">Send</button>
                <button type="button" class="button" onclick="closeReplyComposer()">Cancel</button>
            </div>
            </form>
        </div>
        </div>
        <a href="inbox.php" class="button">Back to Inbox</a>

    </main>
    

</body>
</html>
<script>
    function openReplyComposer(to, subject) {
    document.getElementById("replyTo").value = to;
    document.getElementById("replySubject").value = "Re: " + subject;
    document.getElementById("replyModal").classList.remove("hidden");
    }

    function closeReplyComposer() {
    document.getElementById("replyModal").classList.add("hidden");
    }
</script>
