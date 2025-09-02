<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Protect the page: ensure only managers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit();
}

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

// --- NEW: Check for success status after a redirect ---
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $successMsg = "Media file uploaded successfully! You can add another one.";
}

// --- Handle Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Safely get POST data to prevent warnings
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $mediaType = $_POST['media_type'] ?? 'video';
    $category = $_POST['category'] ?? null;
    
    $fileUrl = null;
    $thumbnailUrl = null;

    try {
        // --- 1. Handle the main media file upload (Video or Audio) ---
        if (isset($_FILES["mediaFile"]) && $_FILES["mediaFile"]["error"] == 0) {
            $uploadDir = ($mediaType === 'video') ? "uploads/videos/" : "uploads/audio/";
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
            
            $fileName = time() . "_" . basename($_FILES["mediaFile"]["name"]);
            $uploadPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES["mediaFile"]["tmp_name"], $uploadPath)) {
                $fileUrl = $uploadPath;
            } else {
                throw new Exception("Sorry, there was an error uploading the media file.");
            }
        } else {
            throw new Exception("A media file is required.");
        }

        // --- 2. Handle the thumbnail upload (only for videos) ---
        if ($mediaType === 'video' && isset($_FILES["thumbnailFile"]) && $_FILES["thumbnailFile"]["error"] == 0) {
            $thumbDir = "uploads/thumbnails/";
            if (!is_dir($thumbDir)) { mkdir($thumbDir, 0777, true); }

            $thumbName = time() . "thumb" . basename($_FILES["thumbnailFile"]["name"]);
            $thumbPath = $thumbDir . $thumbName;
            
            if (move_uploaded_file($_FILES["thumbnailFile"]["tmp_name"], $thumbPath)) {
                $thumbnailUrl = $thumbPath;
            }
        }

        // --- 3. Insert record into the 'media' table ---
        $sql = "INSERT INTO media (title, description, media_type, category, file_url, thumbnail_url, uploaded_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("ssssssi", $title, $description, $mediaType, $category, $fileUrl, $thumbnailUrl, $managerUserId);
            if ($stmt->execute()) {
                // --- FIX: Redirect to the same page to clear the form ---
                header("Location: add_media.php?status=success");
                exit(); // Always exit after a redirect
            } else {
                throw new Exception("Database error: " . $stmt->error);
            }
            $stmt->close();
        } else {
            throw new Exception("Database error: could not prepare statement.");
        }

    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        // Clean up uploaded files if DB insertion fails
        if ($fileUrl && file_exists($fileUrl)) unlink($fileUrl);
        if ($thumbnailUrl && file_exists($thumbnailUrl)) unlink($thumbnailUrl);
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Media - Liberation War Museum</title>
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
                            'gold': '#ffd700',
                            'dark': '#1a1a1a'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 font-sans">
    
    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-white/95 backdrop-blur-lg border-r border-slate-200/50 flex-col h-screen fixed hidden lg:flex shadow-xl">
        <div class="h-20 flex items-center justify-center px-6 border-b border-slate-200/50 bg-gradient-to-r from-red-700 to-green-800">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <img src="images/logo.png" alt="Liberation War Museum Logo" class="h-12 w-12 object-cover rounded-full border-2 border-white shadow-lg">
                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-yellow-500 rounded-full border-2 border-white"></div>
                </div>
                <div class="text-left text-white">
                    <h1 class="text-lg font-bold font-serif">Digital Museum</h1>
                    <p class="text-xs opacity-90 font-medium">Liberation War 1971</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="manager_dashboard.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i> Dashboard
            </a>
            <a href="artifact_management.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="archive" class="w-5 h-5 mr-3"></i> Artifacts
            </a>
            <a href="digital_collections.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="gem" class="w-5 h-5 mr-3"></i> Digital Collections
            </a>
            <a href="add_media.php" class="bg-gradient-to-r from-red-700 to-green-800 text-white flex items-center px-4 py-3 text-sm font-semibold rounded-xl shadow-lg">
                <i data-lucide="clapperboard" class="w-5 h-5 mr-3"></i> Add Media
            </a>
            <a href="add_timeline_event.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="milestone" class="w-5 h-5 mr-3"></i> Digital Timeline
            </a>
            <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold mb-4 px-2 pt-6">Analytics & System</div>
             <a href="#" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="users" class="w-5 h-5 mr-3"></i> Visitor Analytics
            </a>
            <a href="#" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="file-text" class="w-5 h-5 mr-3"></i> Content Reports
            </a>
             <a href="#" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="settings" class="w-5 h-5 mr-3"></i> System Settings
            </a>
        </nav>
        <div class="mt-auto px-4 py-4 border-t border-slate-200/50">
            <a href="logout.php" class="text-red-600 hover:bg-red-50 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300">
                <i data-lucide="log-out" class="w-5 h-5 mr-3"></i> Sign Out
            </a>
        </div>
    </aside>

    <main class="flex-1 lg:ml-64">
        <!-- Enhanced Header -->
        <header class="bg-white/90 backdrop-blur-lg border-b border-slate-200/50 flex items-center justify-between px-8 py-4 sticky top-0 z-10 shadow-sm">
            <a href="digital_collections.php" class="flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-red-600 transition-colors duration-300">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Back to Collections
            </a>
            <h1 class="text-2xl font-bold text-slate-900 font-serif">Add New Media File</h1>
        </header>
        
        <div class="p-8">
            <?php if ($successMsg): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert"><p><?php echo $successMsg; ?></p></div>
            <?php endif; ?>
            <?php if ($errorMsg): ?>
                 <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert"><p><?php echo $errorMsg; ?></p></div>
            <?php endif; ?>

            <form id="media-form" method="POST" action="add_media.php" enctype="multipart/form-data" class="space-y-8 max-w-2xl pb-24">
                <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl border border-slate-200/50 shadow-lg">
                    <h3 class="text-xl font-bold text-slate-900 font-serif border-b border-slate-200/50 pb-3 mb-6">Media Details</h3>
                    <div class="space-y-6">
                        <div>
                            <label for="title" class="block mb-2 text-sm font-medium text-slate-700">Title <span class="text-red-500">*</span></label>
                            <input type="text" id="title" name="title" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-3" required>
                        </div>
                         <div>
                            <label for="description" class="block mb-2 text-sm font-medium text-slate-700">Description</label>
                            <textarea id="description" name="description" rows="4" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-3"></textarea>
                        </div>
                        <div>
                            <label for="media_type" class="block mb-2 text-sm font-medium text-slate-700">Media Type <span class="text-red-500">*</span></label>
                            <select id="media_type" name="media_type" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-3" onchange="toggleThumbnailField()">
                                <option value="video">Video</option>
                                <option value="audio">Audio</option>
                            </select>
                        </div>
                        <div>
                            <label for="category" class="block mb-2 text-sm font-medium text-slate-700">Content Category</label>
                            <select id="category" name="category" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-3">
                                <option value="" disabled selected>Select a category</option>
                                <option value="Documentary">Documentary</option>
                                <option value="War Footage">War Footage</option>
                                <option value="Post-War">Post-War</option>
                                <option value="Interview">Interview</option>
                                <option value="Speech">Speech</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl border border-slate-200/50 shadow-lg">
                    <h3 class="text-xl font-bold text-slate-900 font-serif border-b border-slate-200/50 pb-3 mb-6">File Uploads</h3>
                     <div class="space-y-6">
                        <div>
                             <label class="block mb-2 text-sm font-medium text-slate-700">Media File (Video or Audio) <span class="text-red-500">*</span></label>
                             <input type="file" name="mediaFile" accept="video/,audio/" class="block w-full text-sm text-slate-900 border border-slate-300 rounded-lg cursor-pointer bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20" required>
                             <p class="mt-2 text-xs text-slate-500">MP4, MOV, MP3, WAV etc.</p>
                        </div>
                        <div id="thumbnail-field">
                             <label class="block mb-2 text-sm font-medium text-slate-700">Thumbnail Image (for Video)</label>
                             <input type="file" name="thumbnailFile" accept="image/*" class="block w-full text-sm text-slate-900 border border-slate-300 rounded-lg cursor-pointer bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20">
                             <p class="mt-2 text-xs text-slate-500">PNG or JPG. Recommended aspect ratio 16:9.</p>
                        </div>
                     </div>
                </div>
            </form>
        </div>

        <footer class="fixed bottom-0 left-0 lg:left-64 right-0 bg-white/95 backdrop-blur-sm p-4 border-t border-slate-200/50 shadow-lg z-10">
            <div class="flex justify-end gap-4 max-w-4xl mx-auto">
                <a href="digital_collections.php" class="px-6 py-3 text-sm font-semibold text-slate-600 hover:text-red-600 transition-colors duration-300">Cancel</a>
                <button type="submit" form="media-form" class="bg-gradient-to-r from-red-700 to-green-800 text-white px-6 py-3 rounded-xl text-sm font-semibold hover:shadow-lg transition-all duration-300 hover:scale-105 flex items-center gap-2 shadow-md">
                    <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                    Upload Media
                </button>
            </div>
        </footer>
    </main>

    <script>
        lucide.createIcons();
        function toggleThumbnailField() {
            const mediaType = document.getElementById('media_type').value;
            const thumbnailField = document.getElementById('thumbnail-field');
            thumbnailField.style.display = (mediaType === 'video') ? 'block' : 'none';
        }
        
        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial state for thumbnail field
            toggleThumbnailField();
        });
    </script>
</body>
</html>