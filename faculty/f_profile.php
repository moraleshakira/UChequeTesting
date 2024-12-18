<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');

$userId = $_SESSION['auth_user']['userId'];
$query = "SELECT `userId`, `employeeId`, `firstName`, `middleName`, `lastName`, `phoneNumber`, `emailAddress`, `password`, `designation`, `profilePicture`, `status` FROM `employee` WHERE `userId` = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    if ($user['profilePicture']) {
        $profilePicture = 'data:image/jpeg;base64,' . base64_encode($user['profilePicture']);
    } else {
        $profilePicture = '../images/people.jpg';
    }

    ?>
    <div class="tabular--wrapper">
        <div class="profile-container">
            <div class="sidebar2">
                <div class="profile-info">
                    <img src="<?php echo $profilePicture; ?>" alt="Profile Picture" class="profile-pic">
                    <h2><?php echo htmlspecialchars($_SESSION['auth_user']['fullName']); ?></h2>
                    <p><?php echo implode(', ', $_SESSION['roles']); ?></p>
                </div>
                <div class="sidebar-links">
                    <div class="info-section">
                        <p><strong>Email</strong><br><?php echo htmlspecialchars($user['emailAddress']); ?></p>
                    </div>
                    <div class="info-section">
                        <p><strong>Contact</strong><br><?php echo htmlspecialchars($user['phoneNumber']); ?></p>
                    </div>
                </div>
            </div>
            <div class="main-content">
                <div class="edit-profile">
                    <button class="tablinks" onclick="openTab(event, 'profile')">Profile Details</button>
                    <button class="tablinks" onclick="openTab(event, 'security')">Password & Security</button>
                </div>


                <div id="profile" class="tabcontent" style="display: block;">
                    <form class="profile-form">
                        <div class="form-section">
                            <label>First Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['firstName']); ?>" readonly>
                            <label>Middle Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['middleName']); ?>" readonly>
                            <label>Last Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['lastName']); ?>" readonly>
                        </div>
                    </form>
                </div>

                <div id="edit" class="tabcontent" style="display: none;">
                    <form class="profile-form">
                        <div class="form-section">
                            <label>Profile Picture</label>
                            <input type="text" placeholder="Choose photos">
                            <label>First Name</label>
                            <input type="text" value=" " placeholder="Shakira">
                            <label>Middle Name</label>
                            <input type="text" value="" placeholder="Shakira">
                            <label>Last Name</label>
                            <input type="text" value="" placeholder="Morales">
                            <label>Phone Number</label>
                            <input type="text" value="" placeholder="09123456789">
                            <label>Email</label>
                            <input type="text" value="" placeholder="Email">
                        </div>
                        <button type="submit" class="btn-pass">Save</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }
    </script>
    <?php
} else {
    echo "<h4>No Record Found!</h4>";
}

include('./includes/footer.php');
?>
