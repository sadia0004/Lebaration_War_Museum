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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 
                        'serif': ['Playfair Display', 'serif'],
                        'sans': ['Inter', 'sans-serif'] 
                    },
                    colors: {
                        'liberation': {
                            'red': '#dc143c',
                            'green': '#006a4e',
                            'gold': '#ffd700'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .form-bg {
            background-image: linear-gradient(to bottom, rgba(0, 106, 78, 0.8), rgba(220, 20, 60, 0.8)), url('https://upload.wikimedia.org/wikipedia/commons/thumb/3/32/Martyred_Intellectuals_Memorial.jpg/1280px-Martyred_Intellectuals_Memorial.jpg');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen form-bg font-sans py-12">
<div class="bg-white/90 backdrop-blur-lg p-8 sm:p-10 rounded-3xl shadow-2xl w-full max-w-md border border-white/20">
    
    <div class="text-center mb-8">
        <a href="index.php">
            <img src="images/logo.png" alt="Museum Logo" class="w-20 h-20 mx-auto rounded-full border-4 border-white shadow-lg">
        </a>
        <h2 class="text-3xl font-bold font-serif text-center mt-4 text-gray-900">Create Your Museum Account</h2>
        <p class="text-slate-600">Join us to explore the history of 1971.</p>
    </div>

    <?php if ($errorMsg): ?>
        <div class="mb-4 p-4 bg-red-100 text-red-800 border-l-4 border-liberation-red rounded-r-lg" role="alert">
            <p class="font-semibold">Registration Failed</p>
            <p><?php echo htmlspecialchars($errorMsg); ?></p>
        </div>
    <?php endif; ?>

    <form action="register_user.php" method="POST" enctype="multipart/form-data" class="space-y-5">
        <div>
            <label for="fullName" class="block font-medium text-gray-700 mb-1.5">Full Name</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i data-lucide="user" class="w-5 h-5 text-gray-400"></i>
                </span>
                <input type="text" id="fullName" name="fullName" placeholder="Enter your full name" required class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg shadow-sm bg-gray-50 focus:ring-red-500/30 focus:border-red-500 transition">
            </div>
        </div>
        <div>
            <label for="email" class="block font-medium text-gray-700 mb-1.5">Email Address</label>
            <div class="relative">
                 <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i data-lucide="at-sign" class="w-5 h-5 text-gray-400"></i>
                </span>
                <input type="email" id="email" name="email" placeholder="you@example.com" required class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg shadow-sm bg-gray-50 focus:ring-red-500/30 focus:border-red-500 transition">
            </div>
        </div>
        <div>
            <label for="password" class="block font-medium text-gray-700 mb-1.5">Password</label>
            <div class="relative">
                 <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i data-lucide="lock" class="w-5 h-5 text-gray-400"></i>
                </span>
                <input type="password" id="password" name="password" placeholder="••••••••" required class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg shadow-sm bg-gray-50 focus:ring-red-500/30 focus:border-red-500 transition">
            </div>
        </div>
        
        <div>
            <label for="userRole" class="block font-medium text-gray-700 mb-1.5">Register As</label>
            <select id="userRole" name="userRole" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm bg-gray-50 focus:ring-red-500/30 focus:border-red-500 transition">
                <option value="" disabled selected>-- Select a Role --</option>
                <option value="visitor">Visitor</option>
                <option value="manager">Manager</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        
        <div>
            <label for="profilePhoto" class="block font-medium text-gray-700 mb-1.5">Profile Photo (Optional)</label>
            <input type="file" id="profilePhoto" name="profilePhoto" accept="image/*" class="block w-full text-sm text-slate-700 border border-slate-300 rounded-lg cursor-pointer bg-slate-50 focus:outline-none file:bg-slate-200 file:text-slate-700 file:px-4 file:py-2 file:mr-4 file:border-0 hover:file:bg-slate-300">
        </div>
        <button type="submit" class="w-full py-3 bg-gradient-to-r from-liberation-red to-liberation-green text-white font-semibold rounded-lg hover:shadow-lg transition-all duration-300 hover:scale-105">Register</button>
    </form>
    <p class="text-sm text-gray-600 mt-6 text-center">
        Already have an account? <a href="login.php" class="font-semibold text-liberation-green hover:underline">Login here</a>
    </p>
</div>
<script>
    lucide.createIcons();
</script>
</body>
</html>