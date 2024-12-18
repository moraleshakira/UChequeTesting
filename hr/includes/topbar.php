<div class="main--content">
            <div class="header--wrapper">
            <div class="header--title">
                <h2>
                    <?php 
                    if (basename($_SERVER['PHP_SELF']) == 'h_profile.php') {
                        echo 'Profile'; 
                    } else {
                        echo 'Dashboard'; 
                    }
                    ?>
                </h2>
            </div>


              <?php
              $userId = $_SESSION['auth_user']['userId'];
              $query = "SELECT profilePicture FROM employee WHERE userId = ?";
              $stmt = $con->prepare($query);
              
              if ($stmt) {
                  $stmt->bind_param("i", $userId);
                  $stmt->execute();
                  $stmt->bind_result($imageBlob);
                  $stmt->fetch();
                  $stmt->close();
              }
              $imageDataUri = $imageBlob ? "data:image/jpeg;base64," . base64_encode($imageBlob) : "default-profile.png";
              ?>

              <div class="user--info">
    <div class="profile-dropdown">
        <div onclick="toggle()" class="profile-dropdown-btn">
            <div class="profile-img" style="background-image: url('<?php echo $imageDataUri; ?>');"></div>
            <i class="bx bx-chevron-down"></i>
        </div>

        <ul class="profile-dropdown-list">
            <li class="profile-dropdown-list-item">
                <a href="h_profile.php">
                    <i class="bx bxs-user"></i>
                    My Profile
                </a>
            </li>

            <li class="profile-dropdown-list-item">
                <a href="../admin/controller/logout.php">
                    <i class="bx bxs-log-out"></i>
                    Log out
                </a>
            </li>
        </ul>
    </div>
</div>

            </div>