<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Protect the page: check if the user is logged in and is a manager.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit();
}

// Configuration
$baseURL = "http://localhost/MUSEUM/";

// Retrieve user info from the session for personalization.
$fullName = $_SESSION['full_name'] ?? 'Manager';
$profilePhotoUrl = $_SESSION['profile_photo_url'] ?? null;
$userId = $_SESSION['user_id'];

// User initials for avatar
$userInitials = '';
if (!empty($fullName)) {
    $parts = explode(' ', $fullName);
    $userInitials = strtoupper(substr($parts[0], 0, 1) . (count($parts) > 1 ? substr(end($parts), 0, 1) : ''));
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

// Placeholder for pending reviews, you can replace this with a real query
$pendingReviews = rand(3, 15);

// Fetch recent artifacts
$recentArtifacts = [];
$recentArtifactsQuery = "SELECT artifact_id, title, contributor_name, object_type, created_at FROM artifacts ORDER BY created_at DESC LIMIT 5";
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
    <title>Manager Dashboard - Digital Liberation War Museum</title>
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
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-8px)' }
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 font-sans">
    
    <aside class="w-72 bg-white/95 backdrop-blur-lg border-r border-slate-200/50 flex-col h-screen fixed hidden lg:flex shadow-xl">
        <div class="h-20 flex items-center justify-center px-6 border-b border-slate-200/50 bg-gradient-to-r from-red-700 to-green-800">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <img src="images/logo.png" alt="Liberation War Museum Logo" class="h-12 w-12 object-cover rounded-full border-2 border-white shadow-lg">
                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-yellow-500 rounded-full border-2 border-white"></div>
                </div>
                <div class="text-left text-white">
                    <h1 class="text-lg font-bold font-serif">Manager Portal</h1>
                    <p class="text-xs opacity-90 font-medium">Liberation War 1971</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2">
            <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold mb-4 px-2">Management</div>
            
            <a href="manager_dashboard.php" class="bg-gradient-to-r from-red-700 to-green-800 text-white flex items-center px-4 py-3 text-sm font-semibold rounded-xl shadow-lg">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i> Dashboard
            </a>
            <a href="artifact_management.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
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

            <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold mb-4 px-2 pt-6">Analytics & System</div>
             <a href="visitor_analytics.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="users" class="w-5 h-5 mr-3"></i> Visitor Analytics
            </a>
            <a href="content_report.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
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

    <main class="flex-1 lg:ml-72">
        <header class="bg-white/90 backdrop-blur-lg border-b border-slate-200/50 sticky top-0 z-40 shadow-sm">
            <div class="flex items-center justify-between px-6 lg:px-8 py-4">
                <div class="flex items-center space-x-4">
                    <h2 class="text-2xl font-bold text-slate-900 font-serif">
                        Welcome, <?php echo htmlspecialchars(explode(' ', $fullName)[0]); ?>!
                    </h2>
                </div>
                
                <div class="flex items-center space-x-4">
                    <form class="relative hidden md:block" action="#" method="GET">
                        <input type="text" 
                               name="q"
                               placeholder="Search artifacts, collections..." 
                               class="w-64 pl-10 pr-4 py-2 rounded-full border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 bg-white/80 backdrop-blur transition-all duration-300">
                        <i data-lucide="search" class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    </form>
                    
                    <div class="flex items-center gap-3">
                         <?php if ($profilePhotoUrl && file_exists(ltrim($profilePhotoUrl, $baseURL))): ?>
                            <img src="<?php echo htmlspecialchars($profilePhotoUrl); ?>" 
                                 alt="Profile photo" 
                                 class="w-11 h-11 rounded-full object-cover border-3 border-liberation-gold shadow-lg">
                        <?php else: ?>
                            <div class="w-11 h-11 bg-gradient-to-br from-red-600 to-green-600 rounded-full flex items-center justify-center shadow-lg">
                                <span class="text-sm font-bold text-white"><?php echo htmlspecialchars($userInitials); ?></span>
                            </div>
                        <?php endif; ?>
                         <div class="text-sm text-right hidden lg:block">
                            <p class="font-semibold text-slate-800"><?php echo htmlspecialchars($fullName); ?></p>
                            <p class="text-xs text-slate-500">Museum Manager</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="p-6 lg:p-10 space-y-10">
            <section>
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-3xl font-bold text-slate-900 font-serif">Dashboard Overview</h2>
                        <p class="text-slate-600">Here's a summary of the museum's digital presence.</p>
                    </div>
                     <div class="text-right">
                        <p id="time" class="text-2xl font-bold text-liberation-green"></p>
                        <p id="date" class="text-sm text-slate-500"></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-lg border border-white/20 hover:-translate-y-1.5 transition-transform duration-300" data-aos="fade-up" data-aos-delay="100">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-slate-600 font-medium">Total Artifacts</span>
                            <div class="w-10 h-10 bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center rounded-full">
                               <i data-lucide="archive" class="w-5 h-5 text-liberation-green"></i>
                            </div>
                        </div>
                        <p class="text-4xl font-bold text-slate-900 counter-number"><?php echo number_format($totalArtifacts); ?></p>
                    </div>
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-lg border border-white/20 hover:-translate-y-1.5 transition-transform duration-300" data-aos="fade-up" data-aos-delay="200">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-slate-600 font-medium">Digital Exhibitions</span>
                            <div class="w-10 h-10 bg-gradient-to-br from-red-100 to-red-200 flex items-center justify-center rounded-full">
                               <i data-lucide="star" class="w-5 h-5 text-liberation-red"></i>
                            </div>
                        </div>
                        <p class="text-4xl font-bold text-slate-900 counter-number"><?php echo number_format($digitalExhibitions); ?></p>
                    </div>
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-lg border border-white/20 hover:-translate-y-1.5 transition-transform duration-300" data-aos="fade-up" data-aos-delay="300">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-slate-600 font-medium">Monthly Visitors</span>
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center rounded-full">
                               <i data-lucide="users" class="w-5 h-5 text-blue-600"></i>
                            </div>
                        </div>
                        <p class="text-4xl font-bold text-slate-900 counter-number"><?php echo number_format($monthlyVisitors); ?></p>
                    </div>
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-lg border border-white/20 hover:-translate-y-1.5 transition-transform duration-300" data-aos="fade-up" data-aos-delay="400">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-slate-600 font-medium">Content Reviews</span>
                            <div class="w-10 h-10 bg-gradient-to-br from-yellow-100 to-yellow-200 flex items-center justify-center rounded-full">
                               <i data-lucide="clipboard-check" class="w-5 h-5 text-amber-600"></i>
                            </div>
                        </div>
                        <p class="text-4xl font-bold text-slate-900 counter-number"><?php echo number_format($pendingReviews); ?></p>
                    </div>
                </div>
            </section>

            <section class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20" data-aos="fade-up">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                    <div>
                        <h3 class="text-3xl font-bold text-slate-900 font-serif">Recently Added Artifacts</h3>
                        <p class="text-slate-600">Review the latest additions to the museum collection.</p>
                    </div>
                    <a href="artifact_management.php#add-new" class="px-6 py-3 bg-gradient-to-r from-red-600 to-green-600 text-white rounded-full hover:shadow-lg transition-all duration-300 font-semibold hover:scale-105 flex items-center flex-shrink-0">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add New Artifact
                    </a>
                </div>
                
                <div class="space-y-3">
                    <?php if (!empty($recentArtifacts)): ?>
                        <?php foreach ($recentArtifacts as $artifact): ?>
                            <article class="group flex items-center gap-4 p-4 rounded-xl bg-gradient-to-r from-white to-slate-50 border border-slate-200/50 hover:shadow-md hover:border-red-500/20 transition-all duration-300">
                                <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-red-500/10 to-green-500/10 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                    <i data-lucide="box" class="w-5 h-5 text-liberation-green"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-slate-900 text-sm line-clamp-1 mb-1">
                                        <?php echo htmlspecialchars($artifact['title']); ?>
                                    </p>
                                    <p class="text-xs text-slate-500 line-clamp-1">
                                        Contributed by <?php echo htmlspecialchars($artifact['contributor_name'] ?? 'N/A'); ?>
                                    </p>
                                </div>
                                <div class="hidden md:block">
                                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-1 rounded-full"><?php echo htmlspecialchars($artifact['object_type'] ?: 'General'); ?></span>
                                </div>
                                <div class="hidden lg:block text-sm text-slate-500">
                                    <?php echo isset($artifact['created_at']) ? date('M d, Y', strtotime($artifact['created_at'])) : 'N/A'; ?>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="artifact_management.php?action=edit&id=<?php echo $artifact['artifact_id'] ?? ''; ?>" class="p-2 text-slate-500 hover:text-liberation-green transition-colors"><i data-lucide="edit-3" class="w-4 h-4"></i></a>
                                    <button class="p-2 text-slate-500 hover:text-liberation-red transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <i data-lucide="archive-x" class="w-16 h-16 text-slate-300 mx-auto mb-4"></i>
                            <p class="text-slate-500 text-lg">No recently added artifacts.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

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

        // Date and Time updater
        function updateTime() {
            const timeEl = document.getElementById('time');
            const dateEl = document.getElementById('date');
            if (timeEl && dateEl) {
                const now = new Date();
                timeEl.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
                dateEl.textContent = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
            }
        }
        updateTime();
        setInterval(updateTime, 1000);

        // Counter animation for stats
        function animateCounters() {
            const counters = document.querySelectorAll('.counter-number');
            const animationDuration = 2000;
            
            counters.forEach(counter => {
                const target = parseInt(counter.textContent.replace(/,/g, ''));
                if (isNaN(target)) return;
                
                let startTime = null;

                const animate = (currentTime) => {
                    if (!startTime) startTime = currentTime;
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / animationDuration, 1);
                    const easeOutQuad = 1 - Math.pow(1 - progress, 4);
                    const current = Math.floor(target * easeOutQuad);
                    
                    counter.textContent = current.toLocaleString();
                    
                    if (progress < 1) {
                        requestAnimationFrame(animate);
                    } else {
                        counter.textContent = target.toLocaleString();
                    }
                };
                requestAnimationFrame(animate);
            });
        }

        // Trigger counter animation when the element is in view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        const statsSection = document.querySelector('.grid-cols-1');
        if (statsSection) {
            observer.observe(statsSection);
        }
    </script>
</body>
</html>