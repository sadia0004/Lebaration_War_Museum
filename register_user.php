<?php
// register_user.php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- DB Connection for Museum ---
$host = "localhost";
$username = "root";
$password = "";
$database = "museum"; 

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$successMsg = "";
$errorMsg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $rawPassword = $_POST['password'];
    $userRole = trim($_POST['userRole']); // Capture the selected role from the form
    $profilePhotoPath = null; 

    // Basic validation for the role
    $allowedRoles = ['visitor', 'manager', 'admin'];
    if (empty($userRole) || !in_array($userRole, $allowedRoles)) {
        $errorMsg = "Please select a valid user role.";
    }

    // --- Check if email already exists ---
    if (empty($errorMsg)) {
        $checkStmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        if ($checkStmt) {
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkStmt->store_result();
            
            if ($checkStmt->num_rows > 0) {
                $errorMsg = "This email is already registered.";
            }
            $checkStmt->close();
        } else {
            $errorMsg = "Database error while checking existing user.";
        }
    }

    // --- Proceed if no errors ---
    if (empty($errorMsg)) {
        $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);

        // Handle profile photo upload
        if (isset($_FILES["profilePhoto"]) && $_FILES["profilePhoto"]["error"] === UPLOAD_ERR_OK) {
            $uploadDir = "uploads/profile_photos/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = time() . "_" . basename($_FILES["profilePhoto"]["name"]);
            $uploadPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES["profilePhoto"]["tmp_name"], $uploadPath)) {
                $profilePhotoPath = $uploadPath;
            }
        }

        // Set user status to 'active' by default
        $userStatus = 'active';

        $insertStmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, role, status, profile_photo_url) VALUES (?, ?, ?, ?, ?, ?)");
        if ($insertStmt) {
            $insertStmt->bind_param("ssssss", $fullName, $email, $hashedPassword, $userRole, $userStatus, $profilePhotoPath);
            if ($insertStmt->execute()) {
                header("Location: login.php?registered=success");
                exit();
            } else {
                $errorMsg = "Database error: could not create account.";
            }
            $insertStmt->close();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Digital Liberation War Museum</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
<div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md">
    <h2 class="text-3xl font-bold text-center mb-6 text-gray-800">Create Your Museum Account</h2>

    <?php if ($errorMsg): ?>
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"><?php echo htmlspecialchars($errorMsg); ?></div>
    <?php endif; ?>

    <form action="register_user.php" method="POST" enctype="multipart/form-data" class="space-y-5">
        <div>
            <label for="fullName" class="block font-medium text-gray-700 mb-1">Full Name</label>
            <input type="text" id="fullName" name="fullName" placeholder="Enter your full name" required class="w-full px-4 py-2 border rounded-md">
        </div>
        <div>
            <label for="email" class="block font-medium text-gray-700 mb-1">Email Address</label>
            <input type="email" id="email" name="email" placeholder="you@example.com" required class="w-full px-4 py-2 border rounded-md">
        </div>
        <div>
            <label for="password" class="block font-medium text-gray-700 mb-1">Password</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required class="w-full px-4 py-2 border rounded-md">
        </div>
        
        <div>
            <label for="userRole" class="block font-medium text-gray-700 mb-1">Register As</label>
            <select id="userRole" name="userRole" required class="w-full px-4 py-2 border rounded-md bg-white">
                <option value="" disabled selected>-- Select a Role --</option>
                <option value="visitor">Visitor</option>
                <option value="manager">Manager</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        
        <div>
            <label for="profilePhoto" class="block font-medium text-gray-700 mb-1">Profile Photo (Optional)</label>
            <input type="file" id="profilePhoto" name="profilePhoto" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
        </div>
        <button type="submit" class="w-full py-2 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700 transition">Register</button>
    </form>
    <p class="text-sm text-gray-500 mt-6 text-center">
        Already have an account? <a href="login.php" class="text-indigo-600 hover:underline">Login here</a>
    </p>
</div>
</body>
</html>