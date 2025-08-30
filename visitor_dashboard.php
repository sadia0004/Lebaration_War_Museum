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

// Fetch featured artifacts
$featuredArtifacts = [];
$artifactsQuery = "SELECT a.artifact_id, a.title, a.description, a.object_type, a.significance_comment, a.contributor_name, a.collection_date, a.found_place,
                          COALESCE(a.artifact_image_url, 'images/default_artifact.png') as file_url
                   FROM artifacts a
                   WHERE a.is_featured = 1
                   ORDER BY a.created_at DESC
                   LIMIT 8";
$artifactsResult = $conn->query($artifactsQuery);
if ($artifactsResult && $artifactsResult->num_rows > 0) {
    while ($row = $artifactsResult->fetch_assoc()) {
        $featuredArtifacts[] = $row;
    }
}

// Fetch Featured Videos with category (no duration_seconds)
$featuredVideos = [];
$videosQuery = "SELECT media_id, title, description, file_url, thumbnail_url, category FROM media WHERE media_type = 'video' ORDER BY created_at DESC LIMIT 6";
$videosResult = $conn->query($videosQuery);
if ($videosResult && $videosResult->num_rows > 0) {
    while ($row = $videosResult->fetch_assoc()) {
        $featuredVideos[] = $row;
    }
}

// Fetch Featured Audio Clips with category (no duration_seconds)
$featuredAudios = [];
$audiosQuery = "SELECT media_id, title, description, file_url, category FROM media WHERE media_type = 'audio' ORDER BY created_at DESC LIMIT 6";
$audiosResult = $conn->query($audiosQuery);
if ($audiosResult && $audiosResult->num_rows > 0) {
    while ($row = $audiosResult->fetch_assoc()) {
        $featuredAudios[] = $row;
    }
}

// Get museum statistics
$totalArtifacts = 0;
$totalVideos = 0;
$totalAudios = 0;
$todayVisitors = 0;

$statsQuery = "SELECT 
    (SELECT COUNT(*) FROM artifacts) as total_artifacts,
    (SELECT COUNT(*) FROM media WHERE media_type = 'video') as total_videos,
    (SELECT COUNT(*) FROM media WHERE media_type = 'audio') as total_audios";
$statsResult = $conn->query($statsQuery);
if ($statsResult) {
    $stats = $statsResult->fetch_assoc();
    $totalArtifacts = $stats['total_artifacts'] ?? 0;
    $totalVideos = $stats['total_videos'] ?? 0;
    $totalAudios = $stats['total_audios'] ?? 0;
}

// For today's visitors, we'll simulate since we don't have session tracking in current schema
$todayVisitors = rand(50, 200);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Liberation War Museum - Preserving History, Inspiring Future</title>
    <meta name="description" content="Explore the digital archives of Bangladesh Liberation War. Discover artifacts, stories, and documents from 1971.">
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
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'counter': 'counter 2s ease-out'
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' }
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
            
            <a href="#" class="bg-gradient-to-r from-red-700 to-green-800 text-white flex items-center px-4 py-3 text-sm font-semibold rounded-xl shadow-lg">
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
                        স্বাগতম, <?php echo htmlspecialchars(explode(' ', $fullName)[0]); ?>!
                    </h2>
                    <div class="hidden md:flex items-center space-x-6 text-sm text-slate-600">
                        <span class="flex items-center" title="Visitors today">
                            <i data-lucide="users" class="w-4 h-4 mr-1"></i>
                            <?php echo number_format($todayVisitors); ?> today
                        </span>
                        <span class="flex items-center" title="Total artifacts">
                            <i data-lucide="archive" class="w-4 h-4 mr-1"></i>
                            <?php echo number_format($totalArtifacts); ?> artifacts
                        </span>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Enhanced Search -->
                    <form class="relative hidden md:block" action="search_results.php" method="GET">
                        <input type="text" 
                               name="q"
                               placeholder="Search archives..." 
                               class="w-64 pl-10 pr-4 py-2 rounded-full border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 bg-white/80 backdrop-blur transition-all duration-300">
                        <i data-lucide="search" class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
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
        <section class="min-h-96 bg-gradient-to-r from-red-700 via-green-700 to-slate-800 flex flex-col justify-center items-center text-center text-white p-8 relative overflow-hidden" style="background-image: linear-gradient(135deg, rgba(220, 20, 60, 0.85) 0%, rgba(0, 106, 78, 0.85) 50%, rgba(45, 55, 72, 0.9) 100%), url('https://upload.wikimedia.org/wikipedia/commons/thumb/3/32/Martyred_Intellectuals_Memorial.jpg/1200px-Martyred_Intellectuals_Memorial.jpg'); background-size: cover; background-position: center; background-attachment: fixed;">
            <div class="absolute inset-0 bg-gradient-to-b from-black/20 to-black/40"></div>
            <div class="relative z-10 max-w-4xl mx-auto" data-aos="fade-up">
                <h1 class="text-4xl md:text-6xl font-bold font-serif mb-6 animate-pulse">
                    Digital Gateway to <span class="text-yellow-400">Liberation</span>
                </h1>
                <p class="text-xl md:text-2xl mb-8 opacity-95 font-light leading-relaxed">
                    Preserving the memories of 1971 • Honoring the brave souls • Inspiring future generations
                </p>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-10" role="region" aria-label="Museum statistics">
                    <div class="bg-white/90 backdrop-blur-lg border border-white/30 rounded-2xl p-4 text-center hover:scale-105 transition-transform duration-300 animate-float" data-aos="fade-up" data-aos-delay="100">
                        <div class="text-2xl font-bold text-slate-900 counter-number"><?php echo number_format($totalArtifacts); ?></div>
                        <div class="text-sm text-slate-600 font-medium">Artifacts</div>
                    </div>
                    <div class="bg-white/90 backdrop-blur-lg border border-white/30 rounded-2xl p-4 text-center hover:scale-105 transition-transform duration-300 animate-float" style="animation-delay: 1s;" data-aos="fade-up" data-aos-delay="200">
                        <div class="text-2xl font-bold text-slate-900 counter-number"><?php echo number_format($totalVideos); ?></div>
                        <div class="text-sm text-slate-600 font-medium">Videos</div>
                    </div>
                    <div class="bg-white/90 backdrop-blur-lg border border-white/30 rounded-2xl p-4 text-center hover:scale-105 transition-transform duration-300 animate-float" style="animation-delay: 2s;" data-aos="fade-up" data-aos-delay="300">
                        <div class="text-2xl font-bold text-slate-900 counter-number"><?php echo number_format($totalAudios); ?></div>
                        <div class="text-sm text-slate-600 font-medium">Audio Stories</div>
                    </div>
                    <div class="bg-white/90 backdrop-blur-lg border border-white/30 rounded-2xl p-4 text-center hover:scale-105 transition-transform duration-300 animate-float" style="animation-delay: 3s;" data-aos="fade-up" data-aos-delay="400">
                        <div class="text-2xl font-bold text-slate-900 counter-number"><?php echo number_format($todayVisitors); ?></div>
                        <div class="text-sm text-slate-600 font-medium">Today's Visitors</div>
                    </div>
                </div>
            </div>
        </section>

        <div class="p-6 lg:p-10 space-y-12">
            <!-- Featured Artifacts -->
            <section class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20" data-aos="fade-up">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-3xl font-bold text-slate-900 font-serif mb-2">Featured Artifacts</h2>
                        <p class="text-slate-600 text-lg">Discover precious relics that tell the story of our liberation</p>
                    </div>
                    <a href="explore_museum.php" class="px-6 py-3 bg-gradient-to-r from-red-600 to-green-600 text-white rounded-full hover:shadow-lg transition-all duration-300 font-semibold hover:scale-105">
                        View All <i data-lucide="arrow-right" class="w-4 h-4 ml-2 inline"></i>
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                    <?php if (!empty($featuredArtifacts)): ?>
                        <?php foreach ($featuredArtifacts as $index => $artifact): ?>
                        <article class="bg-white border border-slate-200 transition-all duration-500 hover:-translate-y-2 hover:scale-[1.02] hover:shadow-2xl hover:border-red-500 rounded-2xl overflow-hidden shadow-lg group" data-aos="zoom-in" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="relative overflow-hidden">
                                <img src="<?php echo $baseURL . htmlspecialchars($artifact['file_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($artifact['title']); ?>"
                                     loading="lazy"
                                     class="h-52 w-full object-cover transition-transform duration-700 group-hover:scale-110"
                                     onerror="this.src='<?php echo $baseURL; ?>images/default_artifact.png'">
                                <div class="absolute top-4 left-4">
                                    <span class="bg-gradient-to-r from-red-600 to-green-600 text-white px-3 py-1 text-xs rounded-full font-semibold">
                                        <?php echo htmlspecialchars($artifact['object_type'] ?: 'Artifact'); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="p-6">
                                <h3 class="font-bold text-slate-900 mb-2 text-lg line-clamp-2 font-serif">
                                    <?php echo htmlspecialchars($artifact['title']); ?>
                                </h3>
                                <p class="text-slate-600 text-sm mb-3 line-clamp-2 leading-relaxed">
                                    <?php echo htmlspecialchars($artifact['description'] ?: $artifact['significance_comment'] ?: 'A significant piece from our liberation history...'); ?>
                                </p>
                                <?php if ($artifact['collection_date']): ?>
                                <div class="flex items-center text-xs text-green-600 font-semibold">
                                    <i data-lucide="calendar" class="w-3 h-3 mr-1"></i>
                                    <?php echo date('F j, Y', strtotime($artifact['collection_date'])); ?>
                                </div>
                                <?php endif; ?>
                                <?php if ($artifact['contributor_name']): ?>
                                <div class="mt-1 text-xs text-slate-500 truncate">
                                    Contributed by: <?php echo htmlspecialchars($artifact['contributor_name']); ?>
                                </div>
                                <?php endif; ?>
                                
                                <!-- View Details Button -->
                                <div class="mt-4">
                                    <a href="artifact_detail.php?id=<?php echo (int)$artifact['artifact_id']; ?>" 
                                       class="w-full bg-gradient-to-r from-red-600 to-green-600 text-white text-center px-4 py-2 rounded-lg text-sm font-semibold hover:shadow-lg transition-all duration-300 hover:scale-105 inline-block">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-full text-center py-12">
                            <i data-lucide="archive" class="w-16 h-16 text-slate-300 mx-auto mb-4"></i>
                            <p class="text-slate-500 text-lg">No artifacts available at the moment</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Videos & Audio Section -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Video Archives -->
                <section class="xl:col-span-2 bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20" data-aos="fade-right">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900 font-serif mb-1">Video Archives</h2>
                            <p class="text-slate-600">Documentary footage and testimonies</p>
                        </div>
                        <a href="video_gallery.php" class="text-red-600 hover:text-green-600 font-semibold text-sm flex items-center transition-colors duration-300">
                            See all <i data-lucide="external-link" class="w-4 h-4 ml-1"></i>
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php if (!empty($featuredVideos)): ?>
                            <?php foreach (array_slice($featuredVideos, 0, 4) as $index => $video): ?>
                            <article class="group relative overflow-hidden rounded-xl bg-white shadow-lg hover:shadow-xl transition-all duration-300" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                                <div class="relative h-40 overflow-hidden">
                                    <img src="<?php echo $baseURL . htmlspecialchars($video['thumbnail_url'] ?: 'images/default_video.png'); ?>" 
                                         alt="<?php echo htmlspecialchars($video['title']); ?> thumbnail"
                                         loading="lazy"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                                    <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                        <div class="w-14 h-14 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                            <i data-lucide="play" class="w-6 h-6 text-white ml-1"></i>
                                        </div>
                                    </div>
                                    
                                    <!-- Category Badge -->
                                    <?php if ($video['category']): ?>
                                    <div class="absolute top-2 right-2 bg-red-600/80 text-white text-xs px-2 py-1 rounded-full">
                                        <?php echo htmlspecialchars($video['category']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="p-4">
                                    <h3 class="font-bold text-slate-900 mb-2 line-clamp-2 leading-tight">
                                        <?php echo htmlspecialchars($video['title']); ?>
                                    </h3>
                                    <p class="text-slate-600 text-sm line-clamp-2">
                                        <?php echo htmlspecialchars($video['description'] ?: 'A documentary piece from our liberation struggle...'); ?>
                                    </p>
                                </div>
                            </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-span-full text-center py-8">
                                <i data-lucide="video-off" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
                                <p class="text-slate-500">No videos available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
                
                <!-- Audio Archives -->
                <section class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20" data-aos="fade-left">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900 font-serif mb-1">Audio Stories</h2>
                            <p class="text-slate-600">Voices from history</p>
                        </div>
                        <a href="audio_archives.php" class="text-red-600 hover:text-green-600 font-semibold text-sm flex items-center transition-colors duration-300">
                            See all <i data-lucide="external-link" class="w-4 h-4 ml-1"></i>
                        </a>
                    </div>
                    
                    <div class="space-y-4">
                        <?php if (!empty($featuredAudios)): ?>
                            <?php foreach (array_slice($featuredAudios, 0, 5) as $index => $audio): ?>
                            <article class="group flex items-center gap-4 p-4 rounded-xl bg-gradient-to-r from-white to-slate-50 border border-slate-200/50 hover:shadow-md hover:border-red-500/20 transition-all duration-300 cursor-pointer" data-aos="slide-left" data-aos-delay="<?php echo $index * 100; ?>">
                                <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-red-500/10 to-green-500/10 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                    <i data-lucide="headphones" class="w-5 h-5 text-red-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-slate-900 text-sm line-clamp-1 mb-1">
                                        <?php echo htmlspecialchars($audio['title']); ?>
                                    </h3>
                                    <p class="text-xs text-slate-500 line-clamp-1">
                                        <?php echo htmlspecialchars($audio['description'] ?: 'Historical audio recording...'); ?>
                                    </p>
                                    <?php if ($audio['category']): ?>
                                    <p class="text-xs text-green-600 font-medium mt-1">
                                        <?php echo htmlspecialchars($audio['category']); ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-shrink-0">
                                    <i data-lucide="play-circle" class="w-5 h-5 text-slate-400 group-hover:text-red-600 transition-colors"></i>
                                </div>
                            </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i data-lucide="mic-off" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
                                <p class="text-slate-500">No audio content available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <!-- Call to Action -->
            <section class="bg-gradient-to-r from-red-600 via-green-600 to-slate-800 rounded-3xl p-8 lg:p-12 text-white text-center shadow-xl" data-aos="zoom-in">
                <div class="max-w-3xl mx-auto">
                    <h2 class="text-3xl lg:text-4xl font-bold font-serif mb-4">
                        Start Your Journey Through History
                    </h2>
                    <p class="text-xl opacity-90 mb-8 leading-relaxed">
                        Explore our comprehensive digital archives and immerse yourself in the heroic struggle for Bangladesh's independence
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="virtual_tour.php" class="px-8 py-4 bg-white text-slate-900 rounded-full font-bold hover:shadow-lg hover:scale-105 transition-all duration-300 flex items-center justify-center">
                            <i data-lucide="map-pin" class="w-5 h-5 mr-2"></i>
                            Take Virtual Tour
                        </a>
                        <a href="explore_museum.php" class="px-8 py-4 border-2 border-white text-white rounded-full font-bold hover:bg-white hover:text-slate-900 transition-all duration-300 flex items-center justify-center">
                            <i data-lucide="compass" class="w-5 h-5 mr-2"></i>
                            Explore Collections
                        </a>
                    </div>
                </div>
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
                this.classList.add('ring-2', 'ring-red-500/20');
            });
            
            searchInput.addEventListener('blur', function() {
                this.classList.remove('ring-2', 'ring-red-500/20');
            });
        }

        // Add smooth scroll behavior
        document.documentElement.style.scrollBehavior = 'smooth';

        // Counter animation for stats
        function animateCounters() {
            const counters = document.querySelectorAll('.counter-number');
            const animationDuration = 2000;
            
            counters.forEach(counter => {
                const target = parseInt(counter.textContent.replace(/,/g, ''));
                const startTime = Date.now();
                
                const animateCount = () => {
                    const elapsed = Date.now() - startTime;
                    const progress = Math.min(elapsed / animationDuration, 1);
                    
                    const easeOutQuart = 1 - Math.pow(1 - progress, 4);
                    const current = Math.floor(target * easeOutQuart);
                    
                    counter.textContent = current.toLocaleString();
                    
                    if (progress < 1) {
                        requestAnimationFrame(animateCount);
                    } else {
                        counter.textContent = target.toLocaleString();
                    }
                };
                
                requestAnimationFrame(animateCount);
            });
        }

        // Trigger counter animation when hero section comes into view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    setTimeout(animateCounters, 500);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        const heroSection = document.querySelector('section');
        if (heroSection) {
            observer.observe(heroSection);
        }

        // Error handling for missing images
        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('error', function() {
                if (this.src.includes('default_')) return;
                
                if (this.alt.includes('artifact') || this.closest('.group')) {
                    this.src = '<?php echo $baseURL; ?>images/default_artifact.png';
                } else if (this.alt.includes('video') || this.alt.includes('thumbnail')) {
                    this.src = '<?php echo $baseURL; ?>images/default_video.png';
                } else {
                    this.src = '<?php echo $baseURL; ?>images/logo.png';
                }
                
                this.classList.add('opacity-60');
            });
        });

        // Loading animation
        window.addEventListener('load', function() {
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
