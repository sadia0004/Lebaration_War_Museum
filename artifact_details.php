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

// Get artifact ID from URL
$artifactId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($artifactId <= 0) {
    die("Invalid artifact ID");
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

// Fetch artifact details with media
$artifactQuery = "SELECT a.*, 
                         COALESCE(a.artifact_image_url, 'images/default_artifact.png') as image_url,
                         u.full_name as added_by_name
                  FROM artifacts a
                  LEFT JOIN users u ON a.added_by_user_id = u.user_id
                  WHERE a.artifact_id = ? LIMIT 1";

$artifact = null;
if ($stmt = $conn->prepare($artifactQuery)) {
    $stmt->bind_param("i", $artifactId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $artifact = $result->fetch_assoc();
    }
    $stmt->close();
}

// If artifact not found
if (!$artifact) {
    $conn->close();
    die("Artifact not found");
}

// Fetch related media files
$mediaFiles = [];
$mediaQuery = "SELECT m.* FROM media m
               INNER JOIN artifact_media am ON m.media_id = am.media_id
               WHERE am.artifact_id = ?
               ORDER BY am.is_primary_display DESC, m.created_at DESC";

if ($stmt = $conn->prepare($mediaQuery)) {
    $stmt->bind_param("i", $artifactId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $mediaFiles[] = $row;
    }
    $stmt->close();
}

// Log the view in user_view_history
$historyQuery = "INSERT INTO user_view_history (user_id, artifact_id) VALUES (?, ?)";
if ($stmt = $conn->prepare($historyQuery)) {
    $stmt->bind_param("ii", $userId, $artifactId);
    $stmt->execute();
    $stmt->close();
}

// Get related artifacts (same type or period)
$relatedArtifacts = [];
$relatedQuery = "SELECT artifact_id, title, object_type, 
                        COALESCE(artifact_image_url, 'images/default_artifact.png') as image_url
                 FROM artifacts 
                 WHERE (object_type = ? OR period = ?) AND artifact_id != ?
                 ORDER BY created_at DESC 
                 LIMIT 6";

if ($stmt = $conn->prepare($relatedQuery)) {
    $stmt->bind_param("ssi", $artifact['object_type'], $artifact['period'], $artifactId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $relatedArtifacts[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($artifact['title']); ?> - Digital Liberation War Museum</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr($artifact['description'] ?: $artifact['significance_comment'] ?: 'A significant artifact from the Bangladesh Liberation War of 1971.', 0, 160)); ?>">
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
            <a href="explore_museum.php" class="bg-gradient-to-r from-red-700 to-green-800 text-white flex items-center px-4 py-3 text-sm font-semibold rounded-xl shadow-lg">
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
                    <!-- Breadcrumb -->
                    <nav class="flex items-center space-x-2 text-sm text-slate-600">
                        <a href="visitor_dashboard.php" class="hover:text-red-600 transition-colors">Museum</a>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        <a href="explore_museum.php" class="hover:text-red-600 transition-colors">Artifacts</a>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        <span class="text-slate-900 font-medium">Details</span>
                    </nav>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Back Button -->
                    <a href="explore_museum.php" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-all duration-300 flex items-center">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                        Back to Gallery
                    </a>
                    
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
            <!-- Artifact Detail Header -->
            <section class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20 mb-8" data-aos="fade-up">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Image Section -->
                    <div class="space-y-4">
                        <div class="relative overflow-hidden rounded-2xl shadow-lg group">
                            <img src="<?php echo $baseURL . htmlspecialchars($artifact['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($artifact['title']); ?>"
                                 class="w-full h-96 object-cover transition-transform duration-700 group-hover:scale-105"
                                 onerror="this.src='<?php echo $baseURL; ?>images/default_artifact.png'">
                            
                            <!-- Status Badge -->
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
                                <span class="<?php echo $statusClass; ?> px-3 py-1 text-sm font-semibold rounded-full">
                                    <?php echo htmlspecialchars($artifact['status']); ?>
                                </span>
                            </div>
                            
                            <!-- Object Type Badge -->
                            <div class="absolute bottom-4 left-4">
                                <span class="bg-gradient-to-r from-red-600 to-green-600 text-white px-4 py-2 text-sm font-semibold rounded-full">
                                    <?php echo htmlspecialchars($artifact['object_type'] ?: 'Artifact'); ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Additional Media -->
                        <?php if (!empty($mediaFiles)): ?>
                        <div class="grid grid-cols-3 gap-2">
                            <?php foreach (array_slice($mediaFiles, 1, 3) as $media): ?>
                            <div class="relative overflow-hidden rounded-lg h-24 cursor-pointer hover:opacity-75 transition-opacity">
                                <?php if ($media['media_type'] == 'image'): ?>
                                    <img src="<?php echo htmlspecialchars($media['file_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($media['title']); ?>"
                                         class="w-full h-full object-cover">
                                <?php elseif ($media['media_type'] == 'video'): ?>
                                    <div class="w-full h-full bg-slate-200 flex items-center justify-center">
                                        <i data-lucide="play-circle" class="w-8 h-8 text-slate-600"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="w-full h-full bg-slate-200 flex items-center justify-center">
                                        <i data-lucide="headphones" class="w-8 h-8 text-slate-600"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Details Section -->
                    <div class="space-y-6">
                        <div>
                            <h1 class="text-4xl font-bold text-slate-900 font-serif mb-2">
                                <?php echo htmlspecialchars($artifact['title']); ?>
                            </h1>
                            <p class="text-lg text-slate-600 font-medium">
                                Collection #<?php echo htmlspecialchars($artifact['collection_number']); ?>
                            </p>
                        </div>
                        
                        <!-- Key Information -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php if ($artifact['period']): ?>
                            <div class="flex items-center space-x-3 p-3 bg-slate-50 rounded-lg">
                                <i data-lucide="clock" class="w-5 h-5 text-green-600"></i>
                                <div>
                                    <div class="text-xs font-medium text-slate-500 uppercase tracking-wide">Period</div>
                                    <div class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($artifact['period']); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($artifact['found_place']): ?>
                            <div class="flex items-center space-x-3 p-3 bg-slate-50 rounded-lg">
                                <i data-lucide="map-pin" class="w-5 h-5 text-green-600"></i>
                                <div>
                                    <div class="text-xs font-medium text-slate-500 uppercase tracking-wide">Origin</div>
                                    <div class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($artifact['found_place']); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($artifact['materials']): ?>
                            <div class="flex items-center space-x-3 p-3 bg-slate-50 rounded-lg">
                                <i data-lucide="layers" class="w-5 h-5 text-green-600"></i>
                                <div>
                                    <div class="text-xs font-medium text-slate-500 uppercase tracking-wide">Materials</div>
                                    <div class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($artifact['materials']); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($artifact['measurements']): ?>
                            <div class="flex items-center space-x-3 p-3 bg-slate-50 rounded-lg">
                                <i data-lucide="ruler" class="w-5 h-5 text-green-600"></i>
                                <div>
                                    <div class="text-xs font-medium text-slate-500 uppercase tracking-wide">Dimensions</div>
                                    <div class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($artifact['measurements']); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex gap-3">
                            <button class="px-6 py-3 bg-gradient-to-r from-red-600 to-green-600 text-white rounded-lg font-semibold hover:shadow-lg transition-all duration-300 hover:scale-105 flex items-center">
                                <i data-lucide="heart" class="w-4 h-4 mr-2"></i>
                                Add to Favorites
                            </button>
                            <button class="px-6 py-3 border-2 border-red-600 text-red-600 rounded-lg font-semibold hover:bg-red-600 hover:text-white transition-all duration-300 flex items-center">
                                <i data-lucide="share-2" class="w-4 h-4 mr-2"></i>
                                Share
                            </button>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Detailed Information -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Main Content -->
                <section class="xl:col-span-2 space-y-8">
                    <!-- Description -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20" data-aos="fade-up">
                        <h2 class="text-2xl font-bold text-slate-900 font-serif mb-4">Description</h2>
                        <div class="prose prose-slate max-w-none">
                            <p class="text-slate-700 leading-relaxed text-lg">
                                <?php echo nl2br(htmlspecialchars($artifact['description'] ?: 'No description available for this artifact.')); ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Historical Significance -->
                    <?php if ($artifact['significance_comment']): ?>
                    <div class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20" data-aos="fade-up">
                        <h2 class="text-2xl font-bold text-slate-900 font-serif mb-4">Historical Significance</h2>
                        <div class="prose prose-slate max-w-none">
                            <p class="text-slate-700 leading-relaxed">
                                <?php echo nl2br(htmlspecialchars($artifact['significance_comment'])); ?>
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Preservation Notes -->
                    <?php if ($artifact['preservation_notes']): ?>
                    <div class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20" data-aos="fade-up">
                        <h2 class="text-2xl font-bold text-slate-900 font-serif mb-4">Preservation & Conservation</h2>
                        <div class="prose prose-slate max-w-none">
                            <p class="text-slate-700 leading-relaxed">
                                <?php echo nl2br(htmlspecialchars($artifact['preservation_notes'])); ?>
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                </section>
                
                <!-- Sidebar Information -->
                <aside class="space-y-6">
                    <!-- Artifact Details -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-3xl p-6 shadow-lg border border-white/20" data-aos="fade-left">
                        <h3 class="text-xl font-bold text-slate-900 font-serif mb-4">Artifact Information</h3>
                        <div class="space-y-3">
                            <?php if ($artifact['accession_number']): ?>
                            <div class="flex justify-between">
                                <span class="text-slate-600 font-medium">Accession Number:</span>
                                <span class="text-slate-900"><?php echo htmlspecialchars($artifact['accession_number']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($artifact['gallery_number']): ?>
                            <div class="flex justify-between">
                                <span class="text-slate-600 font-medium">Gallery:</span>
                                <span class="text-slate-900"><?php echo htmlspecialchars($artifact['gallery_number']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($artifact['condition']): ?>
                            <div class="flex justify-between">
                                <span class="text-slate-600 font-medium">Condition:</span>
                                <span class="text-slate-900"><?php echo htmlspecialchars($artifact['condition']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($artifact['contributor_name']): ?>
                            <div class="flex justify-between">
                                <span class="text-slate-600 font-medium">Contributed by:</span>
                                <span class="text-slate-900"><?php echo htmlspecialchars($artifact['contributor_name']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($artifact['collection_date']): ?>
                            <div class="flex justify-between">
                                <span class="text-slate-600 font-medium">Collection Date:</span>
                                <span class="text-slate-900"><?php echo date('F j, Y', strtotime($artifact['collection_date'])); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="flex justify-between">
                                <span class="text-slate-600 font-medium">Added:</span>
                                <span class="text-slate-900"><?php echo date('F j, Y', strtotime($artifact['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Related Artifacts -->
                    <?php if (!empty($relatedArtifacts)): ?>
                    <div class="bg-white/80 backdrop-blur-lg rounded-3xl p-6 shadow-lg border border-white/20" data-aos="fade-left" data-aos-delay="200">
                        <h3 class="text-xl font-bold text-slate-900 font-serif mb-4">Related Artifacts</h3>
                        <div class="space-y-4">
                            <?php foreach ($relatedArtifacts as $related): ?>
                            <a href="artifact_detail.php?id=<?php echo $related['artifact_id']; ?>" 
                               class="group flex items-center space-x-3 p-3 bg-slate-50 rounded-lg hover:bg-red-50 transition-all duration-300">
                                <img src="<?php echo $baseURL . htmlspecialchars($related['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($related['title']); ?>"
                                     class="w-12 h-12 object-cover rounded-lg">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-slate-900 text-sm line-clamp-1 group-hover:text-red-600 transition-colors">
                                        <?php echo htmlspecialchars($related['title']); ?>
                                    </h4>
                                    <p class="text-xs text-slate-500 mt-1">
                                        <?php echo htmlspecialchars($related['object_type']); ?>
                                    </p>
                                </div>
                                <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400 group-hover:text-red-600 transition-colors"></i>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </aside>
            </div>
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

        // Error handling for missing images
        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('error', function() {
                if (this.src.includes('default_')) return;
                this.src = '<?php echo $baseURL; ?>images/default_artifact.png';
                this.classList.add('opacity-60');
            });
        });

        // Share functionality
        document.querySelector('button[data-share]')?.addEventListener('click', function() {
            if (navigator.share) {
                navigator.share({
                    title: '<?php echo addslashes($artifact['title']); ?>',
                    text: '<?php echo addslashes(substr($artifact['description'] ?: $artifact['significance_comment'] ?: '', 0, 100)); ?>',
                    url: window.location.href
                });
            } else {
                // Fallback copy to clipboard
                navigator.clipboard.writeText(window.location.href).then(function() {
                    alert('Link copied to clipboard!');
                });
            }
        });
    </script>
</body>
</html>
