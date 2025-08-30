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
$userInitials = '';
if (!empty($fullName)) {
    $parts = explode(' ', $fullName);
    $userInitials = strtoupper(substr($parts[0], 0, 1));
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
    <title>Artifact Management - Museum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif']
                    },
                    colors: {
                        'brand': {
                            'green': '#16a34a',
                            'light': '#dcfce7'
                        },
                        'neutral': {
                            'bg': '#f8fafc',
                            'card': '#ffffff',
                            'border': '#e5e7eb',
                            'text-main': '#1f2937',
                            'text-muted': '#6b7280'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .tab-active {
            border-color: #16a34a;
            color: #16a34a;
            font-weight: 600;
            background-color: #f0fdf4;
        }
    </style>
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
            <a href="artifact_management.php" class="bg-brand-green text-white flex items-center px-4 py-2.5 text-sm font-semibold rounded-lg shadow-md" >
                <i data-lucide="archive" class="w-5 h-5 mr-3"></i> Artifacts
            </a>
            <a href="digital_collections.php" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg" >
                <i data-lucide="gem" class="w-5 h-5 mr-3"></i> Digital Collections
            </a>
             <a href="add_media.php" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="clapperboard" class="w-5 h-5 mr-3"></i> Add Media
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

    <main class="flex-1 p-8 lg:ml-64">
        <header class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-neutral-text-main">Artifact Management</h1>
            <div class="flex items-center gap-6">
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-neutral-text-muted absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="search-input" placeholder="Search artifacts..." class="w-64 pl-9 pr-4 py-2 text-sm border border-neutral-border rounded-lg bg-neutral-card focus:outline-none focus:ring-2 focus:ring-brand-green">
                </div>
                <a href="add_artifact.php" class="flex items-center gap-2 bg-brand-green text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-opacity-90 transition-all shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i> Add New Artifact
                </a>
            </div>
        </header>

        <div class="border-b border-neutral-border mb-6">
            <nav id="artifact-tabs" class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
                <button data-type="all" class="tab-active whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                    All Artifacts
                </button>
                <?php foreach ($artifactTypes as $type): ?>
                    <button data-type="<?php echo htmlspecialchars(strtolower($type)); ?>" class="whitespace-nowrap py-3 px-1 border-b-2 border-transparent text-neutral-text-muted hover:text-neutral-text-main hover:border-gray-300 font-medium text-sm">
                        <?php echo htmlspecialchars($type); ?>
                    </button>
                <?php endforeach; ?>
            </nav>
        </div>

        <section id="artifacts-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (!empty($artifacts)): ?>
                <?php foreach ($artifacts as $artifact): ?>
                    <div class="bg-neutral-card rounded-xl border border-neutral-border overflow-hidden flex flex-col group artifact-card"
                        data-title="<?php echo htmlspecialchars(strtolower($artifact['title'])); ?>"
                        data-collection="<?php echo htmlspecialchars(strtolower($artifact['collection_number'])); ?>"
                        data-type="<?php echo htmlspecialchars(strtolower($artifact['object_type'])); ?>">

                        <div class="h-56 bg-gray-100 flex items-center justify-center">
                            <img src="<?php echo $baseURL . htmlspecialchars($artifact['artifact_image_url'] ?? 'images/default_artifact.png'); ?>"
                                alt="<?php echo htmlspecialchars($artifact['title']); ?>"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="p-4 flex-grow flex flex-col">
                            <p class="text-xs text-brand-green font-medium"><?php echo htmlspecialchars($artifact['object_type']); ?></p>
                            <h4 class="font-bold text-neutral-text-main mt-1 truncate group-hover:text-brand-green transition"><?php echo htmlspecialchars($artifact['title']); ?></h4>
                            <p class="text-sm text-neutral-text-muted mt-0.5"><?php echo htmlspecialchars($artifact['collection_number']); ?></p>
                            <div class="mt-auto pt-4 flex items-center justify-end space-x-2">
                                <a href="edit_artifact.php?edit_id=<?php echo $artifact['artifact_id']; ?>" class="text-xs p-2 text-neutral-text-muted hover:text-blue-600 hover:bg-blue-50 rounded-md transition"><i data-lucide="edit" class="w-4 h-4"></i></a>
                                <a href="delete_artifact.php?id=<?php echo $artifact['artifact_id']; ?>" onclick="return confirm('Are you sure you want to delete this artifact? This action cannot be undone.');" class="text-xs p-2 text-neutral-text-muted hover:text-red-600 hover:bg-red-50 rounded-md transition"><i data-lucide="trash-2" class="w-4 h-4"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <div id="no-results-message" class="text-center py-12 text-neutral-text-muted hidden col-span-full">
            <i data-lucide="search-x" class="w-16 h-16 mx-auto text-gray-300"></i>
            <p class="mt-4 text-lg">No Artifacts Found</p>
            <p class="text-sm">No artifacts match your current filter and search criteria.</p>
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
                    tab.classList.add('border-transparent', 'text-neutral-text-muted', 'hover:text-neutral-text-main', 'hover:border-gray-300');
                });
                clickedTab.classList.add('tab-active');
                clickedTab.classList.remove('border-transparent', 'text-neutral-text-muted', 'hover:text-neutral-text-main', 'hover:border-gray-300');

                filterArtifacts();
            });

            // Initial filter on page load
            if (artifactCards.length > 0) {
                filterArtifacts();
            } else {
                noResultsMessage.style.display = 'block';
            }
        });
    </script>
</body>

</html>