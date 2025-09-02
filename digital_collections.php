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

// --- Database Connection ---
$host = "localhost";
$username = "root";
$password = "";
$database = "museum";
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all media items from the database
$mediaItems = [];
$query = "SELECT media_id, title, description, media_type, category, file_url, thumbnail_url, created_at FROM media ORDER BY created_at DESC";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mediaItems[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Collections - Liberation War Museum</title>
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
        .modal-backdrop { background-color: rgba(26, 26, 26, 0.7); backdrop-filter: blur(4px); }
        .tab-active { 
            border-color: #dc143c; /* liberation-red */
            color: #dc143c; 
            font-weight: 600; 
        }
    </style>
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
            
            <a href="manager_dashboard.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i> Dashboard
            </a>
            <a href="artifact_management.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="archive" class="w-5 h-5 mr-3"></i> Artifacts
            </a>
            <a href="digital_collections.php" class="bg-gradient-to-r from-red-700 to-green-800 text-white flex items-center px-4 py-3 text-sm font-semibold rounded-xl shadow-lg">
                <i data-lucide="gem" class="w-5 h-5 mr-3"></i> Digital Collections
            </a>
            <a href="add_media.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="clapperboard" class="w-5 h-5 mr-3"></i> Media Library
            </a>
            <a href="add_timeline_event.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="milestone" class="w-5 h-5 mr-3"></i> Timeline Events
            </a>

            <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold mb-4 px-2 pt-6">Analytics & System</div>
             <a href="#" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
                <i data-lucide="users" class="w-5 h-5 mr-3"></i> Visitor Analytics
            </a>
            <a href="#" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 hover:translate-x-1">
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

    <main class="flex-1 lg:ml-72 p-6 lg:p-10">
        
        <section class="bg-white/80 backdrop-blur-lg rounded-3xl p-8 shadow-lg border border-white/20 mb-8" data-aos="fade-down">
            <header class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 font-serif">Digital Collections</h1>
                    <p class="text-slate-600 mt-1">Manage all video and audio assets for the museum.</p>
                </div>
                <a href="add_media.php" class="px-6 py-3 bg-gradient-to-r from-red-600 to-green-600 text-white rounded-full hover:shadow-lg transition-all duration-300 font-semibold hover:scale-105 flex items-center flex-shrink-0">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add Media
                </a>
            </header>

            <div class="border-b border-slate-200">
                <nav id="media-tabs" class="-mb-px flex space-x-6" aria-label="Tabs">
                    <button data-type="all" class="tab-active whitespace-nowrap py-3 px-1 border-b-2 text-sm">All Media</button>
                    <button data-type="video" class="whitespace-nowrap py-3 px-1 border-b-2 border-transparent text-slate-500 hover:text-liberation-green hover:border-slate-300 font-medium text-sm">Videos</button>
                    <button data-type="audio" class="whitespace-nowrap py-3 px-1 border-b-2 border-transparent text-slate-500 hover:text-liberation-green hover:border-slate-300 font-medium text-sm">Audio</button>
                </nav>
            </div>
        </section>

        <section id="media-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php if (!empty($mediaItems)): ?>
                <?php foreach ($mediaItems as $index => $item): ?>
                <div class="media-card bg-white border border-slate-200 transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl rounded-2xl overflow-hidden flex flex-col group" 
                     data-type="<?php echo htmlspecialchars($item['media_type']); ?>" 
                     data-aos="zoom-in" data-aos-delay="<?php echo ($index % 4) * 100; ?>">
                    
                    <div class="relative h-48 bg-gray-800 flex items-center justify-center cursor-pointer overflow-hidden media-preview-btn"
                         data-url="<?php echo $baseURL . htmlspecialchars($item['file_url']); ?>"
                         data-title="<?php echo htmlspecialchars($item['title']); ?>"
                         data-type="<?php echo htmlspecialchars($item['media_type']); ?>">
                        
                        <?php if ($item['media_type'] === 'video'): ?>
                            <img src="<?php echo $baseURL . htmlspecialchars($item['thumbnail_url'] ?? 'images/default_video.png'); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <div class="w-14 h-14 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                    <i data-lucide="play" class="w-6 h-6 text-white ml-1"></i>
                                </div>
                            </div>
                        <?php else: // Audio ?>
                             <div class="absolute inset-0 bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center">
                                <i data-lucide="headphones" class="w-12 h-12 text-white/50 group-hover:text-white transition-colors"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-4 flex-grow flex flex-col">
                        <span class="text-xs font-semibold <?php echo $item['media_type'] === 'video' ? 'text-liberation-red bg-red-100' : 'text-liberation-green bg-green-100'; ?> px-2 py-1 rounded-full self-start"><?php echo ucfirst($item['media_type']); ?></span>
                        <h4 class="font-serif font-bold text-slate-800 mt-2 group-hover:text-liberation-green transition line-clamp-2" title="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </h4>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <div id="no-results-message" class="text-center py-16 text-slate-500 hidden col-span-full">
            <i data-lucide="folder-search" class="w-20 h-20 mx-auto text-slate-300"></i>
            <p class="mt-4 text-xl font-serif font-semibold">No Media Found</p>
            <p class="text-sm">No items match the selected filter. Click "Add Media" to upload content.</p>
        </div>
    </main>

    <div id="media-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop hidden">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col transform transition-all duration-300 scale-95 opacity-0">
            <div class="flex justify-between items-center p-4 border-b border-slate-200">
                <h3 id="modal-title" class="text-lg font-bold font-serif text-slate-800"></h3>
                <button id="modal-close-btn" class="text-slate-500 hover:text-liberation-red"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <div class="bg-black flex-grow flex items-center justify-center p-2 rounded-b-lg">
                <video id="modal-video-player" class="w-full h-full max-h-[70vh] rounded" controls style="display: none;"></video>
                <audio id="modal-audio-player" class="w-full" controls style="display: none;"></audio>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        lucide.createIcons();
        AOS.init({ duration: 600, once: true, offset: 50 });
        
        document.addEventListener('DOMContentLoaded', () => {
            const tabsContainer = document.getElementById('media-tabs');
            const mediaGrid = document.getElementById('media-grid');
            const noResultsMessage = document.getElementById('no-results-message');
            const mediaCards = document.querySelectorAll('.media-card');

            const modal = document.getElementById('media-modal');
            const modalContent = modal.querySelector('.transform');
            const closeModalBtn = document.getElementById('modal-close-btn');
            const modalTitle = document.getElementById('modal-title');
            const videoPlayer = document.getElementById('modal-video-player');
            const audioPlayer = document.getElementById('modal-audio-player');

            let activeTab = 'all';

            const filterMedia = () => {
                let visibleCount = 0;
                mediaCards.forEach(card => {
                    const type = card.dataset.type || '';
                    if (activeTab === 'all' || type === activeTab) {
                        card.style.display = 'flex';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
                noResultsMessage.style.display = visibleCount === 0 ? 'block' : 'none';
            };

            const openModal = (type, url, title) => {
                modalTitle.textContent = title;
                if (type === 'video') {
                    videoPlayer.src = url;
                    videoPlayer.style.display = 'block';
                    audioPlayer.style.display = 'none';
                    videoPlayer.play();
                } else {
                    audioPlayer.src = url;
                    audioPlayer.style.display = 'block';
                    videoPlayer.style.display = 'none';
                    audioPlayer.play();
                }
                modal.classList.remove('hidden');
                setTimeout(() => modalContent.classList.remove('scale-95', 'opacity-0'), 10);
            };

            const closeModal = () => {
                videoPlayer.pause();
                videoPlayer.src = "";
                audioPlayer.pause();
                audioPlayer.src = "";
                modalContent.classList.add('scale-95', 'opacity-0');
                setTimeout(() => modal.classList.add('hidden'), 300);
            };

            if (tabsContainer) {
                tabsContainer.addEventListener('click', (e) => {
                    const clickedTab = e.target.closest('button');
                    if (!clickedTab) return;
                    activeTab = clickedTab.dataset.type;
                    tabsContainer.querySelectorAll('button').forEach(tab => tab.classList.remove('tab-active'));
                    clickedTab.classList.add('tab-active');
                    filterMedia();
                });
            }

            if (mediaGrid) {
                mediaGrid.addEventListener('click', (e) => {
                    const previewBtn = e.target.closest('.media-preview-btn');
                    if (previewBtn) {
                        const { url, title, type } = previewBtn.dataset;
                        openModal(type, url, title);
                    }
                });
            }
            
            closeModalBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });

            if (mediaCards.length === 0) {
                 noResultsMessage.style.display = 'block';
            } else {
                 filterMedia();
            }
        });
    </script>
</body>
</html>