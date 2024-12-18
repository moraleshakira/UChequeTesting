<?php
include("../config/config.php");
session_start();

if (isset($_POST['addUser'])) {
    $employeeId = $_POST['employeeId'];
    $firstName = $_POST['firstName'];
    $middleName = isset($_POST['middleName']) ? $_POST['middleName'] : null;
    $lastName = $_POST['lastName'];
    $phoneNumber = isset($_POST['phoneNumber']) ? $_POST['phoneNumber'] : null;
    $emailAddress = $_POST['emailAddress'];
    $roles = isset($_POST['role']) ? $_POST['role'] : [];
    $profilePicture = null;
    // $department = isset($_POST['department']) ? $_POST['department'] : null; // department can be null if not required

    // Initialize an array for error messages
    $errorMessages = [];

    // Check if role is selected
    if (empty($roles)) {
        $errorMessages[] = "Role must be selected.";
    }

    // Check if the employeeId, phoneNumber, or emailAddress already exist
    $checkQuery = "SELECT * FROM `employee` WHERE `employeeId` = ? OR `phoneNumber` = ? OR `emailAddress` = ?";
    $checkStmt = mysqli_prepare($con, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "sss", $employeeId, $phoneNumber, $emailAddress);
    mysqli_stmt_execute($checkStmt);
    $result = mysqli_stmt_get_result($checkStmt);

    // Check for existing employeeId, phoneNumber, or emailAddress
    if ($row = mysqli_fetch_assoc($result)) {
        if ($row['employeeId'] == $employeeId) {
            $errorMessages[] = "Employee ID already taken.";
        }
        if ($row['phoneNumber'] == $phoneNumber) {
            $errorMessages[] = "Phone Number already taken.";
        }
        if ($row['emailAddress'] == $emailAddress) {
            $errorMessages[] = "Email Address already taken.";
        }
    }

    // If there are any errors, redirect back to the add user page with error messages
    if (!empty($errorMessages)) {
        $_SESSION['status'] = implode('<br>', $errorMessages); // Join the error messages with a line break
        $_SESSION['status_code'] = "error";
        header('Location: ../add-user.php');
        exit(0);
    }

    // If department is not required (i.e., role is not Faculty), set department to NULL
    if (!in_array(2, $roles)) {  // Assuming "Faculty" role has an ID of 2
        $department = null; // Non-Faculty roles don't require a department
    }

    // Prepare and execute the INSERT query
    $query = "INSERT INTO `employee` 
              (`employeeId`, `firstName`, `middleName`, `lastName`, `phoneNumber`, `emailAddress`, `password`, `profilePicture`) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)"; 

    $stmt = mysqli_prepare($con, $query);
    if (!$stmt) {
        die("Query preparation failed: " . mysqli_error($con));
    }

    // Ensure to bind parameters correctly, allowing department to be NULL
    mysqli_stmt_bind_param($stmt, "ssssssss", $employeeId, $firstName, $middleName, $lastName, $phoneNumber, $emailAddress, $password, $profilePicture);
    $execute = mysqli_stmt_execute($stmt);

    if ($execute) {
        $userId = mysqli_insert_id($con);

        // Insert roles if they exist
        if (!empty($roles)) {
            foreach ($roles as $role) {
                $roleQuery = "INSERT INTO `employee_role` (`userId`, `role_id`) VALUES (?, ?)";
                $roleStmt = mysqli_prepare($con, $roleQuery);
                if (!$roleStmt) {
                    die("Role query preparation failed: " . mysqli_error($con));
                }
                mysqli_stmt_bind_param($roleStmt, "ii", $userId, $role);
                if (!mysqli_stmt_execute($roleStmt)) {
                    die("Role query execution failed: " . mysqli_error($con));
                }
            }
        }

        $_SESSION['status'] = "Employee has been added successfully!";
        $_SESSION['status_code'] = "success";
        header('Location: ../s_user.php');
        exit(0);
    } else {
        echo "Error: " . mysqli_error($con);
    }

    mysqli_close($con);
}
?>
