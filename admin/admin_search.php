<?php
session_start();
require '../db.php';

// Check if admin is not logged in, redirect to login page
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Add time-in handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['time_in'])) {
    $idno = $_POST['idno'];
    $fullName = $_POST['full_name'];
    $purpose = $_POST['purpose'];
    $laboratory = $_POST['laboratory'];
    
    // Check if student already has an active session
    $checkStmt = $conn->prepare("SELECT * FROM curr_sitin WHERE IDNO = ? AND STATUS = 'Active'");
    $checkStmt->bind_param("i", $idno);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // Store error message for SweetAlert
        $_SESSION['swal_error'] = "Student already has an active sit-in session.";
    } else {
        // Insert new sit-in record
        $stmt = $conn->prepare("INSERT INTO curr_sitin (IDNO, FULL_NAME, PURPOSE, LABORATORY, TIME_IN, DATE, STATUS) VALUES (?, ?, ?, ?, NOW(), CURDATE(), 'Active')");
        $stmt->bind_param("isss", $idno, $fullName, $purpose, $laboratory);
        
        if ($stmt->execute()) {
            $_SESSION['swal_success'] = "Time-in recorded successfully.";
            header("Location: admin_search.php");
            exit();
        } else {
            $_SESSION['swal_error'] = "Error recording time-in.";
        }
    }
}

// Only fetch student data when search button is clicked via POST
$student = null;
$searched = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search']) && !empty($_POST['search'])) {
    $searched = true;
    $stmt = $conn->prepare("SELECT * FROM users WHERE IDNO = ?");
    $stmt->bind_param("s", $_POST['search']);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Search</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../logo/ccs.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <a href="admin_dashboard.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-home w-5 mr-2 text-center"></i>
                    <span class="font-medium">HOME</span>
                </a>
                <a href="admin_search.php" class="group px-3 py-2 text-white/90 bg-white/20 rounded-lg transition-all duration-200 flex items-center">
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
        <div class="flex-1 min-w-[400px] bg-white rounded-xl shadow-lg overflow-hidden h-[700px] border border-[rgba(255,255,255,1)]">
        <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(to bottom right, rgb(49, 46, 129), rgb(107, 33, 168), rgb(190, 24, 93))">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                <i class="fas fa-search text-2xl mr-4 relative z-10"></i>
                <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Search Student</h2>
            </div>
            <div class="p-6 h-[calc(100%-4rem)] flex flex-col">
                <div class="mb-6">
                    <form method="POST" action="" class="space-y-3">
                        <div class="flex gap-2">
                            <input type="text" name="search" placeholder="Enter ID Number..." 
                                   class="flex-1 p-3 border border-gray-300 rounded-lg">
                            <button type="submit" class="relative inline-flex items-center justify-center overflow-hidden rounded-lg group bg-gradient-to-br from-purple-600 to-blue-500 p-0.5 text-sm font-medium hover:text-white">
                            <span class="relative rounded-md bg-white px-8 py-3 transition-all duration-300 ease-in-out group-hover:bg-opacity-0 text-purple-700 font-bold group-hover:text-white">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Student Results Area -->
                <div class="flex-1 overflow-y-auto">
                    <div class="space-y-6 pr-2">
                        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $searched): ?>
                            <?php if ($student): ?>
                                <!-- Student Card -->
                                <div class="bg-gradient-to-br from-white to-gray-50 p-8 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border-l-4 border-purple-500">
                                    <!-- Student Header -->
                                    <div class="flex flex-col lg:flex-row items-center gap-8">
                                        <div class="relative group">
                                            <?php if (!empty($student['UPLOAD_IMAGE'])): ?>
                                                <img src="../images/<?php echo htmlspecialchars($student['UPLOAD_IMAGE']); ?>" 
                                                     alt="Student Photo"
                                                     class="w-32 h-32 rounded-2xl object-cover shadow-md group-hover:scale-105 transition-transform duration-300"
                                                     onerror="this.src='../images/image.jpg'">
                                            <?php else: ?>
                                                <img src="../images/image.jpg"
                                                     alt="Default Photo"
                                                     class="w-32 h-32 rounded-2xl object-cover shadow-md group-hover:scale-105 transition-transform duration-300">
                                            <?php endif; ?>
                                            <div class="absolute -bottom-2 -right-2 bg-green-400 rounded-full p-2">
                                                <i class="fas fa-check text-white"></i>
                                            </div>
                                        </div>

                                        <div class="flex-1 text-center lg:text-left">
                                            <h3 class="text-2xl font-bold text-gray-800 mb-2">
                                                <?php echo htmlspecialchars($student['FIRST_NAME'] . ' ' . $student['LAST_NAME']); ?>
                                            </h3>
                                            <div class="inline-block bg-purple-100 text-purple-700 px-4 py-1 rounded-full font-medium mb-2">
                                                <?php echo htmlspecialchars($student['IDNO']); ?>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                                <div class="bg-blue-50 p-3 rounded-xl">
                                                    <p class="text-blue-600 text-sm font-medium">Course</p>
                                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($student['COURSE']); ?></p>
                                                </div>
                                                <div class="bg-purple-50 p-3 rounded-xl">
                                                    <p class="text-purple-600 text-sm font-medium">Year Level</p>
                                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($student['YEAR_LEVEL']); ?></p>
                                                </div>
                                                <div class="bg-pink-50 p-3 rounded-xl">
                                                    <p class="text-pink-600 text-sm font-medium">Email</p>
                                                    <p class="font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($student['EMAIL']); ?></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="w-full lg:w-48 bg-white p-4 rounded-xl shadow-sm">
                                            <div class="text-center mb-3">
                                                <p class="text-sm text-gray-600">Sessions Remaining</p>
                                                <p class="text-3xl font-bold text-purple-600"><?php echo htmlspecialchars($student['SESSION']); ?></p>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 mb-1">
                                                <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-2.5 rounded-full transition-all duration-500" 
                                                     style="width: <?php echo ($student['SESSION'] / 30) * 100; ?>%"></div>
                                            </div>
                                            <p class="text-xs text-gray-500 text-center">
                                                <?php echo htmlspecialchars($student['SESSION']); ?>/30 sessions
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Register Session Section -->
                                    <div class="mt-8 pt-6 border-t border-gray-200">
                                        <h4 class="text-lg font-semibold mb-4 flex items-center text-purple-700">
                                            <i class="fas fa-clipboard-list mr-2"></i>
                                            New Sit-In Session Registration
                                        </h4>

                                        <form method="POST" action="" class="space-y-4">
                                            <input type="hidden" name="idno" value="<?php echo htmlspecialchars($student['IDNO']); ?>">
                                            <input type="hidden" name="full_name" value="<?php echo htmlspecialchars($student['FIRST_NAME'] . ' ' . $student['LAST_NAME']); ?>">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div class="relative">
                                                    <select name="purpose" class="w-full p-3 bg-gray-50 border border-gray-300 rounded-xl appearance-none cursor-pointer hover:border-purple-500 transition-colors" required>
                                                        <option value=""disabled selected>Select Purpose</option>
                                                        <option value="C Programming">C Programming</option>
                                                        <option value="C++ Programming">C++ Programming</option>
                                                        <option value="C# Programming">C# Programming</option>
                                                        <option value="Java Programming">Java Programming</option>
                                                        <option value="Python Programming">Python Programming</option>
                                                        <option value="Other">Other</option>
                                                    </select>
                                                    <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none text-gray-500">
                                                        <i class="fas fa-chevron-down"></i>
                                                    </div>
                                                </div>
                                                <div class="relative">
                                                    <select name="laboratory" class="w-full p-3 bg-gray-50 border border-gray-300 rounded-xl appearance-none cursor-pointer hover:border-purple-500 transition-colors" required>
                                                        <option value=""disabled selected>Select Laboratory</option>
                                                        <option value="Lab 524">Lab 524</option>
                                                        <option value="Lab 526">Lab 526</option>
                                                        <option value="Lab 528">Lab 528</option>
                                                        <option value="Lab 530">Lab 530</option>
                                                        <option value="Lab 542">Lab 542</option>
                                                        <option value="Lab 544">Lab 544</option>
                                                    </select>
                                                    <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none text-gray-500">
                                                        <i class="fas fa-chevron-down"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex justify-end">
                                                <button type="submit" name="time_in"class="relative inline-flex items-center justify-center overflow-hidden rounded-lg group bg-gradient-to-br from-purple-600 to-blue-500 p-0.5 text-sm font-medium hover:text-white">
                                                <span class="relative rounded-md bg-white px-8 py-3 transition-all duration-300 ease-in-out group-hover:bg-opacity-0 text-purple-700 font-bold group-hover:text-white">
                                                    <i class="fas fa-clock"></i>
                                                    Time - In
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <div class="bg-red-50 p-6 rounded-xl inline-block">
                                        <i class="fas fa-user-times text-4xl text-red-400 mb-3"></i>
                                        <p class="text-gray-600">No student found with ID Number: <span class="font-semibold"><?php echo htmlspecialchars($_POST['search']); ?></span></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

        // SweetAlert for success and error messages
        <?php if(isset($_SESSION['swal_error'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '<?php echo $_SESSION['swal_error']; unset($_SESSION['swal_error']); ?>',
            background: '#f3f4f6',
            customClass: {
                popup: 'rounded-lg',
                title: 'text-xl font-bold text-gray-800',
                content: 'text-gray-600'
            }
        });
        <?php endif; ?>

        <?php if(isset($_SESSION['swal_success'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?php echo $_SESSION['swal_success']; unset($_SESSION['swal_success']); ?>',
            background: '#f3f4f6',
            customClass: {
                popup: 'rounded-lg',
                title: 'text-xl font-bold text-gray-800',
                content: 'text-gray-600'
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>