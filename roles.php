<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_roles = isset($_SESSION['roles']) ? $_SESSION['roles'] : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--=============== REMIXICONS ===============-->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">

    <!--=============== CSS ===============-->
    <link rel="stylesheet" href="assets/login.css">

    <title>Ucheque - Roles</title>
</head>
<body>
    <div class="role-selection">
        <h1>Select Your Role</h1>

        <?php if (in_array('hr', $user_roles)): ?>
            <form action="/hr/h_dash.php">
                <button type="submit" class="login__button1">HR Dashboard</button>
            </form>
        <?php endif; ?>

        <?php if (in_array('staff', $user_roles)): ?>
            <form action="/staff/s_dash.php">
                <button type="submit" class="login__button1">Staff Dashboard</button>
            </form>
        <?php endif; ?>

        <?php if (in_array('faculty', $user_roles)): ?>
            <form action="/faculty/f_dash.php">
                <button type="submit" class="login__button1">Faculty Dashboard</button>
            </form>
        <?php endif; ?>

        <?php if (in_array('admin', $user_roles)): ?>
            <form action="/admin/index.php">
                <button type="submit" class="login__button1">Admin Dashboard</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
