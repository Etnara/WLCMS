<?php
// Make session information accessible, allowing us to associate
// data with the logged-in user.
session_cache_expire(30);
session_start();

$loggedIn = false;
$accessLevel = 0;
$userID = null;
$isAdmin = false;
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 1) {
    header('Location: login.php');
    die();
}
if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
    $accessLevel = $_SESSION['access_level'];
    $isAdmin = $accessLevel >= 2;
    $userID = $_SESSION['_id'];
} else {
    header('Location: login.php');
    die();
}
if ($isAdmin && isset($_GET['id'])) {
    require_once('include/input-validation.php');
    $args = sanitize($_GET);
    $id = strtolower($args['id']);
} else {
    $id = $userID;
}
require_once('database/dbPersons.php');
require_once('database/dbCommunications.php');
//if (isset($_GET['removePic'])) {
// if ($_GET['removePic'] === 'true') {
// remove_profile_picture($id);
//}
//}

$user = retrieve_person($id);
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_hours'])) {
    require_once('database/dbPersons.php'); // already required, so you can just remove the duplicate
    $con = connect();

    $newHours = floatval($_POST['new_hours']);
    $safeID = mysqli_real_escape_string($con, $id);

    $update = mysqli_query($con, "
        UPDATE dbpersons
        SET total_hours_volunteered = $newHours
        WHERE id = '$safeID'
        ");

    if ($update) {
        $user = retrieve_person($id); // refresh with updated hours
        echo '
        <div id="success-message" class="absolute left-[40%] top-[15%] z-50 bg-green-800 p-4 text-white rounded-xl text-xl">
        Hours updated successfully!
        </div>
        <script>
        setTimeout(() => {
        const msg = document.getElementById("success-message");
        if (msg) msg.remove();
        }, 3000);
        </script>
        ';
    } else {
        echo '<div class="absolute left-[40%] top-[15%] z-50 bg-red-800 p-4 text-white rounded-xl text-xl">Failed to update hours.</div>';
    }

}

$viewingOwnProfile = $id == $userID;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['url'])) {
        if (!update_profile_pic($id, $_POST['url'])) {
            header('Location: viewProfile.php?id='.$id.'&picsuccess=False');
        } else {
            header('Location: viewProfile.php?id='.$id.'&picsuccess=True');
        }
    }
}

$con = connect();

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_topic'])) {
    $topic = $_POST['delete_topic'];
    mysqli_execute_query($con, "
        DELETE
        FROM speaker_topics
        WHERE speaker='$id'
        AND topic=?
    ", [$topic]);
}

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_topic_dropdown'])) {
    $topic = $_POST['add_topic_dropdown'] == 'New' ?
        $_POST['add_topic_text']
    :   $_POST['add_topic_dropdown'];
    if ($topic)
        mysqli_execute_query($con, "
            INSERT INTO speaker_topics
            SELECT '$id', ?
            WHERE NOT EXISTS (
                SELECT *
                FROM speaker_topics
                WHERE speaker='$id'
                AND topic=?
            )
        ", [$topic, $topic]);
}

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_notes'])) {
    $notes = empty($_POST['update_notes']) ?
        null
    :   $_POST['update_notes'];
    mysqli_execute_query($con, "
        UPDATE dbpersons
        SET notes=?
        WHERE id='$id'
    ", [$notes]);
}

$person = mysqli_query($con, "
    SELECT *
    FROM dbpersons
    WHERE id = '$id'
")->fetch_assoc();
$speaker_topics = mysqli_query($con, "
    SELECT topic
    FROM speaker_topics
    WHERE speaker = '$id'
");
$other_topics = mysqli_query($con, "
    SELECT distinct(topic)
    FROM `speaker_topics`
    WHERE topic NOT IN (
        SELECT topic
        FROM speaker_topics
        WHERE speaker='$id'
    )
");
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Profile Page</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
        function showSection(sectionId) {
        const sections = document.querySelectorAll('.profile-section');
        sections.forEach(section => section.classList.add('hidden'));
        document.getElementById(sectionId).classList.remove('hidden');

        const tabs = document.querySelectorAll('.tab-button');
        tabs.forEach(tab => {
        tab.classList.remove('border-b-4', 'border-red-800');
        tab.classList.add('hover:border-b-2', 'hover:border-red-700');
        });

        const activeTab = document.querySelector(`[data-tab="${sectionId}"]`);
        activeTab.classList.add('border-b-4', 'border-red-800');
        activeTab.classList.remove('hover:border-b-2', 'hover:border-red-700');
        localStorage.setItem('activeTab', sectionId);
        }

        window.onload = () => {
            if (document.referrer.includes(window.location.href)) {
                const activeTab = localStorage.getItem('activeTab');
                showSection(activeTab ? activeTab : 'personal');
            } else {
                showSection('personal'); // Load 'personal' if coming from a different page
            }
        };
        </script>
        <?php
        require_once('header.php');
        require_once('include/output.php');
        ?>

    </head>
    <?php if ($id == 'vmsroot'): ?>
    <div class="absolute left-[40%] top-[20%] bg-red-800 p-4 text-white rounded-xl text-xl">The root user does not have a profile.</div>
    </main></body></html>
<?php die() ?>
<?php elseif (!$user): ?>
<div class="absolute left-[40%] top-[20%] bg-red-800 p-4 text-white rounded-xl text-xl">User does not exist.</div>
</main></body></html>
<?php die() ?>
<?php endif ?>
<?php if (isset($_GET['editSuccess'])): ?>
<div class="absolute left-[40%] top-[15%] z-50 bg-green-800 p-4 text-white rounded-xl text-xl">Profile updated successfully!</div>
<?php endif ?>
<?php if (isset($_GET['rscSuccess'])): ?>
<div class="absolute left-[40%] top-[15%] z-50 bg-green-800 p-4 text-white rounded-xl text-xl">User role/status updated successfully!</div>
<?php endif ?>

<body class="bg-gray-100">
    <!-- Hero Section -->
    <div class="h-48 relative" style="background-image: url('images/heads.jpg')">
    </div>

    <!-- Profile Content -->
    <div class="max-w-6xl mx-auto px-4 -mt-20 relative z-10 flex flex-col md:flex-row gap-6">
        <!-- Left Box -->
        <div class="w-full md:w-1/3 bg-white border border-gray-300 rounded-2xl shadow-lg p-6 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center">
                    <?php if ($viewingOwnProfile): ?>
                    <h2 class="text-xl font-semibold mb-4">My Profile</h2>
                    <h2 class="mb-4">Edit Icon Placeholder</h2>
                    <?php else: ?>
                    <h2 class="text-xl font-semibold mb-4">Viewing <?php echo $user->get_first_name() . ' ' . $user->get_last_name() ?></h2>
                    <?php endif ?>
                </div>
                <div class="space-y-2 divide-y divide-gray-300">
                    <div class="flex justify-between py-2">
                        <?php echo '<span class="font-medium">Organisation</span><span>' . ($person["organization"] ?? "None") . "</span>"; ?>
                    </div>
                    <div class="flex justify-between py-2">
                        <?php echo"<span class=\"font-medium\">Email</span><span>{$person["email"]}</span>"; ?>
                    </div>
                    <div class="flex justify-between py-2">
                        <?php
                        require_once('include/output.php');
                        echo "<span class=\"font-medium\">Phone Number</span><span>" . formatPhoneNumber($person['phone1']) . "</span>";
                        ?>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="font-medium">Status</span><span><?php
                            echo $person["status"];
                            ?></span>
                    </div>
                </div>
            </div>
            <div class="mt-6 space-y-2">
                <button onclick="window.location.href='editProfile.php<?php if ($id != $userID) echo '?id=' . $id ?>';" class="text-lg font-medium w-full px-4 py-2 bg-red-800 text-white rounded-md hover:bg-red-700 cursor-pointer">Edit Profile</button>

                <!-- -->
                <?php if ($id != $userID): ?>
                <button onclick="window.location.href='speakerList.php';" class="text-lg font-medium w-full px-4 py-2 border-2 border-gray-300 text-black rounded-md hover:border-red-700 cursor-pointer">Return to Speaker List</button>
                <?php endif ?>
                <!-- -->


                <?php if ($accessLevel < 2) : ?>
                <button onclick="window.location.href='volunteerReport.php?id=<?php echo $user->get_id() ?>';" class="text-lg font-medium w-full px-4 py-2 border-2 border-gray-300 text-black rounded-md hover:border-blue-700 cursor-pointer">My Volunteering Report</button>
                <?php endif ?>
                <button onclick="window.location.href='index.php';" class="text-lg font-medium w-full px-4 py-2 border-2 border-gray-300 text-black rounded-md hover:border-red-700 cursor-pointer">Return to Dashboard</button>
            </div>
        </div>

        <!-- Right Box -->
        <div class="w-full md:w-2/3 bg-white rounded-2xl shadow-lg border border-gray-300 p-6">
            <!-- Tabs -->
            <div class="flex border-b border-gray-300 mb-4">
                <button class="tab-button px-4 py-2 text-lg font-medium text-gray-700 border-b-4 border-red-800" data-tab="personal" onclick="showSection('personal')">Topics</button>
                <button class="tab-button px-4 py-2 text-lg font-medium text-gray-700" data-tab="contact" onclick="showSection('contact')">Notes</button>
                <button class="tab-button px-4 py-2 text-lg font-medium text-gray-700" data-tab="communications" onclick="showSection('communications')">Past Communications</button>

            </div>

            <!-- Topics Section -->
            <div id="personal" class="profile-section space-y-4">
                <div>
<table>
                    <?php
                    foreach ($speaker_topics as $topic) {
                        echo "
                            <tr>
                                <form action=\"viewProfile.php?id={$id}\" method=\"post\">
                                    <td align=\"center\" style=\"width: 3rem;\">
                                        <button
                                            type=\"submit\"
                                            name=\"delete_topic\"
                                            value=\"{$topic['topic']}\"
                                            class=\"cursor-pointer\"
                        style=\"background-color: #db393b; color: white; border: 2px solid var(--color-gray-300); border-radius: 0.6rem; padding: 0.2rem; margin-top: 0.2rem;\"
                                        >
                                            X
                                        </button>
                                    </td>
                                    <td class=\"text-lg font-medium\">{$topic['topic']}</td>
                                </form>
                            </tr>
                        ";
                    }
                    ?>
</table>
                </div>
                <div>

                    <form action="viewProfile.php?id=<?php echo $id ?>" method="post">

                        <table style="border: 0; width: 100%;">
                        <tr>
                            <td style="width: 10%;">
                                <input
                                    type="submit"
                                    value="Add Topic"
                                    class="text-lg font-medium w-full px-4 py-2 border-2 border-gray-300 hover:border-blue-700 cursor-pointer"
                                    style="border-radius: 1rem 0 0 1rem;"
                                />
                            </td>
                            <td>
                                <select
                                    name="add_topic_dropdown"
                                    id="add_topic_dropdown"
                                    onchange="toggleAddTopicText()"
                                    class="font-medium border-2 border-gray-300"
                                    style="padding: 0.75rem; border-radius: 0 1rem 1rem 0; width:100%"
                                >
                                    <option disabled selected value>-- Topic --</option>
                                    <option value="New">New</option>
                                    <?php
                                    foreach ($other_topics as $topic) {
                                        echo "<option value=\"{$topic['topic']}\">{$topic['topic']}</option>\n";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                            <td colspan="2">
                                <input
                                    type="text"
                                    name="add_topic_text"
                                    id="add_topic_text"
                                    onchange="toggleAddTopicText()"
                                    placeholder="Enter a new topic"
                                    class="font-medium border-2 border-gray-300"
                                    style="display: none; background-color: #e9e9ed; border-radius: 1rem; padding: 0.5rem; margin-top: 1rem; width:100%"
                                >
                            </td>
                        </table>

                    </form>

                    <script>
                        function toggleAddTopicText() {
                            const dropdown = document.getElementById('add_topic_dropdown');
                            const textbox = document.getElementById('add_topic_text');

                            textbox.style.display =
                                dropdown.value === 'New' ?
                                    'block'
                                :   'none';
                        }
                    </script>

                </div>
            </div>

            <!-- Notes Section -->
            <div id="contact" class="profile-section space-y-4 hidden">
                <div>
                    <form action="viewProfile.php?id=<?php echo $id ?>" method="post">
                        <textarea name="update_notes" placeholder="Write any notes you have" rows="3" style="resize:vertical; width:100%; border: 2px solid #cbd5e1; border-radius: 0.375rem; padding: 0.5rem;"><?php echo $person['notes']; ?></textarea>
                        <input type="submit" value="Save" class="text-lg font-medium w-full px-4 py-2 border-2 border-gray-300 text-black rounded-md hover:border-blue-700 cursor-pointer"/>
                    </form>
                </div>
            </div>

            <!-- Past Communications Section -->
             <div id="communications" class="profile-section space-y-4 hidden">
                    <?php
                        $communications = getAllCommunicationsFor($user->get_email());
                        if(count($communications)==0){
                            echo '<p style="text-align: center";>
                            There have been no communications with ' . $user->get_first_name() . ' ' . $user->get_last_name() .
                            '</p>';
                        }else{
                            echo
                            '<h1 class="mb-4" style="text-align: center">
                         History of all contact with ' . $user->get_first_name() . ' ' . $user->get_last_name().
                            '</h1>
                            <table style="margin: 0 auto; border: 0; border-collapse: separate; border-spacing: 30px 0; text-align:center;">
                                <tr>
                                    <th>Date</th>
                                    <th>Contacted By</th>
                                </tr>';
                            foreach($communications as $communication){

                                $admin = retrieve_person_by_email( $communication[0]);
                                echo '<tr>' .
                                '<td>' . $communication[1] . '</td>' .
                                '<td>' . $admin->get_first_name() . ' ' . $admin->get_last_name() . '</td>'
                                . '</tr>';
                            }
                            echo '</table>';
                        }
                    ?>

             </div>

        </div>
    </div>
    </div>
</body>
</html>

