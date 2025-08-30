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

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get filter parameters
$selectedCategory = $_GET['category'] ?? 'All';
$selectedYear = $_GET['year'] ?? 'All';

// Fetch unique categories and years
$categories = ['All'];
$years = ['All'];

$categoryQuery = "SELECT DISTINCT category FROM timeline_events WHERE category IS NOT NULL ORDER BY category ASC";
$categoryResult = $conn->query($categoryQuery);
if ($categoryResult && $categoryResult->num_rows > 0) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

$yearQuery = "SELECT DISTINCT YEAR(event_date) as year FROM timeline_events ORDER BY year ASC";
$yearResult = $conn->query($yearQuery);
if ($yearResult && $yearResult->num_rows > 0) {
    while ($row = $yearResult->fetch_assoc()) {
        $years[] = $row['year'];
    }
}

// Build timeline query with filters
$timelineWhere = "1=1";

if ($selectedCategory !== 'All' && in_array($selectedCategory, $categories)) {
    $timelineWhere .= " AND category = '" . $conn->real_escape_string($selectedCategory) . "'";
}

if ($selectedYear !== 'All' && in_array($selectedYear, $years)) {
    $timelineWhere .= " AND YEAR(event_date) = " . intval($selectedYear);
}

// Fetch timeline events
$events = [];
$eventsQuery = "SELECT * FROM timeline_events WHERE $timelineWhere ORDER BY event_date ASC";
$eventsResult = $conn->query($eventsQuery);
if ($eventsResult && $eventsResult->num_rows > 0) {
    while ($row = $eventsResult->fetch_assoc()) {
        $events[] = $row;
    }
}

// Get statistics
$totalEvents = count($events);
$allEventsQuery = "SELECT COUNT(*) as total FROM timeline_events";
$allEventsResult = $conn->query($allEventsQuery);
$allEventsCount = 0;
if ($allEventsResult) {
    $allEventsCount = $allEventsResult->fetch_assoc()['total'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historical Timeline - Digital Liberation War Museum</title>
    <meta name="description" content="Explore the chronological timeline of Bangladesh Liberation War 1971. Key events, battles, and milestones that led to independence.">
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
            <a href="explore_museum.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="compass" class="w-5 h-5 mr-3"></i> Artifact Gallery
            </a>
            <a href="video_gallery.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="video" class="w-5 h-5 mr-3"></i> Video Archives
            </a>
            <a href="audio_archives.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="mic" class="w-5 h-5 mr-3"></i> Audio Stories
            </a>
            <a href="#" class="bg-gradient-to-r from-red-700 to-green-800 text-white flex items-center px-4 py-3 text-sm font-semibold rounded-xl shadow-lg">
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
                    <!-- Breadcrumb -->
                    <nav class="flex items-center space-x-2 text-sm text-slate-600">
                        <a href="visitor_dashboard.php" class="hover:text-red-600 transition-colors">Museum</a>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        <span class="text-slate-900 font-medium">Historical Timeline</span>
                    </nav>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Filters -->
                    <form method="GET" class="flex items-center space-x-2">
                        <select name="category" onchange="this.form.submit()" 
                                class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 bg-white">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $selectedCategory === $category ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category === 'All' ? 'All Categories' : $category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <select name="year" onchange="this.form.submit()" 
                                class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 bg-white">
                            <?php foreach ($years as $year): ?>
                                <option value="<?php echo htmlspecialchars($year); ?>" <?php echo $selectedYear === $year ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($year === 'All' ? 'All Years' : $year); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    
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
        <section class="bg-gradient-to-r from-red-700 via-green-700 to-slate-800 text-white p-8 relative overflow-hidden" style="background-image: linear-gradient(135deg, rgba(220, 20, 60, 0.85) 0%, rgba(0, 106, 78, 0.85) 50%, rgba(45, 55, 72, 0.9) 100%), url('https://upload.wikimedia.org/wikipedia/commons/thumb/f/f9/Mujibnagar_Memorial.jpg/1200px-Mujibnagar_Memorial.jpg'); background-size: cover;">
            <div class="absolute inset-0 bg-gradient-to-r from-black/40 to-black/60"></div>
            <div class="relative z-10 max-w-4xl mx-auto text-center" data-aos="fade-up">
                <h1 class="text-4xl md:text-5xl font-bold font-serif mb-4">
                    Historical <span class="text-yellow-400">Timeline</span>
                </h1>
                <p class="text-xl md:text-2xl mb-6 opacity-95 font-light leading-relaxed">
                    Journey through the key events of Bangladesh Liberation War • From struggle to independence
                </p>
                
                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-8">
                    <div class="bg-white/20 backdrop-blur-lg rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold"><?php echo number_format($allEventsCount); ?></div>
                        <div class="text-sm opacity-90">Total Events</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-lg rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold"><?php echo number_format($totalEvents); ?></div>
                        <div class="text-sm opacity-90">Showing Now</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-lg rounded-xl p-4 text-center md:col-span-1 col-span-2">
                        <div class="text-2xl font-bold">1971</div>
                        <div class="text-sm opacity-90">Liberation Year</div>
                    </div>
                </div>
            </div>
        </section>

        <div class="p-6 lg:p-10">
            <!-- Timeline -->
            <section class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20" data-aos="fade-up">
                <?php if (!empty($events)): ?>
                    <div class="relative">
                        <!-- Timeline Line -->
                        <div class="absolute left-8 md:left-1/2 transform md:-translate-x-1/2 w-1 bg-gradient-to-b from-red-600 via-green-600 to-red-600 h-full"></div>
                        
                        <!-- Timeline Events -->
                        <div class="space-y-12">
                            <?php foreach ($events as $index => $event): ?>
                            <?php $isEven = $index % 2 === 0; ?>
                            <div class="relative flex items-center <?php echo $isEven ? 'md:flex-row' : 'md:flex-row-reverse'; ?>" data-aos="fade-<?php echo $isEven ? 'right' : 'left'; ?>" data-aos-delay="<?php echo $index * 100; ?>">
                                <!-- Timeline Dot -->
                                <div class="absolute left-8 md:left-1/2 transform md:-translate-x-1/2 w-6 h-6 bg-white border-4 border-red-600 rounded-full z-10 flex items-center justify-center">
                                    <?php
                                    $categoryIcons = [
                                        'Political' => 'flag',
                                        'Military' => 'shield',
                                        'Diplomatic' => 'handshake',
                                        'Social' => 'users',
                                        'Economic' => 'trending-up',
                                        'Other' => 'circle'
                                    ];
                                    $icon = $categoryIcons[$event['category']] ?? 'circle';
                                    ?>
                                    <i data-lucide="<?php echo $icon; ?>" class="w-3 h-3 text-red-600"></i>
                                </div>
                                
                                <!-- Event Card -->
                                <div class="<?php echo $isEven ? 'ml-20 md:ml-0 md:mr-8' : 'ml-20 md:ml-8 md:mr-0'; ?> flex-1 max-w-md">
                                    <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-slate-100">
                                        <!-- Event Header -->
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex items-center space-x-2">
                                                <span class="bg-red-600/10 text-red-700 text-xs px-3 py-1 rounded-full font-semibold">
                                                    <?php echo htmlspecialchars($event['category']); ?>
                                                </span>
                                                <?php if ($event['is_featured']): ?>
                                                <span class="bg-yellow-500/10 text-yellow-700 text-xs px-2 py-1 rounded-full font-semibold">
                                                    Featured
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Event Date -->
                                        <div class="text-2xl font-bold text-slate-900 mb-2 font-serif">
                                            <?php echo date('F j, Y', strtotime($event['event_date'])); ?>
                                        </div>
                                        
                                        <!-- Event Title -->
                                        <h3 class="text-xl font-bold text-slate-900 mb-3 font-serif leading-tight">
                                            <?php echo htmlspecialchars($event['event_title']); ?>
                                        </h3>
                                        
                                        <!-- Location -->
                                        <?php if ($event['location']): ?>
                                        <div class="flex items-center text-sm text-slate-600 mb-3">
                                            <i data-lucide="map-pin" class="w-4 h-4 mr-2 text-green-600"></i>
                                            <?php echo htmlspecialchars($event['location']); ?>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <!-- Event Description -->
                                        <p class="text-slate-700 text-sm leading-relaxed mb-4">
                                            <?php echo htmlspecialchars($event['event_description']); ?>
                                        </p>
                                        
                                        <!-- Historical Significance -->
                                        <?php if ($event['event_significance']): ?>
                                        <div class="bg-slate-50 rounded-lg p-4 border-l-4 border-green-600">
                                            <h4 class="text-sm font-semibold text-slate-900 mb-2 flex items-center">
                                                <i data-lucide="lightbulb" class="w-4 h-4 mr-2 text-green-600"></i>
                                                Historical Significance
                                            </h4>
                                            <p class="text-slate-600 text-sm leading-relaxed">
                                                <?php echo htmlspecialchars($event['event_significance']); ?>
                                            </p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="text-center py-16">
                        <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i data-lucide="clock" class="w-12 h-12 text-slate-400"></i>
                        </div>
                        <h3 class="text-2xl font-semibold text-slate-900 mb-2">No Timeline Events Found</h3>
                        <p class="text-slate-600 mb-6">
                            No events match your current filter criteria. Try adjusting your filters.
                        </p>
                        <a href="?" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-300 inline-flex items-center">
                            <i data-lucide="refresh-ccw" class="w-4 h-4 mr-2"></i>
                            Clear Filters
                        </a>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        // Initialize animations
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });

        // Initialize Lucide icons
        lucide.createIcons();

        // Add smooth scroll behavior
        document.documentElement.style.scrollBehavior = 'smooth';
    </script>
</body>
</html>
