<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Protect the page: check if the user is logged in and is a visitor.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'visitor') {
    header("Location: login.php");
    exit();
}

// Configuration
$baseURL = "http://localhost/MUSEUM/";
$fullName = $_SESSION['full_name'] ?? 'Visitor';
$profilePhotoUrl = $_SESSION['profile_photo_url'] ?? null;
$userId = $_SESSION['user_id'];

// User initials for avatar
$userInitials = '';
if (!empty($fullName)) {
    $parts = explode(' ', $fullName);
    $userInitials = strtoupper(substr($parts[0], 0, 1) . (count($parts) > 1 ? substr(end($parts), 0, 1) : ''));
}

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "museum";

try {
    $conn = new mysqli($host, $username, $password, $database);
    $conn->set_charset("utf8mb4");
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Sorry, the museum is temporarily unavailable. Please try again later.");
}

// Get search and filter parameters
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$typeFilter = isset($_GET['type']) ? $_GET['type'] : 'all';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Initialize arrays for results
$artifacts = [];
$objectTypes = [];

// Get filter options - Object Types
$typesQuery = "SELECT DISTINCT object_type FROM artifacts WHERE object_type IS NOT NULL AND object_type != '' ORDER BY object_type ASC";
$typesResult = $conn->query($typesQuery);
if ($typesResult && $typesResult->num_rows > 0) {
    while ($row = $typesResult->fetch_assoc()) {
        $objectTypes[] = $row['object_type'];
    }
}

// Available statuses based on your ENUM
$statuses = ['On Display', 'In Storage', 'Under Restoration', 'On Loan'];

// Build the artifacts query with prepared statements
$baseQuery = "SELECT 
    artifact_id, 
    title, 
    collection_number, 
    object_type, 
    period, 
    description, 
    significance_comment,
    contributor_name,
    collection_date,
    found_place,
    materials,
    measurements,
    status,
    artifact_image_url,
    created_at
FROM artifacts";

$whereConditions = [];
$params = [];
$types = "";

// Add search condition
if (!empty($searchQuery)) {
    $whereConditions[] = "(title LIKE ? OR description LIKE ? OR object_type LIKE ? OR period LIKE ? OR materials LIKE ?)";
    $searchParam = "%$searchQuery%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
    $types .= "sssss";
}

// Add status filter
if ($statusFilter !== 'all') {
    $whereConditions[] = "status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

// Add type filter
if ($typeFilter !== 'all') {
    $whereConditions[] = "object_type = ?";
    $params[] = $typeFilter;
    $types .= "s";
}

// Build WHERE clause
$whereClause = !empty($whereConditions) ? " WHERE " . implode(" AND ", $whereConditions) : "";

// Order by clause
$orderClause = " ORDER BY ";
switch ($sortBy) {
    case 'oldest':
        $orderClause .= "created_at ASC";
        break;
    case 'title':
        $orderClause .= "title ASC";
        break;
    case 'type':
        $orderClause .= "object_type ASC, title ASC";
        break;
    default: // newest
        $orderClause .= "created_at DESC";
}

// Complete query
$artifactsQuery = $baseQuery . $whereClause . $orderClause;

try {
    if ($stmt = $conn->prepare($artifactsQuery)) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $artifacts[] = $row;
        }
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Artifact query error: " . $e->getMessage());
    $artifacts = []; // Graceful degradation
}

// Get statistics
$totalCount = count($artifacts);
$stats = ['total' => 0, 'on_display' => 0, 'in_storage' => 0];

try {
    $statsQuery = "SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN status = 'On Display' THEN 1 END) as on_display,
        COUNT(CASE WHEN status = 'In Storage' THEN 1 END) as in_storage
    FROM artifacts";
    $statsResult = $conn->query($statsQuery);
    if ($statsResult) {
        $stats = $statsResult->fetch_assoc();
    }
} catch (Exception $e) {
    error_log("Statistics query error: " . $e->getMessage());
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artifact Gallery - Digital Liberation War Museum</title>
    <meta name="description" content="Explore the complete collection of Liberation War artifacts, weapons, documents and historical items.">
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
            <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold mb-4 px-2">Explore Collections</div>
            
            <a href="visitor_dashboard.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="home" class="w-5 h-5 mr-3"></i> Museum Home
            </a>
            <a href="#" class="bg-gradient-to-r from-red-700 to-green-800 text-white flex items-center px-4 py-3 text-sm font-semibold rounded-xl shadow-lg">
                <i data-lucide="compass" class="w-5 h-5 mr-3"></i> Artifact Gallery
            </a>
            <a href="video_gallery.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="video" class="w-5 h-5 mr-3"></i> Video Archives
            </a>
            <a href="audio_archives.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="mic" class="w-5 h-5 mr-3"></i> Audio Stories
            </a>
            <a href="timeline.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="clock" class="w-5 h-5 mr-3"></i> Historical Timeline
            </a>
            <a href="heroes.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="star" class="w-5 h-5 mr-3"></i> Heroes & Martyrs
            </a>
            
            <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold mb-4 px-2 pt-6">Educational</div>
            <a href="virtual_tour.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="map" class="w-5 h-5 mr-3"></i> Virtual Tour
            </a>
            <a href="learning.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="book-open" class="w-5 h-5 mr-3"></i> Learning Center
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
                        Artifact Gallery
                    </h2>
                    <div class="hidden md:flex items-center space-x-6 text-sm text-slate-600">
                        <span class="flex items-center" title="Total artifacts">
                            <i data-lucide="archive" class="w-4 h-4 mr-1"></i>
                            <?php echo number_format((int)$stats['total']); ?> total
                        </span>
                        <span class="flex items-center" title="On display">
                            <i data-lucide="eye" class="w-4 h-4 mr-1"></i>
                            <?php echo number_format((int)$stats['on_display']); ?> on display
                        </span>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
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

        <!-- Hero Section -->
        <section class="h-64 bg-gradient-to-r from-red-700 via-green-700 to-slate-800 flex flex-col justify-center items-center text-center text-white p-8 relative overflow-hidden" style="background-image: linear-gradient(135deg, rgba(220, 20, 60, 0.9) 0%, rgba(0, 106, 78, 0.9) 50%, rgba(45, 55, 72, 0.95) 100%), url('https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/Liberation_War_of_Bangladesh_3.jpg/1200px-Liberation_War_of_Bangladesh_3.jpg'); background-size: cover; background-position: center;">
            <div class="absolute inset-0 bg-gradient-to-b from-black/20 to-black/40"></div>
            <div class="relative z-10 max-w-4xl mx-auto">
                <h1 class="text-4xl md:text-5xl font-bold font-serif mb-4">
                    Liberation War Artifacts
                </h1>
                <p class="text-xl opacity-95 font-light leading-relaxed">
                    Explore our comprehensive collection of historical artifacts from the 1971 Bangladesh Liberation War
                </p>
            </div>
        </section>

        <div class="p-6 lg:p-10">
            <!-- Search and Filters -->
            <section class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 mb-8 shadow-lg border border-white/20">
                <form method="GET" class="space-y-4">
                    <!-- Search Bar -->
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1 relative">
                            <input type="text" 
                                   name="search" 
                                   value="<?php echo htmlspecialchars($searchQuery); ?>"
                                   placeholder="Search artifacts by title, description, materials..." 
                                   class="w-full pl-10 pr-4 py-3 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300">
                            <i data-lucide="search" class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        </div>
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-red-600 to-green-600 text-white rounded-lg font-semibold hover:shadow-lg transition-all duration-300 hover:scale-105">
                            <i data-lucide="search" class="w-4 h-4 mr-2 inline"></i>
                            Search
                        </button>
                    </div>
                    
                    <!-- Filter Options -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Object Type Filter -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Artifact Type</label>
                            <select name="type" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300">
                                <option value="all" <?php echo $typeFilter === 'all' ? 'selected' : ''; ?>>All Types</option>
                                <?php foreach ($objectTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $typeFilter === $type ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300">
                                <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $statusFilter === $status ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($status); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Sort Options -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Sort By</label>
                            <select name="sort" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300">
                                <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                                <option value="title" <?php echo $sortBy === 'title' ? 'selected' : ''; ?>>Title A-Z</option>
                                <option value="type" <?php echo $sortBy === 'type' ? 'selected' : ''; ?>>By Type</option>
                            </select>
                        </div>
                    </div>
                </form>
            </section>

            <!-- Results Summary -->
            <div class="flex items-center justify-between mb-6">
                <div class="text-slate-600">
                    <span class="font-semibold text-slate-900"><?php echo number_format($totalCount); ?></span> 
                    <?php if ($searchQuery || $typeFilter !== 'all' || $statusFilter !== 'all'): ?>
                        artifacts found
                        <?php if ($searchQuery): ?>
                            for "<span class="font-medium text-red-600"><?php echo htmlspecialchars($searchQuery); ?></span>"
                        <?php endif; ?>
                    <?php else: ?>
                        total artifacts
                    <?php endif; ?>
                </div>
                
                <?php if ($searchQuery || $typeFilter !== 'all' || $statusFilter !== 'all'): ?>
                <a href="explore_museum.php" class="text-red-600 hover:text-green-600 text-sm font-semibold flex items-center transition-colors duration-300">
                    <i data-lucide="x" class="w-4 h-4 mr-1"></i>
                    Clear Filters
                </a>
                <?php endif; ?>
            </div>

            <!-- Artifacts Grid -->
            <?php if (!empty($artifacts)): ?>
            <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-6">
                <?php foreach ($artifacts as $artifact): ?>
                <article class="bg-white border border-slate-200 transition-all duration-500 hover:-translate-y-2 hover:scale-[1.02] hover:shadow-2xl hover:border-red-500 rounded-2xl overflow-hidden shadow-lg group">
                    <div class="relative overflow-hidden h-64">
                        <img src="<?php echo $baseURL . htmlspecialchars($artifact['artifact_image_url'] ?: 'images/default_artifact.png'); ?>" 
                             alt="<?php echo htmlspecialchars($artifact['title']); ?>"
                             loading="lazy"
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                             onerror="this.src='<?php echo $baseURL; ?>images/default_artifact.png'">
                        
                        <!-- Overlays -->
                        <div class="absolute top-4 left-4">
                            <span class="bg-gradient-to-r from-red-600 to-green-600 text-white px-3 py-1 text-xs rounded-full font-semibold">
                                <?php echo htmlspecialchars($artifact['object_type'] ?: 'Artifact'); ?>
                            </span>
                        </div>
                        
                        <div class="absolute top-4 right-4">
                            <?php 
                            $statusClass = '';
                            switch($artifact['status']) {
                                case 'On Display': $statusClass = 'bg-emerald-500 text-white'; break;
                                case 'In Storage': $statusClass = 'bg-slate-500 text-white'; break;
                                case 'Under Restoration': $statusClass = 'bg-amber-500 text-white'; break;
                                case 'On Loan': $statusClass = 'bg-purple-500 text-white'; break;
                                default: $statusClass = 'bg-gray-500 text-white';
                            }
                            ?>
                            <span class="<?php echo $statusClass; ?> px-2 py-1 text-xs font-semibold rounded-full">
                                <?php echo htmlspecialchars($artifact['status']); ?>
                            </span>
                        </div>
                        
                        <!-- Gradient overlay for better text readability -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>
                    
                    <div class="p-6">
                        <div class="mb-3">
                            <h3 class="font-bold text-slate-900 text-lg line-clamp-2 font-serif mb-1">
                                <?php echo htmlspecialchars($artifact['title']); ?>
                            </h3>
                            <p class="text-sm text-slate-500 font-medium">
                                <?php echo htmlspecialchars($artifact['collection_number']); ?>
                            </p>
                        </div>
                        
                        <?php if ($artifact['period']): ?>
                        <div class="flex items-center text-xs text-green-600 font-semibold mb-2">
                            <i data-lucide="clock" class="w-3 h-3 mr-1"></i>
                            <?php echo htmlspecialchars($artifact['period']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <p class="text-slate-600 text-sm line-clamp-3 mb-4 leading-relaxed">
                            <?php echo htmlspecialchars($artifact['description'] ?: $artifact['significance_comment'] ?: 'A significant artifact from the Bangladesh Liberation War of 1971.'); ?>
                        </p>
                        
                        <!-- Additional Info -->
                        <div class="space-y-2 mb-4">
                            <?php if ($artifact['materials']): ?>
                            <div class="flex items-center text-xs text-slate-500">
                                <i data-lucide="layers" class="w-3 h-3 mr-2 flex-shrink-0"></i>
                                <span class="font-medium">Material:</span>
                                <span class="ml-1 truncate"><?php echo htmlspecialchars($artifact['materials']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($artifact['measurements']): ?>
                            <div class="flex items-center text-xs text-slate-500">
                                <i data-lucide="ruler" class="w-3 h-3 mr-2 flex-shrink-0"></i>
                                <span class="font-medium">Size:</span>
                                <span class="ml-1 truncate"><?php echo htmlspecialchars($artifact['measurements']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($artifact['found_place']): ?>
                            <div class="flex items-center text-xs text-slate-500">
                                <i data-lucide="map-pin" class="w-3 h-3 mr-2 flex-shrink-0"></i>
                                <span class="font-medium">Origin:</span>
                                <span class="ml-1 truncate"><?php echo htmlspecialchars($artifact['found_place']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($artifact['contributor_name']): ?>
                            <div class="flex items-center text-xs text-slate-500">
                                <i data-lucide="user" class="w-3 h-3 mr-2 flex-shrink-0"></i>
                                <span class="font-medium">Contributed by:</span>
                                <span class="ml-1 truncate"><?php echo htmlspecialchars($artifact['contributor_name']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <a href="artifact_detail.php?id=<?php echo (int)$artifact['artifact_id']; ?>" 
                               class="flex-1 bg-gradient-to-r from-red-600 to-green-600 text-white text-center px-4 py-2 rounded-lg text-sm font-semibold hover:shadow-lg transition-all duration-300 hover:scale-105">
                                View Details
                            </a>
                            <button class="p-2 border-2 border-red-600 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition-colors duration-300" 
                                    title="Add to favorites"
                                    onclick="toggleFavorite(<?php echo (int)$artifact['artifact_id']; ?>)">
                                <i data-lucide="heart" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </section>
            <?php else: ?>
            <!-- No Results -->
            <section class="text-center py-16">
                <div class="max-w-md mx-auto">
                    <i data-lucide="search-x" class="w-24 h-24 text-slate-300 mx-auto mb-6"></i>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">No Artifacts Found</h3>
                    <p class="text-slate-600 mb-6">
                        <?php if ($searchQuery): ?>
                            We couldn't find any artifacts matching your search for "<?php echo htmlspecialchars($searchQuery); ?>".
                        <?php else: ?>
                            No artifacts match your current filters.
                        <?php endif; ?>
                    </p>
                    <a href="explore_museum.php" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-600 to-green-600 text-white rounded-lg font-semibold hover:shadow-lg transition-all duration-300 hover:scale-105">
                        <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                        View All Artifacts
                    </a>
                </div>
            </section>
            <?php endif; ?>
        </div>
    </main>

    <!-- Scripts -->
    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Auto-submit form on filter change
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const selects = form.querySelectorAll('select');
            
            selects.forEach(select => {
                select.addEventListener('change', function() {
                    form.submit();
                });
            });
        });

        // Search input enhancement
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    this.form.submit();
                }
            });
        }

        // Favorite functionality
        function toggleFavorite(artifactId) {
            const button = event.target.closest('button');
            const icon = button.querySelector('i');
            
            // Toggle visual state
            if (button.classList.contains('border-red-600')) {
                button.classList.remove('border-red-600', 'text-red-600');
                button.classList.add('bg-red-600', 'text-white');
                icon.setAttribute('data-lucide', 'heart');
            } else {
                button.classList.add('border-red-600', 'text-red-600');
                button.classList.remove('bg-red-600', 'text-white');
                icon.setAttribute('data-lucide', 'heart');
            }
            
            lucide.createIcons();
            console.log('Favorite toggled for artifact:', artifactId);
            
            // Here you would make an AJAX call to save/remove favorite
            // fetch('toggle_favorite.php', { method: 'POST', body: JSON.stringify({artifact_id: artifactId}) })
        }

        // Smooth scroll behavior
        document.documentElement.style.scrollBehavior = 'smooth';

        // Loading animation
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.group');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>
