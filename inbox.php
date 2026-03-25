<?php
session_cache_expire(30);
session_start();

$loggedIn = false;
$accessLevel = 0;
$userID = null;
if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    //$accessLevel = $_SESSION['access_level'];
    $userID = $_SESSION['_id'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php require_once('universal.inc') ?>
    <link rel="stylesheet" href="css/messages.css">
    <link rel="stylesheet" href="css/compose_email.css">
    
    <script>
        function toggleBulkActions() {
            const checkboxes = document.querySelectorAll('.messageCheckbox');
            const bulkBar = document.getElementById('bulk-actions');
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            bulkBar.style.display = anyChecked ? 'flex' : 'none';
        }

        function toggleSelectAll(masterCheckbox) {
            const checkboxes = document.querySelectorAll('.messageCheckbox');
            checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
            toggleBulkActions();
        }

        function confirmAndSubmit(formId, msg) {
            if (confirm(msg)) {
                document.getElementById(formId).submit();
            }
        }
        function openCompose() {
            const win = document.getElementById('compose-window');
            win.classList.remove('hidden', 'minimized');
        }

        function closeCompose() {
            document.getElementById('compose-window').classList.add('hidden');
        }

        function minimizeCompose() {
            document.getElementById('compose-window').classList.toggle('minimized');
        }

        
    </script>
    <style>
        #bulk-actions {
            display: none;
            justify-content: flex-end;
            align-items: center;

        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
    <title>Email Inbox</title>
</head>
<body>
<?php require_once('header.php') ?>
<h1>Email Inbox</h1>
<main class="general">



<button type="button" id="compose-btn" onclick="openCompose()">Compose New Email</button>

<!-- Compose Window -->
    <div id="compose-window" class="compose-window hidden">
    <div class="compose-header" id="compose-drag-handle">
        <span>New Message</span>
        <div class="compose-controls">
        <button type="button" onclick="minimizeCompose()" title="Minimize">─</button>
        <button type="button" onclick="closeCompose()" title="Close">✕</button>
        </div>
    </div>
    <div class="compose-body" id="compose-body">
        <div style="flex">
            <input type="text" name="to" placeholder="To" class="compose-field">
            <select name="List_of_Speakers" class="compose-select">
                <option value="">-- Or select from previous speakers --</option>
                <?php
                require_once('database/dbPersons.php');
                $speakers = getAcceptedSpeakers();
                foreach ($speakers as $speaker) {
                    echo '<option value="' . htmlspecialchars($speaker->get_email()) . '">' 
                        . htmlspecialchars($speaker->get_first_name() . ' ' . $speaker->get_last_name()) . " (" . htmlspecialchars($speaker->get_email()) . ")"
                        . '</option>';
                }
                ?>
            </select>
        </div>

        <input type="text"   name="subject" placeholder="Subject" class="compose-field">
        <textarea name="body" class="compose-textarea" placeholder="Write your message..."></textarea>
        <div class="compose-footer">
        <button class="send-btn" type="submit" onclick="sendEmail()">Send</button>
        </div>
    </div>
    </div>
    
    <?php
    require_once('database/dbinfo.php');
    require_once('database/dbEmails.php');
    require_once('database/dbPersons.php');
    require_once('include/output.php');

    
    $allEmails = getAllEmails();

    ?>
    <?php if (count($allEmails) > 0): ?>
        <form id="bulkDeleteForm" action="deleteEmailChain.php" method="POST">
            <div class="top-bar">
                <div id="bulk-actions" style="display:none;">
                    <span><strong>With Selected:</strong></span>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="general">
                    <thead>
                        <tr>
                            
                            <th>Speaker</th>
                            <th>Title</th>
                            <th>Received</th>
                            <th>Delete Email</th>
                        </tr>
                    </thead>
                    <tbody class="standout">
                        <?php 
                            $id_to_name_hash = [];
                        
                                foreach ($allEmails as $email):
                                    $emailID = $email['id'];
                                    $speaker_email = $email['speaker_email'] ?? '';
                                    $title = $email['subject'];
                                    $time = new DateTime($email['time_sent']);
                                    $class = 'message email';
                                    $speaker = retrieve_person_by_email($speaker_email);
                                
                                
                        ?>
                        <tr class="<?= $class ?>" data-message-id="<?= $emailID ?>" onclick="window.location='viewEmail.php?id=<?= $emailID ?>'">
                            <td><?= $speaker->get_first_name() . " " . $speaker->get_last_name() ?></td>
                            <td><?= $title ?></td>
                            <td><?= $time->format("m/d/Y") ?></td>
                            <td>
                                <a class="button delete" 
                                href="deleteEmailChain.php?id=<?= $emailID ?>" 
                                onclick="return confirm('Are you sure you want to delete this email chain?');">
                                Delete Chain
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
        <?php else: ?>
            <p class="no-messages standout">You currently have no notifications.</p>
        <?php endif; ?>

        <a class="button cancel" href="index.php">Return to Dashboard</a>

        <script>
            document.getElementById('selectAll').addEventListener('change', function () {
                const checkboxes = document.querySelectorAll('.rowCheckbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
                toggleBulkActions();
            });

            document.querySelectorAll('.rowCheckbox').forEach(cb => {
                cb.addEventListener('change', toggleBulkActions);
            });

            function toggleBulkActions() {
                const anyChecked = [...document.querySelectorAll('.rowCheckbox')].some(cb => cb.checked);
                document.getElementById('bulk-actions').style.display = anyChecked ? 'block' : 'none';
            }
            document.querySelector('select[name="List_of_Speakers"]').addEventListener('change', function() {
            if (this.value) {
                document.querySelector('input[name="to"]').value = this.value;
            }
        });
        function sendEmail() {
            const to       = document.querySelector('input[name="to"]').value.trim();
            const speaker  = document.querySelector('select[name="List_of_Speakers"]').value.trim();
            const subject  = document.querySelector('input[name="subject"]').value.trim();
            const body     = document.querySelector('textarea[name="body"]').value.trim();

            const recipient = to !== '' ? to : speaker;

            if (!recipient || recipient === '') {
                alert('Please enter a recipient.');
                return;
            }

            const formData = new FormData();
            formData.append('to', recipient);
            formData.append('subject', subject);
            formData.append('body', body);

            fetch('curlMailgunOut.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                alert('Email sent!');
                closeCompose();
            })
            .catch(err => {
                alert('Failed to send email.');
                console.error(err);
            });
}
        </script>
</main>
</body>
</html>