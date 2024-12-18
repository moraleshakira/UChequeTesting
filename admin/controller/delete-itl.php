<?php
require '../config/config.php';
session_start();

if (!isset($_SESSION['auth_user'])) {
    header('Location: ../login.php');
    exit;
}

if (isset($_GET['itl_id']) && is_numeric($_GET['itl_id'])) {
    $itl_id = (int)$_GET['itl_id'];

    $query = "SELECT filePath FROM itl_extracted_data WHERE id = ?";
    $stmt = $con->prepare($query);

    if (!$stmt) {
        die("Error preparing the query: " . $con->error);
    }

    $stmt->bind_param('i', $itl_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($filePath);

    if ($stmt->fetch()) {
        // The filePath should only be the file name (not the entire path).
        $fullFilePath = "../../uploads/" . $filePath;

        // Debug: Output the file path to check if it is correct.
        echo $fullFilePath;  // Use for debugging, to verify the path

        if (file_exists($fullFilePath)) {
            if (!unlink($fullFilePath)) {
                $last_error = error_get_last();
                $_SESSION['status'] = "Failed to delete the file. Error: " . $last_error['message'];
                $_SESSION['status_code'] = "error";
                header('Location: ../itl.php');
                exit;
            } else {
                // File deletion successful
                echo "File deleted successfully.";  // Debugging output
            }
        } else {
            // The file doesn't exist, show an error
            echo "File does not exist.";  // Debugging output
        }
    }

    // Delete the record from the database
    $deleteQuery = "DELETE FROM itl_extracted_data WHERE id = ?";
    $deleteStmt = $con->prepare($deleteQuery);

    if (!$deleteStmt) {
        die("Error preparing the delete query: " . $con->error);
    }

    $deleteStmt->bind_param('i', $itl_id);

    if ($deleteStmt->execute()) {
        $_SESSION['status'] = "ITL and associated file deleted successfully!";
        $_SESSION['status_code'] = "success";
        header('Location: ../itl.php');
    } else {
        $_SESSION['status'] = "Failed to delete ITL.";
        $_SESSION['status_code'] = "error";
        header('Location: ../itl.php');
    }

    $deleteStmt->close();
} else {
    $_SESSION['status'] = "Invalid ITL ID.";
    $_SESSION['status_code'] = "error";
    header('Location: ../itl.php');
}

$con->close();
?>
