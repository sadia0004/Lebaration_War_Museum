<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration
$baseURL = "http://localhost/MUSEUM/";
$fullName = $_SESSION['full_name'] ?? 'Visitor';

// --- Database Connection ---
$host = "localhost";
$username = "root";
$password = "";
$database = "museum";
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// Fetch all heroes from the database, including dates
$heroes = [];
$query = "SELECT hero_id, full_name, title, bio, image_url, date_of_birth, date_of_death FROM heroes ORDER BY FIELD(title, 'Bir Sreshtho') DESC, full_name ASC";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $heroes[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heroes & Martyrs - Digital Liberation War Museum</title>
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
    
    <!-- Sidebar for logged-in users -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <aside class="w-72 bg-white/95 backdrop-blur-lg border-r border-slate-200/50 flex-col h-screen fixed hidden lg:flex shadow-xl">
        <div class="h-20 flex items-center justify-center px-6 border-b border-slate-200/50 bg-gradient-to-r from-red-700 to-green-800">
            <div class="flex items-center space-x-3">
                <div class="relative"><img src="images/logo.png" alt="Logo" class="h-12 w-12 object-cover rounded-full border-2 border-white shadow-lg"><div class="absolute -bottom-1 -right-1 w-4 h-4 bg-yellow-500 rounded-full border-2 border-white"></div></div>
                <div class="text-left text-white"><h1 class="text-lg font-bold font-serif">Digital Museum</h1><p class="text-xs opacity-90 font-medium">Liberation War 1971</p></div>
            </div>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="visitor_dashboard.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300">
                <i data-lucide="home" class="w-5 h-5 mr-3"></i> Museum Home
            </a>
            <a href="explore_museum.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300"><i data-lucide="compass" class="w-5 h-5 mr-3"></i> Artifact Gallery</a>
            <a href="video_gallery.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300"><i data-lucide="video" class="w-5 h-5 mr-3"></i> Video Archives</a>
            <a href="audio_archives.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300"><i data-lucide="mic" class="w-5 h-5 mr-3"></i> Audio Stories</a>
            <a href="timeline.php" class="text-slate-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-green-50 hover:text-red-600 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300"><i data-lucide="clock" class="w-5 h-5 mr-3"></i> Historical Timeline</a>
            <a href="heroes.php" class="bg-gradient-to-r from-red-700 to-green-800 text-white flex items-center px-4 py-3 text-sm font-semibold rounded-xl shadow-lg"><i data-lucide="star" class="w-5 h-5 mr-3"></i> Heroes & Martyrs</a>
        </nav>
        <div class="mt-auto px-4 py-4 border-t border-slate-200/50">
            <a href="logout.php" class="text-red-600 hover:bg-red-50 flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300"><i data-lucide="log-out" class="w-5 h-5 mr-3"></i> Sign Out</a>
        </div>
    </aside>
    <?php endif; ?>

    <main class="flex-1 <?php if (isset($_SESSION['user_id'])): ?>lg:ml-72<?php endif; ?>">
        <!-- Header for non-logged-in users -->
        <?php if (!isset($_SESSION['user_id'])): ?>
        <header class="bg-white/90 backdrop-blur-lg border-b border-slate-200/50 sticky top-0 z-40 shadow-sm">
            <div class="container mx-auto flex items-center justify-between px-6 lg:px-8 py-4">
                 <a href="index.php" class="flex items-center space-x-3"><img src="images/logo.png" alt="Logo" class="h-12 w-12 object-cover rounded-full"><h1 class="text-xl font-bold font-serif text-slate-800">Liberation War Museum</h1></a>
                <div><a href="login.php" class="px-6 py-2 bg-liberation-green text-white rounded-full font-semibold hover:bg-liberation-red transition">Login</a></div>
            </div>
        </header>
        <?php endif; ?>

        <!-- Hero Section -->
        <section class="h-[50vh] bg-cover bg-center bg-fixed flex items-center justify-center text-white" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/memorial.jpg');">
            <div class="text-center" data-aos="fade-up">
                <h1 class="text-5xl md:text-7xl font-bold font-serif">Heroes of 1971</h1>
                <p class="mt-4 text-xl text-slate-300">Honoring the brave souls who sacrificed everything for our freedom.</p>
            </div>
        </section>

        <!-- Heroes Grid -->
        <div class="p-6 lg:p-10">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
                <?php if (!empty($heroes)): ?>
                    <?php foreach ($heroes as $index => $hero): ?>
                    <article class="bg-white/80 backdrop-blur-lg rounded-2xl overflow-hidden shadow-lg border border-white/20 group flex flex-col" data-aos="fade-up" data-aos-delay="<?php echo ($index % 3) * 100; ?>">
                        <div class="relative h-64 overflow-hidden">
                            <img src="<?php echo htmlspecialchars($hero['image_url'] ?: 'images/default_hero.png'); ?>" 
                                 alt="<?php echo htmlspecialchars($hero['full_name']); ?>"
                                 class="w-full h-full object-cover object-top transition-transform duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent"></div>
                            <div class="absolute bottom-0 left-0 p-6">
                                <span class="text-sm font-bold bg-liberation-gold text-slate-900 px-3 py-1 rounded-full"><?php echo htmlspecialchars($hero['title']); ?></span>
                                <h3 class="text-2xl font-bold text-white mt-2 font-serif"><?php echo htmlspecialchars($hero['full_name']); ?></h3>
                            </div>
                        </div>
                        <div class="p-6 flex-grow flex flex-col">
                            <?php if ($hero['date_of_birth'] && $hero['date_of_death']): ?>
                            <div class="flex items-center text-sm text-slate-500 mb-4">
                                <i data-lucide="calendar" class="w-4 h-4 mr-2 text-liberation-green"></i>
                                <span><?php echo date('j F Y', strtotime($hero['date_of_birth'])); ?> – <?php echo date('j F Y', strtotime($hero['date_of_death'])); ?></span>
                            </div>
                            <?php endif; ?>
                            <p class="text-slate-600 text-sm leading-relaxed line-clamp-3 flex-grow">
                                <?php echo htmlspecialchars($hero['bio']); ?>
                            </p>
                            <div class="mt-6 border-t border-slate-200 pt-4">
                                <a href="hero_detail.php?id=<?php echo $hero['hero_id']; ?>" class="font-semibold text-liberation-green hover:text-liberation-red transition">Read Full Biography <i data-lucide="arrow-right" class="inline w-4 h-4"></i></a>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="col-span-full text-center text-slate-500 py-16">No hero profiles are available at the moment. Please check back later.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true, offset: 50 });
        lucide.createIcons();
    </script>
</body>
</html>

