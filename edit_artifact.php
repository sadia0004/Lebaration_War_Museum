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
$successMsg = "";
$errorMsg = "";

// Database Connection
$host = "localhost";
$username = "root";
$password = "";
$database = "museum";
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// Check if an ID is provided for editing
if (!isset($_GET['edit_id']) || !filter_var($_GET['edit_id'], FILTER_VALIDATE_INT)) {
    header("Location: artifact_management.php");
    exit();
}
$artifactId = $_GET['edit_id'];

// --- Handle Form Submission for UPDATE ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $postedArtifactId = $_POST['artifact_id'];
    $artifactImageUrl = $_POST['existing_image_url']; // Keep old image by default

    // Handle potential new image upload
    if (isset($_FILES["artifactImage"]) && $_FILES["artifactImage"]["error"] == 0) {
        $uploadDir = "uploads/artifacts/";
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        $fileName = time() . "_" . basename($_FILES["artifactImage"]["name"]);
        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES["artifactImage"]["tmp_name"], $uploadPath)) {
            // New upload successful, delete old image if it exists
            if ($artifactImageUrl && file_exists($artifactImageUrl)) {
                unlink($artifactImageUrl);
            }
            $artifactImageUrl = $uploadPath; // Set new image path
        } else {
            $errorMsg = "Error uploading new image.";
        }
    }

    if (empty($errorMsg)) {
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        
        $sql = "UPDATE artifacts SET 
                    collection_number = ?, accession_number = ?, title = ?, object_type = ?, period = ?, 
                    description = ?, significance_comment = ?, contributor_name = ?, collection_date = ?, 
                    found_place = ?, measurements = ?, materials = ?, gallery_number = ?, `condition` = ?, 
                    preservation_notes = ?, correction_notes = ?, is_featured = ?, artifact_image_url = ?, 
                    status = ?
                WHERE artifact_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssssssssssissi",
            $_POST['collection_number'], $_POST['accession_number'], $_POST['title'], $_POST['object_type'], $_POST['period'],
            $_POST['description'], $_POST['significance_comment'], $_POST['contributor_name'], $_POST['collection_date'],
            $_POST['found_place'], $_POST['measurements'], $_POST['materials'], $_POST['gallery_number'], $_POST['condition'],
            $_POST['preservation_notes'], $_POST['correction_notes'], $isFeatured, $artifactImageUrl,
            $_POST['status'], $postedArtifactId
        );

        if ($stmt->execute()) {
            $successMsg = "Changes saved successfully! You will be redirected shortly.";
            header("Refresh: 2; URL=artifact_management.php?status=updated");
        } else {
            $errorMsg = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// --- Fetch existing artifact data to pre-fill the form ---
$stmt = $conn->prepare("SELECT * FROM artifacts WHERE artifact_id = ?");
$stmt->bind_param("i", $artifactId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $artifact = $result->fetch_assoc();
} else {
    // No artifact found with that ID, redirect
    header("Location: artifact_management.php");
    exit();
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Artifact: <?php echo htmlspecialchars($artifact['title']); ?></title>
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
            <a href="artifact_management.php" class="bg-gradient-to-r from-red-700 to-green-800 text-white flex items-center px-4 py-3 text-sm font-semibold rounded-xl shadow-lg">
                <i data-lucide="archive" class="w-5 h-5 mr-3"></i> Artifacts
            </a>
            <a href="digital_collections.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="gem" class="w-5 h-5 mr-3"></i> Digital Collections
            </a>
            <a href="add_media.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="clapperboard" class="w-5 h-5 mr-3"></i> Media Library
            </a>
            <a href="add_timeline_event.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="milestone" class="w-5 h-5 mr-3"></i> Timeline Events
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
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-4xl font-bold text-slate-900 font-serif">Edit Artifact</h1>
                        <p class="text-slate-600 mt-2 line-clamp-1">Now editing: <strong><?php echo htmlspecialchars($artifact['title']); ?></strong></p>
                    </div>
                    <a href="artifact_management.php" class="px-6 py-2.5 bg-slate-200/60 hover:bg-slate-300/60 text-slate-700 rounded-full text-sm font-semibold flex items-center gap-2 transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Back to Artifacts
                    </a>
                </div>
            </header>

            <?php if ($successMsg): ?>
                <div class="bg-green-100 border-l-4 border-liberation-green text-green-800 p-4 mb-6 rounded-r-lg shadow" role="alert" data-aos="fade-left"><?php echo $successMsg; ?></div>
            <?php endif; ?>
            <?php if ($errorMsg): ?>
                <div class="bg-red-100 border-l-4 border-liberation-red text-red-800 p-4 mb-6 rounded-r-lg shadow" role="alert" data-aos="fade-left"><?php echo $errorMsg; ?></div>
            <?php endif; ?>

            <form id="artifact-form" method="POST" action="edit_artifact.php?edit_id=<?php echo $artifact['artifact_id']; ?>" enctype="multipart/form-data" class="space-y-8 pb-24">
                <input type="hidden" name="artifact_id" value="<?php echo $artifact['artifact_id']; ?>">
                <input type="hidden" name="existing_image_url" value="<?php echo htmlspecialchars($artifact['artifact_image_url']); ?>">
                
                <div class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20" data-aos="fade-up">
                    <h3 class="text-2xl font-bold font-serif text-slate-800 border-b border-slate-200 pb-4 mb-6">Core Identification</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-slate-700">Title <span class="text-liberation-red">*</span></label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($artifact['title']); ?>" class="bg-slate-50 border border-slate-300 text-sm rounded-lg w-full p-2.5 focus:ring-red-500/30 focus:border-red-500" required>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-slate-700">Collection Number <span class="text-liberation-red">*</span></label>
                            <input type="text" name="collection_number" value="<?php echo htmlspecialchars($artifact['collection_number']); ?>" class="bg-slate-50 border border-slate-300 text-sm rounded-lg w-full p-2.5 focus:ring-red-500/30 focus:border-red-500" required>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-slate-700">Accession Number</label>
                            <input type="text" name="accession_number" value="<?php echo htmlspecialchars($artifact['accession_number']); ?>" class="bg-slate-50 border border-slate-300 text-sm rounded-lg w-full p-2.5 focus:ring-red-500/30 focus:border-red-500">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-slate-700">Object Type</label>
                            <select name="object_type" class="bg-slate-50 border border-slate-300 text-sm rounded-lg w-full p-2.5 focus:ring-red-500/30 focus:border-red-500">
                                <?php $types = ['Documents', 'Personal Items', 'Weaponry', 'Media', 'Clothing', 'Other']; ?>
                                <?php foreach($types as $type): ?>
                                    <option <?php if($artifact['object_type'] == $type) echo 'selected'; ?>><?php echo $type; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20" data-aos="fade-up" data-aos-delay="100">
                    <h3 class="text-2xl font-bold font-serif text-slate-800 border-b border-slate-200 pb-4 mb-6">Historical Context</h3>
                    <div class="space-y-6">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-slate-700">Description</label>
                            <textarea name="description" rows="4" class="bg-slate-50 border border-slate-300 text-sm rounded-lg w-full p-2.5 focus:ring-red-500/30 focus:border-red-500"><?php echo htmlspecialchars($artifact['description']); ?></textarea>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-slate-700">Significance</label>
                            <textarea name="significance_comment" rows="3" class="bg-slate-50 border border-slate-300 text-sm rounded-lg w-full p-2.5 focus:ring-red-500/30 focus:border-red-500"><?php echo htmlspecialchars($artifact['significance_comment']); ?></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-slate-700">Period</label>
                                <input type="text" name="period" value="<?php echo htmlspecialchars($artifact['period']); ?>" class="bg-slate-50 border border-slate-300 text-sm rounded-lg w-full p-2.5 focus:ring-red-500/30 focus:border-red-500">
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-slate-700">Contributor (Donor)</label>
                                <input type="text" name="contributor_name" value="<?php echo htmlspecialchars($artifact['contributor_name']); ?>" class="bg-slate-50 border border-slate-300 text-sm rounded-lg w-full p-2.5 focus:ring-red-500/30 focus:border-red-500">
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-slate-700">Collection Date</label>
                                <input type="date" name="collection_date" value="<?php echo htmlspecialchars($artifact['collection_date']); ?>" class="bg-slate-50 border border-slate-300 text-sm rounded-lg w-full p-2.5 focus:ring-red-500/30 focus:border-red-500">
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-slate-700">Found Place</label>
                                <input type="text" name="found_place" value="<?php echo htmlspecialchars($artifact['found_place']); ?>" class="bg-slate-50 border border-slate-300 text-sm rounded-lg w-full p-2.5 focus:ring-red-500/30 focus:border-red-500">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20" data-aos="fade-up" data-aos-delay="200">
                    <h3 class="text-2xl font-bold font-serif text-slate-800 border-b border-slate-200 pb-4 mb-6">Curatorial & Physical Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-slate-700">Materials</label>
                            <input type="text" name="materials" value="<?php echo htmlspecialchars($artifact['materials']); ?>" class="bg-slate-50 border border-slate-300 text-sm rounded-lg w-full p-2.5 focus:ring-red-500/30 focus:border-red-500">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-slate-700">Measurements</label>
                            <input type="text" name="measurements" value="<?php echo htmlspecialchars($artifact['measurements']); ?>" class="bg-slate-50 border border-slate-300 text-sm rounded-lg w-full p-2.5 focus:ring-red-500/30 focus:border-red-500">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-slate-700">Gallery Number</label>
                            <input type="text" name="gallery_number" value="<?php echo htmlspecialchars($artifact['gallery_number']); ?>" class="bg-slate-50 border border-slate-300 text-sm rounded-lg w-full p-2.5 focus:ring-red-500/30 focus:border-red-500">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-slate-700">Condition</label>
                            <select name="condition" class="bg-slate-50 border border-slate-300 text-sm rounded-lg w-full p-2.5 focus:ring-red-500/30 focus:border-red-500">
                                <?php $conditions = ['Excellent', 'Good', 'Fair', 'Poor']; ?>
                                <?php foreach($conditions as $condition): ?>
                                    <option <?php if($artifact['condition'] == $condition) echo 'selected'; ?>><?php echo $condition; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-slate-700">Status</label>
                            <select name="status" class="bg-slate-50 border border-slate-300 text-sm rounded-lg w-full p-2.5 focus:ring-red-500/30 focus:border-red-500">
                                <?php $statuses = ['In Storage', 'On Display', 'Under Restoration', 'On Loan']; ?>
                                <?php foreach($statuses as $status): ?>
                                    <option <?php if($artifact['status'] == $status) echo 'selected'; ?>><?php echo $status; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-slate-700">Preservation Notes</label>
                            <input type="text" name="preservation_notes" value="<?php echo htmlspecialchars($artifact['preservation_notes']); ?>" class="bg-slate-50 border border-slate-300 text-sm rounded-lg w-full p-2.5 focus:ring-red-500/30 focus:border-red-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-slate-700">Correction Notes</label>
                            <input type="text" name="correction_notes" value="<?php echo htmlspecialchars($artifact['correction_notes']); ?>" class="bg-slate-50 border border-slate-300 text-sm rounded-lg w-full p-2.5 focus:ring-red-500/30 focus:border-red-500">
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20" data-aos="fade-up" data-aos-delay="300">
                    <h3 class="text-2xl font-bold font-serif text-slate-800 border-b border-slate-200 pb-4 mb-6">Digital Options</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-slate-700">Change Primary Image</label>
                            <?php if(!empty($artifact['artifact_image_url'])): ?>
                            <div class="mb-4">
                                <p class="text-xs text-slate-500 mb-2">Current Image:</p>
                                <img src="<?php echo $baseURL . htmlspecialchars($artifact['artifact_image_url']); ?>" class="w-32 h-32 object-cover rounded-md border p-1 bg-slate-50">
                            </div>
                            <?php endif; ?>
                            <input type="file" name="artifactImage" class="block w-full text-sm text-slate-700 border border-slate-300 rounded-lg cursor-pointer bg-slate-50 focus:outline-none file:bg-slate-200 file:text-slate-700 file:px-4 file:py-2 file:mr-4 file:border-0 hover:file:bg-slate-300">
                            <p class="mt-1 text-xs text-slate-500">Upload a new file only to replace the current one.</p>
                        </div>
                        <div class="flex items-center pt-8">
                            <input id="is_featured" name="is_featured" type="checkbox" value="1" <?php if($artifact['is_featured'] == 1) echo 'checked'; ?> class="w-5 h-5 text-liberation-green bg-slate-100 border-slate-300 rounded focus:ring-liberation-green/50 focus:ring-2">
                            <label for="is_featured" class="ml-3 text-sm font-medium text-slate-800">Feature this artifact on visitor dashboard?</label>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <footer class="fixed bottom-0 left-0 lg:left-72 right-0 bg-white/95 backdrop-blur-sm p-4 border-t border-slate-200/50 shadow-lg z-10" data-aos="fade-up" data-aos-delay="400">
            <div class="flex justify-end gap-4 max-w-7xl mx-auto px-8">
                <a href="artifact_management.php" class="px-8 py-3 text-sm font-semibold text-slate-600 hover:text-slate-900 bg-slate-200/60 hover:bg-slate-300/60 rounded-full transition-colors">Cancel</a>
                <button type="submit" form="artifact-form" class="bg-gradient-to-r from-red-600 to-green-600 text-white px-8 py-3 rounded-full text-sm font-semibold hover:shadow-lg transition-all duration-300 hover:scale-105 flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Save Changes
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