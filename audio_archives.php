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

// Get search and filter parameters
$searchQuery = trim($_GET['q'] ?? '');
$selectedCategory = $_GET['category'] ?? 'All';

// Fetch unique categories DYNAMICALLY from database
$categories = ['All'];
$categoryQuery = "SELECT DISTINCT category FROM media WHERE media_type = 'audio' AND category IS NOT NULL ORDER BY category ASC";
$categoryResult = $conn->query($categoryQuery);
if ($categoryResult && $categoryResult->num_rows > 0) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

// Build audio query with filters
$audioWhere = "media_type = 'audio'";

// Add category filter
if ($selectedCategory !== 'All' && in_array($selectedCategory, $categories)) {
    $audioWhere .= " AND category = '" . $conn->real_escape_string($selectedCategory) . "'";
}

// Add search filter
if (!empty($searchQuery)) {
    $searchEscaped = $conn->real_escape_string($searchQuery);
    $audioWhere .= " AND (title LIKE '%$searchEscaped%' OR description LIKE '%$searchEscaped%')";
}

// Fetch audio files based on filters
$audios = [];
$audioQuery = "SELECT * FROM media WHERE $audioWhere ORDER BY created_at DESC";
$audioResult = $conn->query($audioQuery);
if ($audioResult && $audioResult->num_rows > 0) {
    while ($row = $audioResult->fetch_assoc()) {
        $audios[] = $row;
    }
}

// Get statistics
$totalAudios = count($audios);
$allAudiosQuery = "SELECT COUNT(*) as total FROM media WHERE media_type = 'audio'";
$allAudiosResult = $conn->query($allAudiosQuery);
$allAudiosCount = 0;
if ($allAudiosResult) {
    $allAudiosCount = $allAudiosResult->fetch_assoc()['total'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audio Stories - Digital Liberation War Museum</title>
    <meta name="description" content="Listen to audio stories, interviews, and speeches from the Bangladesh Liberation War of 1971. Voices of freedom fighters and historical testimonies.">
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
            <a href="#" class="bg-gradient-to-r from-red-700 to-green-800 text-white flex items-center px-4 py-3 text-sm font-semibold rounded-xl shadow-lg">
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
                    <!-- Breadcrumb -->
                    <nav class="flex items-center space-x-2 text-sm text-slate-600">
                        <a href="visitor_dashboard.php" class="hover:text-red-600 transition-colors">Museum</a>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        <span class="text-slate-900 font-medium">Audio Stories</span>
                    </nav>
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
        <section class="bg-gradient-to-r from-red-700 via-green-700 to-slate-800 text-white p-8 relative overflow-hidden" style="background-image: linear-gradient(135deg, rgba(220, 20, 60, 0.85) 0%, rgba(0, 106, 78, 0.85) 50%, rgba(45, 55, 72, 0.9) 100%); background-size: cover;">
            <div class="absolute inset-0 bg-gradient-to-r from-black/20 to-black/40"></div>
            <div class="relative z-10 max-w-4xl mx-auto text-center" data-aos="fade-up">
                <h1 class="text-4xl md:text-5xl font-bold font-serif mb-4">
                    Audio <span class="text-yellow-400">Stories</span>
                </h1>
                <p class="text-xl md:text-2xl mb-6 opacity-95 font-light leading-relaxed">
                    Listen to voices from 1971 • Testimonies, speeches, and interviews from our heroes
                </p>
                
                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-8">
                    <div class="bg-white/20 backdrop-blur-lg rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold"><?php echo number_format($allAudiosCount); ?></div>
                        <div class="text-sm opacity-90">Total Audio</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-lg rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold"><?php echo number_format($totalAudios); ?></div>
                        <div class="text-sm opacity-90">Showing Now</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-lg rounded-xl p-4 text-center md:col-span-1 col-span-2">
                        <div class="text-2xl font-bold"><?php echo count($categories) - 1; ?></div>
                        <div class="text-sm opacity-90">Categories</div>
                    </div>
                </div>
            </div>
        </section>

        <div class="p-6 lg:p-10">
            <!-- Search and Filter Section -->
            <section class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20 mb-8" data-aos="fade-up">
                <form method="GET" class="flex flex-col lg:flex-row gap-4 items-center">
                    <!-- Search Input -->
                    <div class="flex-1 w-full lg:w-auto">
                        <div class="relative">
                            <input type="search" 
                                   name="q" 
                                   value="<?php echo htmlspecialchars($searchQuery); ?>"
                                   placeholder="Search audio stories, interviews, speeches..."
                                   class="w-full pl-12 pr-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 bg-white">
                            <i data-lucide="search" class="w-5 h-5 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                        </div>
                    </div>
                    
                    <!-- Category Filter -->
                    <div class="flex items-center gap-4">
                        <label for="category" class="text-sm font-medium text-slate-700 whitespace-nowrap">Filter by:</label>
                        <select name="category" id="category" 
                                class="border border-slate-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 bg-white min-w-[150px]">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $selectedCategory === $category ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Search Button -->
                    <button type="submit" 
                            class="px-8 py-3 bg-gradient-to-r from-red-600 to-green-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all duration-300 hover:scale-105 flex items-center">
                        <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                        Search
                    </button>
                </form>
                
                <!-- Active Filters Display -->
                <?php if (!empty($searchQuery) || $selectedCategory !== 'All'): ?>
                <div class="mt-6 flex flex-wrap items-center gap-2">
                    <span class="text-sm font-medium text-slate-700">Active filters:</span>
                    
                    <?php if (!empty($searchQuery)): ?>
                    <span class="inline-flex items-center gap-2 bg-red-100 text-red-800 text-sm px-3 py-1 rounded-full">
                        Search: "<?php echo htmlspecialchars($searchQuery); ?>"
                        <a href="?category=<?php echo urlencode($selectedCategory); ?>" class="hover:text-red-600">
                            <i data-lucide="x" class="w-3 h-3"></i>
                        </a>
                    </span>
                    <?php endif; ?>
                    
                    <?php if ($selectedCategory !== 'All'): ?>
                    <span class="inline-flex items-center gap-2 bg-green-100 text-green-800 text-sm px-3 py-1 rounded-full">
                        Category: <?php echo htmlspecialchars($selectedCategory); ?>
                        <a href="?q=<?php echo urlencode($searchQuery); ?>" class="hover:text-green-600">
                            <i data-lucide="x" class="w-3 h-3"></i>
                        </a>
                    </span>
                    <?php endif; ?>
                    
                    <a href="?" class="text-sm text-slate-600 hover:text-red-600 transition-colors">Clear all</a>
                </div>
                <?php endif; ?>
            </section>

            <!-- Audio Gallery -->
            <section class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20" data-aos="fade-up">
                <?php if (!empty($audios)): ?>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <?php foreach ($audios as $index => $audio): ?>
                        <article class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-500 border border-slate-100" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <!-- Audio Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-red-500/10 to-green-500/10 rounded-full flex items-center justify-center">
                                        <i data-lucide="headphones" class="w-6 h-6 text-red-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-900 text-lg font-serif line-clamp-2">
                                            <?php echo htmlspecialchars($audio['title']); ?>
                                        </h3>
                                        <?php if ($audio['category']): ?>
                                        <span class="inline-block mt-1 bg-red-600/10 text-red-700 text-xs px-3 py-1 rounded-full font-semibold">
                                            <?php echo htmlspecialchars($audio['category']); ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <?php if ($audio['description']): ?>
                            <p class="text-slate-600 text-sm mb-4 line-clamp-3 leading-relaxed">
                                <?php echo htmlspecialchars($audio['description']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <!-- Audio Player -->
                            <div class="bg-slate-50 rounded-xl p-4">
                                <audio controls preload="none" class="w-full">
                                    <source src="<?php echo $baseURL . htmlspecialchars($audio['file_url']); ?>" type="audio/mpeg">
                                    <source src="<?php echo $baseURL . htmlspecialchars($audio['file_url']); ?>" type="audio/wav">
                                    <source src="<?php echo $baseURL . htmlspecialchars($audio['file_url']); ?>" type="audio/ogg">
                                    Your browser does not support the audio element.
                                </audio>
                            </div>
                            
                            <!-- Audio Meta -->
                            <div class="flex items-center justify-between mt-4 text-xs text-slate-500">
                                <span class="flex items-center">
                                    <i data-lucide="calendar" class="w-3 h-3 mr-1"></i>
                                    <?php echo date('M j, Y', strtotime($audio['created_at'])); ?>
                                </span>
                                <span class="flex items-center">
                                    <i data-lucide="mic" class="w-3 h-3 mr-1"></i>
                                    Audio Story
                                </span>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="text-center py-16">
                        <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i data-lucide="mic-off" class="w-12 h-12 text-slate-400"></i>
                        </div>
                        <h3 class="text-2xl font-semibold text-slate-900 mb-2">No Audio Stories Found</h3>
                        <p class="text-slate-600 mb-6">
                            <?php if (!empty($searchQuery) || $selectedCategory !== 'All'): ?>
                                No audio stories match your search criteria. Try adjusting your filters.
                            <?php else: ?>
                                The audio archive is being prepared. Please check back soon.
                            <?php endif; ?>
                        </p>
                        <?php if (!empty($searchQuery) || $selectedCategory !== 'All'): ?>
                        <a href="?" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-300 inline-flex items-center">
                            <i data-lucide="refresh-ccw" class="w-4 h-4 mr-2"></i>
                            Clear Filters
                        </a>
                        <?php endif; ?>
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

        // Enhanced search functionality
        const searchInput = document.querySelector('input[name="q"]');
        if (searchInput) {
            searchInput.addEventListener('focus', function() {
                this.parentElement.classList.add('ring-2', 'ring-red-500/20');
            });
            
            searchInput.addEventListener('blur', function() {
                this.parentElement.classList.remove('ring-2', 'ring-red-500/20');
            });
        }

        // Add keyboard shortcuts for search
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'k') {
                e.preventDefault();
                if (searchInput) {
                    searchInput.focus();
                }
            }
        });

        // Audio player enhancements
        document.querySelectorAll('audio').forEach((audio, index) => {
            // Add loading state
            audio.addEventListener('loadstart', function() {
                this.parentElement.classList.add('opacity-50');
            });
            
            audio.addEventListener('canplay', function() {
                this.parentElement.classList.remove('opacity-50');
            });
            
            // Pause other audio when one starts playing
            audio.addEventListener('play', function() {
                document.querySelectorAll('audio').forEach((otherAudio) => {
                    if (otherAudio !== this) {
                        otherAudio.pause();
                    }
                });
            });
            
            // Error handling
            audio.addEventListener('error', function() {
                console.log('Audio loading error for:', this.src);
                const errorMsg = document.createElement('div');
                errorMsg.className = 'text-red-600 text-sm text-center py-4';
                errorMsg.textContent = 'Unable to load audio file';
                this.parentElement.replaceChild(errorMsg, this);
            });
        });

        // Auto-submit form on category change
        document.getElementById('category').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</body>
</html>
