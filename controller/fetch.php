<?php
include '../config/config.php';

$employeeId = $_GET['employeeId'];

$query = $conn->prepare("SELECT designation, facultyCredits FROM employee WHERE employeeId = ?");
$query->bind_param("s", $employeeId);
$query->execute();
$result = $query->get_result();
$data = $result->fetch_assoc();

echo json_encode($data);
?>
