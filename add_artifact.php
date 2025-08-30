<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Protect the page: ensure only managers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit();
}

// === IMPORTANT: This MUST match your project's folder name in htdocs ===
$baseURL = "http://localhost/MUSEUM/";
$managerUserId = $_SESSION['user_id'];
$successMsg = "";
$errorMsg = "";

// --- Database Connection ---
$host = "localhost";
$username = "root";
$password = "";
$database = "museum";
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Handle Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- 1. Handle the Image Upload ---
    $artifactImageUrl = null;
    if (isset($_FILES["artifactImage"]) && $_FILES["artifactImage"]["error"] == 0) {
        $uploadDir = "uploads/artifacts/"; // Directory to store artifact images
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES["artifactImage"]["name"]);
        $uploadPath = $uploadDir . $fileName;

        // Move the file to the server
        if (move_uploaded_file($_FILES["artifactImage"]["tmp_name"], $uploadPath)) {
            $artifactImageUrl = $uploadPath; // The path to be stored in the database
        } else {
            $errorMsg = "Sorry, there was an error uploading your file.";
        }
    }

    // --- 2. Proceed only if there was no upload error ---
    if (empty($errorMsg)) {
        // Prepare data for insertion
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

        // Prepare the SQL INSERT statement with all columns
        $sql = "INSERT INTO artifacts (
                    collection_number, accession_number, title, object_type, period, 
                    description, significance_comment, contributor_name, collection_date, 
                    found_place, measurements, materials, gallery_number, `condition`, 
                    preservation_notes, correction_notes, is_featured, artifact_image_url, 
                    status, added_by_user_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param(
                "ssssssssssssssssissi",
                $_POST['collection_number'],
                $_POST['accession_number'],
                $_POST['title'],
                $_POST['object_type'],
                $_POST['period'],
                $_POST['description'],
                $_POST['significance_comment'],
                $_POST['contributor_name'],
                $_POST['collection_date'],
                $_POST['found_place'],
                $_POST['measurements'],
                $_POST['materials'],
                $_POST['gallery_number'],
                $_POST['condition'],
                $_POST['preservation_notes'],
                $_POST['correction_notes'],
                $isFeatured,
                $artifactImageUrl,
                $_POST['status'],
                $managerUserId
            );

            if ($stmt->execute()) {
                $successMsg = "Artifact added successfully! You will be redirected shortly.";
                // Redirect after a short delay to allow the user to see the message
                header("Refresh: 2; URL=artifact_management.php");
            } else {
                $errorMsg = "Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errorMsg = "Database error: could not prepare statement.";
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Artifact - Museum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'sans': ['Inter', 'sans-serif'] },
                    colors: {
                        'brand': { 'green': '#16a34a', 'light': '#dcfce7' },
                        'neutral': { 'bg': '#f8fafc', 'card': '#ffffff', 'border': '#e5e7eb', 'text-main': '#1f2937', 'text-muted': '#6b7280' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-neutral-bg flex min-h-screen text-neutral-text-main">

    <aside class="w-64 bg-neutral-card border-r border-neutral-border flex-col h-screen fixed hidden lg:flex">
         <div class="h-20 flex items-center justify-start px-6 border-b border-neutral-border">
            <div class="flex items-center space-x-3">
                <img src="images/logo.png" alt="Museum Logo" class="h-10 w-10 object-cover rounded-md">
                <div class="text-left">
                    <h1 class="text-base font-bold">Liberation War</h1>
                    <p class="text-xs text-neutral-text-muted">Digital Museum</p>
                </div>
            </div>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="manager_dashboard.php" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i> Dashboard Overview
            </a>
            <a href="artifact_management.php" class="bg-brand-green text-white flex items-center px-4 py-2.5 text-sm font-semibold rounded-lg shadow-md">
                <i data-lucide="archive" class="w-5 h-5 mr-3"></i> Artifact Management
            </a>
        </nav>
        <div class="mt-auto px-4 py-6 border-t border-neutral-border">
            <a href="logout.php" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="log-out" class="w-5 h-5 mr-3"></i> Sign Out
            </a>
        </div>
    </aside>

    <main class="flex-1 p-8 lg:ml-64">
        <header class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-neutral-text-main">Add New Artifact</h1>
        </header>

        <?php if ($successMsg): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p><?php echo $successMsg; ?></p>
            </div>
        <?php endif; ?>
        <?php if ($errorMsg): ?>
             <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p><?php echo $errorMsg; ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="add_artifact.php" enctype="multipart/form-data" class="space-y-8">
            <div class="bg-neutral-card p-6 rounded-xl border border-neutral-border">
                <h3 class="text-lg font-semibold border-b border-neutral-border pb-3 mb-6">Core Identification</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-1.5 text-sm font-medium">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green" placeholder="e.g., Freedom Fighter's Diary" required>
                    </div>
                     <div>
                        <label class="block mb-1.5 text-sm font-medium">Collection Number <span class="text-red-500">*</span></label>
                        <input type="text" name="collection_number" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green" placeholder="e.g., CN-1971-001" required>
                    </div>
                    <div>
                        <label class="block mb-1.5 text-sm font-medium">Accession Number</label>
                        <input type="text" name="accession_number" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green" placeholder="e.g., AN-2024-001">
                    </div>
                    <div>
                        <label class="block mb-1.5 text-sm font-medium">Object Type</label>
                        <select name="object_type" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green">
                            <option>Documents</option><option>Personal Items</option><option>Weaponry</option><option>Media</option><option>Clothing</option><option>Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-neutral-card p-6 rounded-xl border border-neutral-border">
                <h3 class="text-lg font-semibold border-b border-neutral-border pb-3 mb-6">Historical Context</h3>
                <div class="space-y-6">
                     <div>
                        <label class="block mb-1.5 text-sm font-medium">Description</label>
                        <textarea name="description" rows="4" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green" placeholder="A detailed description..."></textarea>
                    </div>
                     <div>
                        <label class="block mb-1.5 text-sm font-medium">Significance</label>
                        <textarea name="significance_comment" rows="3" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green" placeholder="Explain the historical significance..."></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">Period</label>
                            <input type="text" name="period" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green" placeholder="e.g., During War, Pre-War">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">Contributor (Donor)</label>
                            <input type="text" name="contributor_name" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">Collection Date</label>
                            <input type="date" name="collection_date" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">Found Place</label>
                            <input type="text" name="found_place" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-neutral-card p-6 rounded-xl border border-neutral-border">
                <h3 class="text-lg font-semibold border-b border-neutral-border pb-3 mb-6">Curatorial & Physical Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-1.5 text-sm font-medium">Materials</label>
                        <input type="text" name="materials" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green" placeholder="e.g., Steel, Wood, Paper, Ink">
                    </div>
                     <div>
                        <label class="block mb-1.5 text-sm font-medium">Measurements</label>
                        <input type="text" name="measurements" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green">
                    </div>
                    <div>
                        <label class="block mb-1.5 text-sm font-medium">Gallery Number</label>
                        <input type="text" name="gallery_number" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green">
                    </div>
                    <div>
                        <label class="block mb-1.5 text-sm font-medium">Condition</label>
                        <select name="condition" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green">
                            <option>Excellent</option><option>Good</option><option>Fair</option><option>Poor</option>
                        </select>
                    </div>
                     <div>
                        <label class="block mb-1.5 text-sm font-medium">Status</label>
                        <select name="status" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green">
                            <option>In Storage</option><option>On Display</option><option>Under Restoration</option><option>On Loan</option>
                        </select>
                    </div>
                     <div>
                        <label class="block mb-1.5 text-sm font-medium">Preservation Notes</label>
                        <input type="text" name="preservation_notes" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green">
                    </div>
                     <div class="md:col-span-2">
                        <label class="block mb-1.5 text-sm font-medium">Correction Notes</label>
                        <input type="text" name="correction_notes" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green">
                    </div>
                </div>
            </div>

            <div class="bg-neutral-card p-6 rounded-xl border border-neutral-border">
                <h3 class="text-lg font-semibold border-b border-neutral-border pb-3 mb-6">Digital Options</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    <div>
                         <label class="block mb-1.5 text-sm font-medium">Primary Image</label>
                         <input type="file" name="artifactImage" accept="image/*" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                         <p class="mt-1 text-xs text-gray-500">PNG, JPG, or GIF (MAX. 5MB).</p>
                    </div>
                     <div class="flex items-center pt-6">
                        <input id="is_featured" name="is_featured" type="checkbox" value="1" class="w-4 h-4 text-brand-green bg-gray-100 border-gray-300 rounded focus:ring-brand-green">
                        <label for="is_featured" class="ml-2 text-sm font-medium">Feature this artifact on visitor dashboard?</label>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end gap-4 pt-4">
                <a href="artifact_management.php" class="px-6 py-2.5 text-sm font-semibold text-neutral-text-muted hover:text-neutral-text-main">Cancel</a>
                <button type="submit" class="bg-brand-green text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-opacity-90 shadow-sm flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Save Artifact
                </button>
            </div>
        </form>
    </main>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>