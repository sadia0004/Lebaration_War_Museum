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

// --- Fetch Data for Stats ---
$totalArtifacts = $conn->query("SELECT COUNT(artifact_id) as total FROM artifacts")->fetch_assoc()['total'] ?? 0;
$totalMedia = $conn->query("SELECT COUNT(media_id) as total FROM media")->fetch_assoc()['total'] ?? 0;
$totalUsers = $conn->query("SELECT COUNT(user_id) as total FROM users")->fetch_assoc()['total'] ?? 0;
$totalViews = $conn->query("SELECT COUNT(history_id) as total FROM user_view_history")->fetch_assoc()['total'] ?? 0;
$featuredCount = $conn->query("SELECT COUNT(artifact_id) as total FROM artifacts WHERE is_featured = 1")->fetch_assoc()['total'] ?? 0;

// Most Viewed Artifact
$mostViewedResult = $conn->query("SELECT a.title, COUNT(h.history_id) as view_count FROM user_view_history h JOIN artifacts a ON h.artifact_id = a.artifact_id GROUP BY h.artifact_id ORDER BY view_count DESC LIMIT 1");
$mostViewedArtifact = $mostViewedResult->fetch_assoc() ?? ['title' => 'N/A', 'view_count' => 0];


// --- Fetch Data for Charts ---
// Artifact Status Chart
$artifactStatusData = $conn->query("SELECT `status`, COUNT(artifact_id) as count FROM artifacts GROUP BY `status`");
$artifactStatusLabels = [];
$artifactStatusCounts = [];
while($row = $artifactStatusData->fetch_assoc()) {
    $artifactStatusLabels[] = $row['status'];
    $artifactStatusCounts[] = $row['count'];
}

// Media Type Chart
$mediaTypeData = $conn->query("SELECT media_type, COUNT(media_id) as count FROM media GROUP BY media_type");
$mediaTypeLabels = [];
$mediaTypeCounts = [];
while($row = $mediaTypeData->fetch_assoc()) {
    $mediaTypeLabels[] = ucfirst($row['media_type']);
    $mediaTypeCounts[] = $row['count'];
}

// --- Fetch Data for Tables ---
// Artifacts Table
$artifacts = [];
$artifactsResult = $conn->query("SELECT artifact_id, title, object_type, status, is_featured, created_at, contributor_name FROM artifacts ORDER BY created_at DESC LIMIT 5");
while($row = $artifactsResult->fetch_assoc()) {
    $artifacts[] = $row;
}

// Media Table
$mediaItems = [];
$mediaResult = $conn->query("SELECT media_id, title, media_type, category, created_at FROM media ORDER BY created_at DESC LIMIT 5");
while($row = $mediaResult->fetch_assoc()) {
    $mediaItems[] = $row;
}


$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Reports - Digital Liberation War Museum</title>
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
    <style>
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
    </style>
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
            <a href="add_timeline_event.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="milestone" class="w-5 h-5 mr-3"></i> Timeline Events
            </a>
            <a href="content_report.php" class="bg-gradient-to-r from-red-700 to-green-800 text-white flex items-center px-4 py-3 text-sm font-semibold rounded-xl shadow-lg">
                <i data-lucide="bar-chart-3" class="w-5 h-5 mr-3"></i> Content Reports
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
                <h2 class="text-2xl font-bold text-slate-900 font-serif">Content Analytics & Reports</h2>
                <button id="export-btn" class="px-4 py-2 bg-gradient-to-r from-red-600 to-green-600 text-white rounded-full hover:shadow-lg transition-all duration-300 font-semibold hover:scale-105 flex items-center">
                    <i data-lucide="download" class="w-4 h-4 mr-2"></i> Export Report
                </button>
            </div>
        </header>

        <div id="report-content" class="p-6 lg:p-10 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-6">
                <!-- Main Stats -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-lg border border-white/20" data-aos="fade-up">
                    <p class="text-sm font-medium text-slate-500">Total Artifacts</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2"><?php echo $totalArtifacts; ?></p>
                </div>
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-lg border border-white/20" data-aos="fade-up" data-aos-delay="100">
                    <p class="text-sm font-medium text-slate-500">Media Files</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2"><?php echo $totalMedia; ?></p>
                </div>
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-lg border border-white/20" data-aos="fade-up" data-aos-delay="200">
                    <p class="text-sm font-medium text-slate-500">Registered Users</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2"><?php echo $totalUsers; ?></p>
                </div>
                <!-- Secondary Stats -->
                 <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-lg border border-white/20" data-aos="fade-up" data-aos-delay="300">
                    <p class="text-sm font-medium text-slate-500">Featured Artifacts</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2"><?php echo $featuredCount; ?></p>
                </div>
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-lg border border-white/20" data-aos="fade-up" data-aos-delay="400">
                    <p class="text-sm font-medium text-slate-500">Total Content Views</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2"><?php echo $totalViews; ?></p>
                </div>
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-lg border border-white/20" data-aos="fade-up" data-aos-delay="500">
                    <p class="text-sm font-medium text-slate-500">Most Viewed Artifact</p>
                    <p class="text-lg font-bold text-liberation-green mt-2 truncate" title="<?php echo htmlspecialchars($mostViewedArtifact['title']); ?>"><?php echo htmlspecialchars($mostViewedArtifact['title']); ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                <div class="lg:col-span-3 bg-white/80 backdrop-blur-lg rounded-3xl p-6 shadow-lg border border-white/20" data-aos="fade-right">
                    <h3 class="text-xl font-bold text-slate-800 font-serif mb-6">Artifact Status Distribution</h3>
                    <div><canvas id="artifactStatusChart"></canvas></div>
                </div>
                <div class="lg:col-span-2 bg-white/80 backdrop-blur-lg rounded-3xl p-6 shadow-lg border border-white/20" data-aos="fade-left">
                    <h3 class="text-xl font-bold text-slate-800 font-serif mb-6">Media Type Distribution</h3>
                    <div><canvas id="mediaTypeChart"></canvas></div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20" data-aos="fade-up">
                 <h3 class="text-xl font-bold text-slate-800 font-serif mb-4">Recently Added Content</h3>
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-x-12 gap-y-8">
                    <div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                 <thead class="text-left text-slate-500"><tr><th class="py-2 font-semibold">Recent Artifacts</th><th class="py-2 font-semibold">Contributor</th><th class="py-2 font-semibold">Status</th></tr></thead>
                                 <tbody class="divide-y divide-slate-200">
                                    <?php if(empty($artifacts)): ?>
                                        <tr><td colspan="3" class="py-4 text-center text-slate-500">No recent artifacts found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($artifacts as $item): ?>
                                        <tr class="hover:bg-slate-50">
                                            <td class="py-3 font-medium text-slate-800"><?php echo htmlspecialchars($item['title']); ?></td>
                                            <td class="py-3 text-slate-600"><?php echo htmlspecialchars($item['contributor_name'] ?: 'N/A'); ?></td>
                                            <td class="py-3">
                                                <?php 
                                                    $statusColor = 'bg-gray-100 text-gray-800'; // Default
                                                    if ($item['status'] === 'On Display') $statusColor = 'bg-green-100 text-liberation-green';
                                                    if ($item['status'] === 'In Storage') $statusColor = 'bg-yellow-100 text-amber-700';
                                                ?>
                                                <span class="badge <?php echo $statusColor; ?>"><?php echo htmlspecialchars($item['status']); ?></span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                 </tbody>
                            </table>
                        </div>
                    </div>
                     <div>
                        <div class="overflow-x-auto">
                           <table class="w-full text-sm">
                                <thead class="text-left text-slate-500"><tr><th class="py-2 font-semibold">Recent Media</th><th class="py-2 font-semibold">Category</th><th class="py-2 font-semibold">Type</th></tr></thead>
                                 <tbody class="divide-y divide-slate-200">
                                    <?php if(empty($mediaItems)): ?>
                                        <tr><td colspan="3" class="py-4 text-center text-slate-500">No recent media found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($mediaItems as $item): ?>
                                        <tr class="hover:bg-slate-50">
                                            <td class="py-3 font-medium text-slate-800"><?php echo htmlspecialchars($item['title']); ?></td>
                                            <td class="py-3 text-slate-600"><?php echo htmlspecialchars($item['category']); ?></td>
                                            <td class="py-3">
                                                <?php 
                                                    $typeColor = $item['media_type'] === 'video' ? 'bg-red-100 text-liberation-red' : 'bg-blue-100 text-blue-700';
                                                ?>
                                                <span class="badge <?php echo $typeColor; ?>"><?php echo htmlspecialchars(ucfirst($item['media_type'])); ?></span>
                                            </td>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        AOS.init({ duration: 800, easing: 'ease-in-out', once: true, offset: 50 });
        lucide.createIcons();

        document.addEventListener('DOMContentLoaded', function() {
            // Chart.js Global Config
            Chart.defaults.font.family = "'Inter', 'sans-serif'";
            Chart.defaults.color = '#64748b'; // slate-500

            // Artifact Status Chart
            const artifactStatusCtx = document.getElementById('artifactStatusChart')?.getContext('2d');
            if (artifactStatusCtx) {
                new Chart(artifactStatusCtx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode($artifactStatusLabels); ?>,
                        datasets: [{
                            label: 'Artifact Count',
                            data: <?php echo json_encode($artifactStatusCounts); ?>,
                            backgroundColor: ['rgba(0, 106, 78, 0.7)','rgba(255, 215, 0, 0.7)','rgba(220, 20, 60, 0.7)','rgba(100, 100, 100, 0.7)'],
                            borderColor: ['#006a4e','#ffd700','#dc143c','#646464'],
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        indexAxis: 'y',
                        plugins: { legend: { display: false } },
                        scales: { x: { grid: { color: '#e2e8f0' } }, y: { grid: { display: false } } }
                    }
                });
            }

            // Media Type Chart
            const mediaTypeCtx = document.getElementById('mediaTypeChart')?.getContext('2d');
            if (mediaTypeCtx) {
                new Chart(mediaTypeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode($mediaTypeLabels); ?>,
                        datasets: [{
                            data: <?php echo json_encode($mediaTypeCounts); ?>,
                            backgroundColor: ['rgba(220, 20, 60, 0.8)', 'rgba(23, 117, 209, 0.8)'],
                            borderColor: ['#ffffff'],
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: { legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true, pointStyle: 'rectRounded' } } }
                    }
                });
            }

            // PDF Export Functionality
            const exportBtn = document.getElementById('export-btn');
            const reportContent = document.getElementById('report-content');
            
            if (exportBtn && reportContent) {
                exportBtn.addEventListener('click', () => {
                    const originalBtnText = exportBtn.innerHTML;
                    exportBtn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin"></i>Generating...`;
                    lucide.createIcons(); // Re-render icon

                    html2canvas(reportContent, {
                        scale: 2, // Higher scale for better quality
                        backgroundColor: '#f1f5f9' // slate-100
                    }).then(canvas => {
                        const imgData = canvas.toDataURL('image/png');
                        const { jsPDF } = window.jspdf;
                        
                        // A4 size in mm: 210 x 297
                        const pdf = new jsPDF('p', 'mm', 'a4');
                        const pdfWidth = pdf.internal.pageSize.getWidth();
                        const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
                        
                        pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                        pdf.save('museum-content-report-<?php echo date("Y-m-d"); ?>.pdf');

                        exportBtn.innerHTML = originalBtnText;
                        lucide.createIcons();
                    });
                });
            }
        });
    </script>
</body>
</html>

