<?php
include '../config/config.php'; // Database connection

// Directory where files will be uploaded
$uploadDir = '../uploads/';
$fileName = basename($_FILES['uploadFile']['name']);
$filePath = $uploadDir . $fileName;

// Sanitize filename
$fileNameSanitized = preg_replace("/[^a-zA-Z0-9\.\-_]/", "_", $fileName);

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (move_uploaded_file($_FILES['uploadFile']['tmp_name'], $filePath)) {
    // File successfully uploaded
    $fullName = $_POST['fullName'];
    $id = $_POST['id'];
    $academicYear = $_POST['academicYear'];
    $academicSemester = $_POST['academicSemester'];

    $sql = "INSERT INTO uploaded_files (employeeId, fullName, academicYear, academicSemester, filePath) 
            VALUES (?, ?, ?, ?, ?)";
    
    // Prepare the statement
    if (!$stmt = $conn->prepare($sql)) {
        die("SQL Error: " . $conn->error); // Debugging error
    }

    // Bind parameters
    $stmt->bind_param("sssss", $id, $fullName, $academicYear, $academicSemester, $filePath);

    // Execute the query
    if ($stmt->execute()) {
        header("Location: ../admin/itl.php?message=File uploaded and saved successfully.");
    } else {
        echo "Database execution error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Error uploading file.";
}

$conn->close();
?>
