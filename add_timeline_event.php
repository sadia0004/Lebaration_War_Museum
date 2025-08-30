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

// Check for a success status from a redirect
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $successMsg = "Timeline event added successfully! You can add another one.";
}

// --- Handle Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Safely get POST data
    $event_date = $_POST['event_date'] ?? '';
    $event_title = trim($_POST['event_title'] ?? '');
    $event_description = trim($_POST['event_description'] ?? null);
    $event_significance = trim($_POST['event_significance'] ?? null);
    $location = trim($_POST['location'] ?? null);
    $category = $_POST['category'] ?? 'Other';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    $imageUrl = null;

    // Validation
    if (empty($event_date) || empty($event_title)) {
        $errorMsg = "Event Date and Event Title are required fields.";
    } else {
        try {
            // Handle image upload
            if (isset($_FILES["eventImage"]) && $_FILES["eventImage"]["error"] == 0) {
                $uploadDir = "uploads/timeline/";
                if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
                
                $fileName = time() . "_" . basename($_FILES["eventImage"]["name"]);
                $uploadPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES["eventImage"]["tmp_name"], $uploadPath)) {
                    $imageUrl = $uploadPath;
                } else {
                    throw new Exception("Sorry, there was an error uploading the image.");
                }
            }

            // Insert into database
            $sql = "INSERT INTO timeline_events (event_date, event_title, event_description, event_significance, location, category, image_url, is_featured, added_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("sssssssii", $event_date, $event_title, $event_description, $event_significance, $location, $category, $imageUrl, $is_featured, $managerUserId);
                if ($stmt->execute()) {
                    header("Location: add_timeline_event.php?status=success");
                    exit();
                } else {
                    throw new Exception("Database error: " . $stmt->error);
                }
                $stmt->close();
            } else {
                throw new Exception("Database error: could not prepare statement.");
            }
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            if ($imageUrl && file_exists($imageUrl)) { unlink($imageUrl); }
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
    <title>Add Timeline Event - Museum</title>
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
            <a href="add_media.php" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="clapperboard" class="w-5 h-5 mr-3"></i> Add Media
            </a>
            <a href="#" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="users" class="w-5 h-5 mr-3"></i> Visitor Analytics
            </a>
           <a href="add_timeline_event.php" class="bg-brand-green text-white flex items-center px-4 py-2.5 text-sm font-semibold rounded-lg shadow-md" >
                <i data-lucide="milestone" class="w-5 h-5 mr-3"></i> Digital Timeline
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
             <a href="manager_dashboard.php" class="flex items-center gap-2 text-sm font-semibold text-neutral-text-muted hover:text-neutral-text-main">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Back to Dashboard
            </a>
            <h1 class="text-xl font-bold text-neutral-text-main">Add New Timeline Event</h1>
        </header>
        
        <div class="p-8">
            <?php if ($successMsg): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert"><p><?php echo $successMsg; ?></p></div>
            <?php endif; ?>
            <?php if ($errorMsg): ?>
                 <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert"><p><?php echo $errorMsg; ?></p></div>
            <?php endif; ?>

            <form id="timeline-form" method="POST" action="add_timeline_event.php" enctype="multipart/form-data" class="space-y-8 max-w-3xl pb-24">
                <div class="bg-neutral-card p-6 rounded-xl border border-neutral-border">
                    <h3 class="text-lg font-semibold border-b border-neutral-border pb-3 mb-6">Event Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="event_title" class="block mb-1.5 text-sm font-medium">Event Title <span class="text-red-500">*</span></label>
                            <input type="text" id="event_title" name="event_title" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5" placeholder="e.g., Historic Speech by Bangabandhu" required>
                        </div>
                        <div>
                            <label for="event_date" class="block mb-1.5 text-sm font-medium">Event Date <span class="text-red-500">*</span></label>
                            <input type="date" id="event_date" name="event_date" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5" required>
                        </div>
                        <div>
                            <label for="location" class="block mb-1.5 text-sm font-medium">Location</label>
                            <input type="text" id="location" name="location" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5" placeholder="e.g., Dhaka">
                        </div>
                        <div>
                            <label for="category" class="block mb-1.5 text-sm font-medium">Category</label>
                            <select id="category" name="category" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5">
                                <option>Political</option><option>Military</option><option>Diplomatic</option><option>Social</option><option>Economic</option><option>Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-neutral-card p-6 rounded-xl border border-neutral-border">
                    <h3 class="text-lg font-semibold border-b border-neutral-border pb-3 mb-6">Narrative</h3>
                    <div class="space-y-6">
                        <div>
                            <label for="event_description" class="block mb-1.5 text-sm font-medium">Event Description</label>
                            <textarea id="event_description" name="event_description" rows="5" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5" placeholder="A detailed description of what happened..."></textarea>
                        </div>
                        <div>
                            <label for="event_significance" class="block mb-1.5 text-sm font-medium">Significance</label>
                            <textarea id="event_significance" name="event_significance" rows="3" class="bg-gray-50 border border-neutral-border text-sm rounded-lg w-full p-2.5" placeholder="Explain the historical importance..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-neutral-card p-6 rounded-xl border border-neutral-border">
                    <h3 class="text-lg font-semibold border-b border-neutral-border pb-3 mb-6">Display Options</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                         <div>
                             <label class="block mb-1.5 text-sm font-medium">Event Image</label>
                             <input type="file" name="eventImage" accept="image/*" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                             <p class="mt-1 text-xs text-gray-500">PNG or JPG. An image that represents the event.</p>
                        </div>
                        <div class="flex items-center pt-6">
                            <input id="is_featured" name="is_featured" type="checkbox" value="1" class="w-4 h-4 text-brand-green bg-gray-100 border-gray-300 rounded">
                            <label for="is_featured" class="ml-2 text-sm font-medium">Feature this event on the timeline?</label>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <footer class="fixed bottom-0 left-0 lg:left-64 right-0 bg-white/95 backdrop-blur-sm p-4 border-t border-neutral-border shadow-lg z-10">
            <div class="flex justify-end gap-4 max-w-4xl mx-auto">
                <a href="manager_dashboard.php" class="px-6 py-2.5 text-sm font-semibold text-neutral-text-muted hover:text-neutral-text-main">Cancel</a>
                <button type="submit" form="timeline-form" class="bg-brand-green text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-opacity-90 shadow-sm flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Save Event
                </button>
            </div>
        </footer>
    </main>
    <script> lucide.createIcons(); </script>
</body>
</html>