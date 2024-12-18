<?php
include '../config/config.php';

if (isset($_GET['employeeId'])) {
    $employeeId = $_GET['employeeId'];

    $sql = "SELECT pdf_file, pdf_file_name FROM employee WHERE employeeId = '$employeeId'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        header("Content-type: application/pdf");
        header("Content-Disposition: inline; filename=" . $row['pdf_file_name']);
        echo $row['pdf_file'];
    } else {
        echo "File not found.";
    }
}

$conn->close();
?>
