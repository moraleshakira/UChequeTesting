<?php
session_start();

$user_roles = isset($_SESSION['roles']) ? $_SESSION['roles'] : [];

if (empty($user_roles)) {
    header("Location: ../login.php");
    exit();
}

if (count($user_roles) === 1) {
    $single_role = $user_roles[0];
    switch ($single_role) {
        case 'Admin':
            $_SESSION['status'] = "Welcome " . $_SESSION['auth_user']['firstName'] . ' ' . $_SESSION['auth_user']['lastName'];
            $_SESSION['status_code'] = "success";
            header("Location: ./admin/index.php");
            break;
        case 'Hr':
            header("Location: ./hr/h_dash.php");
            break;
        case 'Staff':
            header("Location: ./staff/s_dash.php");
            break;
        case 'Faculty':
            header("Location: ./faculty/f_dash.php");
            break;
        default:
            $_SESSION['status'] = "Role not recognized.";
            $_SESSION['status_code'] = "error";
            header("Location: ../login.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/login.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>Login</title>
</head>
<body>
  <div class="wrapper">
    <div class="container main">
      <div class="row">
          <div class="col-md-6 side-image">
                      
              <!-------------      image     ------------->
              
              <img src="" alt="">
          </div>

          <div class="col-md-6 right">
              
              <div class="input-box">
                <form action="./controller/role_redirect.php" method="POST"  autocomplete="off"> 
                  <img src="./assets/images/logoall-grey.png" class="logo-font" alt="">
                  <header>Log As</header>
                 
                  <div class="input-field">
                    <?php if (in_array('Admin', $user_roles)): ?>
                    <button name="selected_role" type="submit" class="submit" value="Admin">Admin</button><br>
                    <?php endif; ?>

                    <?php if (in_array('Staff', $user_roles)): ?>
                    <button name="selected_role" type="submit" class="submit" value="Staff">Staff</button><br>
                    <?php endif; ?>

                    <?php if (in_array('Faculty', $user_roles)): ?>
                    <button name="selected_role" type="submit" class="submit" value="Faculty">Faculty</button><br>
                    <?php endif; ?>

                    <?php if (in_array('Hr', $user_roles)): ?>
                    <button name="selected_role" type="submit" class="submit" value="Hr">Hr</button><br>
                    <?php endif; ?>
                  </div> 
                  
                </form>
                
              </div>  
          </div>
      </div>
    </div>
</div>
</body>
</html>