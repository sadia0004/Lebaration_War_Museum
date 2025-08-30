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

            $thumbName = time() . "_thumb_" . basename($_FILES["thumbnailFile"]["name"]);
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
    <title>Add Media - Museum</title>
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
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i> Dashboard
            </a>
            <a href="artifact_management.php" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="archive" class="w-5 h-5 mr-3"></i> Artifacts
            </a>
            <a href="digital_collections.php" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="gem" class="w-5 h-5 mr-3"></i> Digital Collections
            </a>
             <a href="add_media.php" class="bg-brand-green text-white flex items-center px-4 py-2.5 text-sm font-semibold rounded-lg shadow-md">
                <i data-lucide="clapperboard" class="w-5 h-5 mr-3"></i> Add Media
            </a>
             
             <a href="add_timeline_event.php" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="milestone" class="w-5 h-5 mr-3"></i> Digital Timeline
            </a>

             <a href="#" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="users" class="w-5 h-5 mr-3"></i> Visitor Analytics
            </a>
            <a href="#" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="file-text" class="w-5 h-5 mr-3"></i> Content Reports
            </a>
             <a href="#" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="settings" class="w-5 h-5 mr-3"></i> System Settings
            </a>
        </nav>
        <div class="mt-auto px-4 py-6 border-t border-neutral-border">
            <a href="logout.php" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="log-out" class="w-5 h-5 mr-3"></i> Sign Out
            </a>
        </div>
    </aside>

    <main class="flex-1 lg:ml-64">
        <header class="bg-neutral-card/80 backdrop-blur-lg border-b border-neutral-border flex items-center justify-between px-8 py-4 sticky top-0 z-10">
            <a href="digital_collections.php" class="flex items-center gap-2 text-sm font-semibold text-neutral-text-muted hover:text-neutral-text-main">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Back to Collections
            </a>
            <h1 class="text-xl font-bold text-neutral-text-main">Add New Media File</h1>
        </header>
        
        <div class="p-8">
            <?php if ($successMsg): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert"><p><?php echo $successMsg; ?></p></div>
            <?php endif; ?>
            <?php if ($errorMsg): ?>
                 <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert"><p><?php echo $errorMsg; ?></p></div>
            <?php endif; ?>

            <form id="media-form" method="POST" action="add_media.php" enctype="multipart/form-data" class="space-y-8 max-w-2xl pb-24">
                <div class="bg-neutral-card p-6 rounded-xl border border-neutral-border">
                    <h3 class="text-lg font-semibold border-b border-neutral-border pb-3 mb-6">Media Details</h3>
                    <div class="space-y-6">
                        <div>
                            <label for="title" class="block mb-1.5 text-sm font-medium">Title <span class="text-red-500">*</span></label>
                            <input type="text" id="title" name="title" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green" required>
                        </div>
                         <div>
                            <label for="description" class="block mb-1.5 text-sm font-medium">Description</label>
                            <textarea id="description" name="description" rows="4" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green"></textarea>
                        </div>
                        <div>
                            <label for="media_type" class="block mb-1.5 text-sm font-medium">Media Type <span class="text-red-500">*</span></label>
                            <select id="media_type" name="media_type" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green" onchange="toggleThumbnailField()">
                                <option value="video">Video</option>
                                <option value="audio">Audio</option>
                            </select>
                        </div>
                        <div>
                            <label for="category" class="block mb-1.5 text-sm font-medium">Content Category</label>
                            <select id="category" name="category" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5 focus:ring-brand-green focus:border-brand-green">
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
                
                <div class="bg-neutral-card p-6 rounded-xl border border-neutral-border">
                    <h3 class="text-lg font-semibold border-b border-neutral-border pb-3 mb-6">File Uploads</h3>
                     <div class="space-y-6">
                        <div>
                             <label class="block mb-1.5 text-sm font-medium">Media File (Video or Audio) <span class="text-red-500">*</span></label>
                             <input type="file" name="mediaFile" accept="video/*,audio/*" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" required>
                             <p class="mt-1 text-xs text-gray-500">MP4, MOV, MP3, WAV etc.</p>
                        </div>
                        <div id="thumbnail-field">
                             <label class="block mb-1.5 text-sm font-medium">Thumbnail Image (for Video)</label>
                             <input type="file" name="thumbnailFile" accept="image/*" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                             <p class="mt-1 text-xs text-gray-500">PNG or JPG. Recommended aspect ratio 16:9.</p>
                        </div>
                     </div>
                </div>
            </form>
        </div>

        <footer class="fixed bottom-0 left-0 lg:left-64 right-0 bg-white/95 backdrop-blur-sm p-4 border-t border-neutral-border shadow-lg z-10">
            <div class="flex justify-end gap-4 max-w-4xl mx-auto">
                <a href="digital_collections.php" class="px-6 py-2.5 text-sm font-semibold text-neutral-text-muted hover:text-neutral-text-main">Cancel</a>
                <button type="submit" form="media-form" class="bg-brand-green text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-opacity-90 shadow-sm flex items-center gap-2">
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
    </script>
</body>
</html>