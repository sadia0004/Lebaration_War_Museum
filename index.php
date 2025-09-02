<?php
session_start();
// ==============================================================================
// === CONFIGURATION ===
// ==============================================================================
// This MUST match your project's folder name in htdocs
$baseURL = "http://localhost/MUSEUM/"; 

// --- DYNAMIC CONTENT (Simulating a database fetch) ---

// Hero Stats
$totalArtifacts = 21000;
$totalVisitors = 1166212;
$establishmentYear = 1996;
$heritageYears = date('Y') - $establishmentYear;

// Digital Archive Categories
$archiveCategories = [
    ['icon' => 'fa-shield-alt', 'title' => 'Mukti Bahini Equipment', 'description' => 'Weapons & Materials', 'color_bg' => 'bg-red-50', 'color_hover' => 'hover:bg-red-100', 'color_text' => 'text-red-600', 'aos_delay' => '0'],
    ['icon' => 'fa-scroll', 'title' => 'Historical Documents', 'description' => 'Rare Papers & Media', 'color_bg' => 'bg-green-50', 'color_hover' => 'hover:bg-green-100', 'color_text' => 'text-green-600', 'aos_delay' => '100'],
    ['icon' => 'fa-camera', 'title' => 'Photo Archive', 'description' => 'Rare Photographs', 'color_bg' => 'bg-yellow-50', 'color_hover' => 'hover:bg-yellow-100', 'color_text' => 'text-yellow-600', 'aos_delay' => '200'],
    ['icon' => 'fa-heart', 'title' => 'Personal Effects', 'description' => 'Martyrs\' Belongings', 'color_bg' => 'bg-red-50', 'color_hover' => 'hover:bg-red-100', 'color_text' => 'text-red-600', 'aos_delay' => '300'],
];

// Virtual Tour Items (using local images for reliability)
$virtualTourItems = [
    ['image' => $baseURL . 'uploads/artifacts/1756579360_download (3).jpeg', 'title' => 'Gallery of Historic Photos', 'aos_delay' => '0'],
    ['image' => $baseURL . 'uploads/artifacts/1756724716_WhatsApp Image 2025-09-01 at 16.50.11_7a72ed69.jpg', 'title' => 'Interactive Storytelling', 'aos_delay' => '100'],
    ['image' => $baseURL . 'uploads/artifacts/1756725024_images (1).jpeg', 'title' => 'Mukti Bahini Weapons', 'aos_delay' => '200'],
];

// Collection Items (using local images for reliability)
$collectionItems = [
    ['image' => $baseURL . 'uploads/artifacts/1756579360_download (3).jpeg', 'title' => 'Freedom Weapons', 'description' => 'Weapons used by Mukti Bahini', 'color' => 'text-red-600', 'aos_delay' => '0'],
    ['image' => $baseURL . 'uploads/artifacts/1756724716_WhatsApp Image 2025-09-01 at 16.50.11_7a72ed69.jpg', 'title' => 'Historic Paintings', 'description' => 'Visual stories of 1971', 'color' => 'text-green-600', 'aos_delay' => '100'],
    ['image' => $baseURL . 'uploads/artifacts/1756725024_images (1).jpeg', 'title' => 'Documents & Manuscripts', 'description' => 'Rare papers and publications', 'color' => 'text-yellow-600', 'aos_delay' => '200'],
    ['image' => $baseURL . 'uploads/artifacts/1756579360_download (3).jpeg', 'title' => 'Martyrs’ Medals', 'description' => 'Honoring our heroes', 'color' => 'text-red-600', 'aos_delay' => '300'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liberation War Museum Bangladesh - Interactive Digital Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .hero-gradient { background: linear-gradient(135deg, rgba(0, 106, 78, 0.9) 0%, rgba(220, 20, 60, 0.8) 100%); }
        .artifact-card { transition: all 0.5s ease; }
        .artifact-card:hover { transform: translateY(-8px) scale(1.02); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); }
        .parallax-bg { background-attachment: fixed; background-position: center; background-repeat: no-repeat; background-size: cover; }
    </style>
</head>
<body class="text-gray-800 bg-gray-50">

    <nav class="bg-white/95 backdrop-blur-sm shadow-lg sticky top-0 z-50 transition-all duration-500">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="#" class="flex items-center space-x-3">
                <img src="<?php echo $baseURL; ?>images/logo.png" alt="Liberation War Museum Logo" class="w-14 h-14 rounded-sm">
                <h1 class="text-xl font-serif font-bold text-red-700">Liberation War Museum</h1>
                <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">Est. <?php echo $establishmentYear; ?></span>
            </a>
            <div class="hidden md:flex items-center space-x-8">
                <a href="#home" class="hover:text-red-600 transition">Home</a>
                <a href="#about" class="hover:text-red-600 transition">Our Museum</a>
                <a href="#artifacts" class="hover:text-red-600 transition">Digital Archive</a>
                <a href="#virtual-tour" class="hover:text-red-600 transition">Virtual Tour</a>
                <a href="#collections" class="hover:text-red-600 transition">Collections</a>
                <a href="#contact" class="hover:text-red-600 transition">Visit</a>
            </div>
            <div class="hidden md:flex items-center space-x-3">
                <?php if (isset($_SESSION['user_id'])): 
                    $dashboardLink = $_SESSION['role'] === 'manager' ? 'manager_dashboard.php' : 'visitor_dashboard.php';
                ?>
                    <a href="<?php echo $dashboardLink; ?>" class="px-4 py-2 border-2 border-green-600 text-green-600 rounded-lg hover:bg-green-600 hover:text-white transition">Dashboard</a>
                    <a href="logout.php" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="px-4 py-2 border-2 border-red-600 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition">Login</a>
                    <a href="register_user.php" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Sign Up</a>
                <?php endif; ?>
            </div>
            <button class="md:hidden">
                <i class="fas fa-bars text-red-700"></i>
            </button>
        </div>
    </nav>

    <section id="home" class="relative h-screen flex items-center justify-center parallax-bg" style="background-image: url('https://dwg-office.com/wp-content/uploads/2016/06/DSC_0789.jpg')">
        <div class="absolute inset-0 hero-gradient"></div>
        <div class="relative z-10 text-center text-white px-4 max-w-5xl mx-auto" data-aos="fade-up">
            <h1 class="text-4xl md:text-7xl font-serif font-bold mb-6 leading-tight">
                Preserving the Memory of <span class="text-yellow-300">1971</span>
            </h1>
            <p class="text-xl md:text-2xl mb-4 font-light leading-relaxed">
                The heroic struggle of the Bengali nation for democratic and national rights
            </p>
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 mb-8 inline-block">
                <div class="grid grid-cols-3 gap-8 text-center">
                    <div>
                        <div class="text-2xl font-bold text-yellow-300"><?php echo number_format($totalArtifacts, 0, '', ','); ?>+</div>
                        <div class="text-sm">Artifacts</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-yellow-300"><?php echo number_format($totalVisitors, 0, '', ','); ?></div>
                        <div class="text-sm">Visitors</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-yellow-300"><?php echo $heritageYears; ?> Years</div>
                        <div class="text-sm">Heritage</div>
                    </div>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="#artifacts" class="bg-red-600 hover:bg-red-700 text-white px-8 py-4 rounded-full font-semibold transition duration-300 transform hover:scale-105">
                    <i class="fas fa-archive mr-2"></i>Explore Digital Archive
                </a>
                <a href="#virtual-tour" class="border border-white/30 bg-white/10 backdrop-blur-sm text-white px-8 py-4 rounded-full font-semibold transition duration-300 hover:bg-white/20">
                    <i class="fas fa-vr-cardboard mr-2"></i>Virtual Museum Tour
                </a>
            </div>
        </div>
    </section>

    <section id="about" class="py-20 bg-gradient-to-br from-gray-50 to-white">
        <div class="container mx-auto px-4 grid lg:grid-cols-2 gap-16 items-center">
            <div data-aos="fade-right" class="space-y-6">
                <div class="inline-block bg-red-100 text-red-800 px-4 py-2 rounded-full text-sm font-semibold">
                    Citizens' Initiative Since <?php echo $establishmentYear; ?>
                </div>
                <h2 class="text-4xl md:text-5xl font-serif font-bold text-gray-900 leading-tight">
                    A Monument to <span class="text-red-600">Freedom</span> & <span class="text-green-600">Democracy</span>
                </h2>
                <p class="text-lg text-gray-600 leading-relaxed">
                    The Liberation War Museum commemorates the heroic struggle of the Bengali nation for their democratic and national rights.
                </p>
                <div class="bg-gray-100 p-6 rounded-lg border border-gray-200">
                    <h3 class="font-semibold text-gray-900 mb-3">Our Mission</h3>
                    <p class="text-gray-600">
                        Educate new generations about the aspirations for which their forefathers fought.
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <div class="text-center p-4">
                        <div class="text-3xl font-bold text-red-600">1,300</div>
                        <div class="text-gray-600">Objects Displayed</div>
                    </div>
                    <div class="text-center p-4">
                        <div class="text-3xl font-bold text-green-600">20,000 sqm</div>
                        <div class="text-gray-600">Museum Area</div>
                    </div>
                </div>
            </div>
            <div class="relative" data-aos="fade-left">
                <div class="absolute -inset-4 bg-gradient-to-r from-red-600 to-green-600 rounded-2xl opacity-20 transform rotate-1"></div>
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/97/Artifact_in_liberation_war_museum%2C_Agargaon_61.jpg/2560px-Artifact_in_liberation_war_museum%2C_Agargaon_61.jpg" alt="Inside Liberation War Museum" class="relative rounded-2xl shadow-2xl w-full">
            </div>
        </div>
    </section>

    <section id="artifacts" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl md:text-5xl font-serif font-bold mb-6 text-gray-900">
                    Digital <span class="text-red-600">Archive</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Explore <?php echo number_format($totalArtifacts, 0, '', ','); ?>+ artifacts, documents, and testimonies from the 1971 Liberation War
                </p>
            </div>
            <div class="grid md:grid-cols-4 gap-6 mb-12">
                <?php foreach ($archiveCategories as $category): ?>
                    <div class="text-center p-6 <?php echo $category['color_bg']; ?> rounded-xl <?php echo $category['color_hover']; ?> transition duration-500 cursor-pointer" data-aos="flip-left" data-aos-delay="<?php echo $category['aos_delay']; ?>">
                        <i class="fas <?php echo $category['icon']; ?> text-3xl <?php echo $category['color_text']; ?> mb-4"></i>
                        <h3 class="font-semibold <?php echo str_replace('text-', 'text-', $category['color_text']) . '00'; ?>"><?php echo $category['title']; ?></h3>
                        <p class="text-sm text-gray-600 mt-2"><?php echo $category['description']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="virtual-tour" class="py-20 bg-gradient-to-br from-gray-900 via-red-900 to-green-900 text-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-4xl md:text-5xl font-serif font-bold mb-6">Virtual <span class="text-yellow-300">Museum</span> Experience</h2>
                <p class="text-xl text-gray-300 max-w-3xl mx-auto mb-8">
                    Explore galleries, view 3D artifacts, and watch interactive storytelling videos from home
                </p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach ($virtualTourItems as $item): ?>
                    <div class="relative overflow-hidden rounded-xl artifact-card hover:shadow-2xl" data-aos="fade-up" data-aos-delay="<?php echo $item['aos_delay']; ?>">
                        <img src="<?php echo $item['image']; ?>" class="w-full h-64 object-cover transition-transform duration-500 transform hover:scale-110">
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 hover:opacity-100 transition duration-500">
                            <h3 class="text-white font-bold text-lg p-4 text-center"><?php echo $item['title']; ?></h3>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="collections" class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-4xl md:text-5xl font-serif font-bold mb-6 text-gray-900">Our <span class="text-red-600">Collections</span></h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Diverse artifacts and historical treasures preserved for generations
                </p>
            </div>
            <div class="grid md:grid-cols-4 gap-8">
                <?php foreach ($collectionItems as $item): ?>
                    <div class="rounded-xl overflow-hidden shadow-lg hover:scale-105 transition transform duration-500" data-aos="zoom-in" data-aos-delay="<?php echo $item['aos_delay']; ?>">
                        <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-56 object-cover">
                        <div class="p-4 bg-white">
                            <h3 class="font-semibold <?php echo $item['color']; ?>"><?php echo $item['title']; ?></h3>
                            <p class="text-gray-600 text-sm"><?php echo $item['description']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <footer id="contact" class="bg-gray-900 text-white py-12 mt-12">
        <div class="container mx-auto px-4 grid md:grid-cols-3 gap-12">
            <div>
                <h3 class="font-semibold text-xl mb-4">Liberation War Museum</h3>
                <p class="text-gray-300">
                    Commemorating the struggle of 1971 and educating future generations.
                </p>
            </div>
            <div>
                <h3 class="font-semibold text-xl mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="#home" class="hover:text-red-500 transition">Home</a></li>
                    <li><a href="#about" class="hover:text-red-500 transition">Our Museum</a></li>
                    <li><a href="#artifacts" class="hover:text-red-500 transition">Digital Archive</a></li>
                    <li><a href="#virtual-tour" class="hover:text-red-500 transition">Virtual Tour</a></li>
                    <li><a href="#collections" class="hover:text-red-500 transition">Collections</a></li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold text-xl mb-4">Contact Us</h3>
                <p class="text-gray-300">Agargaon, Dhaka, Bangladesh</p>
                <p class="text-gray-300">info@liberationwarmuseum.org</p>
                <p class="text-gray-300">+880 2 48110991</p>
            </div>
        </div>
        <div class="text-center mt-8 text-gray-500 text-sm">© <?php echo date('Y'); ?> Liberation War Museum. All Rights Reserved.</div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 1200, once: true });
    </script>
</body>
</html>