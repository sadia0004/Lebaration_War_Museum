<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Protect the page: ensure only managers or admins can access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['manager', 'admin'])) {
    header("Location: login.php");
    exit();
}

// Configuration
$baseURL = "http://localhost/MUSEUM/";

// --- Database Connection ---
$host = "localhost";
$username = "root";
$password = "";
$database = "museum";
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// --- Fetch Data for Analytics ---

// KPI Stats
$totalVisitorsResult = $conn->query("SELECT COUNT(user_id) as total FROM users WHERE role = 'visitor'");
$totalVisitors = $totalVisitorsResult->fetch_assoc()['total'] ?? 0;

$totalViewsResult = $conn->query("SELECT COUNT(history_id) as total FROM user_view_history");
$totalViews = $totalViewsResult->fetch_assoc()['total'] ?? 0;

$avgViewsPerVisitor = ($totalVisitors > 0) ? round($totalViews / $totalVisitors, 2) : 0;

$mostActiveUserResult = $conn->query("
    SELECT u.full_name, COUNT(h.history_id) as view_count 
    FROM user_view_history h 
    JOIN users u ON h.user_id = u.user_id 
    GROUP BY h.user_id 
    ORDER BY view_count DESC 
    LIMIT 1
");
$mostActiveUser = $mostActiveUserResult->fetch_assoc() ?? ['full_name' => 'N/A', 'view_count' => 0];

// Recent Registrations Table
$recentUsers = [];
$recentUsersResult = $conn->query("SELECT full_name, email, created_at FROM users WHERE role = 'visitor' ORDER BY created_at DESC LIMIT 5");
while($row = $recentUsersResult->fetch_assoc()) {
    $recentUsers[] = $row;
}

// Top Viewed Artifacts Table
$topArtifacts = [];
$topArtifactsResult = $conn->query("
    SELECT a.title, a.object_type, COUNT(h.history_id) as view_count
    FROM user_view_history h
    JOIN artifacts a ON h.artifact_id = a.artifact_id
    GROUP BY h.artifact_id
    ORDER BY view_count DESC
    LIMIT 5
");
while($row = $topArtifactsResult->fetch_assoc()) {
    $topArtifacts[] = $row;
}

// Recent Activity Feed
$recentActivity = [];
$activityResult = $conn->query("
    SELECT u.full_name, a.title as artifact_title, h.viewed_at
    FROM user_view_history h
    JOIN users u ON h.user_id = u.user_id
    JOIN artifacts a ON h.artifact_id = a.artifact_id
    ORDER BY h.viewed_at DESC
    LIMIT 7
");
while($row = $activityResult->fetch_assoc()) {
    $recentActivity[] = $row;
}

// Data for Visitor Sign-ups Chart (Last 7 Days)
$signupData = [];
$signupQuery = "SELECT DATE(created_at) as signup_date, COUNT(user_id) as count 
                FROM users 
                WHERE role = 'visitor' AND created_at >= CURDATE() - INTERVAL 7 DAY 
                GROUP BY DATE(created_at) 
                ORDER BY signup_date ASC";
$signupResult = $conn->query($signupQuery);
$signupLabels = [];
$signupCounts = [];
// Initialize last 7 days with 0 count
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $signupLabels[] = date('M d', strtotime($date));
    $signupData[$date] = 0;
}
while($row = $signupResult->fetch_assoc()) {
    $signupData[$row['signup_date']] = $row['count'];
}
$signupCounts = array_values($signupData);


$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Analytics - Digital Liberation War Museum</title>
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
    
    <!-- Sidebar Navigation -->
    <aside class="w-72 bg-white/95 backdrop-blur-lg border-r border-slate-200/50 flex-col h-screen fixed hidden lg:flex shadow-xl z-50">
        <div class="h-20 flex items-center justify-center px-6 border-b border-slate-200/50 bg-gradient-to-r from-red-700 to-green-800">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <img src="images/logo.png" alt="Museum Logo" class="h-12 w-12 object-cover rounded-full border-2 border-white shadow-lg">
                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-yellow-500 rounded-full border-2 border-white"></div>
                </div>
                <div class="text-left text-white">
                    <h1 class="text-lg font-bold font-serif">Manager Portal</h1>
                    <p class="text-xs opacity-90 font-medium">Liberation War 1971</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="manager_dashboard.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i> Dashboard
            </a>
            <a href="artifact_management.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="archive" class="w-5 h-5 mr-3"></i> Artifacts
            </a>
            <a href="digital_collections.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="gem" class="w-5 h-5 mr-3"></i> Digital Collections
            </a>
            <a href="content_report.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="bar-chart-3" class="w-5 h-5 mr-3"></i> Content Reports
            </a>
             <a href="visitor_analytics.php" class="bg-gradient-to-r from-red-700 to-green-800 text-white flex items-center px-4 py-3 text-sm font-semibold rounded-xl shadow-lg">
                <i data-lucide="users" class="w-5 h-5 mr-3"></i> Visitor Analytics
            </a>
        </nav>

        <div class="mt-auto px-4 py-4 border-t border-slate-200/50">
            <a href="logout.php" class="text-red-600 hover:bg-red-50 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300">
                <i data-lucide="log-out" class="w-5 h-5 mr-3"></i> Sign Out
            </a>
        </div>
    </aside>

    <main id="main-content" class="flex-1 lg:ml-72">
        <header class="bg-white/90 backdrop-blur-lg border-b border-slate-200/50 sticky top-0 z-40 shadow-sm">
            <div class="flex items-center justify-between px-6 lg:px-8 py-4">
                <h2 class="text-2xl font-bold text-slate-900 font-serif">Visitor Analytics</h2>
            </div>
        </header>

        <div class="p-6 lg:p-10 space-y-8">
            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-lg border border-white/20" data-aos="fade-up">
                    <p class="text-sm font-medium text-slate-500">Total Visitors</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2"><?php echo $totalVisitors; ?></p>
                </div>
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-lg border border-white/20" data-aos="fade-up" data-aos-delay="100">
                    <p class="text-sm font-medium text-slate-500">Total Artifact Views</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2"><?php echo $totalViews; ?></p>
                </div>
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-lg border border-white/20" data-aos="fade-up" data-aos-delay="200">
                    <p class="text-sm font-medium text-slate-500">Avg. Views per Visitor</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2"><?php echo $avgViewsPerVisitor; ?></p>
                </div>
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-lg border border-white/20" data-aos="fade-up" data-aos-delay="300">
                    <p class="text-sm font-medium text-slate-500">Most Active Visitor</p>
                    <p class="text-lg font-bold text-liberation-green mt-2 truncate" title="<?php echo htmlspecialchars($mostActiveUser['full_name']); ?>"><?php echo htmlspecialchars($mostActiveUser['full_name']); ?></p>
                </div>
            </div>

            <!-- Charts & Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white/80 backdrop-blur-lg rounded-3xl p-6 shadow-lg border border-white/20" data-aos="fade-right">
                    <h3 class="text-xl font-bold text-slate-800 font-serif mb-6">Visitor Sign-ups (Last 7 Days)</h3>
                    <div><canvas id="visitorSignupChart"></canvas></div>
                </div>
                <div class="lg:col-span-1 bg-white/80 backdrop-blur-lg rounded-3xl p-6 shadow-lg border border-white/20" data-aos="fade-left">
                    <h3 class="text-xl font-bold text-slate-800 font-serif mb-6">Recent Activity Feed</h3>
                    <div class="space-y-4">
                        <?php if(empty($recentActivity)): ?>
                            <p class="text-slate-500 text-center py-8">No recent visitor activity.</p>
                        <?php else: ?>
                            <?php foreach($recentActivity as $activity): ?>
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-slate-100 rounded-full flex-shrink-0 flex items-center justify-center"><i data-lucide="eye" class="w-4 h-4 text-slate-500"></i></div>
                                <div>
                                    <p class="text-sm font-medium text-slate-800">
                                        <span class="font-bold"><?php echo htmlspecialchars($activity['full_name']); ?></span> viewed <span class="text-liberation-green"><?php echo htmlspecialchars($activity['artifact_title']); ?></span>
                                    </p>
                                    <p class="text-xs text-slate-500"><?php echo date('M d, g:i a', strtotime($activity['viewed_at'])); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Top Artifacts & Recent Registrations -->
            <div class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20" data-aos="fade-up">
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-x-12 gap-y-8">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800 font-serif mb-4">Top 5 Most Viewed Artifacts</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                 <thead class="text-left text-slate-500"><tr><th class="py-2 font-semibold">Artifact Title</th><th class="py-2 font-semibold text-right">Views</th></tr></thead>
                                 <tbody class="divide-y divide-slate-200">
                                    <?php if(empty($topArtifacts)): ?>
                                        <tr><td colspan="2" class="py-4 text-center text-slate-500">No artifacts have been viewed yet.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($topArtifacts as $item): ?>
                                        <tr class="hover:bg-slate-50">
                                            <td class="py-3 font-medium text-slate-800"><?php echo htmlspecialchars($item['title']); ?></td>
                                            <td class="py-3 text-slate-600 font-bold text-right"><?php echo $item['view_count']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                 </tbody>
                            </table>
                        </div>
                    </div>
                     <div>
                        <h3 class="text-xl font-bold text-slate-800 font-serif mb-4">Recent Visitor Registrations</h3>
                        <div class="overflow-x-auto">
                           <table class="w-full text-sm">
                                <thead class="text-left text-slate-500"><tr><th class="py-2 font-semibold">Visitor Name</th><th class="py-2 font-semibold">Registration Date</th></tr></thead>
                                 <tbody class="divide-y divide-slate-200">
                                    <?php if(empty($recentUsers)): ?>
                                        <tr><td colspan="2" class="py-4 text-center text-slate-500">No new visitors have registered.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($recentUsers as $user): ?>
                                        <tr class="hover:bg-slate-50">
                                            <td class="py-3 font-medium text-slate-800"><?php echo htmlspecialchars($user['full_name']); ?></td>
                                            <td class="py-3 text-slate-600"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                 </tbody>
                           </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        AOS.init({ duration: 800, easing: 'ease-in-out', once: true, offset: 50 });
        lucide.createIcons();

        document.addEventListener('DOMContentLoaded', function() {
            Chart.defaults.font.family = "'Inter', 'sans-serif'";
            Chart.defaults.color = '#64748b';

            // Visitor Signups Chart
            const visitorSignupCtx = document.getElementById('visitorSignupChart')?.getContext('2d');
            if (visitorSignupCtx) {
                new Chart(visitorSignupCtx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($signupLabels); ?>,
                        datasets: [{
                            label: 'New Visitors',
                            data: <?php echo json_encode($signupCounts); ?>,
                            backgroundColor: 'rgba(0, 106, 78, 0.1)',
                            borderColor: '#006a4e',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1 } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
