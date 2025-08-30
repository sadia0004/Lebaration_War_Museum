<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Protect the page: check if the user is logged in and is a manager.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit();
}

// ==============================================================================
// === This MUST match your project's folder name in htdocs ===
$baseURL = "http://localhost/MUSEUM/";
// ==============================================================================

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
    <title>Digital Collections - Museum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'sans': ['Inter', 'sans-serif'] },
                    colors: {
                        'brand': { 'green': '#16a34a', 'light': '#dcfce7' },
                        'neutral': { 'bg': '#f8fafc', 'card': '#ffffff', 'border': '#e5e7eb', 'text-main': '#1f2937', 'text-muted': '#6b7280' }
                    }
                }
            }
        }
    </script>
     <style>
        .modal-backdrop { background-color: rgba(0,0,0,0.7); backdrop-filter: blur(4px); }
        .tab-active { 
            border-color: #16a34a; 
            color: #16a34a; 
            font-weight: 600; 
            background-color: #f0fdf4;
        }
    </style>
</head>
<body class="bg-neutral-bg flex min-h-screen text-neutral-text-main">

    <aside class="w-64 bg-neutral-card border-r border-neutral-border flex-col h-screen fixed hidden lg:flex">
         <div class="h-20 flex items-center justify-start px-6 border-b border-neutral-border">
            <div class="flex items-center space-x-3">
                <img src="images/logo.png" alt="Museum Logo" class="h-10 w-10 object-cover rounded-md">
                <div class="text-left">
                    <h1 class="text-base font-bold">Liberation War</h1>
                    <p class="text-xs text-neutral-text-muted">Digital Museum</p>
                </div>
            </div>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="manager_dashboard.php" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i> Dashboard
            </a>
            <a href="artifact_management.php" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="archive" class="w-5 h-5 mr-3"></i> Artifacts
            </a>
            <a href="digital_collections.php" class="bg-brand-green text-white flex items-center px-4 py-2.5 text-sm font-semibold rounded-lg shadow-md">
                <i data-lucide="gem" class="w-5 h-5 mr-3"></i> Digital Collections
            </a>
             <a href="add_media.php" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="clapperboard" class="w-5 h-5 mr-3"></i> Add Media
            </a>
             <a href="add_timeline_event.php" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="milestone" class="w-5 h-5 mr-3"></i> Digital Timeline
            </a>
             <a href="#" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="users" class="w-5 h-5 mr-3"></i> Visitor Analytics
            </a>
            <a href="#" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="file-text" class="w-5 h-5 mr-3"></i> Content Reports
            </a>
             <a href="#" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="settings" class="w-5 h-5 mr-3"></i> System Settings
            </a>
        </nav>
        <div class="mt-auto px-4 py-6 border-t border-neutral-border">
            <a href="logout.php" class="text-neutral-text-muted hover:bg-gray-100 hover:text-neutral-text-main flex items-center px-4 py-2.5 text-sm font-medium rounded-lg">
                <i data-lucide="log-out" class="w-5 h-5 mr-3"></i> Sign Out
            </a>
        </div>
    </aside>

    <main class="flex-1 p-8 lg:ml-64">
        <header class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-neutral-text-main">Digital Collections</h1>
            <a href="add_media.php" class="flex items-center gap-2 bg-brand-green text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-opacity-90 transition-all shadow-sm">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Media
            </a>
        </header>

        <div class="border-b border-neutral-border mb-6">
            <nav id="media-tabs" class="-mb-px flex space-x-6" aria-label="Tabs">
                <button data-type="all" class="tab-active whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">All Media</button>
                <button data-type="video" class="whitespace-nowrap py-3 px-1 border-b-2 border-transparent text-neutral-text-muted hover:text-neutral-text-main hover:border-gray-300 font-medium text-sm">Videos</button>
                <button data-type="audio" class="whitespace-nowrap py-3 px-1 border-b-2 border-transparent text-neutral-text-muted hover:text-neutral-text-main hover:border-gray-300 font-medium text-sm">Audio</button>
            </nav>
        </div>

        <section id="media-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php if (!empty($mediaItems)): ?>
                <?php foreach ($mediaItems as $item): ?>
                <div class="media-card bg-neutral-card rounded-xl border border-neutral-border overflow-hidden flex flex-col group" data-type="<?php echo htmlspecialchars($item['media_type']); ?>">
                    <div class="relative h-48 bg-gray-800 flex items-center justify-center cursor-pointer media-preview-btn"
                         data-url="<?php echo $baseURL . htmlspecialchars($item['file_url']); ?>"
                         data-title="<?php echo htmlspecialchars($item['title']); ?>"
                         data-type="<?php echo htmlspecialchars($item['media_type']); ?>">
                        
                        <?php if ($item['media_type'] === 'video'): ?>
                            <img src="<?php echo $baseURL . htmlspecialchars($item['thumbnail_url'] ?? 'images/default_video.png'); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center"><i data-lucide="play-circle" class="w-12 h-12 text-white/80"></i></div>
                        <?php else: // Audio ?>
                            <div class="absolute inset-0 bg-gray-700 flex items-center justify-center"><i data-lucide="music" class="w-12 h-12 text-white/80"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="p-4 flex-grow flex flex-col">
                        <span class="text-xs font-semibold <?php echo $item['media_type'] === 'video' ? 'text-purple-600 bg-purple-100' : 'text-sky-600 bg-sky-100'; ?> px-2 py-1 rounded-full self-start"><?php echo ucfirst($item['media_type']); ?></span>
                        <h4 class="font-bold text-neutral-text-main mt-2 group-hover:text-brand-green transition" title="<?php echo htmlspecialchars($item['title']); ?>"><?php echo htmlspecialchars($item['title']); ?></h4>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <div id="no-results-message" class="text-center py-16 text-neutral-text-muted hidden col-span-full">
            <i data-lucide="folder-search" class="w-16 h-16 mx-auto text-gray-300"></i>
            <p class="mt-4 text-lg font-semibold">No Media Found</p>
            <p class="text-sm">No items match the selected filter. Click "Add Media" to upload content.</p>
        </div>
    </main>

    <div id="media-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop hidden">
        <div class="bg-neutral-card rounded-lg shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col transform transition-all duration-300 scale-95 opacity-0">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 id="modal-title" class="text-lg font-bold"></h3>
                <button id="modal-close-btn" class="text-neutral-text-muted hover:text-red-500"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <div class="bg-black flex-grow flex items-center justify-center">
                <video id="modal-video-player" class="w-full h-full max-h-[70vh]" controls style="display: none;"></video>
                <audio id="modal-audio-player" class="w-full" controls style="display: none;"></audio>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        
        // This is a new, rewritten script to ensure all buttons work correctly.
        document.addEventListener('DOMContentLoaded', () => {
            // --- Elements ---
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

            // --- Functions ---
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

            // --- Event Listeners ---
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

            // --- Initial State ---
            if (mediaCards.length === 0) {
                 noResultsMessage.style.display = 'block';
            } else {
                filterMedia();
            }
        });
    </script>
</body>
</html>