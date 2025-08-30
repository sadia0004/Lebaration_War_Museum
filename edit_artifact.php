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
            $successMsg = "Changes saved successfully! <a href='artifact_management.php' class='font-bold underline hover:text-green-800'>Return to list.</a>";
            header("Refresh: 3; URL=artifact_management.php?status=updated");
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

    <main class="flex-1 lg:ml-64">
        <header class="bg-neutral-card/80 backdrop-blur-lg border-b border-neutral-border flex items-center justify-between px-8 py-4 sticky top-0 z-10">
            <a href="artifact_management.php" class="flex items-center gap-2 text-sm font-semibold text-neutral-text-muted hover:text-neutral-text-main">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Back to Artifacts
            </a>
            <h1 class="text-xl font-bold text-neutral-text-main">Edit Artifact</h1>
        </header>

        <div class="p-8">
            <?php if ($successMsg): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert"><?php echo $successMsg; ?></div>
            <?php endif; ?>
            <?php if ($errorMsg): ?>
                 <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert"><?php echo $errorMsg; ?></div>
            <?php endif; ?>

            <form id="artifact-form" method="POST" action="edit_artifact.php?edit_id=<?php echo $artifact['artifact_id']; ?>" enctype="multipart/form-data" class="space-y-8 pb-24">
                <input type="hidden" name="artifact_id" value="<?php echo $artifact['artifact_id']; ?>">
                <input type="hidden" name="existing_image_url" value="<?php echo htmlspecialchars($artifact['artifact_image_url']); ?>">
                
                <div class="bg-neutral-card p-6 rounded-xl border">
                    <h3 class="text-lg font-semibold border-b pb-3 mb-6">Core Identification</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($artifact['title']); ?>" class="bg-gray-50 border w-full p-2.5 rounded-lg" required>
                        </div>
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">Collection Number <span class="text-red-500">*</span></label>
                            <input type="text" name="collection_number" value="<?php echo htmlspecialchars($artifact['collection_number']); ?>" class="bg-gray-50 border w-full p-2.5 rounded-lg" required>
                        </div>
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">Accession Number</label>
                            <input type="text" name="accession_number" value="<?php echo htmlspecialchars($artifact['accession_number']); ?>" class="bg-gray-50 border w-full p-2.5 rounded-lg">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">Object Type</label>
                            <select name="object_type" class="bg-gray-50 border w-full p-2.5 rounded-lg">
                                <?php $types = ['Documents', 'Personal Items', 'Weaponry', 'Media', 'Clothing', 'Other']; ?>
                                <?php foreach($types as $type): ?>
                                    <option <?php if($artifact['object_type'] == $type) echo 'selected'; ?>><?php echo $type; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-neutral-card p-6 rounded-xl border">
                    <h3 class="text-lg font-semibold border-b pb-3 mb-6">Historical Context</h3>
                    <div class="space-y-6">
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">Description</label>
                            <textarea name="description" rows="4" class="bg-gray-50 border w-full p-2.5 rounded-lg"><?php echo htmlspecialchars($artifact['description']); ?></textarea>
                        </div>
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">Significance</label>
                            <textarea name="significance_comment" rows="3" class="bg-gray-50 border w-full p-2.5 rounded-lg"><?php echo htmlspecialchars($artifact['significance_comment']); ?></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block mb-1.5 text-sm font-medium">Period</label>
                                <input type="text" name="period" value="<?php echo htmlspecialchars($artifact['period']); ?>" class="bg-gray-50 border w-full p-2.5 rounded-lg">
                            </div>
                            <div>
                                <label class="block mb-1.5 text-sm font-medium">Contributor (Donor)</label>
                                <input type="text" name="contributor_name" value="<?php echo htmlspecialchars($artifact['contributor_name']); ?>" class="bg-gray-50 border w-full p-2.5 rounded-lg">
                            </div>
                            <div>
                                <label class="block mb-1.5 text-sm font-medium">Collection Date</label>
                                <input type="date" name="collection_date" value="<?php echo htmlspecialchars($artifact['collection_date']); ?>" class="bg-gray-50 border w-full p-2.5 rounded-lg">
                            </div>
                            <div>
                                <label class="block mb-1.5 text-sm font-medium">Found Place</label>
                                <input type="text" name="found_place" value="<?php echo htmlspecialchars($artifact['found_place']); ?>" class="bg-gray-50 border w-full p-2.5 rounded-lg">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-neutral-card p-6 rounded-xl border">
                    <h3 class="text-lg font-semibold border-b pb-3 mb-6">Curatorial & Physical Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">Materials</label>
                            <input type="text" name="materials" value="<?php echo htmlspecialchars($artifact['materials']); ?>" class="bg-gray-50 border w-full p-2.5 rounded-lg">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">Measurements</label>
                            <input type="text" name="measurements" value="<?php echo htmlspecialchars($artifact['measurements']); ?>" class="bg-gray-50 border w-full p-2.5 rounded-lg">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">Gallery Number</label>
                            <input type="text" name="gallery_number" value="<?php echo htmlspecialchars($artifact['gallery_number']); ?>" class="bg-gray-50 border w-full p-2.5 rounded-lg">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">Condition</label>
                            <select name="condition" class="bg-gray-50 border w-full p-2.5 rounded-lg">
                                <?php $conditions = ['Excellent', 'Good', 'Fair', 'Poor']; ?>
                                <?php foreach($conditions as $condition): ?>
                                    <option <?php if($artifact['condition'] == $condition) echo 'selected'; ?>><?php echo $condition; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">Status</label>
                            <select name="status" class="bg-gray-50 border w-full p-2.5 rounded-lg">
                                <?php $statuses = ['In Storage', 'On Display', 'Under Restoration', 'On Loan']; ?>
                                <?php foreach($statuses as $status): ?>
                                    <option <?php if($artifact['status'] == $status) echo 'selected'; ?>><?php echo $status; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">Preservation Notes</label>
                            <input type="text" name="preservation_notes" value="<?php echo htmlspecialchars($artifact['preservation_notes']); ?>" class="bg-gray-50 border w-full p-2.5 rounded-lg">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block mb-1.5 text-sm font-medium">Correction Notes</label>
                            <input type="text" name="correction_notes" value="<?php echo htmlspecialchars($artifact['correction_notes']); ?>" class="bg-gray-50 border w-full p-2.5 rounded-lg">
                        </div>
                    </div>
                </div>

                <div class="bg-neutral-card p-6 rounded-xl border">
                    <h3 class="text-lg font-semibold border-b pb-3 mb-6">Digital Options</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                        <div>
                            <label class="block mb-1.5 text-sm font-medium">Change Primary Image</label>
                            <?php if(!empty($artifact['artifact_image_url'])): ?>
                            <div class="mb-4">
                                <p class="text-xs text-gray-500 mb-2">Current Image:</p>
                                <img src="<?php echo $baseURL . htmlspecialchars($artifact['artifact_image_url']); ?>" class="w-32 h-32 object-cover rounded-md border p-1">
                            </div>
                            <?php endif; ?>
                            <input type="file" name="artifactImage" class="block w-full text-sm text-gray-900 border rounded-lg cursor-pointer bg-gray-50">
                            <p class="mt-1 text-xs text-gray-500">Upload a new file only to replace the current one.</p>
                        </div>
                        <div class="flex items-center pt-8">
                            <input id="is_featured" name="is_featured" type="checkbox" value="1" <?php if($artifact['is_featured'] == 1) echo 'checked'; ?> class="w-4 h-4 text-brand-green bg-gray-100 border-gray-300 rounded">
                            <label for="is_featured" class="ml-2 text-sm font-medium">Feature this artifact on visitor dashboard?</label>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <footer class="fixed bottom-0 left-0 lg:left-64 right-0 bg-white/95 backdrop-blur-sm p-4 border-t border-neutral-border shadow-lg z-10">
            <div class="flex justify-end gap-4 max-w-7xl mx-auto px-8">
                <a href="artifact_management.php" class="px-6 py-2.5 text-sm font-semibold text-neutral-text-muted hover:text-neutral-text-main">Cancel</a>
                <button type="submit" form="artifact-form" class="bg-brand-green text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-opacity-90 shadow-sm flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Save Changes
                </button>
            </div>
        </footer>
    </main>
    <script> lucide.createIcons(); </script>
</body>
</html>