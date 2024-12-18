<?php
include '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pdfFile'])) {
    $employeeId = $_POST['employeeId'];
    $pdfFile = $_FILES['pdfFile']['tmp_name'];
    $pdfFileName = $_FILES['pdfFile']['name'];
    $pdfFileSize = $_FILES['pdfFile']['size'];

    if ($pdfFile && $pdfFileSize > 0 && mime_content_type($pdfFile) === 'application/pdf') {
        // Read the PDF file content
        $pdfContent = file_get_contents($pdfFile);
        $pdfContent = mysqli_real_escape_string($conn, $pdfContent);

        // Insert PDF into the database
        $sql = "UPDATE employee SET pdf_file = '$pdfContent', pdf_file_name = '$pdfFileName' WHERE employeeId = '$employeeId'";

        if ($conn->query($sql) === TRUE) {
            header("Location: ../admin/itl.php?message=File+uploaded+successfully");
        } else {
            echo "Error uploading file: " . $conn->error;
        }
    } else {
        echo "Please upload a valid PDF file.";
    }

    $conn->close();
}
?>
