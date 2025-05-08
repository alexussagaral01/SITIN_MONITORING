<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$firstName = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'Guest';

if ($userId) {
    $stmt = $conn->prepare("SELECT UPLOAD_IMAGE FROM users WHERE STUD_NUM = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($userImage);
    $stmt->fetch();
    $stmt->close();
    
    $profileImage = !empty($userImage) ? '../images/' . $userImage : "../images/image.jpg";
} else {
    $profileImage = "../images/image.jpg";
}

// Get selected values from GET or use defaults
$selectedLab = isset($_GET['lab']) ? $_GET['lab'] : 'Lab 517';
$selectedDay = isset($_GET['day']) ? $_GET['day'] : 'Monday';

// Format the lab value for display
$displayLab = $selectedLab;
if (strpos($selectedLab, 'Lab') === false) {
    $displayLab = 'Lab ' . $selectedLab;
}

// Fetch schedules from database for the selected lab and day
$query = "SELECT * FROM lab_schedule WHERE LABORATORY = ? AND DAY = ? ORDER BY TIME_START ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $displayLab, $selectedDay);
$stmt->execute();
$result = $stmt->get_result();
$schedules = $result->fetch_all(MYSQLI_ASSOC);
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
    <title>Lab Schedule</title>
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
        
        /* Custom scrollbar for content */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, rgba(74,105,187,0.7), rgba(205,77,204,0.7));
            border-radius: 10px;
        }

        /* Modern card styling */
        .modern-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(to bottom right, #4f46e5, #7e22ce, #be185d);
            border-bottom: none;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: "";
            position: absolute;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -75px;
            right: -75px;
        }
        
        .card-header::after {
            content: "";
            position: absolute;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            bottom: -50px;
            left: -50px;
        }

        /* Completely new table and card styling */
        .schedule-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }
        
        .schedule-table th {
            background: #2563eb;
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 14px 16px;
            border: none;
            white-space: nowrap;
        }
        
        .schedule-table th:first-child {
            border-top-left-radius: 10px;
        }
        
        .schedule-table th:last-child {
            border-top-right-radius: 10px;
        }
        
        .schedule-table tr {
            transition: all 0.2s ease;
        }
        
        .schedule-table tbody tr:nth-child(odd) {
            background-color: rgba(243, 244, 246, 0.5);
        }
        
        .schedule-table tbody tr:hover {
            background-color: rgba(219, 234, 254, 0.7);
        }
        
        .schedule-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        
        /* Prevent text overflow in the table cells */
        .subject-name {
            font-weight: 500;
            color: #1e40af;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 250px;
        }
        
        .subject-desc {
            font-size: 0.875rem;
            color: #6b7280;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 250px;
        }

        /* Lab selector card */
        .lab-selector {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .lab-selector::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 6px;
            height: 100%;
            background: #2563eb;
        }
        
        .lab-selector h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #1e3a8a;
            margin-bottom: 1rem;
        }
        
        /* Lab selection pills */
        .lab-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .lab-pill {
            padding: 0.4rem 0.75rem;
            background-color: #f3f4f6;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #4b5563;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }
        
        .lab-pill:hover {
            background-color: #e5e7eb;
        }
        
        .lab-pill.active {
            background-color: #2563eb;
            color: white;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);
        }

        /* Day selector card */
        .day-selector {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .day-selector::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 6px;
            height: 100%;
            background: #8b5cf6;
        }
        
        .day-selector h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #5b21b6;
            margin-bottom: 1rem;
        }
        
        /* Calendar style day selection */
        .calendar-days {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
        }
        
        .day-item {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.75rem 0.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .day-item:hover {
            border-color: #c7d2fe;
            background-color: #f5f3ff;
        }
        
        .day-item.active {
            background-color: #8b5cf6;
            color: white;
            border-color: #8b5cf6;
            box-shadow: 0 2px 4px rgba(139, 92, 246, 0.3);
        }
        
        .day-name {
            font-weight: 600;
            font-size: 0.875rem;
            display: block;
        }

        /* Current selection badge */
        .selection-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.75rem;
            background-color: #f3f4f6;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #4b5563;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .selection-badge i {
            margin-right: 0.375rem;
            color: #6b7280;
        }

        /* Schedule controls - grid layout */
        .schedule-controls {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        @media (min-width: 768px) {
            .schedule-controls {
                grid-template-columns: repeat(2, 1fr);
            }
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
        <!-- Removed the bell notification icon from here -->
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
                <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile" class="w-20 h-20 rounded-full border-4 border-white/30 object-cover shadow-lg">
                <div class="absolute bottom-0 right-0 bg-green-500 w-3 h-3 rounded-full border-2 border-white"></div>
            </div>
            <p class="text-white font-semibold text-lg mt-2 mb-0"><?php echo htmlspecialchars($firstName); ?></p>
            <p class="text-purple-200 text-xs mb-3">Student</p>
        </div>

        <div class="px-2 py-2">
            <nav class="flex flex-col space-y-1">
                <a href="dashboard.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-home w-5 mr-2 text-center"></i>
                    <span class="font-medium">HOME</span>
                </a>
                <a href="profile.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-user w-5 mr-2 text-center"></i>
                    <span class="font-medium">PROFILE</span>
                </a>
                <a href="edit.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-edit w-5 mr-2 text-center"></i>
                    <span class="font-medium">EDIT</span>
                </a>
                <a href="history.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-history w-5 mr-2 text-center"></i>
                    <span class="font-medium">HISTORY</span>
                </a>
                
                <!-- VIEW Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full group px-3 py-2 text-white/90 bg-white/20 rounded-lg transition-all duration-200 flex items-center justify-between">
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
                        
                        <a href="lab_resources.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-desktop w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Resources</span>
                        </a>
                        
                        <a href="lab_schedule.php" class="group px-3 py-2 text-white/90 bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-calendar-week w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Schedule</span>
                        </a>
                    </div>
                </div>

                <a href="reservation.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-calendar-alt w-5 mr-2 text-center"></i>
                    <span class="font-medium">RESERVATION</span>
                </a>
                <div class="border-t border-white/10 my-2"></div>
                <a href="../logout.php" class="group px-3 py-2 text-white/90 hover:bg-red-500/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-sign-out-alt w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">LOG OUT</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="container mx-auto px-4 py-8">
        <div class="modern-card">
            <div class="card-header text-white p-5 flex items-center justify-center relative overflow-hidden">
                <i class="fas fa-calendar-alt text-2xl mr-3 relative z-10"></i>
                <h2 class="text-xl font-bold tracking-wider uppercase relative z-10 font-sans">Laboratory Schedule</h2>
            </div>
            
            <div class="p-6">
                <!-- Schedule controls - grid layout -->
                <div class="schedule-controls">
                    <!-- Laboratory selection card -->
                    <div class="lab-selector">
                        <h3>Select Laboratory</h3>
                        <div class="lab-pills">
                            <button class="lab-pill <?php echo $selectedLab == 'Lab 517' || $selectedLab == '517' ? 'active' : ''; ?>" onclick="selectLab('517')">517</button>
                            <button class="lab-pill <?php echo $selectedLab == 'Lab 524' || $selectedLab == '524' ? 'active' : ''; ?>" onclick="selectLab('524')">524</button>
                            <button class="lab-pill <?php echo $selectedLab == 'Lab 526' || $selectedLab == '526' ? 'active' : ''; ?>" onclick="selectLab('526')">526</button>
                            <button class="lab-pill <?php echo $selectedLab == 'Lab 528' || $selectedLab == '528' ? 'active' : ''; ?>" onclick="selectLab('528')">528</button>
                            <button class="lab-pill <?php echo $selectedLab == 'Lab 530' || $selectedLab == '530' ? 'active' : ''; ?>" onclick="selectLab('530')">530</button>
                            <button class="lab-pill <?php echo $selectedLab == 'Lab 542' || $selectedLab == '542' ? 'active' : ''; ?>" onclick="selectLab('542')">542</button>
                        </div>
                    </div>
                    
                    <!-- Day selection card -->
                    <div class="day-selector">
                        <h3>Select Day</h3>
                        <div class="calendar-days">
                            <div class="day-item <?php echo $selectedDay == 'Monday' ? 'active' : ''; ?>" onclick="selectDay('Monday')">
                                <span class="day-name">Mon</span>
                            </div>
                            <div class="day-item <?php echo $selectedDay == 'Tuesday' ? 'active' : ''; ?>" onclick="selectDay('Tuesday')">
                                <span class="day-name">Tue</span>
                            </div>
                            <div class="day-item <?php echo $selectedDay == 'Wednesday' ? 'active' : ''; ?>" onclick="selectDay('Wednesday')">
                                <span class="day-name">Wed</span>
                            </div>
                            <div class="day-item <?php echo $selectedDay == 'Thursday' ? 'active' : ''; ?>" onclick="selectDay('Thursday')">
                                <span class="day-name">Thu</span>
                            </div>
                            <div class="day-item <?php echo $selectedDay == 'Friday' ? 'active' : ''; ?>" onclick="selectDay('Friday')">
                                <span class="day-name">Fri</span>
                            </div>
                            <div class="day-item <?php echo $selectedDay == 'Saturday' ? 'active' : ''; ?>" onclick="selectDay('Saturday')">
                                <span class="day-name">Sat</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Current Selection -->
                <div class="mb-6">
                    <span class="selection-badge">
                        <i class="fas fa-map-marker-alt"></i>
                        Laboratory <?php echo str_replace('Lab ', '', $displayLab); ?>
                    </span>
                    <span class="selection-badge">
                        <i class="fas fa-calendar-day"></i>
                        <?php echo $selectedDay; ?>
                    </span>
                </div>
                
                <!-- Schedule Table -->
                <div class="mt-6 overflow-auto">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th width="25%">Time Slot</th>
                                <th width="45%">Course Details</th>
                                <th width="30%">Professor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($schedules) > 0): ?>
                                <?php foreach ($schedules as $schedule): ?>
                                    <tr>
                                        <td>
                                            <div class="flex items-center">
                                                <i class="far fa-clock text-blue-600 mr-2"></i>
                                                <span>
                                                    <?php 
                                                        $startTime = date('g:i A', strtotime($schedule['TIME_START']));
                                                        $endTime = date('g:i A', strtotime($schedule['TIME_END']));
                                                        echo $startTime . ' - ' . $endTime; 
                                                    ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="subject-name"><?php echo htmlspecialchars($schedule['SUBJECT']); ?></div>
                                                <div class="subject-desc">Course Code</div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex items-center">
                                                <?php 
                                                    // Generate initials for the professor
                                                    $nameParts = explode(' ', $schedule['PROFESSOR']);
                                                    $initials = '';
                                                    foreach ($nameParts as $part) {
                                                        if (!empty($part)) {
                                                            $initials .= strtoupper(substr($part, 0, 1));
                                                        }
                                                    }
                                                    
                                                    // Generate a consistent color based on the professor's name
                                                    $colors = ['blue', 'green', 'purple', 'orange', 'teal', 'pink', 'indigo'];
                                                    $colorIndex = crc32($schedule['PROFESSOR']) % count($colors);
                                                    $color = $colors[$colorIndex];
                                                    
                                                    $bgClass = "bg-" . $color . "-100";
                                                    $textClass = "text-" . $color . "-700";
                                                ?>
                                                <div class="w-8 h-8 rounded-full <?php echo $bgClass; ?> flex items-center justify-center mr-3">
                                                    <span class="<?php echo $textClass; ?> font-medium"><?php echo $initials; ?></span>
                                                </div>
                                                <?php echo htmlspecialchars($schedule['PROFESSOR']); ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-6 text-gray-500 italic">No schedules found for this day and laboratory</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 text-sm text-gray-500">
                    <p>Note: Schedule may change without prior notice. Please check regularly for updates.</p>
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
    
    <script>
        function toggleNav(x) {
            document.getElementById("mySidenav").classList.toggle("-translate-x-0");
            document.getElementById("mySidenav").classList.toggle("-translate-x-full");
        }

        function closeNav() {
            document.getElementById("mySidenav").classList.remove("-translate-x-0");
            document.getElementById("mySidenav").classList.add("-translate-x-full");
        }
        
        // Functions to handle lab and day selection
        function selectLab(lab) {
            const currentDay = new URLSearchParams(window.location.search).get('day') || 'Monday';
            window.location.href = `lab_schedule.php?lab=${lab}&day=${currentDay}`;
        }
        
        function selectDay(day) {
            const currentLab = new URLSearchParams(window.location.search).get('lab') || '517';
            window.location.href = `lab_schedule.php?lab=${currentLab}&day=${day}`;
        }
    </script>
</body>
</html>
