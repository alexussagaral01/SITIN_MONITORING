<?php
session_start();
require '../db.php'; // Add database connection

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_announcement'])) {
    $content = $_POST['new_announcement'];
    $createdBy = 'ADMIN';

    $stmt = $conn->prepare("INSERT INTO announcement (CONTENT, CREATED_DATE, CREATED_BY) VALUES (?, NOW(), ?)");
    $stmt->bind_param("ss", $content, $createdBy);
    $stmt->execute();
    $stmt->close();
    
    // Redirect to refresh the page and prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch announcements from the database with DESC order
$announcements = [];
$result = $conn->query("SELECT ID, CONTENT, CREATED_DATE, CREATED_BY FROM announcement 
                       WHERE CREATED_BY = 'ADMIN' 
                       ORDER BY ID DESC, CREATED_DATE DESC"); // Changed ordering to show newest first
while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
}

// Get actual statistics from database
$totalStudents = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM users");
if ($row = $result->fetch_assoc()) {
    $totalStudents = $row['total'];
}

// Get current active sit-ins
$currentSitIns = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM curr_sitin WHERE STATUS = 'Active' AND TIME_OUT IS NULL");
if ($row = $result->fetch_assoc()) {
    $currentSitIns = $row['total'];
}

// Get total sit-ins (including completed ones)
$totalSitIns = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM curr_sitin");
if ($row = $result->fetch_assoc()) {
    $totalSitIns = $row['total'];
}

// Initialize program counts correctly based on PURPOSE enum values
$programCounts = [
    'C Programming' => 0,
    'C++ Programming' => 0,
    'C# Programming' => 0,
    'Java Programming' => 0,
    'Php Programming' => 0,
    'Python Programming' => 0,
    'Database' => 0,
    'Digital Logic & Design' => 0,
    'Embedded System & IOT' => 0,
    'System Integration & Architecture' => 0,
    'Computer Application' => 0,
    'Web Design & Development' => 0,
    'Project Management' => 0
];

$result = $conn->query("SELECT PURPOSE, COUNT(*) as count FROM curr_sitin GROUP BY PURPOSE");
while ($row = $result->fetch_assoc()) {
    $purpose = $row['PURPOSE'];
    if (array_key_exists($purpose, $programCounts)) {
        $programCounts[$purpose] = $row['count'];
    }
}

// Prepare data for ECharts pie chart
$echartsPieData = [];
foreach ($programCounts as $program => $count) {
    $echartsPieData[] = ['value' => $count, 'name' => $program];
}
$echartsPieDataJSON = json_encode($echartsPieData);

// Get students by year level
$yearLevelCounts = [
    '1st Year' => 0,
    '2nd Year' => 0,
    '3rd Year' => 0,
    '4th Year' => 0
];

$result = $conn->query("SELECT YEAR_LEVEL, COUNT(*) as count FROM users GROUP BY YEAR_LEVEL");
while ($row = $result->fetch_assoc()) {
    if (isset($yearLevelCounts[$row['YEAR_LEVEL']])) {
        $yearLevelCounts[$row['YEAR_LEVEL']] = $row['count'];
    }
}

// Convert to JavaScript array - Fix array methods syntax
$yearLevelJSON = json_encode(array_values($yearLevelCounts)); // Fixed from array.values to array_values
$yearLevelLabelsJSON = json_encode(array_keys($yearLevelCounts)); // Fixed from array.keys to array_keys

// Initialize leaderboard array
$leaderboardData = [];

// Get leaderboard data for top 5 most active students
$leaderboardQuery = "
    SELECT 
        u.FIRST_NAME,
        u.LAST_NAME,
        u.YEAR_LEVEL,
        u.UPLOAD_IMAGE,
        COUNT(c.SITIN_ID) as total_sessions
    FROM users u
    LEFT JOIN curr_sitin c ON u.IDNO = c.IDNO
    GROUP BY u.IDNO, u.FIRST_NAME, u.LAST_NAME, u.YEAR_LEVEL, u.UPLOAD_IMAGE
    ORDER BY total_sessions DESC
    LIMIT 5
";

$result = $conn->query($leaderboardQuery);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $leaderboardData[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../logo/ccs.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Add ECharts library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.3/echarts.min.js"></script>
    <title>Admin Dashboard</title>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif']
                    },
                }
            }
        }
    </script>
    <style>
        /* Add gradient text class for the footer */
        .gradient-text {
            background: linear-gradient(to right, #ec4899, #a855f7, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-purple-800 to-pink-700 min-h-screen font-poppins">
    <!-- Header -->
    <div class="text-center text-white font-bold text-2xl py-4 relative shadow-lg" style="background: linear-gradient(to bottom right, rgb(49, 46, 129), rgb(107, 33, 168), rgb(190, 24, 93))">
        CCS SIT-IN MONITORING SYSTEM
        <div class="absolute top-4 left-6 cursor-pointer" onclick="toggleNav(this)">
            <div class="bar1 w-8 h-1 bg-white my-1 transition-all duration-300"></div>
            <div class="bar2 w-8 h-1 bg-white my-1 transition-all duration-300"></div>
            <div class="bar3 w-8 h-1 bg-white my-1 transition-all duration-300"></div>
        </div>
    </div>

    <!-- Side Navigation -->
    <div id="mySidenav" class="fixed top-0 left-0 h-screen w-72 bg-gradient-to-b from-indigo-900 to-purple-800 transform -translate-x-full transition-transform duration-300 ease-in-out z-50 shadow-xl overflow-y-auto">
        <div class="absolute top-0 right-0 m-3">
            <button onclick="closeNav()" class="text-white hover:text-pink-200 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="flex flex-col items-center mt-6">
            <div class="relative">
                <img src="../images/image.jpg" alt="Logo" class="w-20 h-20 rounded-full border-4 border-white/30 object-cover shadow-lg">
                <div class="absolute bottom-0 right-0 bg-green-500 w-3 h-3 rounded-full border-2 border-white"></div>
            </div>
            <p class="text-white font-semibold text-lg mt-2 mb-0">Admin</p>
            <p class="text-purple-200 text-xs mb-3">Administrator</p>
        </div>

        <div class="px-2 py-2">
            <nav class="flex flex-col space-y-1">
                <a href="admin_dashboard.php" class="group px-3 py-2 text-white/90 bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-home w-5 mr-2 text-center"></i>
                    <span class="font-medium">HOME</span>
                </a>
                <a href="admin_search.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-search w-5 mr-2 text-center"></i>
                    <span class="font-medium">SEARCH</span>
                </a>
                <a href="admin_sitin.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-user-check w-5 mr-2 text-center"></i>
                    <span class="font-medium">SIT-IN</span>
                </a>
                
                <!-- VIEW Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-eye w-5 mr-2 text-center"></i>
                            <span class="font-medium">VIEW</span>
                        </div>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'transform rotate-180': open }"></i>
                    </button>
                    
                    <div x-show="open" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                        class="pl-7 mt-2 space-y-1">
                        
                        <a href="admin_sitinrec.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-book w-5 mr-2 text-center"></i>
                            <span class="font-medium">Sit-in Records</span>
                        </a>
                        
                        <a href="admin_studlist.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-list w-5 mr-2 text-center"></i>
                            <span class="font-medium">List of Students</span>
                        </a>
                        
                        <a href="admin_feedback.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-comments w-5 mr-2 text-center"></i>
                            <span class="font-medium">Feedbacks</span>
                        </a>
                        
                        <a href="#" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-chart-pie w-5 mr-2 text-center"></i>
                            <span class="font-medium">Daily Analytics</span>
                        </a>
                    </div>
                </div>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-desktop w-5 mr-2 text-center"></i>
                            <span class="font-medium">LAB</span>
                        </div>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'transform rotate-180': open }"></i>
                    </button>
                    
                    <div x-show="open" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                        class="pl-7 mt-2 space-y-1">
                        
                        <a href="admin_resources.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-box-open w-5 mr-2 text-center"></i>
                            <span class="font-medium">Resources</span>
                        </a>
                        
                        <a href="admin_lab_schedule.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-calendar-alt w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Schedule</span>
                        </a>
                        
                        <a href="admin_lab_usage.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-chart-bar w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Usage Point</span>
                        </a>
                    </div>
                </div>
                <a href="admin_reports.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-chart-line w-5 mr-2 text-center"></i>
                    <span class="font-medium">SIT-IN REPORT</span>
                </a>

                <a href="#" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-calendar-check w-5 mr-2 text-center"></i>
                    <span class="font-medium">RESERVATION/APPROVAL</span>
                </a>
                
                <div class="border-t border-white/10 my-2"></div>
                
                <a href="../logout.php" class="group px-3 py-2 text-white/90 hover:bg-red-500/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-sign-out-alt w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">LOG OUT</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="px-8 py-8 w-full flex flex-wrap gap-8">
        <!-- Leaderboard Card -->
        <div class="w-full mb-8">
            <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm border border-white/30">
                <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" 
                     style="background: linear-gradient(to bottom right, rgb(49, 46, 129), rgb(107, 33, 168), rgb(190, 24, 93))">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                    <i class="fas fa-trophy text-2xl mr-4 relative z-10"></i>
                    <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Student Leaderboard</h2>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <?php foreach ($leaderboardData as $index => $student): ?>
                            <div class="bg-gradient-to-br <?php 
                                switch($index) {
                                    case 0: echo 'from-yellow-50 to-white border-yellow-200 hover:shadow-yellow-100'; break;
                                    case 1: echo 'from-gray-50 to-white border-gray-200 hover:shadow-gray-100'; break;
                                    case 2: echo 'from-orange-50 to-white border-orange-200 hover:shadow-orange-100'; break;
                                    default: echo 'from-purple-50 to-white border-purple-200 hover:shadow-purple-100';
                                }
                            ?> rounded-xl p-4 shadow-lg border transform hover:scale-105 transition-all duration-300">
                                <div class="flex flex-col items-center text-center space-y-2">
                                    <!-- Rank Icon -->
                                    <div class="text-2xl mb-1">
                                        <?php
                                        switch($index) {
                                            case 0: echo '🥇'; break;
                                            case 1: echo '🥈'; break;
                                            case 2: echo '🥉'; break;
                                            case 3: echo '🏅'; break; // 4th place medal
                                            case 4: echo '🎖️'; break; // 5th place medal
                                            default: echo ($index + 1);
                                        }
                                        ?>
                                    </div>
                                    
                                    <!-- Replace the Student Avatar div with this updated version -->
                                    <div class="w-16 h-16 rounded-full overflow-hidden bg-gradient-to-br from-purple-500 to-indigo-500 flex items-center justify-center">
                                        <?php if (!empty($student['UPLOAD_IMAGE'])): ?>
                                            <img src="../images/<?php echo $student['UPLOAD_IMAGE']; ?>" 
                                                 alt="<?php echo htmlspecialchars($student['FIRST_NAME']); ?>" 
                                                 class="w-full h-full object-cover"
                                                 onerror="this.onerror=null; this.src='../images/default.jpg';">
                                        <?php else: ?>
                                            <img src="../images/default.jpg" 
                                                 alt="Default Profile" 
                                                 class="w-full h-full object-cover">
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Student Name -->
                                    <div class="font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($student['FIRST_NAME'] . ' ' . $student['LAST_NAME']); ?>
                                    </div>
                                    
                                    <!-- Year Level -->
                                    <div class="text-sm text-gray-600">
                                        <?php echo htmlspecialchars($student['YEAR_LEVEL']); ?>
                                    </div>
                                    
                                    <!-- Stats -->
                                    <div class="text-sm space-y-1">
                                        <div class="font-semibold text-purple-600">
                                            <?php echo $student['total_sessions'] ?? 0; ?> sessions
                                        </div>
                                        <div class="text-amber-500 font-medium text-xs">
                                            <?php 
                                            if ($student['total_sessions'] > 20) {
                                                echo '⭐ Most Active';
                                            } elseif ($student['total_sessions'] > 10) {
                                                echo '✨ Active';
                                            } else {
                                                echo '📚 Regular';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="flex-1 min-w-[600px] bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-2xl overflow-hidden h-[900px] backdrop-blur-sm border border-white/30">
            <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(to bottom right, rgb(49, 46, 129), rgb(107, 33, 168), rgb(190, 24, 93))">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                <i class="fas fa-chart-bar text-2xl mr-4 relative z-10"></i>
                <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Statistics</h2>
            </div>
            <div class="p-8 h-[calc(100%-5rem)] flex flex-col">
                <!-- Stats Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <!-- Students Registered Card -->
                    <div class="bg-gradient-to-br from-blue-50 to-white rounded-xl p-4 shadow-lg border border-blue-100/50 transform hover:scale-102 transition-transform duration-300">
                        <div class="flex flex-col items-center text-center">
                            <div class="mb-2 bg-blue-500/10 p-2 rounded-full">
                                <i class="fas fa-user-graduate text-xl text-blue-600"></i>
                            </div>
                            <span class="text-3xl font-bold text-blue-600 mb-1"><?php echo $totalStudents; ?></span>
                            <span class="text-xs text-blue-600/70 font-medium uppercase tracking-wider">Students Registered</span>
                        </div>
                    </div>

                    <!-- Currently Sit-In Card -->
                    <div class="bg-gradient-to-br from-purple-50 to-white rounded-xl p-4 shadow-lg border border-purple-100/50 transform hover:scale-102 transition-transform duration-300">
                        <div class="flex flex-col items-center text-center">
                            <div class="mb-2 bg-purple-500/10 p-2 rounded-full">
                                <i class="fas fa-chair text-xl text-purple-600"></i>
                            </div>
                            <span class="text-3xl font-bold text-purple-600 mb-1"><?php echo $currentSitIns; ?></span>
                            <span class="text-xs text-purple-600/70 font-medium uppercase tracking-wider">Currently Sit-In</span>
                        </div>
                    </div>

                    <!-- Total Sit-Ins Card -->
                    <div class="bg-gradient-to-br from-green-50 to-white rounded-xl p-4 shadow-lg border border-green-100/50 transform hover:scale-102 transition-transform duration-300">
                        <div class="flex flex-col items-center text-center">
                            <div class="mb-2 bg-green-500/10 p-2 rounded-full">
                                <i class="fas fa-clipboard-list text-xl text-green-600"></i>
                            </div>
                            <span class="text-3xl font-bold text-green-600 mb-1"><?php echo $totalSitIns; ?></span>
                            <span class="text-xs text-green-600/70 font-medium uppercase tracking-wider">Total Sit-Ins</span>
                        </div>
                    </div>
                </div>

                <!-- Chart Container -->
                <div class="flex-1 relative bg-white/80 rounded-2xl p-4 shadow-inner">
                    <div id="sitInChart" style="width: 100%; height: 600px; margin: 0 auto;"></div>
                </div>
            </div>
        </div>

        <!-- Announcements Card -->
        <div class="flex-1 min-w-[600px] bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-2xl overflow-hidden h-[900px] backdrop-blur-sm border border-white/30">
            <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(to bottom right, rgb(49, 46, 129), rgb(107, 33, 168), rgb(190, 24, 93))">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                <i class="fas fa-bullhorn text-2xl mr-4 relative z-10"></i>
                <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Announcements</h2>
            </div>
            <div class="p-8 h-[calc(100%-5rem)] flex flex-col">
                <div class="mb-6">
                    <form action="" method="post" class="space-y-4">
                        <textarea 
                            name="new_announcement" 
                            placeholder="Type your announcement here..." 
                            required
                            class="w-full p-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 resize-y min-h-[120px] shadow-inner bg-white/80"
                        ></textarea>
                        <button type="submit" 
                            class="bg-gradient-to-r from-[rgba(74,105,187,1)] to-[rgba(205,77,204,1)] text-white py-3 px-6 rounded-xl hover:shadow-lg transform hover:scale-105 transition-all duration-300 font-medium">
                            <i class="fas fa-paper-plane mr-2"></i>Post Announcement
                        </button>
                    </form>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <h3 class="font-bold text-gray-700 mb-4 text-lg">Posted Announcements</h3>
                    <div class="space-y-4 pr-2">
                        <?php if (empty($announcements)): ?>
                            <p class="text-gray-500 text-center py-4">No announcements available.</p>
                        <?php else: ?>
                            <?php foreach ($announcements as $announcement): ?>
                                <div class="bg-white/80 rounded-xl p-5 shadow-md hover:shadow-lg transition-all duration-300 border-l-4 border-gradient-purple" 
                                     style="border-image: linear-gradient(to bottom, #4A69BB, #CD4DCC) 1;">
                                    <div class="flex items-center text-sm font-bold text-purple-600 mb-3">
                                        <i class="fas fa-user-shield mr-2"></i>
                                        <?php echo htmlspecialchars($announcement['CREATED_BY']); ?>
                                        <span class="mx-2">•</span>
                                        <i class="far fa-calendar-alt mr-2"></i>
                                        <?php echo date('Y-M-d', strtotime($announcement['CREATED_DATE'])); ?>
                                        <div class="ml-auto flex space-x-2">
                                            <button onclick="editAnnouncement(<?php echo $announcement['ID']; ?>, this)" 
                                                    class="text-blue-500 hover:text-blue-700">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a onclick="confirmDelete(<?php echo $announcement['ID']; ?>)" 
                                               class="text-red-500 hover:text-red-700 cursor-pointer">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="text-gray-700 bg-gray-50/80 p-4 rounded-lg announcement-content" 
                                         id="content-<?php echo $announcement['ID']; ?>">
                                        <?php echo htmlspecialchars($announcement['CONTENT']); ?>
                                    </div>
                                    <div class="hidden edit-form" id="edit-<?php echo $announcement['ID']; ?>">
                                        <textarea class="w-full p-4 border border-gray-200 rounded-xl resize-y min-h-[100px]"><?php echo htmlspecialchars($announcement['CONTENT']); ?></textarea>
                                        <div class="mt-3 flex space-x-2">
                                            <button onclick="saveAnnouncement(<?php echo $announcement['ID']; ?>)" 
                                                    class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                                                Save
                                            </button>
                                            <button onclick="cancelEdit(<?php echo $announcement['ID']; ?>)" 
                                                    class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Year Level Chart Section -->
    <div class="px-8 pb-8 w-full">
        <!-- Year Level Chart Card -->
        <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm border border-white/30">
            <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(to bottom right, rgb(49, 46, 129), rgb(107, 33, 168), rgb(190, 24, 93))">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                <i class="fas fa-users text-2xl mr-4 relative z-10"></i>
                <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Students Year Level</h2>
            </div>
            <div class="p-8">
                <!-- Chart Container -->
                <div class="h-[400px] bg-white/80 rounded-2xl p-4 shadow-inner">
                    <div id="yearLevelChart" style="width: 100%; height: 350px; margin: 0 auto;"></div>
                </div>
            </div>
        </div>
    </div>

     <!-- Footer -->
    <div class="py-4 px-6 bg-white/95 backdrop-blur-sm mt-8 relative">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500"></div>
        <p class="text-center text-sm text-gray-600">
            &copy; 2025 CCS Sit-in Monitoring System | <span class="gradient-text font-medium">UC - College of Computer Studies</span>
        </p>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <script>
        function toggleNav(x) {
            document.getElementById("mySidenav").classList.toggle("-translate-x-0");
            document.getElementById("mySidenav").classList.toggle("-translate-x-full");
        }

        function closeNav() {
            document.getElementById("mySidenav").classList.remove("-translate-x-0");
            document.getElementById("mySidenav").classList.add("-translate-x-full");
        }
        
        // Add this function before the existing scripts
        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send AJAX request to delete the announcement
                    fetch(`delete_announcement.php?id=${id}`, {
                        method: 'GET'
                    })
                    .then(response => response.text())
                    .then(() => {
                        // Show success message
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Announcement has been deleted successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Reload the page after clicking OK
                            window.location.reload();
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'There was an error deleting the announcement.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
                }
            });
        }

        // Initialize the charts
        document.addEventListener('DOMContentLoaded', function() {
            // ECharts Nightingale (Rose) Chart for Sit-In distribution
            const sitInChart = echarts.init(document.getElementById('sitInChart'));
            
            // Define colors for each program
            // Define a more diverse color palette
            const colors = [
                '#36A2EB', // Blue
                '#FF6384', // Pink
                '#FFCE56', // Yellow
                '#4BC0C0', // Teal
                '#9966FF', // Purple
                '#FF9F40', // Orange
                '#4CAF50', // Green
                '#E91E63', // Red
                '#2196F3', // Light Blue
                '#FF5722', // Deep Orange
                '#673AB7', // Deep Purple
                '#009688'  // Cyan
            ];

            // Make sure all purposes are included in the legend, even with zero values
            const pieOption = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} ({d}%)'
                },
                legend: {
                    type: 'plain',
                    orient: 'horizontal',
                    bottom: 10,
                    left: 'center',
                    width: '95%',
                    itemGap: 10,
                    itemWidth: 12,
                    itemHeight: 12,
                    textStyle: {
                        fontSize: 11,
                        color: '#666',
                        padding: [0, 4, 0, 4]
                    },
                    formatter: name => {
                        // Split long names into two lines
                        if (name.includes('&')) {
                            return name.replace(' & ', '\n');
                        }
                        return name;
                    },
                    tooltip: {
                        show: true
                    },
                    data: [
                        'C Programming', 'C++ Programming', 'C# Programming',
                        'Java Programming', 'Php Programming', 'Python Programming',
                        'Database', 'Digital Logic & Design', 'Embedded System & IOT',
                        'System Integration & Architecture', 'Computer Application',
                        'Web Design & Development', 'Project Management'
                    ],
                    pageTextStyle: {
                        color: '#666'
                    },
                    selectedMode: false,
                    grid: {
                        left: 10,
                        right: 10,
                        top: 5,
                        bottom: 10
                    }
                },
                grid: {
                    containLabel: true
                },
                title: {
                    text: 'Programming Languages Distribution',
                    left: 'center',
                    top: 20,
                    textStyle: {
                        fontSize: 16,
                        fontWeight: 'bold'
                    }
                },
                series: [{
                    type: 'pie',
                    radius: ['30%', '60%'],
                    center: ['50%', '40%'],  // Moved up more to accommodate legend
                    avoidLabelOverlap: true,
                    itemStyle: {
                        borderRadius: 6,
                        borderColor: '#fff',
                        borderWidth: 2
                    },
                    label: {
                        show: false
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: '14',
                            fontWeight: 'bold',
                            formatter: '{b}\n{d}%'
                        },
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    },
                    data: <?php echo $echartsPieDataJSON; ?>
                }],
                color: colors,
                backgroundColor: 'transparent'
            };

            sitInChart.setOption(pieOption);
            
            // Make the chart responsive
            window.addEventListener('resize', function() {
                sitInChart.resize();
            });
            
            // Replace the Chart.js initialization with ECharts
            const yearLevelChart = echarts.init(document.getElementById('yearLevelChart'));
            const yearLevelOption = {
                xAxis: {
                    type: 'category',
                    data: <?php echo $yearLevelLabelsJSON; ?>,
                    axisLabel: {
                        fontSize: 12,
                        fontWeight: 'bold'
                    }
                },
                yAxis: {
                    type: 'value',
                    name: 'Number of Students',
                    nameLocation: 'middle',
                    nameGap: 40
                },
                series: [
                    {
                        data: <?php echo $yearLevelJSON; ?>,
                        type: 'bar',
                        showBackground: true,
                        backgroundStyle: {
                            color: 'rgba(180, 180, 180, 0.2)'
                        },
                        itemStyle: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                { offset: 0, color: '#83bff6' },
                                { offset: 0.5, color: '#188df0' },
                                { offset: 1, color: '#188df0' }
                            ])
                        }
                    }
                ]
            };

            yearLevelChart.setOption(yearLevelOption);

            // Update resize handler to include yearLevelChart
            window.addEventListener('resize', function() {
                sitInChart.resize();
                yearLevelChart.resize();
            });
        });

        function editAnnouncement(id, button) {
            // Hide content and show edit form
            document.getElementById(`content-${id}`).style.display = 'none';
            document.getElementById(`edit-${id}`).style.display = 'block';
        }

        function cancelEdit(id) {
            // Show content and hide edit form
            document.getElementById(`content-${id}`).style.display = 'block';
            document.getElementById(`edit-${id}`).style.display = 'none';
        }

        function saveAnnouncement(id) {
            const content = document.querySelector(`#edit-${id} textarea`).value;
            
            fetch('update_announcement.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `announcement_id=${id}&content=${encodeURIComponent(content)}`
            })
            .then(response => response.text())
            .then(result => {
                if (result === 'success') {
                    // Update the content display
                    document.getElementById(`content-${id}`).innerText = content;
                    // Hide edit form
                    cancelEdit(id);
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: 'Announcement has been updated successfully.',
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update announcement'
                    });
                }
            });
        }
    </script>
</body>
</html>