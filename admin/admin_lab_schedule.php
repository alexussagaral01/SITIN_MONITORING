<?php
session_start();
require '../db.php'; // Add database connection

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit;
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Add the external CSS file link if needed -->
    <link rel="stylesheet" href="../css/admin_lab_schedule.css">
    <title>Lab Schedule</title>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif']
                    },
                    colors: {
                        'lab-purple': '#7c3aed',
                        'lab-purple-light': '#8b5cf6',
                        'lab-purple-dark': '#6d28d9'
                    }
                }
            }
        }
    </script>
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
                <a href="admin_dashboard.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
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
                        
                        <a href="admin_lab_schedule.php" class="group px-3 py-2 text-white/90 bg-white/20 rounded-lg transition-all duration-200 flex items-center">
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

                <a href="admin_reservation.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
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

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="modern-card">
            <div class="card-header text-white p-5 flex items-center justify-center relative overflow-hidden">
                <i class="fas fa-desktop text-2xl mr-3 relative z-10"></i>
                <h2 class="text-xl font-bold tracking-wider uppercase relative z-10 font-sans">Laboratory Management</h2>
            </div>
            
            <div class="p-6">
                <!-- Search section -->
                <div class="search-section">
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Search for subjects, professors, or time slots...">
                    </div>
                </div>
                
                <!-- Schedule controls - new grid layout -->
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
                    
                    <!-- Schedule actions card -->
                    <div class="schedule-actions">
                        <h3>Schedule Actions</h3>
                        <p class="text-sm text-gray-500 mb-3">Manage laboratory schedules and availability</p>
                        <div class="action-buttons">
                            <button class="btn-action btn-primary" id="openScheduleModal">
                                <i class="fas fa-plus-circle"></i>
                                Add New Schedule
                            </button>
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
                
                <!-- Schedule Table - Redesigned -->
                <div class="mt-6 overflow-fix">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th width="20%">Time Slot</th>
                                <th width="40%">Course Details</th>
                                <th width="25%">Professor</th>
                                <th width="15%" class="text-center">Actions</th>
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
                                                <div class="subject-desc">
                                                    <?php 
                                                        // You can add a description field to your table or use a placeholder
                                                        echo "Course Code"; 
                                                    ?>
                                                </div>
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
                                                    
                                                    $bgColorClass = "bg-{$color}-100";
                                                    $textColorClass = "text-{$color}-700";
                                                ?>
                                                <div class="w-8 h-8 rounded-full <?php echo $bgColorClass; ?> flex items-center justify-center mr-3">
                                                    <span class="<?php echo $textColorClass; ?> font-medium"><?php echo $initials; ?></span>
                                                </div>
                                                <?php echo htmlspecialchars($schedule['PROFESSOR']); ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="flex justify-center space-x-2">
                                                <button class="btn-table-action btn-edit" title="Edit" onclick="editSchedule(<?php echo $schedule['SCHED_ID']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn-table-action btn-delete" title="Delete" onclick="confirmDeleteSchedule(<?php echo $schedule['SCHED_ID']; ?>)">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-6 text-gray-500 italic">No schedules found for this day and laboratory</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Schedule Modal with left and right columns -->
    <div id="scheduleModal" class="modal">
        <div class="modal-content">
            <button type="button" class="modal-close" id="closeScheduleModal">&times;</button>
            
            <div class="modal-columns">
                <!-- Left Column -->
                <div class="modal-left">
                    <h3 class="modal-title">Add New Schedule</h3>
                    <p class="modal-subtitle">Create a new laboratory class schedule with the details on the right.</p>
                    
                    <div class="feature-list">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <span>Select day of the week</span>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-laptop-code"></i>
                            </div>
                            <span>Choose laboratory room</span>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <span>Set time duration</span>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <span>Add subject details</span>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <span>Assign professor</span>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Form -->
                <div class="modal-right">
                    <form id="scheduleForm" action="add_schedule.php" method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="day" class="form-label">Day of Week</label>
                                <div class="input-icon select-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                    <select id="day" name="day" class="form-control form-select" required>
                                        <option value="" disabled selected>Select Day</option>
                                        <option value="Monday">Monday</option>
                                        <option value="Tuesday">Tuesday</option>
                                        <option value="Wednesday">Wednesday</option>
                                        <option value="Thursday">Thursday</option>
                                        <option value="Friday">Friday</option>
                                        <option value="Saturday">Saturday</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="laboratory" class="form-label">Laboratory</label>
                                <div class="input-icon select-icon">
                                    <i class="fas fa-desktop"></i>
                                    <select id="laboratory" name="laboratory" class="form-control form-select" required>
                                        <option value="" disabled selected>Select Laboratory</option>
                                        <option value="Lab 517">Lab 517</option>
                                        <option value="Lab 524">Lab 524</option>
                                        <option value="Lab 526">Lab 526</option>
                                        <option value="Lab 528">Lab 528</option>
                                        <option value="Lab 530">Lab 530</option>
                                        <option value="Lab 542">Lab 542</option>
                                        <option value="Lab 544">Lab 544</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Schedule Time</label>
                            <div class="form-row">
                                <div>
                                    <div class="input-icon">
                                        <i class="fas fa-hourglass-start"></i>
                                        <input type="time" id="time_start" name="time_start" class="form-control" required placeholder="Start Time">
                                    </div>
                                </div>
                                <div>
                                    <div class="input-icon">
                                        <i class="fas fa-hourglass-end"></i>
                                        <input type="time" id="time_end" name="time_end" class="form-control" required placeholder="End Time">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject" class="form-label">Subject</label>
                            <div class="input-icon">
                                <i class="fas fa-book-open"></i>
                                <input type="text" id="subject" name="subject" class="form-control" placeholder="Enter subject name" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="professor" class="form-label">Professor</label>
                            <div class="input-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <input type="text" id="professor" name="professor" class="form-control" placeholder="Enter professor name" required>
                            </div>
                        </div>
                        
                        <div class="btn-row">
                            <button type="button" class="btn btn-cancel" id="cancelScheduleModal">
                                <i class="fas fa-times btn-icon"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save btn-icon"></i>Save Schedule
                            </button>
                        </div>
                    </form>
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
            
            x.classList.toggle("change");
            if (x.classList.contains("change")) {
                x.querySelector(".bar1").classList.add("rotate-45", "translate-y-2");
                x.querySelector(".bar2").classList.add("opacity-0");
                x.querySelector(".bar3").classList.add("-rotate-45", "-translate-y-2");
            } else {
                x.querySelector(".bar1").classList.remove("rotate-45", "translate-y-2");
                x.querySelector(".bar2").classList.remove("opacity-0");
                x.querySelector(".bar3").classList.remove("-rotate-45", "-translate-y-2");
            }
        }

        function closeNav() {
            document.getElementById("mySidenav").classList.remove("-translate-x-0");
            document.getElementById("mySidenav").classList.add("-translate-x-full");
        }
        
        // Show success message after adding or editing schedule
        function showSuccessMessage(message) {
            Swal.fire({
                toast: true,
                icon: 'success',
                title: message,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    popup: 'colored-toast'
                }
            });
        }
        
        // Show error message
        function showErrorMessage(message) {
            Swal.fire({
                toast: true,
                icon: 'error',
                title: message,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    popup: 'colored-toast'
                }
            });
        }
        
        // Confirm delete schedule
        function confirmDeleteSchedule(scheduleId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6d28d9',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Handle delete action here
                    // You would typically send an AJAX request to delete the schedule
                    // Then show success message
                    showSuccessMessage('Schedule has been deleted!');
                }
            });
        }
        
        // Modal functions
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('scheduleModal');
            const openModalBtn = document.getElementById('openScheduleModal');
            const closeModalBtn = document.getElementById('closeScheduleModal');
            const cancelModalBtn = document.getElementById('cancelScheduleModal');
            const scheduleForm = document.getElementById('scheduleForm');
            
            // Make sure modal is hidden on load
            modal.style.display = 'none';
            
            // Open modal
            openModalBtn.addEventListener('click', function() {
                modal.classList.add('active');
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden'; // Prevent scrolling behind modal
            });
            
            // Close modal on X button
            closeModalBtn.addEventListener('click', function() {
                modal.classList.remove('active');
                modal.style.display = 'none';
                document.body.style.overflow = 'auto'; // Enable scrolling again
            });
            
            // Close modal on Cancel button
            cancelModalBtn.addEventListener('click', function() {
                modal.classList.remove('active');
                modal.style.display = 'none';
                document.body.style.overflow = 'auto'; // Enable scrolling again
            });
            
            // Close modal when clicking outside the modal content
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.classList.remove('active');
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto'; // Enable scrolling again
                }
            });
            
            // Handle form submission
            scheduleForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form data
                const formData = new FormData(this);
                
                // Send AJAX request
                fetch('add_schedule.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal
                        modal.classList.remove('active');
                        modal.style.display = 'none';
                        document.body.style.overflow = 'auto';
                        
                        // Show success message
                        Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: data.message || 'Schedule added successfully',
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            customClass: {
                                popup: 'colored-toast'
                            }
                        });
                        
                        // Reset the form
                        scheduleForm.reset();
                        
                        // Refresh the page after a short delay to show the new schedule
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        // Show error message
                        Swal.fire({
                            toast: true,
                            icon: 'error',
                            title: data.message || 'Failed to add schedule',
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            customClass: {
                                popup: 'colored-toast'
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        toast: true,
                        icon: 'error',
                        title: 'An error occurred while adding the schedule',
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'colored-toast'
                        }
                    });
                });
            });
        });

        // Functions to handle lab and day selection
        function selectLab(lab) {
            const currentDay = new URLSearchParams(window.location.search).get('day') || 'Monday';
            window.location.href = `admin_lab_schedule.php?lab=${lab}&day=${currentDay}`;
        }
        
        function selectDay(day) {
            const currentLab = new URLSearchParams(window.location.search).get('lab') || '517';
            window.location.href = `admin_lab_schedule.php?lab=${currentLab}&day=${day}`;
        }
        
        // Function to edit an existing schedule
        function editSchedule(scheduleId) {
            // Fetch schedule details with AJAX
            fetch(`get_schedule.php?id=${scheduleId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const schedule = data.schedule;
                        
                        // Fill the form with existing data
                        document.getElementById('day').value = schedule.DAY;
                        document.getElementById('laboratory').value = schedule.LABORATORY;
                        document.getElementById('time_start').value = schedule.TIME_START;
                        document.getElementById('time_end').value = schedule.TIME_END;
                        document.getElementById('subject').value = schedule.SUBJECT;
                        document.getElementById('professor').value = schedule.PROFESSOR;
                        
                        // Add schedule ID to form for update operation
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'schedule_id';
                        hiddenInput.value = scheduleId;
                        document.getElementById('scheduleForm').appendChild(hiddenInput);
                        
                        // Change form action to update
                        document.getElementById('scheduleForm').action = 'update_schedule.php';
                        
                        // Update modal title
                        document.querySelector('.modal-title').textContent = 'Edit Schedule';
                        
                        // Update button text
                        document.querySelector('.btn-primary').innerHTML = '<i class="fas fa-save btn-icon"></i>Update Schedule';
                        
                        // Show modal
                        const modal = document.getElementById('scheduleModal');
                        modal.classList.add('active');
                        modal.style.display = 'flex';
                        document.body.style.overflow = 'hidden';
                    } else {
                        showErrorMessage(data.message || 'Failed to load schedule details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorMessage('An error occurred while loading schedule details');
                });
        }
        
        // When canceling or closing the modal, reset the form
        document.addEventListener('DOMContentLoaded', function() {
            const closeModalBtn = document.getElementById('closeScheduleModal');
            const cancelModalBtn = document.getElementById('cancelScheduleModal');
            
            function resetForm() {
                document.getElementById('scheduleForm').reset();
                document.getElementById('scheduleForm').action = 'add_schedule.php';
                document.querySelector('.modal-title').textContent = 'Add New Schedule';
                document.querySelector('.btn-primary').innerHTML = '<i class="fas fa-save btn-icon"></i>Save Schedule';
                
                // Remove any hidden schedule ID field
                const hiddenInput = document.querySelector('input[name="schedule_id"]');
                if (hiddenInput) {
                    hiddenInput.remove();
                }
            }
            
            closeModalBtn.addEventListener('click', resetForm);
            cancelModalBtn.addEventListener('click', resetForm);
            
            // Reset the form when opening the modal for a new schedule
            document.getElementById('openScheduleModal').addEventListener('click', resetForm);
        });
        
        // Delete schedule function
        function confirmDeleteSchedule(scheduleId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6d28d9',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send delete request via AJAX
                    fetch('delete_schedule.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `schedule_id=${scheduleId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccessMessage('Schedule has been deleted!');
                            // Reload page after a delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            showErrorMessage(data.message || 'Failed to delete schedule');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showErrorMessage('An error occurred while deleting the schedule');
                    });
                }
            });
        }
    </script>
</body>
</html>