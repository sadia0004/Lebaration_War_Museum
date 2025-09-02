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
    <title>Add Timeline Event - Liberation War Museum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
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
    
    <aside class="w-72 bg-white/95 backdrop-blur-lg border-r border-slate-200/50 flex-col h-screen fixed hidden lg:flex shadow-xl">
        <div class="h-20 flex items-center justify-center px-6 border-b border-slate-200/50 bg-gradient-to-r from-red-700 to-green-800">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <img src="images/logo.png" alt="Liberation War Museum Logo" class="h-12 w-12 object-cover rounded-full border-2 border-white shadow-lg">
                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-yellow-500 rounded-full border-2 border-white"></div>
                </div>
                <div class="text-left text-white">
                    <h1 class="text-lg font-bold font-serif">Manager Portal</h1>
                    <p class="text-xs opacity-90 font-medium">Liberation War 1971</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2">
            <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold mb-4 px-2">Management</div>
            
            <a href="manager_dashboard.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i> Dashboard
            </a>
            <a href="artifact_management.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="archive" class="w-5 h-5 mr-3"></i> Artifacts
            </a>
            <a href="digital_collections.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="gem" class="w-5 h-5 mr-3"></i> Digital Collections
            </a>
            <a href="add_media.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="clapperboard" class="w-5 h-5 mr-3"></i> Media Library
            </a>
            <a href="add_timeline_event.php" class="bg-gradient-to-r from-red-700 to-green-800 text-white flex items-center px-4 py-3 text-sm font-semibold rounded-xl shadow-lg">
                <i data-lucide="milestone" class="w-5 h-5 mr-3"></i> Timeline Events
            </a>

            <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold mb-4 px-2 pt-6">Analytics & System</div>
             <a href="visitor_analytics.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="users" class="w-5 h-5 mr-3"></i> Visitor Analytics
            </a>
            <a href="content_report.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
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

    <main class="flex-1 lg:ml-72">
        <div class="p-6 lg:p-10">
            <header class="mb-8" data-aos="fade-down">
                <h1 class="text-4xl font-bold text-slate-900 font-serif">Add Timeline Event</h1>
                <p class="text-slate-600 mt-2">Create a new entry for the historical timeline of the Liberation War.</p>
            </header>

            <?php if ($successMsg): ?>
                <div class="bg-green-100 border-l-4 border-liberation-green text-green-800 p-4 mb-6 rounded-r-lg shadow" role="alert" data-aos="fade-left">
                    <p class="font-semibold">Success</p>
                    <p><?php echo $successMsg; ?></p>
                </div>
            <?php endif; ?>
            <?php if ($errorMsg): ?>
                <div class="bg-red-100 border-l-4 border-liberation-red text-red-800 p-4 mb-6 rounded-r-lg shadow" role="alert" data-aos="fade-left">
                    <p class="font-semibold">Error</p>
                    <p><?php echo $errorMsg; ?></p>
                </div>
            <?php endif; ?>

            <form id="timeline-form" method="POST" action="add_timeline_event.php" enctype="multipart/form-data" class="space-y-8 max-w-4xl pb-24">
                
                <div class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20" data-aos="fade-up">
                    <h3 class="text-2xl font-bold font-serif text-slate-800 border-b border-slate-200 pb-4 mb-6">Event Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="event_title" class="block mb-2 text-sm font-medium text-slate-700">Event Title <span class="text-liberation-red">*</span></label>
                            <input type="text" id="event_title" name="event_title" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-red-500/30 focus:border-red-500 block w-full p-2.5" placeholder="e.g., Historic 7th March Speech" required>
                        </div>
                        <div>
                            <label for="event_date" class="block mb-2 text-sm font-medium text-slate-700">Event Date <span class="text-liberation-red">*</span></label>
                            <input type="date" id="event_date" name="event_date" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-red-500/30 focus:border-red-500 block w-full p-2.5" required>
                        </div>
                        <div>
                            <label for="location" class="block mb-2 text-sm font-medium text-slate-700">Location</label>
                            <input type="text" id="location" name="location" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-red-500/30 focus:border-red-500 block w-full p-2.5" placeholder="e.g., Ramna Race Course, Dhaka">
                        </div>
                        <div>
                            <label for="category" class="block mb-2 text-sm font-medium text-slate-700">Category</label>
                            <select id="category" name="category" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-red-500/30 focus:border-red-500 block w-full p-2.5">
                                <option>Political</option><option>Military</option><option>Diplomatic</option><option>Social</option><option>Cultural</option><option>Genocide</option><option>Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20" data-aos="fade-up" data-aos-delay="100">
                    <h3 class="text-2xl font-bold font-serif text-slate-800 border-b border-slate-200 pb-4 mb-6">Narrative</h3>
                    <div class="space-y-6">
                        <div>
                            <label for="event_description" class="block mb-2 text-sm font-medium text-slate-700">Event Description</label>
                            <textarea id="event_description" name="event_description" rows="5" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-red-500/30 focus:border-red-500 block w-full p-2.5" placeholder="A detailed, neutral description of what occurred during the event."></textarea>
                        </div>
                        <div>
                            <label for="event_significance" class="block mb-2 text-sm font-medium text-slate-700">Significance</label>
                            <textarea id="event_significance" name="event_significance" rows="3" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-red-500/30 focus:border-red-500 block w-full p-2.5" placeholder="Explain the historical importance of this event and its impact on the Liberation War."></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20" data-aos="fade-up" data-aos-delay="200">
                    <h3 class="text-2xl font-bold font-serif text-slate-800 border-b border-slate-200 pb-4 mb-6">Display Options</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                         <div>
                             <label class="block mb-2 text-sm font-medium text-slate-700">Event Image</label>
                             <input type="file" name="eventImage" accept="image/*" class="block w-full text-sm text-slate-700 border border-slate-300 rounded-lg cursor-pointer bg-slate-50 focus:outline-none file:bg-slate-200 file:text-slate-700 file:px-4 file:py-2 file:mr-4 file:border-0 hover:file:bg-slate-300">
                             <p class="mt-1 text-xs text-slate-500">PNG or JPG. An image that represents the event.</p>
                        </div>
                        <div class="flex items-center pt-6">
                            <input id="is_featured" name="is_featured" type="checkbox" value="1" class="w-5 h-5 text-liberation-green bg-slate-100 border-slate-300 rounded focus:ring-liberation-green/50 focus:ring-2">
                            <label for="is_featured" class="ml-3 text-sm font-medium text-slate-800">Feature this event on the timeline?</label>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <footer class="fixed bottom-0 left-0 lg:left-72 right-0 bg-white/95 backdrop-blur-sm p-4 border-t border-slate-200/50 shadow-lg z-10" data-aos="fade-up" data-aos-delay="300">
            <div class="flex justify-end gap-4 max-w-4xl mx-auto">
                <a href="manager_dashboard.php" class="px-8 py-3 text-sm font-semibold text-slate-600 hover:text-slate-900 bg-slate-200/60 hover:bg-slate-300/60 rounded-full transition-colors">Cancel</a>
                <button type="submit" form="timeline-form" class="bg-gradient-to-r from-red-600 to-green-600 text-white px-8 py-3 rounded-full text-sm font-semibold hover:shadow-lg transition-all duration-300 hover:scale-105 flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Save Event
                </button>
            </div>
        </footer>
    </main>
    
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        lucide.createIcons();
        AOS.init({ duration: 600, once: true, offset: 50 });
    </script>
</body>
</html>