<?php
include '../config/config.php';

$employeeId = $_POST['employeeId'];
$designation = $_POST['designation'];
$facultyCredits = $_POST['facultyCredits'];

$query = $conn->prepare("UPDATE employee SET designation = ?, facultyCredits = ? WHERE employeeId = ?");
$query->bind_param("sis", $designation, $facultyCredits, $employeeId);

if ($query->execute()) {
    header("Location: ../admin/itl.php?success=1");
} else {
    header("Location: ../admin/itl.php?error=1");
}
?>
