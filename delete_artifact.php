<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Protect the page: ensure only managers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit();
}

// Check if an ID is provided
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header("Location: artifact_management.php?status=invalid_id");
    exit();
}

$artifactId = $_GET['id'];

// --- Database Connection ---
$host = "localhost";
$username = "root";
$password = "";
$database = "museum";
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- 1. Get the image path BEFORE deleting the record ---
$imagePath = null;
$stmtSelect = $conn->prepare("SELECT artifact_image_url FROM artifacts WHERE artifact_id = ?");
$stmtSelect->bind_param("i", $artifactId);
$stmtSelect->execute();
$result = $stmtSelect->get_result();
if ($result->num_rows > 0) {
    $imagePath = $result->fetch_assoc()['artifact_image_url'];
}
$stmtSelect->close();

// --- 2. Delete the artifact record from the database ---
$stmtDelete = $conn->prepare("DELETE FROM artifacts WHERE artifact_id = ?");
$stmtDelete->bind_param("i", $artifactId);

if ($stmtDelete->execute()) {
    // --- 3. If database deletion is successful, delete the image file from the server ---
    if ($imagePath && file_exists($imagePath)) {
        unlink($imagePath); // Deletes the file
    }
    // Redirect with success message
    header("Location: artifact_management.php?status=deleted");
} else {
    // Redirect with error message
    header("Location: artifact_management.php?status=delete_error");
}

$stmtDelete->close();
$conn->close();
exit();