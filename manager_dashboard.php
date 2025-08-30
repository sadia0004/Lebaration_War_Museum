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
// === IMPORTANT: This MUST match your project's folder name in htdocs ===
$baseURL = "http://localhost/MUSEUM/";
// ==============================================================================

// Retrieve user info from the session for personalization.
$fullName = $_SESSION['full_name'] ?? 'Manager';
$profilePhotoUrl = $_SESSION['profile_photo_url'] ?? null;
$userInitials = '';
if (!empty($fullName)) {
    $parts = explode(' ', $fullName);
    $userInitials = strtoupper(substr($parts[0], 0, 1)); // Only the first initial
}

// --- Database Connection and Data Fetching ---
$host = "localhost";
$username = "root";
$password = "";
$database = "museum";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Data Fetching for Dashboard Widgets ---
$totalArtifactsResult = $conn->query("SELECT COUNT(artifact_id) as total FROM artifacts");
$totalArtifacts = $totalArtifactsResult->fetch_assoc()['total'] ?? 0;

$featuredArtifactsResult = $conn->query("SELECT COUNT(artifact_id) as total FROM artifacts WHERE is_featured = 1");
$digitalExhibitions = $featuredArtifactsResult->fetch_assoc()['total'] ?? 0;

$monthlyVisitorsResult = $conn->query("SELECT COUNT(user_id) as total FROM users WHERE role = 'visitor' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
$monthlyVisitors = $monthlyVisitorsResult->fetch_assoc()['total'] ?? 0;

$recentArtifacts = [];
$recentArtifactsQuery = "SELECT title, contributor_name, object_type FROM artifacts ORDER BY created_at DESC LIMIT 3";
$recentArtifactsResult = $conn->query($recentArtifactsQuery);
if ($recentArtifactsResult && $recentArtifactsResult->num_rows > 0) {
    while ($row = $recentArtifactsResult->fetch_assoc()) {
        $recentArtifacts[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - Liberation War Museum</title>
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
                            'green': '#16a34a', // Primary Green (e.g., #16a34a from Tailwind's green-600)
                            'light': '#dcfce7', // Light green background
                        },
                        'neutral': {
                            'bg': '#f8fafc', // Background
                            'card': '#ffffff', // Card background
                            'border': '#e5e7eb', // Borders
                            'text-main': '#1f2937', // Main text
                            'text-muted': '#6b7280', // Muted text
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .active-sidebar-link {
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
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
            <a href="manager_dashboard.php" class="bg-brand-green text-white flex items-center px-4 py-2.5 text-sm font-semibold rounded-lg shadow-md">
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

    <main class="flex-1 p-8 lg:ml-64">
        <header class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <h1 class="text-2xl font-bold text-neutral-text-main">Management Portal</h1>
                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-1 rounded-full">Administrative Access</span>
            </div>
            <div class="flex items-center gap-6">
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-neutral-text-muted absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" placeholder="Search artifacts, collections..." class="w-64 pl-9 pr-4 py-2 text-sm border border-neutral-border rounded-lg bg-neutral-card focus:outline-none focus:ring-2 focus:ring-brand-green">
                </div>
                <div class="flex items-center gap-3">
                    <?php if ($profilePhotoUrl && file_exists($profilePhotoUrl)): ?>
                        <img src="<?php echo $baseURL . htmlspecialchars($profilePhotoUrl); ?>" alt="Profile Photo" class="w-9 h-9 rounded-full object-cover">
                    <?php else: ?>
                        <div class="w-9 h-9 bg-gray-800 rounded-full flex items-center justify-center">
                            <span class="text-sm font-medium text-white"><?php echo htmlspecialchars($userInitials); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="text-sm text-right">
                        <p class="font-semibold"><?php echo htmlspecialchars($fullName); ?></p>
                        <p class="text-xs text-neutral-text-muted">Museum Manager</p>
                    </div>
                </div>
            </div>
        </header>

        <section class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-xl font-semibold">Welcome back, <span class="text-brand-green"><?php echo htmlspecialchars(explode(' ', $fullName)[0]); ?></span></h2>
                <p class="text-neutral-text-muted">Here's your museum management overview for today</p>
            </div>
            <div class="text-right">
                <p id="time" class="text-2xl font-bold"></p>
                <p id="date" class="text-sm text-neutral-text-muted"></p>
            </div>
        </section>

        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-neutral-card p-6 rounded-xl border border-neutral-border flex justify-between items-start">
                <div>
                    <p class="text-sm text-neutral-text-muted">Total Artifacts</p>
                    <p class="text-3xl font-bold mt-2"><?php echo number_format($totalArtifacts); ?></p>
                    <p class="text-xs text-brand-green flex items-center gap-1 mt-1"><i data-lucide="arrow-up" class="w-3 h-3"></i> +12 this week</p>
                </div>
                <div class="bg-brand-light p-3 rounded-lg"><i data-lucide="box" class="text-brand-green w-6 h-6"></i></div>
            </div>
            <div class="bg-neutral-card p-6 rounded-xl border border-neutral-border flex justify-between items-start">
                <div>
                    <p class="text-sm text-neutral-text-muted">Digital Exhibitions</p>
                    <p class="text-3xl font-bold mt-2"><?php echo number_format($digitalExhibitions); ?></p>
                    <p class="text-xs text-brand-green flex items-center gap-1 mt-1"><i data-lucide="arrow-up" class="w-3 h-3"></i> 2 active now</p>
                </div>
                <div class="bg-brand-light p-3 rounded-lg"><i data-lucide="layout-template" class="text-brand-green w-6 h-6"></i></div>
            </div>
            <div class="bg-neutral-card p-6 rounded-xl border border-neutral-border flex justify-between items-start">
                <div>
                    <p class="text-sm text-neutral-text-muted">Monthly Visitors</p>
                    <p class="text-3xl font-bold mt-2"><?php echo number_format($monthlyVisitors); ?></p>
                    <p class="text-xs text-brand-green flex items-center gap-1 mt-1"><i data-lucide="arrow-up" class="w-3 h-3"></i> +8.2% vs last month</p>
                </div>
                <div class="bg-brand-light p-3 rounded-lg"><i data-lucide="ticket" class="text-brand-green w-6 h-6"></i></div>
            </div>
            <div class="bg-neutral-card p-6 rounded-xl border border-neutral-border flex justify-between items-start">
                <div>
                    <p class="text-sm text-neutral-text-muted">Content Reviews</p>
                    <p class="text-3xl font-bold mt-2">24</p>
                    <p class="text-xs text-orange-600 flex items-center gap-1 mt-1"><i data-lucide="alert-circle" class="w-3 h-3"></i> 5 pending approval</p>
                </div>
                <div class="bg-orange-100 p-3 rounded-lg"><i data-lucide="clipboard-check" class="text-orange-600 w-6 h-6"></i></div>
            </div>
        </section>

        <section class="bg-neutral-card p-6 rounded-xl border border-neutral-border">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Recently Added Artifacts</h3>
                <button class="flex items-center gap-2 bg-brand-green text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-opacity-90 transition-all shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i> Add New Artifact
                </button>
            </div>
            <div class="text-xs uppercase text-neutral-text-muted font-semibold grid grid-cols-3 gap-4 px-4 py-2 border-b border-neutral-border">
                <span>Artifact Details</span>
                <span class="text-center">Category & Status</span>
                <span></span>
            </div>
            <div class="divide-y divide-neutral-border">
                <?php if (!empty($recentArtifacts)): ?>
                    <?php foreach ($recentArtifacts as $artifact): ?>
                        <div class="grid grid-cols-3 gap-4 items-center p-4 hover:bg-gray-50">
                            <div class="flex items-center gap-4">
                                <div class="bg-brand-light p-3 rounded-full"><i data-lucide="box" class="text-brand-green w-5 h-5"></i></div>
                                <div>
                                    <p class="font-semibold"><?php echo htmlspecialchars($artifact['title']); ?></p>
                                    <p class="text-xs text-neutral-text-muted">Donated by <?php echo htmlspecialchars($artifact['contributor_name'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                            <div class="text-center">
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-1 rounded-full"><?php echo htmlspecialchars($artifact['object_type']); ?></span>
                            </div>
                            <div class="text-right">
                                <button class="text-neutral-text-muted hover:text-neutral-text-main"><i data-lucide="more-vertical" class="w-5 h-5"></i></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-neutral-text-muted py-8">No recently added artifacts.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
        lucide.createIcons();

        function updateTime() {
            const timeEl = document.getElementById('time');
            const dateEl = document.getElementById('date');

            if (timeEl && dateEl) {
                const now = new Date();
                const timeOptions = {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                };
                timeEl.textContent = now.toLocaleTimeString('en-US', timeOptions).replace(' ', ''); // Remove space for format like 12:09AM

                const dateOptions = {
                    weekday: 'long',
                    month: 'long',
                    day: 'numeric'
                };
                dateEl.textContent = now.toLocaleDateString('en-US', dateOptions);
            }
        }
        updateTime();
        setInterval(updateTime, 1000);
    </script>
</body>

</html>