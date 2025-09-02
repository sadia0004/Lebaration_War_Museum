<?php
// login.php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'manager') {
        header("Location: manager_dashboard.php");
    } else {
        header("Location: visitor_dashboard.php");
    }
    exit();
}


// --- DB Connection for Museum ---
$host = "localhost";
$username = "root";
$password = "";
$database = "museum";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errorMsg = "";
$successMsg = "";

// Display success message after registration
if (isset($_GET['registered']) && $_GET['registered'] === 'success') {
    $successMsg = "Registration successful! Please login.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $inputPassword = $_POST['password'];

    // --- Prepare statement to fetch user data ---
    $stmt = $conn->prepare("SELECT user_id, full_name, password_hash, profile_photo_url, role, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($userId, $fullName, $passwordHash, $profilePhotoUrl, $userRole, $userStatus);
        $stmt->fetch();

        // Check if account is active
        if ($userStatus !== 'active') {
             $errorMsg = "Your account is not active. Please contact an administrator.";
        }
        // Verify password
        elseif (password_verify($inputPassword, $passwordHash)) {
            // --- Set Session Variables ---
            $_SESSION['user_id'] = $userId;
            $_SESSION['full_name'] = $fullName;
            $_SESSION['profile_photo_url'] = $profilePhotoUrl;
            $_SESSION['role'] = $userRole;

            // --- Redirect based on user role ---
            if ($userRole === "visitor") {
                header("Location: visitor_dashboard.php");
            } elseif ($userRole === "manager") {
                header("Location: manager_dashboard.php");
            } elseif ($userRole === "admin") {
                header("Location: admin_dashboard.php");
            } else {
                $errorMsg = "Invalid user role assigned.";
            }
            exit();
        } else {
            $errorMsg = "Incorrect password.";
        }
    } else {
        $errorMsg = "User not found with that email.";
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Digital Liberation War Museum</title>
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
<body class="flex items-center justify-center min-h-screen form-bg font-sans">
    <div class="bg-white/90 backdrop-blur-lg p-8 sm:p-10 rounded-3xl shadow-2xl w-full max-w-md border border-white/20">
        
        <div class="text-center mb-8">
            <a href="index.php">
                <img src="images/logo.png" alt="Museum Logo" class="w-20 h-20 mx-auto rounded-full border-4 border-white shadow-lg">
            </a>
            <h2 class="text-3xl font-bold font-serif text-center mt-4 text-gray-900">Museum Portal Access</h2>
            <p class="text-slate-600">Sign in to continue your journey.</p>
        </div>

        <?php if ($successMsg): ?>
            <div class="mb-4 p-4 bg-green-100 text-green-800 border-l-4 border-liberation-green rounded-r-lg" role="alert">
                <p class="font-semibold">Success</p>
                <p><?php echo htmlspecialchars($successMsg); ?></p>
            </div>
        <?php endif; ?>
        <?php if ($errorMsg): ?>
            <div class="mb-4 p-4 bg-red-100 text-red-800 border-l-4 border-liberation-red rounded-r-lg" role="alert">
                 <p class="font-semibold">Login Failed</p>
                <p><?php echo htmlspecialchars($errorMsg); ?></p>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium mb-1.5 text-gray-700">Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i data-lucide="at-sign" class="w-5 h-5 text-gray-400"></i>
                    </span>
                    <input type="email" name="email" required placeholder="your@email.com" class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg shadow-sm bg-gray-50 focus:ring-red-500/30 focus:border-red-500 transition">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5 text-gray-700">Password</label>
                 <div class="relative">
                     <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i data-lucide="lock" class="w-5 h-5 text-gray-400"></i>
                    </span>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg shadow-sm bg-gray-50 focus:ring-red-500/30 focus:border-red-500 transition">
                </div>
            </div>
            <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-liberation-red to-liberation-green text-white rounded-lg font-semibold hover:shadow-lg transition-all duration-300 hover:scale-105">
                Login
            </button>
        </form>
        <p class="text-sm text-gray-600 mt-6 text-center">
            Don’t have an account? <a href="register_user.php" class="font-semibold text-liberation-green hover:underline">Register here</a>
        </p>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>