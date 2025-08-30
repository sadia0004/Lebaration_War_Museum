<?php
// --- MANAGER DASHBOARD DATA HANDLER ---
// This script ensures the user is an authenticated manager and fetches all necessary data for the dashboard.

// Start the session to access user data.
session_start();

// 1. SECURITY CHECK: Verify user is logged in and is a manager.
// If 'user_id' is not set in the session or the role is not 'manager', redirect to login.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    // Destroy the session just in case there's any invalid data.
    session_destroy();
    header("Location: login.html");
    exit();
}

// Include the database connection file.
require_once 'db_connect.php';

// 2. DATA FETCHING: Get manager's details and museum statistics.

// Get the current manager's user ID from the session.
$manager_id = $_SESSION['user_id'];
$manager_name = 'Manager'; // Default name
$manager_photo_url = null; // Default photo

// Fetch the manager's full name and profile photo URL from the database.
$stmt = $conn->prepare("SELECT full_name, profile_photo_url FROM users WHERE user_id = ?");
$stmt->bind_param("i", $manager_id);
$stmt->execute();
$result = $stmt->get_result();
if ($user = $result->fetch_assoc()) {
    $manager_name = htmlspecialchars($user['full_name']);
    $manager_photo_url = $user['profile_photo_url'];
}
$stmt->close();


// --- Fetch Dashboard Statistics ---

// a) Total Artifacts
$total_artifacts_query = $conn->query("SELECT COUNT(*) AS total FROM artifacts");
$total_artifacts = $total_artifacts_query->fetch_assoc()['total'];

// b) Digital Exhibitions (Assuming 'Media' object type represents this)
$digital_exhibitions_query = $conn->query("SELECT COUNT(*) AS total FROM media WHERE media_type IN ('video', 'audio')");
$digital_exhibitions = $digital_exhibitions_query->fetch_assoc()['total'];

// c) Monthly Visitors (Static for now, would require a logging system)
$monthly_visitors = "12,847"; // Placeholder

// d) Pending Content Reviews (Placeholder, would need a 'status' column in artifacts/media)
$pending_reviews = "5"; // Placeholder


// The connection will be closed automatically at the end of the script execution.
?>
