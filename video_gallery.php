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

// Get category filter from URL
$selectedCategory = $_GET['category'] ?? 'All';

// Fetch unique categories for filter dropdown
$categories = ['All'];
$categoryQuery = "SELECT DISTINCT category FROM media WHERE media_type = 'video' AND category IS NOT NULL ORDER BY category ASC";
$categoryResult = $conn->query($categoryQuery);
if ($categoryResult && $categoryResult->num_rows > 0) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

// Build video query with category filter
$videoWhere = "media_type = 'video'";
if ($selectedCategory !== 'All') {
    $videoWhere .= " AND category = '" . $conn->real_escape_string($selectedCategory) . "'";
}

// Fetch videos
$videos = [];
$videoQuery = "SELECT * FROM media WHERE $videoWhere ORDER BY created_at DESC";
$videoResult = $conn->query($videoQuery);
if ($videoResult && $videoResult->num_rows > 0) {
    while ($row = $videoResult->fetch_assoc()) {
        $videos[] = $row;
    }
}

// Get total video count for stats
$totalVideos = count($videos);
$allVideosQuery = "SELECT COUNT(*) as total FROM media WHERE media_type = 'video'";
$allVideosResult = $conn->query($allVideosQuery);
$allVideosCount = 0;
if ($allVideosResult) {
    $allVideosCount = $allVideosResult->fetch_assoc()['total'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Gallery - Digital Liberation War Museum</title>
    <meta name="description" content="Explore video archives documenting the Bangladesh Liberation War of 1971. Watch documentaries, interviews, and historical footage.">
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
            <a href="#" class="bg-gradient-to-r from-red-700 to-green-800 text-white flex items-center px-4 py-3 text-sm font-semibold rounded-xl shadow-lg">
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
                    <!-- Breadcrumb -->
                    <nav class="flex items-center space-x-2 text-sm text-slate-600">
                        <a href="visitor_dashboard.php" class="hover:text-red-600 transition-colors">Museum</a>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        <span class="text-slate-900 font-medium">Video Archives</span>
                    </nav>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Category Filter -->
                    <form method="GET" class="flex items-center space-x-2">
                        <label for="category" class="text-sm font-medium text-slate-700">Filter by:</label>
                        <select name="category" id="category" onchange="this.form.submit()" 
                                class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 bg-white">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $selectedCategory === $category ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category); ?>
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
        <section class="bg-gradient-to-r from-red-700 via-green-700 to-slate-800 text-white p-8 relative overflow-hidden" style="background-image: linear-gradient(135deg, rgba(220, 20, 60, 0.85) 0%, rgba(0, 106, 78, 0.85) 50%, rgba(45, 55, 72, 0.9) 100%); background-size: cover;">
            <div class="absolute inset-0 bg-gradient-to-r from-black/20 to-black/40"></div>
            <div class="relative z-10 max-w-4xl mx-auto text-center" data-aos="fade-up">
                <h1 class="text-4xl md:text-5xl font-bold font-serif mb-4">
                    Video <span class="text-yellow-400">Archives</span>
                </h1>
                <p class="text-xl md:text-2xl mb-6 opacity-95 font-light leading-relaxed">
                    Witness the Liberation War through archival footage, documentaries, and testimonies
                </p>
                
                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-8">
                    <div class="bg-white/20 backdrop-blur-lg rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold"><?php echo number_format($allVideosCount); ?></div>
                        <div class="text-sm opacity-90">Total Videos</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-lg rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold"><?php echo number_format($totalVideos); ?></div>
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
            <!-- Current Filter Display -->
            <?php if ($selectedCategory !== 'All'): ?>
            <div class="mb-8 bg-white/80 backdrop-blur-lg rounded-2xl p-4 border border-white/20" data-aos="fade-up">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 bg-red-600 rounded-full"></div>
                        <span class="text-lg font-semibold text-slate-900">
                            Showing: <span class="text-red-600"><?php echo htmlspecialchars($selectedCategory); ?></span> Videos
                        </span>
                    </div>
                    <a href="?category=All" class="text-sm text-slate-600 hover:text-red-600 transition-colors">
                        <i data-lucide="x-circle" class="w-4 h-4 inline mr-1"></i>Clear Filter
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Video Gallery -->
            <section class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20" data-aos="fade-up">
                <?php if (!empty($videos)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
                        <?php foreach ($videos as $index => $video): ?>
                        <article class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 group" data-aos="zoom-in" data-aos-delay="<?php echo $index * 100; ?>">
                            <!-- Video Player -->
                            <div class="relative h-56 bg-slate-900">
                                <?php if ($video['thumbnail_url']): ?>
                                    <img src="<?php echo $baseURL . htmlspecialchars($video['thumbnail_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($video['title']); ?> thumbnail"
                                         class="w-full h-full object-cover"
                                         onerror="this.style.display='none'">
                                <?php endif; ?>
                                
                                <!-- Play Button Overlay -->
                                <div class="absolute inset-0 bg-black/30 flex items-center justify-center cursor-pointer video-overlay" data-video-url="<?php echo $baseURL . htmlspecialchars($video['file_url']); ?>">
                                    <div class="w-16 h-16 bg-red-600/90 backdrop-blur-sm rounded-full flex items-center justify-center hover:scale-110 transition-transform duration-300">
                                        <i data-lucide="play" class="w-8 h-8 text-white ml-1"></i>
                                    </div>
                                </div>
                                
                                <!-- Category Badge -->
                                <?php if ($video['category']): ?>
                                <div class="absolute top-4 right-4">
                                    <span class="bg-red-600/90 text-white text-xs px-3 py-1 rounded-full font-semibold">
                                        <?php echo htmlspecialchars($video['category']); ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Video Info -->
                            <div class="p-6">
                                <h3 class="font-bold text-slate-900 mb-2 text-lg line-clamp-2 font-serif leading-tight">
                                    <?php echo htmlspecialchars($video['title']); ?>
                                </h3>
                                <p class="text-slate-600 text-sm mb-4 line-clamp-3 leading-relaxed">
                                    <?php echo htmlspecialchars($video['description'] ?: 'A significant video from our liberation war archives...'); ?>
                                </p>
                                
                                <!-- Video Meta -->
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <span class="flex items-center">
                                        <i data-lucide="calendar" class="w-3 h-3 mr-1"></i>
                                        <?php echo date('M j, Y', strtotime($video['created_at'])); ?>
                                    </span>
                                    <span class="flex items-center">
                                        <i data-lucide="video" class="w-3 h-3 mr-1"></i>
                                        Video
                                    </span>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="text-center py-16">
                        <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i data-lucide="video-off" class="w-12 h-12 text-slate-400"></i>
                        </div>
                        <h3 class="text-2xl font-semibold text-slate-900 mb-2">No Videos Found</h3>
                        <p class="text-slate-600 mb-6">
                            <?php if ($selectedCategory !== 'All'): ?>
                                No videos available in the "<?php echo htmlspecialchars($selectedCategory); ?>" category.
                            <?php else: ?>
                                The video archive is being prepared. Please check back soon.
                            <?php endif; ?>
                        </p>
                        <?php if ($selectedCategory !== 'All'): ?>
                        <a href="?category=All" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-300 inline-flex items-center">
                            <i data-lucide="video" class="w-4 h-4 mr-2"></i>
                            View All Videos
                        </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <!-- Video Modal -->
    <div id="videoModal" class="fixed inset-0 bg-black/90 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 id="modalTitle" class="text-lg font-semibold text-slate-900"></h3>
                <button onclick="closeVideoModal()" class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center hover:bg-slate-200 transition-colors">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="p-4">
                <video id="modalVideo" class="w-full rounded-lg" controls autoplay>
                    <source src="" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>
    </div>

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

        // Video Modal Functions
        function openVideoModal(videoUrl, title) {
            const modal = document.getElementById('videoModal');
            const modalVideo = document.getElementById('modalVideo');
            const modalTitle = document.getElementById('modalTitle');
            
            modalVideo.src = videoUrl;
            modalTitle.textContent = title;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeVideoModal() {
            const modal = document.getElementById('videoModal');
            const modalVideo = document.getElementById('modalVideo');
            
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modalVideo.pause();
            modalVideo.src = '';
            document.body.style.overflow = 'auto';
        }

        // Add click handlers for video overlays
        document.addEventListener('DOMContentLoaded', function() {
            const videoOverlays = document.querySelectorAll('.video-overlay');
            videoOverlays.forEach((overlay, index) => {
                overlay.addEventListener('click', function() {
                    const videoUrl = this.getAttribute('data-video-url');
                    const title = this.closest('article').querySelector('h3').textContent;
                    openVideoModal(videoUrl, title);
                });
                
                // Add keyboard support
                overlay.setAttribute('tabindex', '0');
                overlay.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });
        });

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeVideoModal();
            }
        });

        // Close modal on outside click
        document.getElementById('videoModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeVideoModal();
            }
        });

        // Error handling for missing images
        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('error', function() {
                if (this.src.includes('default_')) return;
                this.src = '<?php echo $baseURL; ?>images/default_video.png';
                this.classList.add('opacity-60');
            });
        });
    </script>
</body>
</html>
