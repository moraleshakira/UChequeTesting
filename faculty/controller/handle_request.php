<?php
session_start();
require '../../vendor/autoload.php';
require '../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['auth_user'])) {
    echo "Error: User not logged in.";
    exit;
}

$userId = $_SESSION['auth_user']['userId']; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startMonth = $con->real_escape_string($_POST['startMonth']);
    $endMonth = $con->real_escape_string($_POST['endMonth']);

    $monthOrder = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    $startIndex = array_search($startMonth, $monthOrder);
    $endIndex = array_search($endMonth, $monthOrder);

    if ($startIndex === false || $endIndex === false || $startIndex > $endIndex) {
        echo "Error: Invalid month range.";
        exit;
    }

    $query = "INSERT INTO request (userId, startMonth, endMonth, status) 
              VALUES ('$userId', '$startMonth', '$endMonth', 'Pending')";

    if ($con->query($query)) {
        $_SESSION['status'] = "Request submitted successfully!";
        $_SESSION['status_code'] = "success";
        header("Location: ../f_request.php"); 
        exit;
    } else {
        echo "Error: " . $con->error;
    }
}
?>
