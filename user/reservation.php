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
    $stmt = $conn->prepare("SELECT UPLOAD_IMAGE, IDNO, FIRST_NAME, LAST_NAME FROM users WHERE STUD_NUM = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($userImage, $idNumber, $firstName, $lastName);
    $stmt->fetch();
    $stmt->close();
    
    $profileImage = !empty($userImage) ? '../images/' . $userImage : "../images/image.jpg";
    $studentName = $firstName . ' ' . $lastName;
} else {
    $profileImage = "../images/image.jpg";
    $idNumber = '';
    $studentName = 'Guest';
}

// Process reservation form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $purpose = isset($_POST['purpose']) ? $_POST['purpose'] : '';
    $lab = isset($_POST['lab']) ? $_POST['lab'] : '';
    $date = isset($_POST['date']) ? $_POST['date'] : '';
    $timeIn = isset($_POST['time_in']) ? $_POST['time_in'] : '';
    $pcNumber = isset($_POST['available_pc']) ? $_POST['available_pc'] : '';
    
    // Validate and insert reservation
    if (!empty($purpose) && !empty($lab) && !empty($date) && !empty($timeIn) && !empty($pcNumber)) {
        $stmt = $conn->prepare("INSERT INTO reservations (student_id, student_name, purpose, lab_number, reservation_date, time_in, pc_number) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssi", $userId, $studentName, $purpose, $lab, $date, $timeIn, $pcNumber);
        
        if ($stmt->execute()) {
            // Success message
            $successMessage = "Reservation confirmed successfully!";
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $errorMessage = "All fields are required";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../logo/ccs.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
    <title>Reservation</title>
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
                <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile" class="w-20 h-20 rounded-full border-4 border-white/30 object-cover shadow-lg">
                <div class="absolute bottom-0 right-0 bg-green-500 w-3 h-3 rounded-full border-2 border-white"></div>
            </div>
            <p class="text-white font-semibold text-lg mt-2 mb-0"><?php echo htmlspecialchars($firstName); ?></p>
            <p class="text-purple-200 text-xs mb-3">Student</p>
        </div>

        <div class="px-2 py-2">
            <nav class="flex flex-col space-y-1">
                <a href="dashboard.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-home w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">HOME</span>
                </a>
                <a href="profile.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-user w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">PROFILE</span>
                </a>
                <a href="edit.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-edit w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">EDIT</span>
                </a>
                <a href="history.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-history w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">HISTORY</span>
                </a>
                
                <!-- VIEW Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center justify-between">
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
                        
                        <a href="lab_resource.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-desktop w-5 mr-2 text-center"></i>
                            <span class="font-medium group-hover:translate-x-1 transition-transform">Lab Resource</span>
                        </a>
                        
                        <a href="lab_schedule.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-calendar-week w-5 mr-2 text-center"></i>
                            <span class="font-medium group-hover:translate-x-1 transition-transform">Lab Schedule</span>
                        </a>
                    </div>
                </div>

                <a href="reservation.php" class="group px-3 py-2 text-white/90 bg-white/20 rounded-lg transition-all duration-200 flex items-center">
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

    <!-- Main Content: Reservation & PC Selection -->
    <div class="container mx-auto p-4">
        <?php if (isset($successMessage)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                <p class="font-medium"><?php echo $successMessage; ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errorMessage)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                <p class="font-medium"><?php echo $errorMessage; ?></p>
            </div>
        <?php endif; ?>
        
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Reservation Form -->
            <div class="w-full md:w-1/2"> <!-- Reservation container -->
                <div class="w-full bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
                    <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(to bottom right, rgb(49, 46, 129), rgb(107, 33, 168), rgb(190, 24, 93))">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                        <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                        <i class="fas fa-calendar-alt text-2xl mr-4 relative z-10"></i>
                        <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Make a Reservation</h2>
                    </div>
                    <form action="reservation.php" method="post" class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Student ID -->
                            <div class="flex items-center gap-4">
                                <i class="fas fa-id-card text-purple-500"></i>
                                <input type="text" id="id_number" name="id_number" value="<?php echo htmlspecialchars($idNumber); ?>" 
                                    class="flex-1 p-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed focus:ring-2 focus:ring-purple-500/50" readonly placeholder="Student ID">
                            </div>
                            <!-- Full Name -->
                            <div class="flex items-center gap-4">
                                <i class="fas fa-user text-purple-500"></i>
                                <input type="text" id="student_name" name="student_name" value="<?php echo htmlspecialchars($studentName); ?>" 
                                    class="flex-1 p-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed focus:ring-2 focus:ring-purple-500/50" readonly placeholder="Full Name">
                            </div>
                            <!-- Course -->
                            <div class="flex items-center gap-4">
                                <i class="fas fa-graduation-cap text-purple-500"></i>
                                <input type="text" id="course" name="course" value="BS-Information Technology" 
                                    class="flex-1 p-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed focus:ring-2 focus:ring-purple-500/50" readonly placeholder="Course">
                            </div>
                            <!-- Year Level -->
                            <div class="flex items-center gap-4">
                                <i class="fas fa-calendar-alt text-purple-500"></i>
                                <input type="text" id="year_level" name="year_level" value="3rd Year" 
                                    class="flex-1 p-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed focus:ring-2 focus:ring-purple-500/50" readonly placeholder="Year Level">
                            </div>
                            <!-- Purpose -->
                            <div class="flex items-center gap-4">
                                <i class="fas fa-tasks text-purple-500"></i>
                                <select id="purpose" name="purpose" required
                                    class="flex-1 p-3 border border-gray-300 rounded-lg bg-white hover:border-purple-400 focus:ring-2 focus:ring-purple-500/50 transition-colors duration-200">
                                    <option value="" disabled selected>Select Purpose</option>
                                    <option value="C Programming">C Programming</option>
                                    <option value="C++ Programming">C++ Programming</option>
                                    <option value="C# Programming">C# Programming</option>
                                    <option value="Java Programming">Java Programming</option>
                                    <option value="Python Programming">Python Programming</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <!-- Laboratory -->
                            <div class="flex items-center gap-4">
                                <i class="fas fa-flask text-purple-500"></i>
                                <select id="lab" name="lab" required
                                    class="flex-1 p-3 border border-gray-300 rounded-lg bg-white hover:border-purple-400 focus:ring-2 focus:ring-purple-500/50 transition-colors duration-200" 
                                    onchange="updatePcOptions()">
                                    <option value="" disabled selected>Select Laboratory</option>
                                    <?php foreach ([524, 526, 528, 530, 542, 544] as $labNumber): ?>
                                        <option value="<?php echo $labNumber; ?>"><?php echo $labNumber; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- Date -->
                            <div class="flex items-center gap-4">
                                <i class="fas fa-calendar text-purple-500"></i>
                                <input type="date" id="date" name="date" required
                                    min="<?php echo date('Y-m-d'); ?>"
                                    class="flex-1 p-3 border border-gray-300 rounded-lg bg-white hover:border-purple-400 focus:ring-2 focus:ring-purple-500/50 transition-colors duration-200">
                            </div>
                            <!-- Time In -->
                            <div class="flex items-center gap-4">
                                <i class="fas fa-clock text-purple-500"></i>
                                <input type="time" id="time_in" name="time_in" required
                                    class="flex-1 p-3 border border-gray-300 rounded-lg bg-white hover:border-purple-400 focus:ring-2 focus:ring-purple-500/50 transition-colors duration-200">
                            </div>
                            <!-- Remaining Sessions -->
                            <div class="flex items-center gap-4">
                                <i class="fas fa-hourglass-half text-purple-500"></i>
                                <input type="text" id="remaining_session" name="remaining_session" value="30" 
                                    class="flex-1 p-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed focus:ring-2 focus:ring-purple-500/50" readonly placeholder="Remaining Sessions">
                            </div>
                            
                            <!-- Hidden PC input (will be updated by the PC selection panel) -->
                            <input type="hidden" id="available_pc" name="available_pc" value="">
                        </div>
                        <!-- Submit Button -->
                        <div class="flex justify-center pt-4">
                            <button type="submit" 
                                class="px-8 py-3 bg-gradient-to-r from-[rgba(74,105,187,1)] to-[rgba(205,77,204,1)] text-white font-semibold rounded-lg 
                                hover:shadow-lg transform hover:scale-105 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500/50">
                                Confirm Reservation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- PC Selection Panel -->
            <div class="w-full md:w-1/2 flex flex-col"> 
                <div class="flex-1 bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
                    <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(to bottom right, rgb(49, 46, 129), rgb(107, 33, 168), rgb(190, 24, 93))">
                        <i class="fas fa-desktop text-xl mr-3"></i>
                        <h2 class="text-xl font-bold tracking-wider uppercase">Select a PC</h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="mb-4">
                            <select id="lab_selector" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500/50" onchange="syncLabSelectors()">
                                <option value="" disabled selected>Select Laboratory</option>
                                <?php foreach ([524, 526, 528, 530, 542, 544] as $labNumber): ?>
                                    <option value="<?php echo $labNumber; ?>"><?php echo $labNumber; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div id="pc_message" class="text-center py-6 text-gray-500">
                            Please select a laboratory to view available PCs
                        </div>
                        
                        <div id="pc_grid" class="hidden grid grid-cols-5 gap-4 p-4 max-h-96 overflow-y-auto">
                            <!-- PC cards will be dynamically generated -->
                        </div>
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
        // Toggle sidebar navigation
        function toggleNav() {
        const sidenav = document.getElementById("mySidenav");
        if (sidenav.classList.contains("-translate-x-full")) {
            sidenav.classList.remove("-translate-x-full");
            sidenav.classList.add("-translate-x-0");
            sidenav.classList.add("animate-slide-in");
        } else {
            closeNav();
        }
        }

        function closeNav() {
            const sidenav = document.getElementById("mySidenav");
            sidenav.classList.remove("-translate-x-0");
            sidenav.classList.add("-translate-x-full");
            sidenav.classList.remove("animate-slide-in");
        }
        
        // Function to synchronize laboratory selectors
        function syncLabSelectors() {
            const labSelector = document.getElementById('lab_selector');
            const mainLabSelector = document.getElementById('lab');
            
            if (labSelector.value) {
                mainLabSelector.value = labSelector.value;
                generatePcGrid(labSelector.value);
            }
        }
        
        // Function to update PC selector based on selected lab
        function updatePcOptions() {
            const labSelector = document.getElementById('lab');
            const pcLabSelector = document.getElementById('lab_selector');
            
            if (labSelector.value) {
                pcLabSelector.value = labSelector.value;
                generatePcGrid(labSelector.value);
            }
        }
        
        // Function to generate PC grid with cards
        function generatePcGrid(labNumber) {
            const pcGrid = document.getElementById('pc_grid');
            const pcMessage = document.getElementById('pc_message');

            if (labNumber) {
                // Show PC grid and hide message
                pcGrid.classList.remove('hidden');
                pcMessage.classList.add('hidden');

                // Clear existing content
                pcGrid.innerHTML = '';

                // Generate PC cards
                for (let i = 1; i <= 50; i++) {
                    const pcCard = document.createElement('div');
                    pcCard.className = 'rounded-lg border border-gray-200 overflow-hidden shadow-sm transition-all duration-200 hover:shadow-md';
                    pcCard.setAttribute('data-pc', i);
                    pcCard.onclick = function() { selectPC(i); };

                    // Create card content
                    pcCard.innerHTML = `
                        <div class="flex flex-col items-center justify-center p-3">
                            <div class="text-purple-700 mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="text-center text-sm font-medium text-gray-800">PC ${i}</div>
                            <div class="mt-1 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">AVAILABLE</div>
                        </div>
                    `;

                    pcGrid.appendChild(pcCard);
                }
            } else {
                // Hide PC grid and show message
                pcGrid.classList.add('hidden');
                pcMessage.classList.remove('hidden');
            }
        }

        // Function to handle PC selection
        function selectPC(pcNumber) {
            // Reset all PC cards
            const pcCards = document.querySelectorAll('#pc_grid > div');
            pcCards.forEach(card => {
                card.classList.remove('ring-2', 'ring-purple-500', 'bg-purple-50');
            });
            
            // Highlight selected PC
            const selectedCard = document.querySelector(`div[data-pc="${pcNumber}"]`);
            if (selectedCard) {
                selectedCard.classList.add('ring-2', 'ring-purple-500', 'bg-purple-50');
            }
            
            // Update the hidden input value
            document.getElementById('available_pc').value = pcNumber;
        }
    </script>
</body>
</html>