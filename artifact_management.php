<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Protect the page: check if the user is logged in and is a manager.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit();
}

// ==============================================================================
// === This MUST match your project's folder name in htdocs ===
$baseURL = "http://localhost/MUSEUM/";
// ==============================================================================

// Retrieve user info from the session.
$fullName = $_SESSION['full_name'] ?? 'Manager';
$profilePhotoUrl = $_SESSION['profile_photo_url'] ?? null;
$userInitials = '';
if (!empty($fullName)) {
    $parts = explode(' ', $fullName);
    $userInitials = strtoupper(substr($parts[0], 0, 1) . (count($parts) > 1 ? substr(end($parts), 0, 1) : ''));
}

// --- Database Connection ---
$host = "localhost";
$username = "root";
$password = "";
$database = "museum";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all artifacts for display
$artifacts = [];
$query = "SELECT artifact_id, title, collection_number, object_type, artifact_image_url FROM artifacts ORDER BY created_at DESC";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $artifacts[] = $row;
    }
}

// Define the fixed list of artifact types for the filter tabs
$artifactTypes = ['Documents', 'Personal Items', 'Weaponry', 'Media', 'Clothing', 'Other'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artifact Management - Digital Liberation War Museum</title>
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
    <style>
        .tab-active {
            border-color: #dc143c;
            color: #dc143c;
            font-weight: 600;
            background-color: #fef2f2;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 font-sans">
    <!-- Skip to content for accessibility -->
    <a href="#main-content" class="sr-only focus:not-sr-only fixed top-4 left-4 z-50 bg-red-600 text-white px-4 py-2 rounded-md focus:outline-none">Skip to main content</a>

    <!-- Sidebar Navigation -->
    <aside class="w-72 bg-white/95 backdrop-blur-lg border-r border-slate-200/50 flex-col h-screen fixed hidden lg:flex shadow-xl">
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
                <i data-lucide="clapperboard" class="w-5 h-5 mr-3"></i> Add Media
            </a>
            <a href="add_timeline_event.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="milestone" class="w-5 h-5 mr-3"></i> Digital Timeline
            </a>
            
            <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold mb-4 px-2 pt-6">Analytics</div>
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

    <main id="main-content" class="flex-1 lg:ml-72">
        <!-- Enhanced Header -->
        <header class="bg-white/90 backdrop-blur-lg border-b border-slate-200/50 sticky top-0 z-40 shadow-sm">
            <div class="flex items-center justify-between px-6 lg:px-8 py-4">
                <div class="flex items-center space-x-4">
                    <h2 class="text-2xl font-bold text-slate-900 font-serif">
                        Artifact Management
                    </h2>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Enhanced Search -->
                    <div class="relative">
                        <i data-lucide="search" class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="text" 
                               id="search-input"
                               placeholder="Search artifacts..." 
                               class="w-64 pl-10 pr-4 py-2 rounded-full border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 bg-white/80 backdrop-blur transition-all duration-300">
                    </div>
                    
                    <!-- Profile -->
                    <?php if ($profilePhotoUrl && file_exists($profilePhotoUrl)): ?>
                        <img src="<?php echo $baseURL . htmlspecialchars($profilePhotoUrl); ?>" 
                             alt="Profile photo" 
                             class="w-11 h-11 rounded-full object-cover border-3 border-yellow-500 shadow-lg">
                    <?php else: ?>
                        <div class="w-11 h-11 bg-gradient-to-br from-red-600 to-green-600 rounded-full flex items-center justify-center shadow-lg">
                            <span class="text-sm font-bold text-white"><?php echo htmlspecialchars($userInitials); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <div class="p-6 lg:p-10">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 font-serif mb-2">Artifact Management</h1>
                    <p class="text-slate-600 text-lg">Manage and organize all artifacts in the collection</p>
                </div>
                <a href="add_artifact.php" class="flex items-center gap-2 bg-gradient-to-r from-red-600 to-green-600 text-white px-6 py-3 rounded-full text-sm font-semibold hover:shadow-lg transition-all duration-300 hover:scale-105 shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i> Add New Artifact
                </a>
            </div>

            <div class="border-b border-slate-200 mb-8">
                <nav id="artifact-tabs" class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
                    <button data-type="all" class="tab-active whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                        All Artifacts
                    </button>
                    <?php foreach ($artifactTypes as $type): ?>
                        <button data-type="<?php echo htmlspecialchars(strtolower($type)); ?>" class="whitespace-nowrap py-3 px-1 border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium text-sm">
                            <?php echo htmlspecialchars($type); ?>
                        </button>
                    <?php endforeach; ?>
                </nav>
            </div>

            <section id="artifacts-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (!empty($artifacts)): ?>
                    <?php foreach ($artifacts as $artifact): ?>
                        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden flex flex-col group artifact-card transition-all duration-500 hover:-translate-y-2 hover:scale-[1.02] hover:shadow-2xl hover:border-red-500"
                            data-title="<?php echo htmlspecialchars(strtolower($artifact['title'])); ?>"
                            data-collection="<?php echo htmlspecialchars(strtolower($artifact['collection_number'])); ?>"
                            data-type="<?php echo htmlspecialchars(strtolower($artifact['object_type'])); ?>">

                            <div class="h-56 bg-gray-100 flex items-center justify-center overflow-hidden">
                                <img src="<?php echo $baseURL . htmlspecialchars($artifact['artifact_image_url'] ?? 'images/default_artifact.png'); ?>"
                                    alt="<?php echo htmlspecialchars($artifact['title']); ?>"
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                                    onerror="this.src='<?php echo $baseURL; ?>images/default_artifact.png'">
                            </div>
                            <div class="p-6 flex-grow flex flex-col">
                                <p class="text-xs text-green-600 font-medium"><?php echo htmlspecialchars($artifact['object_type']); ?></p>
                                <h4 class="font-bold text-slate-900 mt-1 truncate group-hover:text-red-600 transition font-serif"><?php echo htmlspecialchars($artifact['title']); ?></h4>
                                <p class="text-sm text-slate-500 mt-0.5"><?php echo htmlspecialchars($artifact['collection_number']); ?></p>
                                <div class="mt-auto pt-4 flex items-center justify-end space-x-2">
                                    <a href="edit_artifact.php?edit_id=<?php echo $artifact['artifact_id']; ?>" class="text-xs p-2 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-md transition"><i data-lucide="edit" class="w-4 h-4"></i></a>
                                    <a href="delete_artifact.php?id=<?php echo $artifact['artifact_id']; ?>" onclick="return confirm('Are you sure you want to delete this artifact? This action cannot be undone.');" class="text-xs p-2 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-md transition"><i data-lucide="trash-2" class="w-4 h-4"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-12">
                        <i data-lucide="archive" class="w-16 h-16 text-slate-300 mx-auto mb-4"></i>
                        <p class="text-slate-500 text-lg">No artifacts available at the moment</p>
                        <a href="add_artifact.php" class="mt-4 inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-600 to-green-600 text-white rounded-full text-sm font-semibold hover:shadow-lg transition-all duration-300">
                            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add Your First Artifact
                        </a>
                    </div>
                <?php endif; ?>
            </section>

            <div id="no-results-message" class="text-center py-12 text-slate-500 hidden col-span-full">
                <i data-lucide="search-x" class="w-16 h-16 mx-auto text-slate-300"></i>
                <p class="mt-4 text-lg">No Artifacts Found</p>
                <p class="text-sm">No artifacts match your current filter and search criteria.</p>
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();

        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('search-input');
            const tabsContainer = document.getElementById('artifact-tabs');
            const artifactCards = document.querySelectorAll('.artifact-card');
            const noResultsMessage = document.getElementById('no-results-message');
            let activeTab = 'all';

            function filterArtifacts() {
                const query = searchInput.value.toLowerCase().trim();
                let visibleCount = 0;

                artifactCards.forEach(card => {
                    const title = card.dataset.title || '';
                    const collection = card.dataset.collection || '';
                    const type = card.dataset.type || '';

                    const typeMatch = (activeTab === 'all' || type === activeTab);
                    const searchMatch = (title.includes(query) || collection.includes(query));

                    if (typeMatch && searchMatch) {
                        card.style.display = 'flex';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                noResultsMessage.style.display = visibleCount === 0 ? 'block' : 'none';
            }

            // Event listener for search input
            searchInput.addEventListener('input', filterArtifacts);

            // Event listener for tabs
            tabsContainer.addEventListener('click', (e) => {
                const clickedTab = e.target.closest('button');
                if (!clickedTab) return;

                activeTab = clickedTab.dataset.type;

                tabsContainer.querySelectorAll('button').forEach(tab => {
                    tab.classList.remove('tab-active');
                    tab.classList.add('border-transparent', 'text-slate-500', 'hover:text-slate-700', 'hover:border-slate-300');
                });
                clickedTab.classList.add('tab-active');
                clickedTab.classList.remove('border-transparent', 'text-slate-500', 'hover:text-slate-700', 'hover:border-slate-300');

                filterArtifacts();
            });

            // Initial filter on page load
            if (artifactCards.length > 0) {
                filterArtifacts();
            } else {
                noResultsMessage.style.display = 'block';
            }
            
            // Error handling for missing images
            document.querySelectorAll('img').forEach(img => {
                img.addEventListener('error', function() {
                    if (this.src.includes('default_')) return;
                    
                    if (this.alt.includes('artifact') || this.closest('.group')) {
                        this.src = '<?php echo $baseURL; ?>images/default_artifact.png';
                    } else {
                        this.src = '<?php echo $baseURL; ?>images/logo.png';
                    }
                    
                    this.classList.add('opacity-60');
                });
            });
        });
    </script>
</body>

</html>