<?php
include '../config/config.php';

if (isset($_GET['employeeId'])) {
    $employeeId = $_GET['employeeId'];

    $sql = "UPDATE employee SET pdf_file = NULL, pdf_file_name = NULL WHERE employeeId = '$employeeId'";
    if ($conn->query($sql) === TRUE) {
        header("Location: ../admin/itl.php?message=File+deleted+successfully");
    } else {
        echo "Error deleting file: " . $conn->error;
    }
}

$conn->close();
?>
